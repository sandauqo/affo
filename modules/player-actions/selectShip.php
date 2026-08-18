<?php

trait SelectShip {
    	
	function selectShip( $ship_id ) {
		self::checkAction( 'selectShip' );
		
		$player_id = self::getActivePlayerId();
		$player = FfoPlayer::getFullPlayer($player_id);
		$action_id = $this->getGameStateValue('action_id');
		$round = $this->getGameStateValue('round');
		$state = $this->gamestate->state();
		
		if ($state['name'] == 'command-emigrate' || $state['name'] == 'command-emigratepassable' || $state['name'] == 'command-harborguard') {
			$bigs = 0;
			if ($state['name'] == 'command-harborguard') {
				foreach ($player["ships"] as $s) {
					// Don't include printed ore
					if ($s["type"] == "knarr" || $s["type"] == "longship") {
						$bigs++;
					}
				}
			}
			
			// Spend the boat
			$spent = $this->spendBoat( $player, $ship_id, "action-$action_id" );
			if (! isset($spent)) {
				throw new BgaVisibleSystemException( "You don't have that ship" );
			}
			
			if ($spent["type"] == 'whalingboat') {
				throw new BgaVisibleSystemException( "You cannot emigrate with that ship" );
			}
			
			if ($spent["type"] == "longship" && Occupation::has($player_id, 12)) {
				// Draw a weapon if you emigrate a longship
				Action::unshift(["draw-weapon:occupation-12"]);
			}
			
			// Add an emigration
			$player["emigrations"][] = $spent["type"];
			self::notifyAllPlayers( "emigrate", '', array(
				'player_id' => $player_id,
				'emigration' => $spent["type"],
			));
			
			$cost = $round;
			
			// Patron discount
			if (Occupation::has($player_id, 2)) {
				if ($cost >= 2) {
					$cost--;
				}
			}
			
			if (Occupation::has($player_id, 170)) {
				$cost -= 2;
				if ($cost < 0) {
					$cost = 0;
				}
			}
			
			$cost -= $bigs;
			
			if ($cost < 0) {
				$cost = 0;
			}
			
			// Spend the coin
			$this->spend( $player, "coin", $cost, "action-$action_id" );
			
			if (Occupation::has($player_id, 125)) {
				Action::unshift(["take-rune:occupation-125"]);
			}
		}
		else if ($state['name'] == 'command-swapwhalingboatknarr') {
			$num_small = 0;
			$num_big = 0;
			foreach ($player["ships"] as $s) {
				if ($s["type"] == "whalingboat") {
					$num_small++;
				} else {
					$num_big++;
				}
			}
			
			if ($num_big >= 4) {
				// No room!
				throw new BgaUserException( self::_("You do not have enough room to buy that boat") );
			}
			
			$spent = $this->spendBoat( $player, $ship_id, "action-$action_id" );
			if (! isset($spent)) {
				throw new BgaVisibleSystemException( "You don't have that ship" );
			}
			
			if ($spent["type"] != 'whalingboat') {
				throw new BgaVisibleSystemException( "You cannot swap that for a knarr" );
			}
			
			FfoPlayer::addShip( $player, "knarr" );
		} 
		else if ($state['name'] == 'command-buylongshipfifthcol') {
			$num_small = 0;
			$num_big = 0;
			foreach ($player["ships"] as $s) {
				if ($s["type"] == "whalingboat") {
					$num_small++;
				} else {
					$num_big++;
				}
			}
			
			if ($num_big >= 4) {
				// No room!
				throw new BgaUserException( self::_("You do not have enough room to buy that boat") );
			}
			
			$spent = $this->spendBoat( $player, $ship_id, "action-$action_id" );
			if (! isset($spent)) {
				throw new BgaVisibleSystemException( "You don't have that ship" );
			}
			
			if ($spent["type"] != 'whalingboat') {
				throw new BgaVisibleSystemException( "You cannot swap that for a knarr" );
			}
			
			// TODO: should spend 1 coin here, or as a action cost param?

			FfoPlayer::addShip( $player, "longship" );
		}
		else if ($state['name'] == 'command-wharfowner') {
			$spent = $this->spendBoat( $player, $ship_id, "occupation-61" );
			if (! isset($spent)) {
				throw new BgaVisibleSystemException( "You don't have that ship" );
			}
			
			if ($spent["type"] != 'longship') {
				throw new BgaVisibleSystemException( "You cannot swap that for 8 silver" );
			}
			
			$this->gain($player, "coin", 8, "occupation-61");
		} 
		else if ($state['name'] == 'command-shipbuilder') {
			$this->spend( $player, "coin", 2, "occupation-64" );
			$spent = $this->spendBoat( $player, $ship_id, "occupation-64" );
			if (! isset($spent)) {
				throw new BgaVisibleSystemException( "You don't have that ship" );
			}
			
			if ($spent["type"] != 'whalingboat') {
				throw new BgaVisibleSystemException( "You cannot swap that for a longship" );
			}
			
			FfoPlayer::addShip( $player, "longship", 0, "occupation-64" );
		} 
		else if ($state['name'] == 'command-swordfighter') {
			$found = null;
			foreach ($player["ships"] as $k => &$g) {
				if ($g["id"] == $ship_id) {
					$found = &$g;
					break;
				}
			}
			if (! isset($found)) {
				throw new BgaVisibleSystemException( "You don't have that ship" );
			}
			
			// The boat must have ore + be a longship
			if ($found["type"] != 'longship' || $found["ore"] <= 0) {
				throw new BgaVisibleSystemException( "You must select a longship with ore on it" );
			}
			
			// Remove the ore
			$found["ore"]--;
			
			// Set the global
			$this->setGameStateValue( 'occ_swordfighter', 1 );
			
			self::notifyAllPlayers( "equipShip", '', array(
				'player_id' => $player_id,
				'ship' => $found,
			));
		}
		else if ($state['name'] == 'command-berserker') {
			$found = null;
			foreach ($player["ships"] as $k => &$g) {
				if ($g["id"] == $ship_id) {
					$found = &$g;
					break;
				}
			}
			if (! isset($found)) {
				throw new BgaVisibleSystemException( "You don't have that ship" );
			}
			
			// The boat must have ore + be a longship
			if ($found["type"] != 'longship' || $found["ore"] <= 0) {
				throw new BgaVisibleSystemException( "You must select a longship with ore on it" );
			}
			
			// Remove the ore
			$found["ore"]--;
			
			// Set the global
			$this->setGameStateValue( 'occ_berserker', $ship_id );
			
			self::notifyAllPlayers( "equipShip", '', array(
				'player_id' => $player_id,
				'ship' => $found,
			));
		} 
		else if ( $state['name'] == 'command-emigrateminor' || $state['name'] == 'command-emigrateminorpassable') {

			// Spend the boat
			$spent = $this->spendBoat( $player, $ship_id, "action-$action_id" );

			if (! isset($spent)) {
				throw new BgaVisibleSystemException( "You don't have that ship" );
			}
			
			if ($spent["type"] != 'whalingboat') {
				throw new BgaVisibleSystemException( "You can only emigrate with a whaling boat" );
			}

			if ($action_id == 124) {
				if (! $this->canAfford($player, "coin", 2)) {
					throw new BgaUserException( self::_("You cannot afford that action") );
				}
				$this->spend($player, "coin", 2, "action-$action_id");
			}

			if ($action_id == 128) {
				$this->setGameStateValue( 'double_action_first_taken', 1);
			}

			// Add an emigration
			$player["emigrations"][] = $spent["type"];
			self::notifyAllPlayers( "emigrate", '', array(
				'player_id' => $player_id,
				'emigration' => $spent["type"],
			));
		}
		FfoPlayer::saveFullPlayer($player);
		
		$this->gamestate->nextState( 'done' );
	}
}