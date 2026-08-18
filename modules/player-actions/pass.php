<?php 

trait Pass {
    function pass( $check = true ) {
		if ($check) {
			self::checkAction( 'pass' );
		}

		if ($this->getGameStateValue( 'no_pass' )) {
			throw new BgaVisibleSystemException( "You cannot pass" );
		}
		
		$player_id = self::getActivePlayerId();
		$state = $this->gamestate->state();

		if ($state['name'] == 'activatePreGameOccupation') {
			$this->setString('preGameOccupation'.$player_id, 1);
		}


		if ($state['name'] == 'pickAction') {
			// Set them to 'passed'
			self::DbQuery("UPDATE player SET passed = 1 WHERE player_id = $player_id");
			
			// Notify
			self::notifyAllPlayers( "playerPass", '', array(
				'player_id' => $player_id,
			));
		} 
		else if ($state['name'] == 'command-pickmodal') {
			$player_id = self::getActivePlayerId();
			
			if (! $this->getGameStateValue( 'can_pass_override' )) {
				throw new BgaVisibleSystemException( "You cannot pass" );
			}
		} 
		else if ($state['name'] == 'command-personalfeast') {
			// Do a personal feast for this player!
			$player_id = self::getCurrentPlayerId();
			
			$this->stFeast( $player_id );
		}
		else if ($state['name'] == 'command-peacemaker') {
			// Draw a weapon
			$player_id = self::getCurrentPlayerId();
			Weapon::draw($player_id);
		}
		else if ($state['name'] == 'gameEnd-placegoods') {
			$player_id = self::getCurrentPlayerId();
			if (Occupation::has($player_id, 189)) {
				// Seafarer - 1 coin per exploration board
				$player = FfoPlayer::getFullPlayer($player_id);
				$num_islands = 0;
				foreach ($player["boards"] as $board) {
					if ($board["category"] == "island") {
						$num_islands++;
					}
				}
				if ($num_islands >= 2) {
					$num_coins = 0;
					if ($num_islands >= 4) {
						$num_coins = 16;
					} else if ($num_islands >= 3) {
						$num_coins = 9;
					} else if ($num_islands >= 2) {
						$num_coins = 4;
					}
					$this->gain($player, "coin", $num_coins, 'occupation-189');
					$this->updatePlayerScore( $player );
				}
			}
		} 
		else if ($state['name'] == 'command-oreboatman') {
			$ore_to_return = self::getGameStateValue( 'ore_boatman' );
			if ($ore_to_return > 0) {
				$player_id = self::getCurrentPlayerId();
				$player = FfoPlayer::getFullPlayer($player_id);
				$this->spend($player, "ore", $ore_to_return, 'occupation-9');
				FfoPlayer::saveFullPlayer($player);
			}
		} 
		else if ($state['name'] == 'command-fishcook') {
			$stockfish_to_return = self::getGameStateValue( 'occ_fishcook' );
			if ($stockfish_to_return > 0) {
				$player_id = self::getCurrentPlayerId();
				$player = FfoPlayer::getFullPlayer($player_id);
				$this->spend($player, "stockfish", $stockfish_to_return, 'occupation-172');
				FfoPlayer::saveFullPlayer($player);
			}
		}
		else if ($state['name'] == 'command-soberman') {
			$this->setGameStateValue('occ_soberman', 0);
		}
		else if ($state['name'] == 'command-buywhalingboatfifthcol') {
			$player_id = self::getCurrentPlayerId();
			$allAvailableIslands = array_column(Island::getAvailable(), "name");
			$eligibleIslands = FfoPlayer::getEligibleIslands($player_id, 1);
			$availableIslands = array_intersect($eligibleIslands, $allAvailableIslands);
			if (count($availableIslands) == 0) {
				throw new BgaUserException( "You cannot pass because you will not have a ship to explore an island" );
			}
		}
		// If it's a multistate...
		if ($state["type"] == "multipleactiveplayer") {
			$player_id = self::getCurrentPlayerId();
			$this->gamestate->setPlayerNonMultiactive( $player_id, 'pass' );
		} else {
			$this->gamestate->nextState( 'pass' );
		}
	}
}