<?php

trait UseOccupation {

    function useOccupation( $occupation_id, $mode_id, $data ) {
		$player_id = self::getCurrentPlayerId();
		$state = $this->gamestate->state();

		// Must be your turn
		$actives = $this->gamestate->getActivePlayerList();
		if (! in_array($player_id, $actives)) {
			throw new BgaUserException( clienttranslate("It is not your turn") );
		}
		
		// Must have played this occupation
		$played = Occupation::getPlayed($player_id);
		$found = null;
		foreach ($played as $occ) {
			if ($occ["id"] == $occupation_id) {
				$found = $occ;
				break;
			}
		}

		if (! isset($found)) {
			throw new BgaVisibleSystemException( "You have not played that occupation ($occupation_id)" );
		}
		
		// Do the effect...
		$player = FfoPlayer::getFullPlayer($player_id);
		$ability = $found["ability"];
		if ($ability == 44) {
			// Master Tailor
			if (
				self::canAfford( $player, "wool", 1 ) &&
				self::canAfford( $player, "linen", 1 ) &&
				self::canAfford( $player, "hide", 1 )
			) {
				// Spend all!
				$this->spend( $player, "wool", 1, 'occupation-44' );
				$this->spend( $player, "linen", 1, 'occupation-44' );
				$this->spend( $player, "hide", 1, 'occupation-44' );
				
				// Gain
				$this->gain($player, "coin", 3, 'occupation-44');
				$this->gain($player, "clothing", 1, 'occupation-44');
				
				FfoPlayer::saveFullPlayer($player);
			} else {
				throw new BgaUserException( clienttranslate("You cannot afford that action") );
			}
		} 
		else if ($ability == 45) {
			// Pirate
			if (
				self::canAfford( $player, "wood", 1 ) &&
				self::canAfford( $player, "coin", 6 )
			) {
				// Spend all!
				$this->spend( $player, "wood", 1, 'occupation-45' );
				$this->spend( $player, "coin", 6, 'occupation-45' );
				
				// Gain
				$this->gain($player, "treasurechest", 1, 'occupation-45');

				FfoPlayer::saveFullPlayer($player);

				// interacts with Locksmith
				$player_id = self::getActivePlayerId();
				if (Occupation::has($player_id, 28)) {
					Action::push([
						"setplayer-$player_id-nopass", 
						"exchange-coin-oil:occupation-28"
					]);
					$this->gamestate->nextState( "action" );
				}

			} else {
				throw new BgaUserException( clienttranslate("You cannot afford that action") );
			}
		} 
		else if ($ability == 47) {
			// Tanner
			if (
				self::canAfford( $player, "saltmeat", 1 )
			) {
				// Spend all!
				$this->spend( $player, "saltmeat", 1, 'occupation-47' );
				
				// Gain
				$this->gain($player, "hide", 1, 'occupation-47');
				
				FfoPlayer::saveFullPlayer($player);
			} else {
				throw new BgaUserException( clienttranslate("You cannot afford that action") );
			}
		} 
		else if ($ability == 48) {
			// Farmer
			if (
				self::canAfford( $player, "cattle", 1 )
			) {
				// Spend all!
				$this->spend( $player, "cattle", 1, 'occupation-48' );
				
				// Gain
				$this->gain($player, "jewelry", 1, 'occupation-48');
				
				FfoPlayer::saveFullPlayer($player);
			} else {
				throw new BgaUserException( clienttranslate("You cannot afford that action") );
			}
		} 
		else if ($ability == 49) {
			// Plower
			if (
				self::canAfford( $player, "cattle", 1 )
			) {
				// Spend all!
				$this->spend( $player, "cattle", 1, 'occupation-49' );
				FfoPlayer::saveFullPlayer($player);
				
				// Gain
				$this->stHarvestGoods( $player_id, true );
			} else {
				throw new BgaUserException( clienttranslate("You cannot afford that action") );
			}
		} 
		else if ($ability == 50) {
			// Rune Engraver
			if (self::canAfford( $player, "rune", 1 )) {
				// Spend all!
				$this->spend( $player, "rune", 1, 'occupation-50' );
				
				// Gain
				$this->gain($player, "hide", 1, 'occupation-50');
				
				FfoPlayer::saveFullPlayer($player);
			} else {
				throw new BgaUserException( clienttranslate("You cannot afford that action") );
			}
		} 
		else if ($ability == 51) {
			// Master Mason
			if (
				self::canAfford( $player, "rune", 1 )
			) {
				// Spend all!
				$this->spend( $player, "rune", 1, 'occupation-51' );
				
				// Gain
				$this->gain($player, "coin", 1, 'occupation-51');
				if ($mode_id == 0) {
					$this->gain($player, "milk", 1, 'occupation-51');
				} else if ($mode_id == 1) {
					$this->gain($player, "cabbage", 1, 'occupation-51');
				} else {
					throw new BgaVisibleSystemException( "Invalid mode ($mode_id) for $state[name]" );
				}
			} else {
				throw new BgaUserException( clienttranslate("You cannot afford that action") );
			}
			FfoPlayer::saveFullPlayer($player);
		} 
		else if ($ability == 52) {
			// Tutor used via playOccupation with a flag
		}
		else if ($ability == 53) {
			// Farm Shop Owner
			if (!self::canAfford( $player, "coin", 1 )) {
				throw new BgaUserException( clienttranslate("You cannot afford that action") );
			}
			$allowed_types = ['peas', 'flax', 'grain', 'beans', 'cabbage', 'fruits', 'herbs'];

			if (!in_array($data, $allowed_types)) {
				throw new BgaUserException( clienttranslate("You cannot upgrade that good") );
			}
			$this->spend( $player, "coin", 1, 'occupation-53' );
			$this->spend( $player, $data, 1, 'occupation-53' );
			$data_goods = self::getDataGoods();
			$this->gain( $player, $data_goods[$data]["upgrade"], 1, 'occupation-53');
			FfoPlayer::saveFullPlayer($player);
		}
		else if ($ability == 54) {
			// Tradesman
			if (
				self::canAfford( $player, "silverware", 1 )
			) {
				// Spend all!
				$this->spend( $player, "silverware", 1, 'occupation-54' );
				
				// Gain
				if ($mode_id == 0) {
					$this->gain($player, "silk", 1, 'occupation-54');
				} else if ($mode_id == 1) {
					$this->gain($player, "chest", 1, 'occupation-54');
				} else {
					throw new BgaVisibleSystemException( "Invalid mode ($mode_id) for $state[name]" );
				}
			} else {
				throw new BgaUserException( clienttranslate("You cannot afford that action") );
			}
			FfoPlayer::saveFullPlayer($player);
		} 
		else if ($ability == 55) {
			// Treasurer
			if (
				self::canAfford( $player, "coin", 3 ) &&
				self::canAfford( $player, "chest", 1 )
			) {
				// Spend all!
				$this->spend( $player, "coin", 3, 'occupation-55' );
				$this->spend( $player, "chest", 1, 'occupation-55' );
				
				// Gain
				$this->gain($player, "treasurechest", 1, 'occupation-55');
				
				FfoPlayer::saveFullPlayer($player);
			} else {
				throw new BgaUserException( clienttranslate("You cannot afford that action") );
			}
		} 
		else if ($ability == 56) {
			// Arms Dealer
			if (!isset($data)) {
				throw new BgaUserException( "You must pick exactly two weapons" );
			}

			$weapons = explode('_', $data);

			if (count($weapons) != 2) {
				throw new BgaUserException( "You must pick exactly two weapons" );
			}
			$player_id = self::getCurrentPlayerId();
			$hand = Weapon::getHand($player_id);
	
			foreach ($weapons as $w) {
				$found = false;
				foreach ($hand as $h) {
					if ($h["id"] == $w) {
						$found = true;
					}
				}
				if (!$found) {
					throw new BgaVisibleSystemException( "You do not have ${w}" );
				}
			}
	
			$discard = [];
			foreach ($weapons as $w) {
				foreach ($hand as $h) {
					if ($h["id"] == $w) {
						$discard[] = $h;
					}
				}
			}
	
			$this->saveWeapons();
			Weapon::discard($player_id, $discard, 'occupation-56');
			self::notifyAllPlayers( "discardWeapons", '', array(
				'player_id' => $player_id,
				'weapons' => $discard,
				'source' => 'occupation-56'
			));
	
			$player = FfoPlayer::getFullPlayer($player_id);
			$this->gain($player, 'ore', 1, 'occupation-56');
			FfoPlayer::saveFullPlayer($player);

		}
		else if ($ability == 57) {
			// Bean Farmer
			if (
				self::canAfford( $player, "beans", 2 )
			) {
				// Spend all!
				$this->spend( $player, "beans", 2, 'occupation-57' );
				
				// Gain
				$this->gain($player, "peas", 1, 'occupation-57');
				$this->gain($player, "mead", 1, 'occupation-57');
				$this->gain($player, "stockfish", 1, 'occupation-57');
				
				FfoPlayer::saveFullPlayer($player);
			} else {
				throw new BgaUserException( clienttranslate("You cannot afford that action") );
			}
		} 
		else if ($ability == 58) {
			// Linseed Oil Presser
			if (
				self::canAfford( $player, "flax", 2 )
			) {
				// Spend all!
				$this->spend( $player, "flax", 2, 'occupation-58' );
				
				// Gain
				$this->gain($player, "oil", 3, 'occupation-58');
				
				FfoPlayer::saveFullPlayer($player);
			} else {
				throw new BgaUserException( clienttranslate("You cannot afford that action") );
			}
		} 
		else if ($ability == 59) {
			// Linen Weaver
			if (
				self::canAfford( $player, "coin", 1 ) &&
				self::canAfford( $player, "flax", 2 )
			) {
				// Spend all!
				$this->spend( $player, "coin", 1, 'occupation-59' );
				$this->spend( $player, "flax", 2, 'occupation-59' );
				
				// Gain
				$this->gain($player, "linen", 2, 'occupation-59');
				
				FfoPlayer::saveFullPlayer($player);
			} else {
				throw new BgaUserException( clienttranslate("You cannot afford that action") );
			}
		} 
		else if ($ability == 46) {
			// Ship Architect
			if ($mode_id == 0) {
				$other = 'wool';
			} else if ($mode_id == 1) {
				$other = 'linen';
			} else {
				throw new BgaVisibleSystemException( "Invalid mode ($mode_id) for $state[name]" );
			}
			
			if (
				self::canAfford( $player, "wood", 3 ) &&
				self::canAfford( $player, $other, 1 )
			) {
				// Spend all!
				$this->spend( $player, "wood", 3, 'occupation-46' );
				$this->spend( $player, $other, 1, 'occupation-46' );
				
				// Gain
				FfoPlayer::addShip( $player, "longship", 0, 'occupation-46' );
				
				$this->updatePlayerScore( $player );
			} else {
				throw new BgaUserException( clienttranslate("You cannot afford that action") );
			}
		} 
		else if ($ability == 187) {
			// Chronicler
			if ($mode_id == 0) {
				// 1. Check it's your turn for a Viking action
				if ($this->getString("finishedTransition") != "finished") {
					throw new BgaUserException( clienttranslate("You can only use this action during a Viking action") );
				}
				// 2. Check it's in the forge
				if (! Forge::isAvailable("belt")) {
					throw new BgaUserException( clienttranslate("That good is not available") );
				}
				// 3. Remove it from the forge
				$this->saveForge();
				Forge::remove("belt");
				self::notifyAllPlayers( "updateForge", '', array(
					'forge' => Forge::getAvailable(),
				));
				$this->spend($player, "flax", 2, 'occupation-187');
				$this->gain($player, "belt", 1, 'occupation-187');
			} else if ($mode_id == 1) {
				$this->spend($player, "flax", 3, 'occupation-187');
				$this->gain($player, "treasurechest", 1, 'occupation-187');
			} else {
				throw new BgaVisibleSystemException( "Invalid mode ($mode_id) for $state[name]" );
			}
			FfoPlayer::saveFullPlayer($player);
		} 
		else if ($ability == 182) {
			// Single-use only!
			if (! empty($found["used"])) {
				throw new BgaUserException( clienttranslate("You have already used that occupation") );
			}
			
			$this->saveOccupations( $player_id );
			Occupation::use($found["id"]);
			
			$houses = 0;
			foreach ($player["boards"] as $s) {
				if ($s["type"] == "stonehouse" || $s["type"] == "longhouse") {
					$houses++;
				}
			}
			
			if ($houses < 2) {
				throw new BgaUserException( clienttranslate("You do not meet the requirements for that action") );
			}
			
			if ($mode_id == 0) {
				if (! $this->canAfford($player, "wood", 2)) {
					throw new BgaUserException( clienttranslate("You cannot afford that action") );
				}
				$this->spend($player, "wood", 2, 'occupation-182');
				$this->gain($player, "spices", 1, 'occupation-182');
			} else if ($mode_id == 1) {
				if (! $this->canAfford($player, "wood", 5)) {
					throw new BgaUserException( clienttranslate("You cannot afford that action") );
				}
				$this->spend($player, "wood", 5, 'occupation-182');
				$this->gain($player, "spices", 2, 'occupation-182');
			} else {
				throw new BgaVisibleSystemException( "Invalid mode ($mode_id) for $state[name]" );
			}
			FfoPlayer::saveFullPlayer($player);
		}
		else if ($ability == 39) {
			// Modifier
			throw new BgaUserException( clienttranslate("Click on ore equipped to a boat to remove it") );
		} 
		else {
			throw new BgaUserException( clienttranslate("That occupation cannot be used at this time") );
		}
	}

}