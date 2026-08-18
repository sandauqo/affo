<?php 

trait SelectSpecialTile {

    function selectSpecialTile( $special_tile, $num_resources, $num_cards_per_type ) {
		self::checkAction( 'selectSpecialTile' );
		
		$player_id = self::getActivePlayerId();
		$action_id = $this->getGameStateValue('action_id');
		$player = FfoPlayer::getFullPlayer($player_id);
		$forge = Forge::getAvailable();
		$state = $this->gamestate->state();
		$source = 'action-'.$action_id;
		
		// Check tile is available
		$found = null;
		foreach ($forge as $t) {
			if ($t["name"] == $special_tile) {
				$found = $t;
				break;
			}
		}
		
		$data_goods = self::getDataGoods();
		$good = $data_goods[$special_tile];
		if ($state['name'] == 'command-forgebuy') {
			if (! isset($found)) {
				throw new BgaVisibleSystemException( "Special tile not available" );
			}
			
			// You need the cash
			if ($found["cost"] >= 100) {
				throw new BgaUserException( self::_("That tile cannot be bought") );
			}
			
			$cost = $found["cost"];
			
			if (Occupation::has($player_id, 2)) {
				$discount_used = self::incGameStateValue( 'patron_discount_forge', $cost );
				
				if ($discount_used - $cost < 2 && $discount_used >= 2) {
					// Not used the discount, but you have spent 2 or more coins
					$cost--;
				}
			}
			
			if ($player["coin"] < $cost) {
				throw new BgaUserException( self::_("You do not have enough coins to buy that tile") );
			}
			$this->spend( $player, "coin", $cost, 'action-'.$action_id );
		} 
		else if ($state['name'] == 'command-forge') {
			if ($good["type"] == "jewelry") {
				// Special case - allowed to forge this!
			} else {
				if (! isset($found)) {
					throw new BgaVisibleSystemException( "Special tile not available" );
				}
				
				// Must be a tongs item
				if (! $found["tongs"]) {
					throw new BgaUserException( self::_("That tile cannot be forged") );
				}
			}
		} 
		else if ($state['name'] == 'command-forgeminor') {
            if (! isset($found)) {
                throw new BgaVisibleSystemException( "Special tile not available" );
            }
            
            // Must be a tongs item
            if (! $found["tongs"]) {
                throw new BgaUserException( self::_("That tile cannot be forged") );
            }

            if (+$found["strength"] > 8) {
                throw new BgaUserException( self::_("Tile strength must be < 9") );
            }
		} 
		else if ($state['name'] == 'command-fineblacksmith') {
			if (! isset($found)) {
				throw new BgaVisibleSystemException( "Special tile not available" );
			}
			
			// You need the cash
			if (! in_array($found["name"], ["crucifix", "cane"])) {
				throw new BgaUserException( self::_("That tile cannot be bought") );
			}
			
			$source = 'occupation-62';
			$this->spend( $player, "ore", 1, 'occupation-62' );
			$this->spend( $player, "coin", 1, 'occupation-62' );
		}
		else {
			throw new BgaVisibleSystemException( "Unrecognised state" );
		}
		
		// Give it to the player!
		$this->gain($player, $good["type"], 1, $source);
		
		if (isset($found)) {
			$this->saveForge();
			Forge::remove($found["name"]);
			
			self::notifyAllPlayers( "updateForge", '', array(
				'forge' => Forge::getAvailable(),
			));
		}

		FfoPlayer::saveFullPlayer($player);
		$this->gamestate->nextState( "done" );
	}

}