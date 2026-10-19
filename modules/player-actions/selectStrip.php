<?php

trait SelectStrip {
    function selectStrip( $strip_id ) {
		self::checkAction( 'selectStrip' );
		
		$this->setGameStateValue( 'resolved_effect', 1 );
		
		$player_id = self::getActivePlayerId();
		$player = FfoPlayer::getFullPlayer($player_id);
		$strips = MountainStrip::getAvailable();
		$state = $this->gamestate->state();
		
		// Strip must be available
		$found = false;
		foreach ($strips as $s) {
			if ($s["id"] == $strip_id) {
				$found = $s;
				break;
			}
		}
		if (! isset($found)) {
			throw new BgaVisibleSystemException( "That mountain strip is not available" );
		}

		if ($state["name"] == "command-mountain") {
			$taken = $this->getString("takenStrips");
			
			$taken_ids = explode(",", $taken);
			foreach ($taken_ids as $i) {
				if ($i == $strip_id) {
					throw new BgaUserException( clienttranslate("You have already used that mountain strip this turn") );
				}
			}
			
			// Save strip id to list of taken strips (can't take from the same strip twice)
			
			$taken_ids[] = $strip_id;
			$this->setString("takenStrips", implode(",", $taken_ids));
		}
		
		
		$command = $this->getString( 'currentCommand' );
		$components = explode('-', $command);
		$num = $state["name"] == "command-mountain" ? +$components[1] : 1;
		$elements = explode(",", $found["resources"]);
		
		$types = self::getGameStateValue( 'mountainguard_types' );
		$wood_taken = self::getGameStateValue( 'mountain_wood_taken' );
		$silver_taken = self::getGameStateValue( 'mountain_silver_taken' );
		for ($i = 0; $i < $num && count($elements) > 0; $i++) {
			// Take this thing!
			// If it's "coins" it means 2 coins
			$good = array_shift($elements);
			$amt = 1;
			
			if ($good == "coins") {
				$good = "coin";
				$amt = 2;	
			}
			
			if ($good == "orepair") {
				$good = "ore";
				$amt = 2;	
			}

			if ($state["name"] == "command-armedfighter") {
				if (! in_array($good, ["ore", "stone"])) {
					throw new BgaUserException( clienttranslate("You can only take ore or stone") );
				}
			}
			
			$this->gain($player, $good, $amt, 'mountainstrip-'.$strip_id);
			
			if ($good == "coin") {
				$types = $types | 1;
				$silver_taken += 2;
			} else if ($good == "ore") {
				$types = $types | 2;
			} else if ($good == "wood") {
				$types = $types | 4;
				$wood_taken++;
			} else if ($good == "stone") {
				$types = $types | 8;
			}
			
			if ($good == "stone") {
				if (Occupation::has($player_id, 6)) {
					// Stone carver gives extra coin!
					$this->gain($player, "coin", 1, 'occupation-6');
				}
			}
			if ($good == "coin") {
				if (Occupation::has($player_id, 188)) {
					// Mineworker gives extra coin!
					$this->gain($player, "coin", 1, 'occupation-188');
				}
			}
		}
		
		self::setGameStateValue( 'mountainguard_types', $types );
		self::setGameStateValue( 'mountain_wood_taken', $wood_taken );
		self::setGameStateValue( 'mountain_silver_taken', $silver_taken );
		
		// If there are no more strip actions coming up, gain some resources?
		// This is in mountainreset
		
		FfoPlayer::saveFullPlayer($player);
		$this->saveMountainStrips();
		
		// Update based on $elements, and if empty, discard
		MountainStrip::save($strip_id, $elements);
		
		// Notify update strips
		self::notifyAllPlayers( "updateMountainStrips", '', array(
			"mountainstrips" => MountainStrip::getAvailable(),
		));
		
		$this->gamestate->nextState( 'done' );
	}
}