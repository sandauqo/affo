<?php

trait DeclareSuccess {

    function declareSuccess( $num_resources, $num_cards_per_type ) {
		// self::checkAction( 'declareSuccess' );
		
		$player_id = self::getActivePlayerId();
		$player = FfoPlayer::getFullPlayer($player_id);
		$action_id = $this->getGameStateValue('action_id');
		
		$args = $this->argHunt();
		$hunt_type = $args["hunt_type"];

		// Spend resources if applicable
		if ($args["resource_type"]) {
			if ($player[$args["resource_type"]] < $num_resources) {
				throw new BgaVisibleSystemException( "You don't have enough resources" );
			}

			$this->spend( $player, $args["resource_type"], $num_resources, 'action-'.$action_id );
			
			if ($hunt_type == "raid" || $hunt_type == "pillage") {
				if (Occupation::has($player_id, 89)) {
					// Stone counts double!
					$num_resources *= 2;
				}
			}
		}

		// Check you are only spending cards of the correct type...
		
		// Spend cards
		$hand = Weapon::getHand($player_id);
		$to_spend = [];
		$card_value = 0;
		$num_cards = 0;
		
		$has_11 = ($hunt_type == "whales" || $hunt_type == "whalesminor") ? Occupation::has($player_id, 11) : false;
		$has_137 = ($hunt_type == "raid" || $hunt_type == "pillage") ? Occupation::has($player_id, 137) : false;
		
		foreach ($num_cards_per_type as $type => $num) {
			if ($num > 0) {
				$num_cards += $num;
				if (in_array($type, $args["weapon_types"]) || ($has_137 && $type == "spear")) {
					// Fine! Discard this many of that type of card
					$num_discarded = 0;
					foreach ($hand as $c) {
						if ($c["type"] == $type) {
							$to_spend[] = $c;
							$num_discarded++;
							$card_value++;
							
							if (($hunt_type == "whales" || $hunt_type == "whalesminor") && $has_11) {
								// Each spear is worth 2
								$card_value++;
							}
							
							if ($num_discarded == $num) {
								break;
							}
						}
					}
					if ($num_discarded < $num) {
						throw new BgaVisibleSystemException( "You don't have enough cards" );
					}
				} else {
					throw new BgaVisibleSystemException( "You cannot spend that weapon type here" );
				}
			}
		}
		if (count($to_spend) > 0) {
			$this->saveWeapons();
			Weapon::discard($player_id, $to_spend, 'action-'.$action_id);
			self::notifyAllPlayers( "discardWeapons", '', array(
				'player_id' => $player_id,
				'weapons' => $to_spend,
				'source' => 'action-'.$action_id
			));
		}
		
		$die_value = $args["die_value"];
		
		$ore = $args["ore_value"];
		$multiplier = $args["multiplier"];
		
		$bonus = $args["bonus"];
		
		$modifier = $ore + $card_value + $num_resources;
		$value = $die_value + $modifier * $multiplier + $bonus;
		
		if ($hunt_type == 'raid' || $hunt_type == 'pillage' || $hunt_type == 'theft') {
			$min_value = 6;
			if (Occupation::has($player_id, 138)) {
				$min_value = 5;
			}
			
			if ($value < $min_value) {
				throw new BgaUserException( clienttranslate("Result too low to be a success") );
			}
			
			if ($hunt_type == 'raid') {
				if (Occupation::has($player_id, 133)) {
					Action::push(["state-lancebearer"]);
				}
			}
			if (isset($num_cards_per_type["sword"]) && $num_cards_per_type["sword"] == 1) {
				if (Occupation::has($player_id, 135)) {
					Action::push(["draw-weapon-sword:occupation-135"]);
				}
			}
			
			// Allow player to pick reward...
			self::setGameStateValue( 'raid_value', $value );
		} else {
			if ($value > 0) {
				throw new BgaUserException( clienttranslate("Result too high to be a success. You can reduce the result with cards and resources.") );
			} else if ($value < 0) {
				// TODO : Should be allowed, but show warning. Because of this, no translation
				//throw new BgaUserException( "Result too low." );
			}
			
			// Give player the rewards!
			if ($hunt_type == 'game') {
				$this->gain($player, "hide", 1, "action-$action_id");
				$this->gain($player, "gamemeat", 1, "action-$action_id");
				
				if (Occupation::has($player_id, 143)) {
					$this->gain($player, "coin", 1, "occupation-143");
				}
				if (Occupation::has($player_id, 144)) {
					Action::push(["state-deerstalker"]);
				}
				if (Occupation::has($player_id, 163) && $action_id == 10) {
					$this->gain($player, "hide", 1, "occupation-163");
					$this->gain($player, "coin", 1, "occupation-163");
				}
			} else if ($hunt_type == 'snare') {
				$this->saveWeapons();
				$this->gain($player, "fur", 1, "action-$action_id");
				Weapon::draw($player_id, 'snare', 'action-'.$action_id);
				
				if (Occupation::has($player_id, 126)) {
					$this->gain($player, "coin", 1, "occupation-126");
				}
				if (Occupation::has($player_id, 127)) {
					$this->gain($player, "stockfish", 1, "occupation-126");
					if (Occupation::has($player_id, 175)) {
						Action::push(['exchange-coin-oil']);
					}
				}
				if (Occupation::has($player_id, 128)) {
					Action::push(["mountain-1", 'mountainreset']);
				}
				if (Occupation::has($player_id, 129)) {
					Action::push(["state-meatcurer"]);
				}
				if (Occupation::has($player_id, 130) && $action_id == 14) {
					Action::push(["state-snarespecialist"]);
				}

			} else if ($hunt_type == 'whales' || $hunt_type == 'whalesminor') {
				if (Occupation::has($player_id, 140)) {
					$this->gain($player, "oil", 2, "action-$action_id");
				} else {
					$this->gain($player, "oil", 1, "action-$action_id");
				}
				
				$this->gain($player, "skinandbones", 1, "action-$action_id");
				$this->gain($player, "whalemeat", 1, "action-$action_id");
				
				if ($num_resources == 0 && Occupation::has($player_id, 141)) {
					$whalers = 0;
					foreach ($player["ships"] as $s) {
						if ($s["type"] == 'whalingboat') {
							$whalers++;
						}
					}
					if ($hunt_type == 'whales' && $whalers >= 3) {
						$this->gain($player, "peas", 1, "occupation-141");
					} else if ($hunt_type == 'whales' && $whalers >= 2) {
						$this->gain($player, "beans", 1, "occupation-141");
					} else if ($whalers >= 1) {
						$this->gain($player, "cabbage", 1, "occupation-141");
					}
				}
				
				if ($num_cards == 1) {
					if (Occupation::has($player_id, 142)) {
						Action::push(["draw-weapon-spear:occupation-142"]);
					}
				}
			} else if ($hunt_type == 'elk') {
				$this->gain($player, "antlers", 1, "action-$action_id");
				Weapon::draw($player_id, null, 'action-'.$action_id);

			} else if ($hunt_type == 'fish') {
				$this->gain($player, "stockfish", 2, "action-$action_id");
				$this->gain($player, "oil", 1, "action-$action_id");

				if (Occupation::has($player_id, 175)) {
					Action::push(['exchange-coin-oil']);
				}
				if (Occupation::has($player_id, 150)) {
					Action::push(['exchange-stockfish-gamemeat']);
				}
			} else if ($hunt_type == 'game2') {
				$this->gain($player, "antlers", 1, "action-$action_id");
				$this->gain($player, "gamemeat", 1, "action-$action_id");

				if (Occupation::has($player_id, 143)) {
					$this->gain($player, "coin", 1, "occupation-143");
				}
				if (Occupation::has($player_id, 144)) {
					Action::push(["state-deerstalker"]);
				}
				if (Occupation::has($player_id, 163)) {
					$this->gain($player, "hide", 1, "occupation-163");
					$this->gain($player, "coin", 1, "occupation-163");
				}
			}

		}
		
		FfoPlayer::saveFullPlayer($player);
		$this->gamestate->nextState( 'done' );
	}

}