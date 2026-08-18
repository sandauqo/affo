<?php 

trait BuyShip {
    function buyShip( $ship_type ) {
		// For now - don't check. You can do it whenever you want?
		//self::checkAction( 'selectShip' );
		
		// Current player = request player
		$player_id = self::getCurrentPlayerId();
		$player = FfoPlayer::getFullPlayer($player_id);
		
		if ($ship_type == 'whalingboat') {
			$cost = 3;
		} else if ($ship_type == 'knarr') {
			$cost = 5;
		} else if ($ship_type == 'longship') {
			$cost = 8;
		} else {
			throw new BgaVisibleSystemException( "Unrecognised ship type" );
		}
		
		// Check you have enough money
		if ($player["coin"] < $cost) {
			throw new BgaUserException( self::_("You cannot afford that ship") );
		}
		
		// Spend the money
		$this->spend( $player, "coin", $cost, "boatboard-$ship_type", false );
		
		// Check the player has room for this boat type
		$num_small = 0;
		$num_big = 0;
		foreach ($player["ships"] as $s) {
			if ($s["type"] == "whalingboat") {
				$num_small++;
			} else {
				$num_big++;
			}
		}
		if ($ship_type == "whalingboat") {
			if ($num_small >= 3) {
				// No room!
				throw new BgaUserException( self::_("You do not have enough room to buy that boat") );
			}
		} else {
			if ($num_big >= 4) {
				// No room!
				throw new BgaUserException( self::_("You do not have enough room to buy that boat") );
			}
		}
		
		// Give ship
		FfoPlayer::addShip( $player, $ship_type, 0, 'boatboard', $cost );
		
		$this->updatePlayerScore( $player, false );
		FfoPlayer::saveFullPlayer($player);
	}
}