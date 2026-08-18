<?php

trait Affirm {

	function affirm( $check = true ) {
		if ($check) {
			self::checkAction( 'affirm' );
		}
		
		$player_id = self::getCurrentPlayerId();
		
		$state = $this->gamestate->state();
		if ($state['name'] == 'command-exchange') {
			// Perform the swap!
			$action_id = $this->getGameStateValue('action_id');
			
			$player = FfoPlayer::getFullPlayer( $player_id );
			$command = $this->getString( 'currentCommand' );
			
			$components = explode('-', $command);
			$from = $components[1];
			$to = $components[2];
			
			// TODO : Handle exchanges from occupations
			$source = 'action-'.$action_id;
			
			if (! $this->canAfford( $player, $from, 1 )) {
				throw new BgaUserException( self::_("You cannot afford that action") );
			}
			
			$this->spend( $player, $from, 1, $source );
			
			$typeTo = self::getAssetType($to);
			
			if ($typeTo == "good") {
				$this->gain($player, $to, 1, $source);
			} else if ($typeTo == "boat") {
				FfoPlayer::addShip( $player, $to, 0, $source );
			} else if ($typeTo == "resource") {
				$this->gain($player, $to, 1, $source);
			}
			
			FfoPlayer::saveFullPlayer($player);
		} 
		else if ($state['name'] == 'command-inspector2') {
			$action_id = $this->getGameStateValue( 'inspector_action' );
			$round = $this->getGameStateValue('round');
			$player = FfoPlayer::getFullPlayer( $player_id );
				
			if (! $this->canAfford( $player, "coin", $round )) {
				throw new BgaUserException( self::_("You cannot afford that action") );
			}
				
			$this->spend( $player, "coin", $round, 'occupation-60' );
			
			if (Occupation::has($player_id, 151)) {
				$this->gain($player, "coin", 1, "occupation-151");
			}
			
			FfoPlayer::saveFullPlayer($player);
			
			// Return the meeple
			$meeples = FfoPlayer::meepleRefund($player_id, $action_id, 1);
			
			// Notification
			self::notifyAllPlayers( "meepleMove", '', array(
				'meeples' => $meeples,
			));
		} 
		else if ($state['name'] == 'command-hornturner') {
			$player = FfoPlayer::getFullPlayer( $player_id );
			
			if (! $this->canAfford( $player, "sheep", 1 )) {
				throw new BgaUserException( self::_("You cannot afford that action") );
			}
			
			$this->spend( $player, "sheep", 1, 'occupation-63' );
			$this->gain( $player, "saltmeat", 1, 'occupation-63' );
			$this->gain( $player, "hide", 1, 'occupation-63' );
			$this->gain( $player, "wool", 1, 'occupation-63' );
			
			FfoPlayer::saveFullPlayer($player);
		} 
		else if ($state['name'] == 'command-hornturner2') {
			$player = FfoPlayer::getFullPlayer( $player_id );
			
			if (! $this->canAfford( $player, "coin", 3 )) {
				throw new BgaUserException( self::_("You cannot afford that action") );
			}
			
			// Check horn is available
			if (! Forge::isAvailable("horn")) {
				throw new BgaUserException( self::_("That good is not available") );
			}
			$this->saveForge();
			Forge::remove("horn");
			self::notifyAllPlayers( "updateForge", '', array(
				'forge' => Forge::getAvailable(),
			));
			
			$this->spend( $player, "coin", 3, 'occupation-63' );
			$this->gain( $player, "horn", 1, 'occupation-63' );
			
			FfoPlayer::saveFullPlayer($player);
		} 
		else if ($state['name'] == 'command-privatechef') {
			$player = FfoPlayer::getFullPlayer( $player_id );
			
			if (! $this->canAfford( $player, "coin", 4 )) {
				throw new BgaUserException( self::_("You cannot afford that action") );
			}
			
			$this->spend( $player, "coin", 4, 'occupation-67' );
			$this->gain( $player, "silverware", 2, 'occupation-67' );
			$this->gain( $player, "mead", 1, 'occupation-67' );
			$this->gain( $player, "gamemeat", 1, 'occupation-67' );
			
			FfoPlayer::saveFullPlayer($player);
		} 
		else if ($state['name'] == 'command-dragonslayer') {
			$player = FfoPlayer::getFullPlayer( $player_id );
			
			$hand = Weapon::getHand($player_id);
			$to_spend = [];
			$spears = 0;
			$snares = 0;
			foreach ($hand as $w) {
				if ($snares < 2 && $w["type"] == "snare") {
					$to_spend[] = $w;
					$snares++;
				} else if ($spears < 2 && $w["type"] == "spear") {
					$to_spend[] = $w;
					$spears++;
				}
			}
			
			if ($snares < 2 || $spears < 2) {
				throw new BgaUserException( self::_("You cannot afford that action") );
			}
			
			$this->saveWeapons();
			Weapon::discard($player_id, $to_spend, 'occupation-65');
			self::notifyAllPlayers( "discardWeapons", '', array(
				'player_id' => $player_id,
				'weapons' => $to_spend,
				'source' => 'occupation-65'
			));
			
			$this->gain( $player, "treasurechest", 1, 'occupation-65' );
			
			FfoPlayer::saveFullPlayer($player);
		} 
		else if ($state['name'] == 'command-earl') {
			$round = $this->getGameStateValue('round');
			
			if ($this->incGameStateValue( 'num_stonehouses', -1 ) < 0) {
				throw new BgaUserException( self::_("There are no stone houses left") );
			}
			
			$player = FfoPlayer::getFullPlayer( $player_id );
			
			if (! $this->canAfford( $player, "coin", $round )) {
				throw new BgaUserException( self::_("You cannot afford that action") );
			}
				
			$this->spend( $player, "coin", $round, 'occupation-72' );
			
			// Add a board
			$data_boards = self::getDataBoards();
			$new_board = FfoPlayer::addBoard($player, $data_boards["stonehouse"]);
			
			FfoPlayer::saveFullPlayer($player);
		}
		else if ($state['name'] == 'command-cowherd') {
			$player = FfoPlayer::getFullPlayer( $player_id );
			
			if (! $this->canAfford( $player, "grain", 1 ) ||
				! $this->canAfford( $player, "coin", 2 )) {
				throw new BgaUserException( self::_("You cannot afford that action") );
			}
			
			$this->spend( $player, "grain", 1, 'occupation-74' );
			$this->spend( $player, "coin", 2, 'occupation-74' );
			
			$this->gain( $player, "cattle", 1, 'occupation-74' );
			
			FfoPlayer::saveFullPlayer($player);
		}
		else if ($state['name'] == 'command-bonecollector') {
			$player = FfoPlayer::getFullPlayer( $player_id );
			
			if (! $this->canAfford( $player, "coin", 1 )) {
				throw new BgaUserException( self::_("You cannot afford that action") );
			}
			
			$this->spend( $player, "coin", 1, 'occupation-75' );
			
			$this->gain( $player, "skinandbones", 1, 'occupation-75' );
			
			FfoPlayer::saveFullPlayer($player);
		}
		else if ($state['name'] == 'command-breeder') {
			$player = FfoPlayer::getFullPlayer( $player_id );
			
			if (! $this->canAfford( $player, "cattle", 1 )) {
				throw new BgaUserException( self::_("You cannot afford that action") );
			}
			
			$this->spend( $player, "cattle", 1, 'occupation-79' );
			$this->gain( $player, "pregnantcattle", 1, 'occupation-79' );
			
			FfoPlayer::saveFullPlayer($player);
		}
		else if ($state['name'] == 'command-shepherdboy') {
			$player = FfoPlayer::getFullPlayer( $player_id );
			
			if (! $this->canAfford( $player, "coin", 2 )) {
				throw new BgaUserException( self::_("You cannot afford that action") );
			}
			
			$this->spend( $player, "coin", 2, 'occupation-80' );
			$this->gain( $player, "sheep", 1, 'occupation-80' );
			
			FfoPlayer::saveFullPlayer($player);
		} 
		else if ($state['name'] == 'command-quartermaster') {
			$player = FfoPlayer::getFullPlayer( $player_id );
			
			$cost = 8;
			$hand = Weapon::getHand($player_id);
			foreach ($hand as $w) {
				if ($w["type"] == 'sword') {
					$cost -= 1;
				} else if ($w["type"] == 'spear' || $w["type"] == 'bow') {
					$cost -= 0.5;
				}
			}
			if ($cost < 0) {
				$cost = 0;
			}
			if (! $this->canAfford($player, "coin", floor($cost))) {
				throw new BgaUserException( self::_("You cannot afford that action") );
			}
			$this->spend( $player, "coin", floor($cost), 'occupation-81' );
			FfoPlayer::addShip( $player, "longship", 0, 'occupation-81' );
			
			FfoPlayer::saveFullPlayer($player);
		}
		else if ($state['name'] == 'command-longshipbuilder') {
			$player = FfoPlayer::getFullPlayer( $player_id );
				
			$cost = 8;
			$knarrs = 0;
			foreach ($player["ships"] as $s) {
				// Don't include printed ore
				if ($s["type"] == "knarr") {
					$knarrs++;
				}
			}
			if ($knarrs >= 3) {
				$cost -= 7;
			} else if ($knarrs >= 2) {
				$cost -= 5;
			} else if ($knarrs >= 1) {
				$cost -= 2;
			}
			if (! $this->canAfford($player, "coin", floor($cost))) {
				throw new BgaUserException( self::_("You cannot afford that action") );
			}
			$this->spend( $player, "coin", floor($cost), 'occupation-82' );
			FfoPlayer::addShip( $player, "longship", 0, 'occupation-82' );
			
			FfoPlayer::saveFullPlayer($player);
		}
		else if ($state['name'] == 'command-flaxbaker') {
			$player = FfoPlayer::getFullPlayer( $player_id );
			
			if (! $this->canAfford( $player, "flax", 1 )) {
				throw new BgaUserException( self::_("You cannot afford that action") );
			}
			
			$this->spend( $player, "flax", 1, 'occupation-171' );
			$this->gain( $player, "grain", 1, 'occupation-171' );
			$this->gain( $player, "coin", 1, 'occupation-171' );
			
			FfoPlayer::saveFullPlayer($player);
		} 
		else if ($state['name'] == 'command-spicemerchant') {
			$player = FfoPlayer::getFullPlayer( $player_id );
			
			if (! $this->canAfford( $player, "oil", 2 )) {
				throw new BgaUserException( self::_("You cannot afford that action") );
			}
			
			$this->spend( $player, "oil", 2, 'occupation-94' );
			$this->gain( $player, "spices", 1, 'occupation-94' );
			
			FfoPlayer::saveFullPlayer($player);
		} 
		else if ($state['name'] == 'command-ironsmith') {

			$action_id = $this->getGameStateValue('action_id');

			if ($action_id > 1000) {
				throw new BgaUserException( self::_("Ironsmith cannot be used with the fifth action column (final action)."));
			}

			$player = FfoPlayer::getFullPlayer( $player_id );

			if (! $this->canAfford( $player, "coin", 1 )) {
				throw new BgaUserException( self::_("You cannot afford that action") );
			}
			
			$this->spend( $player, "coin", 1, 'occupation-108' );
			
			if (Occupation::has($player_id, 151)) {
				$this->gain($player, "coin", 1, "occupation-151");
			}
			
			// Refund 1 meeple!
			$placed_action_id = $this->getGameStateValue('placed_action_id');
			$meeples = FfoPlayer::meepleRefund($player_id, $placed_action_id, 1);
				self::notifyAllPlayers( "meepleMove", '', array(
					'meeples' => $meeples,
				));
			
			FfoPlayer::saveFullPlayer($player);
		}
		else if ($state['name'] == 'command-silkstitcher') {
			$player = FfoPlayer::getFullPlayer( $player_id );
			
			if (! $this->canAfford( $player, "coin", 3 )) {
				throw new BgaUserException( self::_("You cannot afford that action") );
			}
			
			$this->spend( $player, "coin", 3, 'occupation-114' );
			$this->gain( $player, "silk", 1, 'occupation-114' );
			
			FfoPlayer::saveFullPlayer($player);
		} 
		else if ($state['name'] == 'command-laborer') {
			$player = FfoPlayer::getFullPlayer( $player_id );
			
			if (! $this->canAfford( $player, "coin", 2 )) {
				throw new BgaUserException( self::_("You cannot afford that action") );
			}
			
			$this->spend( $player, "coin", 2, 'occupation-124' );
			$this->gain( $player, "grain", 1, 'occupation-124' );
			$this->gain( $player, "wood", 2, 'occupation-124' );
			
			FfoPlayer::saveFullPlayer($player);
		} 
		else if ($state['name'] == 'command-snarespecialist') {
			$action_id = $this->getGameStateValue('action_id');
			$placed_action_id = $this->getGameStateValue('placed_action_id');
			
			$hand = Weapon::getHand($player_id);
			$to_spend = [];
			foreach ($hand as $w) {
				if ($w["type"] == "snare") {
					$to_spend[] = $w;
					if (count($to_spend) == 2) {
						break;
					}
				}
			}
			
			if (count($to_spend) < 2) {
				throw new BgaUserException( self::_("You cannot afford that action") );
			}
			
			$this->saveWeapons();
			Weapon::discard($player_id, $to_spend, 'occupation-130');
			self::notifyAllPlayers( "discardWeapons", '', array(
				'player_id' => $player_id,
				'weapons' => $to_spend,
				'source' => 'occupation-130'
			));
			
			// Return the meeple
			$meeples = FfoPlayer::meepleRefund($player_id, $placed_action_id, 1);
			
			if (Occupation::has($player_id, 151)) {
				$player = FfoPlayer::getFullPlayer( $player_id );
				$this->gain($player, "coin", 1, "occupation-151");
				FfoPlayer::saveFullPlayer($player);
			}
			
			// Notification
			self::notifyAllPlayers( "meepleMove", '', array(
				'meeples' => $meeples,
			));
		} 
		else if ($state['name'] == 'command-meatcurer') {
			$action_id = $this->getGameStateValue('action_id');
			
			$hand = Weapon::getHand($player_id);
			$to_spend = [];
			foreach ($hand as $w) {
				if ($w["type"] == "snare") {
					$to_spend[] = $w;
					break;
				}
			}
			
			if (count($to_spend) < 1) {
				throw new BgaUserException( self::_("You cannot afford that action") );
			}
			
			$this->saveWeapons();
			Weapon::discard($player_id, $to_spend, 'occupation-129');
			self::notifyAllPlayers( "discardWeapons", '', array(
				'player_id' => $player_id,
				'weapons' => $to_spend,
				'source' => 'occupation-129'
			));
			
			$player = FfoPlayer::getFullPlayer( $player_id );
				
			$this->gain( $player, "saltmeat", 1, 'occupation-129' );
			
			FfoPlayer::saveFullPlayer($player);
		} 
		else if ($state['name'] == 'command-deerstalker') {
			$player = FfoPlayer::getFullPlayer( $player_id );
			
			if (! $this->canAfford( $player, "coin", 2 )) {
				throw new BgaUserException( self::_("You cannot afford that action") );
			}
			
			$this->spend( $player, "coin", 2, 'occupation-144' );
			$this->gain( $player, "skinandbones", 1, 'occupation-144' );
			
			FfoPlayer::saveFullPlayer($player);
		}
		else if ($state['name'] == 'command-spicetrader') {
			$player = FfoPlayer::getFullPlayer( $player_id );
				
			if (! $this->canAfford( $player, "coin", 3 )) {
				throw new BgaUserException( self::_("You cannot afford that action") );
			}
			
			$this->spend( $player, "coin", 3, 'occupation-148' );
			$this->gain( $player, "spices", 1, 'occupation-148' );
			
			FfoPlayer::saveFullPlayer($player);
		}
		else if ($state['name'] == 'confirmTurn') {
			if (self::getGameStateValue( 'pending_draw' )) {
				// Copied code from helper.state-functions.php
				$occupation = Occupation::draw( $player_id );
					
				self::notifyPlayer( $player_id, "drawOccupation", '', array(
					'player_id' => $player_id,
					'occupation' => $occupation,
				));
				unset($occupation["ability"]);
				
				$players = $this->loadPlayersBasicInfos();
				$cards_log = [];
				$cards_args = [
					'i18n' => []
				];
				$starter = $occupation["starter"] ? "true" : "false";
				$key = "inject_occupation_x_$starter";
				$cards_log[] = '${'.$key.'}';
				$cards_args[$key] = clienttranslate('occupation');
				$cards_args["i18n"][] = $key;
				
				self::notifyAllPlayers( "drawOccupation", clienttranslate('${player_name} draws: ${cards_list}'), array(
					'player_id' => $player_id,
					'player_name' => $players[$player_id]["player_name"],
					'ignore_player_id' => $player_id,
					'occupation' => $occupation,
					'cards_list' => [
						'log' => implode(' ', $cards_log),
						'args' => $cards_args
					],
				));
			}
		}
		else if ($state['name'] == 'command-hornblower') {
			$player = FfoPlayer::getFullPlayer($player_id);
				
			if (! self::canAfford( $player, "coin", 1 )) {
				throw new BgaUserException( self::_("You cannot afford that action") );
			}
			$this->spend( $player, "coin", 1, 'occupation-85' );
			Action::unshift( ["hunt-game"] );

			if (Occupation::has($player_id, 145)) {
				Action::unshift( ["mountain-1"] );
				Action::unshift( ["mountainreset"] );
			}
			
			FfoPlayer::saveFullPlayer($player);
		}
		else if ($state['name'] == 'command-hornblower2') {
			$player = FfoPlayer::getFullPlayer($player_id);
			
			if (! self::canAfford( $player, "coin", 2 )) {
				throw new BgaUserException( self::_("You cannot afford that action") );
			}
			$this->spend( $player, "coin", 2, 'occupation-85' );
			Action::unshift( ["hunt-snare-0"] );
				
			FfoPlayer::saveFullPlayer($player);
		}
		else if ($state['name'] == 'command-soberman') {
			$this->setGameStateValue('occ_soberman', 1);
			// Gain 1 coin
			$player = FfoPlayer::getFullPlayer($player_id);
			$this->gain( $player, "coin", 1, 'occupation-160' );
			FfoPlayer::saveFullPlayer($player);
		}
		else if ($state['name'] == 'command-prehunt' || $state['name'] == 'command-preattack') {
			$action_id = $this->getGameStateValue('action_id');
			if (in_array($action_id, [57, 58, 59])) {
				// Raiding/pillaging/plundering
				if (Occupation::has($player_id, 12)) {
					// Immediately before using longship, draw a weapon
					$this->setCanUndo( 0 );
					Weapon::draw( $player_id, null, "occupation-12", 1 );
				}
			}
		}
		else if ($state['name'] == 'command-resolveModifierImmediatelly') {
			$action_id = $this->getGameStateValue('action_id');
			if ($action_id == 5554) {
				$player = FfoPlayer::getFullPlayer($player_id);
				if (!self::canAfford($player, "ore", 1)) {
					throw new BgaUserException( self::_("You need to remove 1 ore from any of your ships") );
				}
			}
		}
		else if ($state['name'] == 'drawPersonalResource') {
			
			self::notifyAllPlayers( "drawOccupation", clienttranslate('${player_name} draws: ${cards_list}'), array(
				'player_id' => $player_id,
				'player_name' => $players[$player_id]["player_name"],
				'ignore_player_id' => $player_id,
				'occupation' => $occupation,
				'cards_list' => [
					'log' => implode(' ', $cards_log),
					'args' => $cards_args
				],
			));


		}
		else {
			throw new BgaVisibleSystemException( "Unexpected affirm" );
		}
		
		$this->gamestate->nextState( 'affirm' );
	}

}