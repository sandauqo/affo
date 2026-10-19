<?php 

trait BuildAction {
    function buildAction($state) {
		$action_id = $this->getGameStateValue('action_id');
		$player_id = self::getCurrentPlayerId();
		$norwegiansLastColVikingNumber = $this->getGameStateValue('5th_col_how_many_vikings');

		$actions = Action::getModifiedAction($action_id, $player_id, $norwegiansLastColVikingNumber);

		if (! isset($actions) || count($actions) < 1) {
			throw new BgaVisibleSystemException( "Action does not exist" );
		}

		$push_or_unshift = 'push';
		
		// TODO : If you undo to the other side of this, the stats should reverse. Save in a string_global temporarily?
		if ($state['name'] == 'pickAction' || $state['name'] == 'chooseVikingsNumber') {
			$this->incStat( 1, 'actions_taken' );
			$this->incStat( 1, 'actions_taken', $player_id );
			$this->setGameStateValue( 'placed_player_id', $player_id );
			
			if (in_array($action_id, [57, 58, 59])) {
				$this->incStat( 1, 'raid_pillage_actions_taken', $player_id );
			} else if (in_array($action_id, [9, 10])) {
				$this->incStat( 1, 'hunt_actions_taken', $player_id );
			} else if (in_array($action_id, [11, 12])) {
				$this->incStat( 1, 'whaling_actions_taken', $player_id );
			} else if (in_array($action_id, [14])) {
				$this->incStat( 1, 'snare_actions_taken', $player_id );
			}
			
			// That action must be empty
			if (! FfoPlayer::isActionAvailable($action_id)) {
				throw new BgaUserException( clienttranslate("That action is not available") );
			}
			
			$number_meeples = $actions[0]->numberMeeples;
			
			if ($action_id == 60 && Occupation::has($player_id, 106)) {
				$longships = 0;
				$player = FfoPlayer::getFullPlayer( $player_id );
				foreach ($player["ships"] as $s) {
					if ($s["type"] == "longship") {
						$longships++;
					}
				}
				if ($longships >= 4) {
					$number_meeples -= 2;
				} else if ($longships >= 3) {
					$number_meeples -= 1;
				}
			}
			
			// The player must have enough available meeples
			$meeples = FfoPlayer::getMeeples($player_id, 'thing');
			if (count($meeples) < $number_meeples) {
				throw new BgaUserException( clienttranslate("You do not have enough vikings to perform that action") );
			}

			if (count($meeples) - $number_meeples == 3) {
				// Earl of Lade
				if (Occupation::has($player_id, 168)) {
					// Gain 1 silver
					Action::push(["take-coin:occupation-168"]);
				}
			}
			
			// Move the meeples
			$meeples = FfoPlayer::moveMeepleToAction($player_id, $action_id, $number_meeples);
				
			if ($number_meeples == 2) {
				$this->saveStringGlobals();
				$o = $this->getString( 'twoVikingsPlaced' . $player_id );
				if (! isset($o)) {
					$o = 0;
				}
				$n = 1 + $o;
				$this->setString( 'twoVikingsPlaced' . $player_id, $n );
				
				if ($n > 1) {
					if (Occupation::has($player_id, 166)) {
						// Draw 1 weapon card
						Action::push(["draw-weapon:occupation-166"]);
					}
				}
			}
			
			// Notification
			self::notifyAllPlayers( "meepleMove", '', array(
				'meeples' => $meeples,
			));
		} 
		else if ($state['name'] == 'command-clone') {
			// That action must be occupied by someone else
			if (! FfoPlayer::isOccupiedByAnotherPlayer($action_id, $player_id)) {
				throw new BgaUserException( clienttranslate("That action is not occupied by another player") );
			}
			
			$command = $this->getString( 'currentCommand' );
			$components = explode('-', $command);
			
			if (Action::getCol($action_id) != $components[1]) {
				throw new BgaUserException( clienttranslate("That action is not in the corresponding column") );
			}
		} 
		else if ($state['name'] == 'command-follower') {
			// That action must be occupied by someone else
			if (count(FfoPlayer::getMeeplesOnAction($action_id)) == 0) {
				throw new BgaUserException( clienttranslate("That action is not occupied") );
			}
			
			// Must be in the second column
			if (Action::getCol($action_id) != 2) {
				throw new BgaUserException( clienttranslate("You must select an action in the 2nd column") );
			}
			
			$push_or_unshift = 'unshift';
			
			// If it's raid/pillage - don't return any Vikings!
			$this->setGameStateValue( 'no_hunt_refunds', 1 );
		}
		else if ($state['name'] == 'command-latecomer') {
			// has to be unoccupied
			if (count(FfoPlayer::getMeeplesOnAction($action_id)) != 0) {
				throw new BgaUserException( clienttranslate("That action is not unoccupied") );
			}
			
			// has to be in 1st col
			if (Action::getCol($action_id) != 1) {
				throw new BgaUserException( clienttranslate("You must select an action in the 1st column") );
			}
			
			// Adjacent to one of yours...
			$condition = FfoPlayer::playerMeeplesCondition( $player_id );
			$beforeAfter = $this->getLatecomerBeforeAfter($action_id);
			$meeples = FeastForOdin::getObjectList( "SELECT * FROM meeple WHERE action_id IN (" . implode(',', $beforeAfter) . ") AND location = 'action' AND $condition" );
			if (count($meeples) == 0) {
				throw new BgaUserException( clienttranslate("You do not have meeples in a space above or below") );
			}
			
			// Must pay 1 coin
			$player = FfoPlayer::getFullPlayer( $player_id );
			$this->spend($player, "coin", 1, "occupation-91");
			FfoPlayer::saveFullPlayer($player);
				
			$push_or_unshift = 'unshift';
			
			// If it's raid/pillage - don't return any Vikings!
			$this->setGameStateValue( 'no_hunt_refunds', 1 );
			// $this->setCanUndo( 0 );
		}
		else if ($state['name'] == 'command-inspector') {
			
			$action_id = $this->getGameStateValue( 'inspector_action' );

			if ($action_id > 1000) {
				throw new BgaUserException( clienttranslate("Inspector cannot be used with the fifth action column (final action)."));
			}

			// That action must contain one of your meeples
			$pre_meeples = FfoPlayer::getMeeplesOnAction($action_id);
			if (count($pre_meeples) == 0 || $pre_meeples[0]["player_id"] != $player_id) {
				throw new BgaUserException( clienttranslate("That action does not contain any of your vikings") );
			}

			if ($this->isSoloGame()) {
				// If it's a solo game, allow only active colour meeples
				$round = FeastForOdin::$game->getGameStateValue('round');
				$active_colour = ($round % 2 == 0) ? 1 : 0;
				if ((int)$pre_meeples[0]['colour'] !== $active_colour) {
					throw new BgaUserException( clienttranslate("You can only return meeples that you placed in this round."));
				}
			}

			$player = FfoPlayer::getFullPlayer( $player_id );
			
			// Return the meeple
			$meeples = FfoPlayer::meepleRefund($player_id, $action_id, 1);
			
			// Notification
			self::notifyAllPlayers( "meepleMove", '', array(
				'meeples' => $meeples,
			));
			
			if (Occupation::has($player_id, 151)) {
				$this->gain($player, "coin", 1, "occupation-151");
				FfoPlayer::saveFullPlayer($player);
			}
			
			$round = self::getGameStateValue( 'round' );
			
			if (count($pre_meeples) > 1 && $this->canAfford( $player, "coin", $round )) {
				// If there is another, ask them:
				$this->gamestate->nextState( 'another' );
			} else {
				// Otherwise, done:
				$this->gamestate->nextState( 'pass' );
			}
			return;
		} 
		else {
			throw new BgaVisibleSystemException( "Unexpected state: " . $state['name'] );
		}
		
		// Save the actionId in a global
		if ($state['name'] == 'pickAction' || $state['name'] == 'chooseVikingsNumber') {
			$this->setGameStateValue('placed_action_id', $action_id);
		}
		$this->setGameStateValue('action_id', $action_id);
		// Push all of the things to a list
		if ($state['name'] == 'pickAction' || $state['name'] == 'chooseVikingsNumber') {
			$commands = $actions[0]->preCommands;
			$commands[] = 'pickmodal';
		} else if ($state['name'] == 'command-clone') {
			// If a clone action, you've already done the preCommands
			// If it's a clone action, action applied is the chosen action
			$commands[] = 'setaction-' . $action_id;
			$commands = $actions[0]->preCommands;
			$commands[] = 'pickmodal';
		} else {
			$commands = ["linkedactions"];
			// If it's not a main pickAction, we need to set the action_id back afterwards
			$commands[] = 'pickmodal';
			$commands[] = 'setaction-' . $this->getGameStateValue('placed_action_id');
		}
		
		if ($push_or_unshift == 'push') {
			Action::push($commands);
		} else {
			Action::unshift($commands);
		}

		$this->gamestate->nextState( 'action' );
	}

    private function getLatecomerBeforeAfter($action_id) {
		// returns 2 action_ids, one above and one below selected action_id 
		if (FeastForOdin::$game->isNorwegiansExpansion()) {
			$firstFew = [77, 5, 9, 81, 93];
			$firstMany = [77, 5, 9, 81, 85, 89];
			$secondFew = [97, 33, 37, 41, 45];
			$secondMany = [97, 33, 37, 41, 45, 49];
			$thirdFew = [121, 57, 61, 69];
			$thirdMany = [121, 57, 61, 125, 69];

			$numPlayers = count(self::loadPlayersBasicInfos());

			$list = $numPlayers < 4 ? $firstFew : $firstMany;
			$list = $numPlayers < 3 ? array_merge($list, $secondFew) : array_merge($list, $secondMany);
			$list = $numPlayers < 3 ? array_merge($list, $thirdFew) : array_merge($list, $thirdMany);

			$key = array_search($action_id, $list);
			if ($key === false) {
				throw new BgaVisibleSystemException("Action not found");
			}

			$before = $key > 0 ? $list[$key - 1] : 0;
			$after = $key < (count($list) - 1) ? $list[$key + 1] : 0;

			return array($before, $after);
		} else {
			return array($action_id - 4, $action_id + 4);
		}
	}

}