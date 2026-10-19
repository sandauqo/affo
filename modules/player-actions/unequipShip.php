<?php

trait UnequipShip {
    function unequipShip( $ship_id ) {
		// You can do this at any time!
		//self::checkAction( 'equipShip' );
		
		$player_id = self::getCurrentPlayerId();
		$player = FfoPlayer::getFullPlayer($player_id);
			
		// Check you have the occupation
		if (! Occupation::has($player_id, 39)) {
			throw new BgaUserException( clienttranslate("You cannot remove ore from ships") );
		}
		
		// Check you have the boat (+ remove its ore)
		$found = null;
		foreach ($player["ships"] as $k => &$s) {
			if ($s["id"] == $ship_id) {
				$max_ore = 0;
				if ($s["type"] == "whalingboat") $max_ore = 1;
				if ($s["type"] == "longship") $max_ore = 3;
				if ($player["ships"][$k]["ore"] == 0) {
					throw new BgaVisibleSystemException( "There is no ore on that ship" );
				}
				$s["ore"]--;
				$found = &$s;
				break;
			}
		}
		if (! isset($found)) {
			throw new BgaVisibleSystemException( "You do not have that ship ($ship_id)" );
		}
		
		// Gain the ore
		$this->gain($player, "ore", 1, "ship-$ship_id", false);
		
		// Notify
		self::notifyAllPlayers( "equipShip", '', array(
			'player_id' => $player_id,
			'ship' => $found,
		));
		
		FfoPlayer::saveFullPlayer($player);
	}
}