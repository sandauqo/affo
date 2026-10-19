<?php

trait PlayOccupation {
    function playOccupation( $occupation_id, $playOrToken = null, $tutor ) {
		$player_id = self::getActivePlayerId();
		if (!Occupation::has($player_id, 176) && !$tutor) {
			// Tutor can do this anytime
			self::checkAction( 'playOccupation' );
		}
		
		if (FeastForOdin::$game->isNorwegiansExpansion()) {
			if (!isset($playOrToken)) {
				throw new BgaUserException( "You must choose if you want to play the occupation or get a vp token instead." );
			}
		}
		
		$state = $this->gamestate->state();
		if ($state['name'] == 'command-playOccupation' || $state['name'] == 'activatePreGameOccupation' || $tutor) {
			$this->setGameStateValue( 'resolved_effect', 1 );

			if ($tutor) {
				$player = FfoPlayer::getFullPlayer( $player_id );
				if (!$this->canAfford($player, 'coin', 1)) {
					throw new BgaUserException( clienttranslate("You cannot afford that action") );
				}

				// Must be your turn
				$actives = $this->gamestate->getActivePlayerList();
				$currentPlayer = self::getCurrentPlayerId();
				if (! in_array($currentPlayer, $actives)) {
					throw new BgaUserException( clienttranslate("It is not your turn") );
				}
			}

			// 1. Check the occ is in player's hand
			$hand = Occupation::getHand($player_id);
			$found = null;
			foreach ($hand as $h) {
				if ($h["id"] == $occupation_id) {
					$found = $h;
					break;
				}
			}
			if (! isset($found)) {
				throw new BgaVisibleSystemException( "That occupation ($occupation_id) is not in your hand" );
			}

			// 2. Play it or take a vp token
			$this->saveOccupations( $player_id );

			if (isset($playOrToken) && $playOrToken == 1) {
				Occupation::discard($occupation_id);
				$location = "discard";
				$notifyText = clienttranslate('${player_name} discards an occupation and takes ${vp} victory points');
				if ($this->getGameStateValue( 'num_vp_token_4' ) > 0) {
					$largestVpToken = 4;
					$this->setGameStateValue( 'num_vp_token_4', $this->getGameStateValue( 'num_vp_token_4' ) - 1);
				} else if ($this->getGameStateValue( 'num_vp_token_3' ) > 0) {
					$largestVpToken = 3;
					$this->setGameStateValue( 'num_vp_token_3', $this->getGameStateValue( 'num_vp_token_3' ) - 1);
				} else {
					$largestVpToken = 2;
				}
				$player = FfoPlayer::getFullPlayer( $player_id );
				$player['vp'] += $largestVpToken;
				FfoPlayer::saveFullPlayer( $player );

			} else {
				Occupation::play($occupation_id);
				$location = "played";
				$notifyText = clienttranslate('${player_name} plays: ${cards_list}');
			}

			$this->incStat( 1, 'occupations_played', $player_id );
			
			// 3. Notify
			$found["location"] = $location;
			$players = FeastForOdin::$game->loadPlayersBasicInfos();
			$cards_log = [];
			$cards_args = [
				'i18n' => []
			];
			$starter = $found["starter"] ? "true" : "false";
			$key = "inject_occupation_$found[ability]_$starter";
			$cards_log[] = '${'.$key.'}';
			$cards_args[$key] = FeastForOdin::$game->m_occupations[$found["ability"]]["name"];
			$cards_args["i18n"][] = $key;

			$notifyData = array(
				'player_id' => $player_id,
				'player_name' => $players[$player_id]["player_name"],
				'occupation' => $found,
				'cards_list' => isset($largestVpToken) ? null : [
					'log' => implode(' ', $cards_log),
					'args' => $cards_args
				],
				'vp' => isset($largestVpToken) ? $largestVpToken : null,
				'num_vp_token_4' => $this->getGameStateValue( 'num_vp_token_4' ),
				'num_vp_token_3' => $this->getGameStateValue( 'num_vp_token_3' ),
			);

			self::notifyAllPlayers( "playOccupation", clienttranslate($notifyText), $notifyData);

			if ($found["ability"] != 176 && Occupation::has($player_id, 176)) {
				// Etiquette Teacher
				if (!$this->getGameStateValue( 'etiquetteTeacherGivenPeasThisAction')) {
					$player = FfoPlayer::getFullPlayer( $player_id );
					$this->gain($player, "peas", 1, "occupation-176");
					FfoPlayer::saveFullPlayer($player);
					$this->setGameStateValue( 'etiquetteTeacherGivenPeasThisAction', 1);
				}
			}

			if ($tutor) {
				$this->spend( $player, "coin", 1, 'occupation-52' );
				FfoPlayer::saveFullPlayer($player);
			}

			// 4. If it's an immediate occ, do the effect now...
			// Simply push onto the FRONT of the command queue (we do it now, not at the end of the turn)
			if (!isset($largestVpToken)) {
				$commands = Occupation::getImmediateCommands( $found["ability"] );
				if (count($commands) > 0) {
					foreach ($commands as &$c) {
						$c .= ":occupation-$found[ability]";
					}
					$commands[] = "flush";
					Action::unshift( $commands );
				}
			}

			if ($found["ability"] == 168) {
				// Earl of Lade
				// triggers if, when played, player is left with 3 vikings
				// www.boardgamegeek.com/thread/2726319/official-ruling-needed-on-earl-of-lade-168-c
				$thing_meeples = FfoPlayer::getMeeples($player_id, 'thing');
				if (count($thing_meeples) == 3) {
					Action::push(["take-coin:occupation-168"]);
				}
			}

			// 5. Check if occ needs to be used immediately to satisfy actions reqs, like 39 Modifier
			$action_id = $this->getGameStateValue('action_id');
			if ($action_id) {
				// $player = FfoPlayer::getFullPlayer( $player_id );
				// Requirements::check($action_id, $player);
				// if ($this->getGameStateValue('takeImmediateActionToContinue')) {
				// 	$this->setGameStateValue('takeImmediateActionToContinue', 0);
				// 	Action::unshift( ['state-takeImmediateAction'] );
				// }
			}	

		} else {
			throw new BgaVisibleSystemException( "Unexpected state to play occupation" );
		}
		
		$this->gamestate->nextState( 'done' );
	}
}