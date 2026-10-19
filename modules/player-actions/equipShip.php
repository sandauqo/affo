<?php

trait EquipShip {
    function equipShip( $ship_id ) {
		// You can do this at any time!
		//self::checkAction( 'equipShip' );
		
		$state = $this->gamestate->state();
		if ($state['name'] == 'command-hunt' || $state['name'] == 'command-hunt-reward' || $state['name'] == 'command-hunt-reward2') {
			throw new BgaUserException( clienttranslate("You cannot add ore to a boat at this time") );
		}
		
		$player_id = self::getCurrentPlayerId();
		$player = FfoPlayer::getFullPlayer($player_id);
		
		if (Occupation::has($player_id, 9) && $state['name'] == 'command-oreboatman') {
			throw new BgaUserException( clienttranslate("You cannot add ore to a boat at this time") );
		}
		
		// Check you have the boat (+ give it ore)
		$found = null;
		foreach ($player["ships"] as $k => &$s) {
			if ($s["id"] == $ship_id) {
				$max_ore = 0;
				if ($s["type"] == "whalingboat") $max_ore = 1;
				if ($s["type"] == "longship") $max_ore = 3;
				if ($player["ships"][$k]["ore"] >= $max_ore) {
					throw new BgaVisibleSystemException( "There is no room for ore on that ship" );
				}
				$s["ore"]++;
				$found = &$s;
				break;
			}
		}
		if (! isset($found)) {
			throw new BgaVisibleSystemException( "You do not have that ship ($ship_id)" );
		}
		
		// Check you have ore
		if ($player["ore"] <= 0) {
			throw new BgaUserException( clienttranslate("You do not have enough ore") );
		}
		
		// Spend the ore
		$this->spend($player, "ore", 1, "ship-$ship_id", false);
		
		// Notify
		self::notifyAllPlayers( "equipShip", '', array(
			'player_id' => $player_id,
			'ship' => $found,
		));
		
		FfoPlayer::saveFullPlayer($player);
	}
}