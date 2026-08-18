<?php 

trait SelectAttackReward {

    function selectAttackReward( $special_tile, $special_tile2, $num_resources, $num_cards_per_type ) {
		// self::checkAction( 'selectSpecialTile' );
		//TODO: naming? tile does not have to be special tile here
		$player_id = self::getActivePlayerId();
		$action_id = $this->getGameStateValue('action_id');
		$player = FfoPlayer::getFullPlayer($player_id);
		$state = $this->gamestate->state();
		$source = 'action-'.$action_id;
		
		$good = $this->getGood($special_tile);

		if (Occupation::has($player_id, 136)) {
			// Raider 
			if ($special_tile2) {
				$good2 = $this->getGood($special_tile2);
			}
		}
			
		$good_strength = $this->getGoodStrength($good, $player_id, $special_tile);
		if (isset($good2)) {
			$good2_strength = $this->getGoodStrength($good2, $player_id, $special_tile2);
		}

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
		$raid_value = $die_value + $modifier * $multiplier + $bonus;

		$min_value = 6;
		if (Occupation::has($player_id, 138)) {
			$min_value = 5;
		}
		
		if ($raid_value < $min_value) {
			throw new BgaUserException( self::_("Result too low to be a success") );
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

		// Take it, if you have enough strength
		$required_strength = $good_strength;
		if (isset($good2)) {
			$required_strength += $good2_strength;
		}
		if (! isset($good_strength) || $raid_value < $required_strength) {
			throw new BgaUserException( self::_("Your strength is not high enough to take that, (have ${raid_value}, need ${required_strength})") );
		}
				
		// Give it to the player!
		$this->gain($player, $good["type"], 1, $source);
		$this->removeFromForge($special_tile);

		if (isset($good2)) {
			// Give it to the player!
			$this->gain($player, $good2["type"], 1, $source);
			$this->removeFromForge($special_tile2);
		}
		
		FfoPlayer::saveFullPlayer($player);

		$this->gamestate->nextState("done");
	}

	private function getGood($tile) {
		// Check tile is available		
		$data_goods = self::getDataGoods();
		$good = $data_goods[$tile];

		if (! isset($good)) {
			throw new BgaVisibleSystemException( "Special tile not available" );
		}
		return $good;
	}

	private function getGoodStrength($good, $player_id, $tile) {
		$good_strength = isset($good["strength"]) ? $good["strength"] : null;
		
		if (! isset($good_strength) && Occupation::has($player_id, 138)) {
			// TODO : Can do this by saying: if green, upgrade strength - 1
			switch ($good["type"]) {
				case 'oil': $good_strength = 5; break;
				case 'hide': $good_strength = 6; break;
				case 'linen': $good_strength = 7; break;
				case 'wool': $good_strength = 7; break;
				case 'skinandbones': $good_strength = 8; break;
				case 'fur': $good_strength = 9; break;
				case 'robe': $good_strength = 10; break;
				case 'clothing': $good_strength = 14; break;
				case 'antlers': $good_strength = 8; break;
				case 'leather': $good_strength = 12; break;
			}
		}

		if (isset($tile) && Occupation::has($player_id, 139)) {
			// Highest available special is reduced by one
			$scalesInForge = FeastForOdin::getObject( "SELECT * from forge WHERE name = 'scales'" );
			$treeInForge = FeastForOdin::getObject( "SELECT * from forge WHERE name = 'tree'" );
			if (isset($scalesInForge) || isset($treeInForge)) {
				// take second highest, because scales (or yule tree) have str of 999
				// ideally should make strength NULLABLE but don't wanna deal with database stuff for existing games
				$sql = "SELECT MAX(strength) as m FROM forge WHERE strength < ( SELECT MAX(strength) FROM forge)";
			} else {
				// just take highest
				$sql = "SELECT MAX(strength) as m FROM forge";
			}

			$highest = FeastForOdin::getObject( $sql );
			$found = $this->findForgeItemByName($tile);
			if ($highest["m"] == $found["strength"]) {
				$good_strength--;
			}
		}
		return $good_strength;
	}

	private function removeFromForge($tile) {
		$found = $this->findForgeItemByName($tile);
		if (isset($found)) {
			$this->saveForge();
			Forge::remove($found["name"]);
			
			self::notifyAllPlayers( "updateForge", '', array(
				'forge' => Forge::getAvailable(),
			));
		}
	}

	private function findForgeItemByName($tile) {
		$forge = Forge::getAvailable();
		$found = null;
		foreach ($forge as $t) {
			if ($t["name"] == $tile) {
				$found = $t;
				break;
			}
		}
		return $found;
	}

}