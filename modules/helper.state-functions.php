<?php

//////////////////////////////////////////////////////////////////////////////
//////////// Game state actions
////////////

include "state-functions/doCommand.php";

trait StateFunctions {

	use DoCommand;

	function stNone() {
		// placeholder
		$this->gamestate->nextState( '' );
	}
	
	function stDoCommand() {
		$this->doCommand();
	}
	
	function stForge() {
		// If there are no tiles left in the Forge, pass!
		$forge = Forge::getAvailable();
		if (count($forge) == 0) {
			$this->actPass( false );
		}
	}
	
	function stPlayOccupation() {
		// If you have no occupations, automatically pass
		$player_id = self::getActivePlayerId();
		$hand = Occupation::getHand($player_id);
		if (count($hand) == 0) {
			$this->actPass( false );
		}
	}
	
	function stSwapWhalingBoatKnarr() {
		$player_id = self::getActivePlayerId();
		$player = FfoPlayer::getFullPlayer($player_id);
			
		// If you don't have a whaling boat,
		// or you have 4 big ships, pass.
		
		$num_small = 0;
		$num_big = 0;
		foreach ($player["ships"] as $s) {
			if ($s["type"] == "whalingboat") {
				$num_small++;
			} else {
				$num_big++;
			}
		}
		
		if ($num_small == 0 || $num_big >= 4) {
			$this->actPass( false );
		}
	}
	
	function stBuyLongshipFifthCol() {
		$player_id = self::getActivePlayerId();
		$player = FfoPlayer::getFullPlayer($player_id);
			
		// If you don't have a whaling boat,
		// or you have 4 big ships, pass.
		
		$num_small = 0;
		$num_big = 0;
		foreach ($player["ships"] as $s) {
			if ($s["type"] == "whalingboat") {
				$num_small++;
			} else {
				$num_big++;
			}
		}
		
		if ($num_small == 0 || $num_big >= 4) {
			$this->actPass( false );
		}
	}

	function stBuyWhalingboatFifthCol() {

		$player_id = self::getActivePlayerId();
		$player = FfoPlayer::getFullPlayer($player_id);
			
		// If you don't have a whaling boat, pass
		
		$num_small = 0;
		foreach ($player["ships"] as $s) {
			if ($s["type"] == "whalingboat") {
				$num_small++;
			}
		}
		
		if ($num_small >= 3) {
			$this->actPass( false );
		}
	}

	function stStoreman() {
		$player_id = self::getActivePlayerId();
		$player = FfoPlayer::getFullPlayer($player_id);
		
		// If you don't have three of any good, pass!
		$amounts = [];
		foreach ($player["goods"] as $g) {
			if ($g["type"] == "sheep" || $g["type"] == "cattle" || $g["type"] == "horse" || $g["type"] == "pig" ||
				$g["type"] == "pregnantsheep" || $g["type"] == "pregnantcattle" || $g["type"] == "pregnanthorse") {
				continue;
			}
			if (! isset($amounts[$g["type"]]))
				$amounts[$g["type"]] = 0;
			$amounts[$g["type"]]++;
		}
		
		$found = false;
		foreach ($amounts as $good => $amount) {
			if ($amount == 3) {
				$found = true;
				break;
			}
		}
		
		if (! $found) {
			$this->actPass( false );
		}
	}
	
	function stUpgrade() {
		$player_id = self::getActivePlayerId();
		$player = FfoPlayer::getFullPlayer($player_id);
		
		$state = $this->gamestate->state();
		$num_goods = 0;
		if ($state['name'] == 'command-trade') {
			// If you have no green goods, pass!
			foreach ($player["goods"] as $g) {
				if ($g["colour"] == "green") {
					$num_goods++;
				}
			}
		} else if ($state['name'] == 'command-flip') {
			// If you have no goods, pass!
			$num_goods = count($player["goods"]);
		} else if ($state['name'] == 'command-payupgrade') {
			// If you have no goods or cash, pass!
			$num_goods = count($player["goods"]);
			if (! $this->canAfford($player, "coin", 1)) {
				$num_goods = 0;
			}
		} else {
			$command = $this->getString( 'currentCommand' );
			
			$components = explode('-', $command);
			$num_tiles = $components[1];
			$num_steps = $components[2];
			
			$valids = ["orange", "red", "green"];
			
			// If you have no (non-blue) goods, pass!
			foreach ($player["goods"] as $g) {
				if (in_array($g["colour"], $valids)) {
					$num_goods++;
				}
			}
		}
		
		if ($num_goods == 0) {
			$this->actPass( false );
		}
	}
	
	function stMeatUpgrade() {
		$player_id = self::getActivePlayerId();
		$player = FfoPlayer::getFullPlayer($player_id);
		
		$num_goods = 0;
		foreach ($player["goods"] as $g) {
			if (in_array($g["type"], ["saltmeat", "whalemeat", "gamemeat"])) {
				$num_goods++;
			}
		}
		
		if ($num_goods == 0) {
			$this->actPass( false );
		}
	}
	
	function stMountain() {
		// If there are no strips left, pass!
		$strips = MountainStrip::getAvailable();
		$taken = $this->getString("takenStrips");
		$taken_ids = explode(",", $taken);
		
		$available = 0;
		foreach ($strips as $s) {
			foreach ($taken_ids as $i) {
				if ($i == $s["id"]) {
					// You've already taken from this strip...
					continue 2;
				}
			}
			$available++;
		}
		if ($available == 0) {
			$this->actPass( false );
		}
	}
	
	function stNextPlayer() {
		$passed = self::getCollectionFromDb("SELECT 
		  player_id id, passed
		FROM player ");
		
		$current = self::getActivePlayerId();
		$next_player_table = self::getNextPlayerTable();
			
		// Check at least one player has not passed
		$all_passed = true;
		foreach ($passed as $player_id => $p) {
			if (! $p["passed"]) {
				$all_passed = false;
				break;
			}
		}
		
		if ($all_passed) {
			foreach ($passed as $player_id => $p) {
				// If anyone has Latecomer 91
				if (Occupation::has($player_id, 91)) {
					// save player state here so undo works properly for latecomer
					$this->saveState($player_id);
					Action::push(["setplayer-$player_id", 'state-latecomer']);
					break;
				}
			}
			
			foreach ($passed as $player_id => $p) {
				// If anyone has Ore Boatman 9
				if (Occupation::has($player_id, 9)) {
					$player = FfoPlayer::getFullPlayer($player_id);
					$longships = 0;
					foreach ($player["ships"] as $s) {
						if ($s["type"] == "longship") {
							$longships++;
						}
					}
					if ($longships > 0) {
						$this->setGameStateValue('ore_boatman', $longships);
						Action::push(["setplayer-$player_id", 'takeper-ore-longship:occupation-9', 'state-oreboatman']);
					}
					break;
				}
			}
			
			// Next phase
			$this->setString("finishedTransition", "finished_postactions");
			$this->gamestate->nextState( 'nextPhase' );
		} else {
			// Next player
			while (true) {
				$current = $next_player_table[$current];
				if (! $passed[$current]["passed"]) {
					// If this player hasn't passed, it's their turn
					break;
				}
			}
			
			$this->gamestate->changeActivePlayer( $current );
			
			$this->gamestate->nextState( 'nextPlayer' );
		}
	}
	
	function stNextPlayerDrawPersonalResource() {
		
		$players = self::getCollectionFromDb("SELECT player_id id FROM player");

        $everyoneHasMarkers = true;

        foreach ($players as $player) {
            if (!FfoPlayer::getPersonalResourceMarker($player['id'])) {
                $everyoneHasMarkers = false;
                break;
            }
        }

		if ($everyoneHasMarkers) {
			if (self::isShortGame()) {
				$this->activePrevPlayer();
				$this->gamestate->nextState('drawSecondResource');
			} else {
				$this->gamestate->nextState('nextPhase');
			}
        } else {
			$this->activePrevPlayer();
			$this->gamestate->nextState('nextPlayer');
        }
	}

	function stNextPlayerDrawPersonalResource2() {
		
		$players = self::getCollectionFromDb("SELECT player_id id FROM player");

        $everyoneHasSecondMarker = true;

        foreach ($players as $player) {
            if (!FfoPlayer::getSecondPersonalResourceMarker($player['id'])) {
                $everyoneHasSecondMarker = false;
                break;
            }
        }

        if ($everyoneHasSecondMarker) {
			if (FeastForOdin::$game->isNorwegiansExpansion()) {
				$this->activePrevPlayer();
				$this->gamestate->nextState('norwegiansSetup');
			} else {
				$this->gamestate->setAllPlayersMultiactive();
				$this->gamestate->nextState('nonNorwegiansSetup');
			}
        } else {
			$this->activePrevPlayer();
			$this->gamestate->nextState('nextPlayer');
        }
	}

	function stNextPlayerBuildArtisanShed() {
		$players = self::getCollectionFromDb("SELECT player_id id FROM player");

        $everyoneHasBuilt = true;

        foreach ($players as $player) {
			$playerFull = FfoPlayer::getFullPlayer( $player['id'] );
            if (!FfoPlayer::hasBuiltArtisanShed($playerFull)) {
                $everyoneHasBuilt = false;
                break;
            }
        }

        if ($everyoneHasBuilt) {
			$this->gamestate->nextState('nextPhase');
        } else {
			$this->activePrevPlayer();
			$this->gamestate->nextState('nextPlayer');
        }
	}

	function stPickAction() {

		$player_id = self::getActivePlayerId();

		if ($this->getString('preGameOccupation'.$player_id) !== '1') {
			// if non-norwegians, harvest exp short game
			if (FeastForOdin::$game->hasMiniExpansion2() && self::isShortGame() && !FeastForOdin::$game->isNorwegiansExpansion()) {
				self::setGameStateValue( 'usingAnyTimeOccupationOutOfAction', 1);
				$this->gamestate->nextState('activatePreGameOccupation');
			}
		}

		$this->setGameStateValue( 'patron_discount_forge', 0 );
		$this->setGameStateValue( 'mountainguard_types', 0 );
		$this->setGameStateValue( 'mountain_wood_taken', 0 );
		$this->setGameStateValue( 'mountain_silver_taken', 0 );
		$this->setGameStateValue( 'pending_play', 0 );
		$this->setGameStateValue( 'pending_draw', 0 );
		$this->setGameStateValue( 'can_pass_override', 0 );
		$this->setGameStateValue( 'resolved_effect', 0 );
		$this->setGameStateValue( 'occ_swordfighter', 0 );
		$this->setGameStateValue( 'occ_berserker', 0 );
		$this->setGameStateValue( 'no_hunt_refunds', 0 );
		$this->setGameStateValue( '5th_col_how_many_vikings', 0);
		$this->setGameStateValue( 'double_action_first_taken', 0);
		$this->setGameStateValue( 'no_pass', 0);
		$this->setGameStateValue( 'takeImmediateActionToContinue', 0);
		$this->setGameStateValue( 'etiquetteTeacherGivenPeasThisAction', 0);
		$this->setGameStateValue( 'goBack', 0);
		$this->setGameStateValue( 'waterford_bonus_knarr_skip_179_occ', 0);

		// Undo helpers
		$this->setGameStateValue( 'undo_occupation', 0 );
		$this->setGameStateValue( 'undo_weapon', 0 );
		$this->setGameStateValue( 'undo_forge', 0 );
		$this->setGameStateValue( 'undo_string_globals', 0 );
		$this->setGameStateValue( 'undo_islands', 0 );
		$this->setGameStateValue( 'undo_mountain_strips', 0 );
		
		// Reset taken mountainstrips
		$this->setString("takenStrips", "");
		$this->setString("occ:70", "");
		$this->setString("occ:71", "");
		$this->setString("finishedTransition", "finished");
		$this->setString('allowed_upgrade_types', '');
		// If the player has no available meeples, auto pass!
		$availableMeeples = FfoPlayer::getMeeples($player_id, 'thing');
		// Or player has meeples in 5th col (Norwegians only), also pass!
		$fifthColMeeples= FfoPlayer::getMeeplesInColumn(5, $player_id);
		if (count($availableMeeples) == 0 || $fifthColMeeples) {
			$this->actPass( false );
		} else {
			$this->setCanUndo( 1 );
			if ($this->getGameStateValue('just_restored')) {
				$this->setGameStateValue( 'just_restored', 0 );
			} else {
				$this->saveState($player_id);
				$this->giveExtraTime( $player_id );
			}
		}
	}
	
	function stPickModal() {
		// If the action has only one modal, pick it!
		$action = $this->getCurrentAction();
		
		if ($this->getGameStateValue( 'can_pass_override' )) {
			// The player can't do the action!
			// They must pass...
		} else if (count($action) === 1) {
			$this->actSelectMode( 0 );
		} else {
			// Check requirements!
			$player_id = self::getActivePlayerId();
			$player = FfoPlayer::getFullPlayer( $player_id );
			$action_id = $this->getGameStateValue('action_id');
			Requirements::check($action_id, $player);
		}
	}
	
	function stDrawWeapon() {
		// Each player draws a random weapon
		$players = self::loadPlayersBasicInfos();
		foreach( $players as $player_id => $player ) {
			
			if (Occupation::has($player_id, 38)) {
				// Peacemaker - may take resource instead
				Action::push([
					"setplayer-$player_id", 
					"state-peacemaker"
				]);
			} else {
				Weapon::draw($player_id);
			}
		}
		
		$this->setString("finishedTransition", "finished_drawweapon");
		$this->gamestate->nextState( '' );
	}
	
	function stSetFirstPlayer() {
		// Set first player to be moose player...
		$first_player_id = $this->getGameStateValue( 'first_player_id' );
		$this->gamestate->changeActivePlayer( $first_player_id );
		
		$this->gamestate->nextState( '' );
	}
	
	function stDetermineStartPlayer() {
		$first_player_id = $this->getGameStateValue( 'placed_player_id' );
		$this->setGameStateValue( 'first_player_id', $first_player_id );
		$this->setCanUndo( 0 );
		
		// Notification
		self::notifyAllPlayers( "firstPlayer", '', array(
			'player_id' => $first_player_id,
		));
		
		// Unpass all players
		self::DbQuery("UPDATE player SET passed = 0 WHERE 1");
		self::notifyAllPlayers( "unpassAll", '', array(
		));
		
		$this->gamestate->changeActivePlayer( $first_player_id );
		$this->gamestate->nextState( '' );
	}
	
	function stNewViking() {
		$players = self::loadPlayersBasicInfos();
		$meeples = [];
		foreach( $players as $player_id => $player ) {
			// For each player: take last Feast meeple and move it to the Thing
			$meeples = array_merge($meeples, FfoPlayer::newMeeple( $player_id ));
			
			$this->setString( 'twoVikingsPlaced' . $player_id, '0' );
		}
		self::notifyAllPlayers( "meepleMove", '', array(
			'meeples' => $meeples,
		));
		$this->gamestate->nextState( '' );
	}
	
	function stHarvestGoods( $only_player_id = null, $plower_goods = false ) {
		$players = self::loadPlayersBasicInfos();
		$data_goods = self::getDataGoods();
		$round = self::getGameStateValue( 'round' );
		$short = self::isShortGame();

		if (! $plower_goods) {
			$this->setString("finishedTransition", "finished_harvest");
		}

		$goods0 = []; //needed below, dont remove 

		$goods1 = [
			$data_goods["peas"],
			$data_goods["flax"],
			$data_goods["beans"]
		];

		$goods2 = array_merge($goods1, [$data_goods["grain"]]);
		$goods3 = array_merge($goods2, [$data_goods["cabbage"]]);
		$goods4 = array_merge($goods3, [$data_goods["fruits"]]);

		// Give each player the relevant goods
		$goods = [];
		if ($plower_goods) {
			$goods = $goods3;
		} else {
			if ($short) {
				// 1: 1
				// 3: 1-2
				// 5: 1-3
				if ($round == 1) {
					$goods = $goods1;
				} else if ($round == 3) {
					$goods = $goods2;
				} else if ($round == 5) {
					$goods = $goods3;
				}
			} else {
				// 1: 1
				// 2: 1-2
				// 4: 1-3
				// 6: 1-4
				if ($round == 1) {
					$goods = $goods1;
				} else if ($round == 2) {
					$goods = $goods2;
				} else if ($round == 4) {
					$goods = $goods3;
				} else if ($round == 6) {
					$goods = $goods4;
				}
			}
		}

		if (!$only_player_id && FeastForOdin::$game->hasMiniExpansion2()) {
			$customHarvest = explode('-', $this->getString("custom_harvest"));
			$roundHarvest = $customHarvest[$round - 1];
			$roundHarvestString = 'goods' . $roundHarvest;
			$goods = $$roundHarvestString;

			// reveal next harvest
			$nextHarvest = $customHarvest[$round];
			self::notifyAllPlayers( "customHarvest", '', array(
				'current_harvest' => $roundHarvest,
				'next_harvest' => $nextHarvest,
				'next_round' => $round + 1,
			));

			// give out personal resources
			foreach( $players as $player_id => $p ) {
				$player = FfoPlayer::getFullPlayer( $player_id );
				$personalResourceMarker = FfoPlayer::getPersonalResourceMarker($player_id);
				$split = explode("-", $personalResourceMarker);

				if ($roundHarvest === '0') {
					$resource = $split[1]; // take back side
				} else {
					$resource = $split[0]; // take front side
				}

				if (in_array($resource, ['sword', 'bow', 'snare', 'spear'])) {
					Weapon::draw($player_id, $resource, "personal-resource-harvest");
				} else {
					$this->gain($player, $resource, 1, "personal-resource-harvest");
				}

				$personalResourceMarker2 = FfoPlayer::getSecondPersonalResourceMarker($player_id);
				if ($personalResourceMarker2) {
					$split = explode("-", $personalResourceMarker2);

					if ($roundHarvest === '0') {
						$resource = $split[1]; // take back side
					} else {
						$resource = $split[0]; // take front side
					}
	
					if (in_array($resource, ['sword', 'bow', 'snare', 'spear'])) {
						Weapon::draw($player_id, $resource, "personal-resource-harvest");
					} else {
						$this->gain($player, $resource, 1, "personal-resource-harvest");
					}
				}

				FfoPlayer::saveFullPlayer($player);
			}
		}

		// Give each player their goods
		if (count($goods) > 0) {
			foreach( $players as $player_id => $p ) {
				if (isset($only_player_id) && $only_player_id != $player_id)
					continue;
				
				$player = FfoPlayer::getFullPlayer( $player_id );
				
				if (Occupation::has($player_id, 169)) {
					// Sower
					$has_flax = false;
					$has_grain = false;
					foreach ($player["goods"] as $g) {
						if ($g["type"] == "grain") {
							$has_grain = true;
						} else if ($g["type"] == "flax") {
							$has_flax = true;
						}
					}
					if ($has_grain && $has_flax) {
						Action::push([
							"setplayer-$player_id",
							"state-sower"
						]);
					} else if ($has_flax) {
						Action::push([
							"setplayer-$player_id",
							"take-flax:occupation-169"
						]);
					} else if ($has_grain) {
						Action::push([
							"setplayer-$player_id",
							"take-grain:occupation-169"
						]);
					}
					
				}
				if (Occupation::has($player_id, 171)) {
					// Flax baker
					Action::push([
						"setplayer-$player_id",
						"state-flaxbaker"
					]);
				}
				
				// For each player: get their goods, add on the new goods, and save
				foreach ($goods as $g) {
					$player["goods"][] = $g;
				}
				self::notifyAllPlayers( "takeGoods", '', array(
					'player_id' => $player_id,
					'goods' => $goods,
					'source' => 'harvest'
				));
				FfoPlayer::saveFullPlayer($player);
				// TODO : Notification new goods!
			}
		} else {
			// No harvest round sometimes has some extra things
			foreach( $players as $player_id => $p ) {
				if (isset($only_player_id) && $only_player_id != $player_id)
					continue;
				
				$player = FfoPlayer::getFullPlayer( $player_id );
				
				$needs_save = false;
				if (Occupation::has($player_id, 92)) {
					// Sympathetic soul
					// Count special tiles
					$specials = 0;
					
					foreach ($player["goods"] as $g) {
						if (! empty($g["special"])) {
							$specials++;
						}
					}
					
					foreach ($player["boards"] as $b) {
						foreach ($b["goods"] as $g) {
							if (! empty($g["special"])) {
								$specials++;
							}
						}
					}
					if ($specials >= 3) {
						$needs_save = true;
					}
					
					if ($specials >= 5) {
						$this->gain($player, "coin", 3, "occupation-92");
					} else if ($specials >= 4) {
						$this->gain($player, "coin", 2, "occupation-92");
					} else if ($specials >= 3) {
						$this->gain($player, "coin", 1, "occupation-92");
					}
				}
				if (Occupation::has($player_id, 93)) {
					// Helper in time of need
					$boats = 0;
					foreach ($player["ships"] as $s) {
						if ($s["type"] == 'whalingboat') {
							$boats++;
						}
					}
					
					if ($boats > 0) {
						$needs_save = true;
					}
					
					if ($boats == 1) {
						$this->gain($player, "beans", 1, "occupation-93");
					} else if ($boats == 2) {
						$this->gain($player, "grain", 1, "occupation-93");
					} else if ($boats == 3) {
						$this->gain($player, "cabbage", 1, "occupation-93");
					}
				}
				if (Occupation::has($player_id, 97)) {
					// Pea counter
					$peas = 0;
					
					foreach ($player["goods"] as $g) {
						if ($g["type"] == "peas") {
							$peas++;
						}
					}
					
					if ($peas > 0) {
						$new_total = min(8, $peas * 2);
						$gain = $new_total - $peas;
						if ($gain > 0) {
							$needs_save = true;
							$this->gain($player, "peas", $gain, "occupation-97");
						}
					}
				}
				if (Occupation::has($player_id, 94)) {
					// Spice merchant
					Action::push([
						"setplayer-$player_id",
						"state-spicemerchant"
					]);
				}
				if (Occupation::has($player_id, 95)) {
					// Wanderer
					Action::push([
						"setplayer-$player_id",
						"mountain-2",
						'mountainreset'
					]);
				}
				if (Occupation::has($player_id, 96)) {
					// Meat merchant
					Action::push([
						"setplayer-$player_id",
						"state-meatmerchant"
					]);
				}
				
				if ($needs_save)
					FfoPlayer::saveFullPlayer($player);
			}
		}
		
		if (! isset($only_player_id)) {
			$this->gamestate->nextState( '' );
		}
	}
	
	function stExchange() {
		$player_id = self::getActivePlayerId();
		$player = FfoPlayer::getFullPlayer( $player_id );
		$command = $this->getString( 'currentCommand' );
		
		$components = explode('-', $command);
		$from = $components[1];
		$type = self::getAssetType($from);
		
		// If you don't have any of the "from" good, then auto-pass
		$found = false;
		
		if ($type == "good") {
			foreach ($player["goods"] as $g) {
				if ($g["type"] == $components[1]) {
					$found = true;
					break;
				}
			}
		} else if ($type == "resource") {
			if ($player[$from] > 0) {
				$found = true;
			}
		} else if ($type == "boat") {
			foreach ($player["ships"] as $s) {
				if ($s["type"] == $components[1]) {
					$found = true;
					break;
				}
			}
		} else {
			throw new BgaVisibleSystemException( "Invalid asset type: $command" );
		}
		
		if (! $found) {
			$this->actPass( false );
		}
	}
	
	function stHuntRoll() {
		// if come from "goBack", do nothing here
		if ($this->getGameStateValue('goBack')) {
			$this->setGameStateValue('goBack', 0);
			$this->gamestate->nextState( '' );
		}
		$command = $this->getString( 'currentCommand' );
		$this->incGameStateValue('num_rolls', 1);
		
		$components = explode('-', $command);
		$hunt_type = $components[1];
		$dice_types = [
			"game" => "d8",
			"snare" => "d8",
			"whales" => "d12",
			"whalesminor" => "d12",
			"raid" => "d8",
			"pillage" => "d12",
			"elk" => "d8",
			"fish" => "d8",
			"game2" => "d8",
			"theft" => "d8",
		];
		$dice_type = $dice_types[$hunt_type];
		
		if ($dice_type == "d8") {
			$value = bga_rand(1, 8);
		} else if ($dice_type == "d12") {
			$value = bga_rand(1, 12);
		} else {
			throw new BgaVisibleSystemException( "Invalid hunt type: $command" );
		}
		// Roll the correct die
		$this->setGameStateValue('die_value', $value);
		// Can't undo after rolling
		$this->setCanUndo( 0 );
		
		// Notify the die roll!
		$key = "inject_die_$dice_type";
		$args = [];
		$args[$key] = "[$dice_type]";
		$this->notifyAllPlayers( "message", clienttranslate('${player_name} rolls ${die_icon} ${die_value}'), array(
			'player_id' => $this->getActivePlayerId(),
			'player_name' => $this->getActivePlayerName(),
			'die_value' => $value,
			'die_icon' => [
				'log' => '${' .$key. '}',
				'args' => $args,
			]
		));
		
		$this->gamestate->nextState( '' );
	}
	
	function stSetAllMultiactive() {
		$this->gamestate->setAllPlayersMultiactive();
		
		$players = self::loadPlayersBasicInfos();
		foreach( $players as $player_id => $p ) {
			$this->giveExtraTime( $player_id );
		}
		
		$this->gamestate->nextState( '' );
	}
	
	function stPreIncome() {
		$players = self::loadPlayersBasicInfos();
				
		foreach( $players as $player_id => $p ) {
			if (Occupation::has($player_id, 8)) {
				// Craft Leader
				$condition = FfoPlayer::playerMeeplesCondition( $player_id );
				$craftActions = Action::filterActions('crafting', 'row');
				$craft_meeples = count(FeastForOdin::getObjectList( "SELECT * FROM meeple WHERE action_id IN (" . implode(',', $craftActions) . ") AND location = 'action' AND $condition" ));
				
				if ($craft_meeples >= 5) {
					$player = FfoPlayer::getFullPlayer( $player_id );
					$this->gain($player, "oil", 1, 'occupation-8');
					FfoPlayer::saveFullPlayer($player);
				}
			}
		}
		
		$this->setGameStateValue( 'occ_steward', 0 );
	}
	
	function stIncome() {
		$players = self::loadPlayersBasicInfos();

		foreach( $players as $player_id => $p ) {
			$player = FfoPlayer::getFullPlayer( $player_id );
			
			$needs_save = false;

			if (Occupation::has($player_id, 42)) {
				if ($this->getGameStateValue( 'occ_steward' )) {
					// Gain one income from Steward!
					$needs_save = true;
					$this->gain($player, "coin", 1, 'occupation-42');
				}
			}
			
			foreach ($player["boards"] as $board) {
				// Find the income of this board
				// We can just used the cached value
				// Income won't change until player puts something on the board
				if (isset($board["_income"]) && $board["_income"] > 0) {
					$needs_save = true;
					$player["coin"] += $board["_income"];
					self::notifyAllPlayers( "diffResources", clienttranslate('${player_name} earns ${diff} coin(s) from ${board_name}'), array(
						'player_id' => $player_id,
						'player_name' => $p["player_name"],
						'resource' => "coin",
						'diff' => $board["_income"],
						'source' => 'board-'.$board["index"],
						'board_name' => $this->m_boards[$board["type"]]["name"],
						'i18n' => ['board_name'],
					));
				}
			}
			
			if ($needs_save) {
				$this->updatePlayerScore( $player, false );
				FfoPlayer::saveFullPlayer($player, [ "resources" => 1 ]);
			}
		}
		
		$this->gamestate->nextState( '' );
	}
	
	function stBreeding( $only_player_id = null ) {
		$players = self::loadPlayersBasicInfos();
		
		foreach( $players as $player_id => $p ) {
			if (isset($only_player_id) && $only_player_id != $player_id)
				continue;
			
			$player = FfoPlayer::getFullPlayer( $player_id );
			$needs_save = false;
			
			$animals = ["sheep", "cattle"];

			if (FeastForOdin::$game->isNorwegiansExpansion()) {
				$animals[] = "horse";
				$animals[] = "pig";
			}

			foreach ($animals as $a) {
				// If they have any pregnants, flip them, and gain new ones
				$pregnants_found = 0;
				$normals_found = 0;
				$pigs_found = 0;
				foreach ($player["goods"] as $good) {
					if ($good["type"] == "pregnant$a") {
						// Spend it!
						$pregnants_found++;
					} else if ($good["type"] == $a && $good["type"] !== "pig") {
						$normals_found++;
					} else if ($good["type"] == "pig" && $a == "pig") {
						$pigs_found++;
					}
				}
				
				$data_goods = self::getDataGoods();
				
				$gain = [];
				$newborn_gain = [];
				if ($pregnants_found > 0) {
					$needs_save = true;
					// Spend all, and gain twice that many normals
					$this->spend( $player, "pregnant$a", $pregnants_found, null, false );
					for ($i = 0; $i < $pregnants_found; $i++) {
						$newborn_gain[] = $data_goods[$a];
						
						$gain[] = $data_goods[$a];
						$gain[] = $data_goods[$a];
						
						$player["goods"][] = $data_goods[$a];
						$player["goods"][] = $data_goods[$a];
					}
				} else if ($normals_found >= 2) {
					$needs_save = true;
					// Spend 1, and gain 1 preggers
					$this->spend( $player, $a, 1, null, false );
					$gain[] = $data_goods["pregnant$a"];
					$player["goods"][] = $data_goods["pregnant$a"];
				} else if ($pigs_found >= 2) {
					$needs_save = true;
					// Just gain one pig
					$gain[] = $data_goods[$a];
					$player["goods"][] = $data_goods[$a];
				}
				
				if (count($newborn_gain) > 0) {
					$gains_goods_log = [];
					$gains_goods_args = [
						'i18n' => []
					];
					foreach ($newborn_gain as $g) {
						$key = "inject_good_$g[type]";
						$gains_goods_log[] = '${'.$key.'}';
						$gains_goods_args[$key] = $this->getAssetName( $g["type"] );
						$gains_goods_args["i18n"][] = $key;
						
						if ($g["type"] == "cattle") {
							if (Occupation::has($player_id, 174)) {
								// Bosporus Traveller
								Action::push([
									"setplayer-$player_id", 
									"state-bosporustraveller"
								]);
							}
						}
					}
					self::notifyAllPlayers( "message", clienttranslate('${player_name} receives newborn(s): ${goods_list}'), array(
						'player_id' => $player_id,
						'player_name' => $p["player_name"],
						'goods_list' => [
							'log' => implode(' ', $gains_goods_log),
							'args' => $gains_goods_args
						]
					));
				}
				
				if (count($gain) > 0) {
					self::notifyAllPlayers( "takeGoods", '', array(
						'player_id' => $player_id,
						'goods' => $gain,
						'source' => null,
					));
				}
			}
			
			if ($needs_save) {
				$this->updatePlayerScore( $player, false );
				FfoPlayer::saveFullPlayer($player);
			}
		}
		
		if (! isset($only_player_id)) {
			foreach( $players as $player_id => $p ) {
				// If you have Sober Man, need to ask if you want to activate it
				if (Occupation::has($player_id, 160)) {
					Action::push([
						"setplayer-$player_id", 
						"state-soberman"
					]);
				}
			}
			
			$this->setString("finishedTransition", "finished_breeding");
			$this->gamestate->nextState( '' );
		}
	}
	
	function stPreFeast() {
		$players = self::loadPlayersBasicInfos();
					
		foreach( $players as $player_id => $p ) {
			$player = FfoPlayer::getFullPlayer( $player_id );
			$needs_save = false;
			
			if (Occupation::has($player_id, 165)) {
				if ($this->canAfford($player, "clothing", 1)) {
					$needs_save = true;
					$this->gain($player, "coin", 2, "occupation-165");
				}
			}
			
			if (Occupation::has($player_id, 167)) {
				if ($this->canAfford($player, "spices", 2)) {
					$needs_save = true;
					$this->gain($player, "coin", 2, "occupation-167");
				} else if ($this->canAfford($player, "spices", 1)) {
					$needs_save = true;
					$this->gain($player, "coin", 1, "occupation-167");
				}
			}
			
			if ($needs_save) {
				FfoPlayer::saveFullPlayer($player);
			}
		}

		// foreach( $players as $player_id => $p ) {
		// 	if (true) {
		// 		// if auto passable option set to true for feast
		// 		self::DbQuery("UPDATE player SET passed = 1 WHERE player_id = $player_id");
		// 		$this->gamestate->setPlayerNonMultiactive( $player_id, 'pass' );
		// 		// $this->pass(false);
		// 	}
		// }
	}
	
	function stFeast( $only_player_id = null ) {
		$players = self::loadPlayersBasicInfos();
				
		foreach( $players as $player_id => $p ) {
			if (isset($only_player_id) && $only_player_id != $player_id)
				continue;
			
			$player = FfoPlayer::getFullPlayer( $player_id );
			
			$needs_save = false;
			foreach ($player["boards"] as &$board) {
				// Find the feast board
				if ($board["type"] == "homefeast") {

					$emigration_columns = 0;
					foreach ($player["emigrations"] as $emigration) {
						$emigration_columns += $emigration == "whalingboat" ? 1 : 2;
					}

					FfoPlayer::checkProvisionals( $this, $board, $player_id, $emigration_columns);
					
					$goods_log = [];
					$goods_args = [
						'i18n' => []
					];
					foreach ($board["goods"] as $g) {
						$key = "inject_good_$g[type]";
						$goods_log[] = '${'.$key.'}';
						$goods_args[$key] = $this->getAssetName( $g["type"] );
						$goods_args["i18n"][] = $key;
					}
					if (count($goods_log) > 0) {
						self::notifyAllPlayers( "message", clienttranslate('${player_name} feeds using: ${goods_list}'), array(
							'player_id' => $player_id,
							'player_name' => $p["player_name"],
							'goods_list' => [
								'log' => implode(' ', $goods_log),
								'args' => $goods_args
							]
						));
					}
					
					if ($board["_penalties"] > 0) {
						$needs_save = true;
						$player["penalty"] += $board["_penalties"];
						self::notifyAllPlayers( "diffResources", clienttranslate('${player_name} takes ${diff} Thing penalty token(s) from the feast'), array(
							'player_id' => $player_id,
							'player_name' => $p["player_name"],
							'resource' => "penalty",
							'diff' => $board["_penalties"],
							'source' => 'board-'.$board["index"],
						));
					}
					
					if (Occupation::has($player_id, 157)) {
						$animals = 0;;
						foreach ($board["goods"] as $g) {
							if (in_array($g["type"], ["cattle", "sheep", "pregnantcattle", "pregnantsheep"])) {
								$animals++;
							}
						}
						$this->gain($player, "skinandbones", $animals, "occupation-157");
					}
					
					if (Occupation::has($player_id, 172)) {
						$stockfish = 0;
						foreach ($board["goods"] as $g) {
							if (in_array($g["type"], ["stockfish"])) {
								$stockfish++;
							}
						}
						if ($stockfish > 0) {
							// Allow player to put this stockfish into their houses
							$this->gain($player, "stockfish", $stockfish, "occupation-172");
							$this->setGameStateValue( 'occ_fishcook', $stockfish );
							// ... if they have any houses, that is
							Action::push([
								"setplayer-$player_id", 
								"state-fishcook"
							]);
						}
					}
					
					if (Occupation::has($player_id, 159)) {
						$gamemeat = 0;
						foreach ($board["goods"] as $g) {
							if (in_array($g["type"], ["gamemeat"])) {
								$gamemeat++;
							}
						}
						if ($gamemeat > 0) {
							if (Occupation::has($player_id, 145)) {
								Action::push([
									"setplayer-$player_id",
									"mountain-1",
									"mountainreset",
									"hunt-game-0"
								]);
							} else {
								Action::push([
									"setplayer-$player_id", 
									"hunt-game-0"
								]);
							}
						}
					}
					
					// Clear board
					$board["goods"] = [];
				}
			}
			
			if (Occupation::has($player_id, 7)) {
				// Miller
				$grains = 0;
				foreach ($player["goods"] as $g) {
					if ($g["type"] == "grain") {
						$grains++;
					}
				}
				
				$coins_gain = 0;
				if ($grains >= 2) {
					$coins_gain = 2;
				} else if ($grains >= 1) {
					$coins_gain = 1;
				}
				
				if ($coins_gain > 0) {
					$this->gain($player, "coin", $coins_gain, "occupation-7");
				}
			}
			
			$this->updatePlayerScore( $player, false );
			FfoPlayer::saveFullPlayer($player);
		}
		
		// TODO : Could be done in the next phase, to let players see what other players fed?
		self::notifyAllPlayers( "clearFeastBoards", '', array(
		));
		
		if (! isset($only_player_id)) {
			// If this is the final round, gameEnd instead
			$round = $this->getGameStateValue( 'round' );
			$short_game = $this->isShortGame();
			
			if (($round == 6 && $short_game) || ($round == 7 && ! $short_game)) {
				$this->setString("finishedTransition", "finished_feastGameEnd");
			} else {
				$this->setString("finishedTransition", "finished_feast");
				
			}
			$this->gamestate->nextState( 'done' );
		}
	}

	function stBonus( $only_player_id = null, $boards = null ) {
		$players = self::loadPlayersBasicInfos();
				
		foreach( $players as $player_id => $p ) {
			if (isset($only_player_id) && $only_player_id != $player_id)
				continue;
			
			$player = FfoPlayer::getFullPlayer( $player_id );
			$needs_save = false;
				
			if (Occupation::has($player_id, 161)) {
				$num = count(FfoPlayer::getMeeplesInColumn(3, $player_id));
				if ($num >= 7) {
					$needs_save = true;
					$this->gain($player, "cabbage", 1, "occupation-161");
				}
			}
			
			if (Occupation::has($player_id, 162)) {
				$num = count(FfoPlayer::getMeeplesInColumn(2, $player_id));
				if ($num >= 7) {
					$needs_save = true;
					$this->gain($player, "flax", 1, "occupation-161");
				}
			}
			
			if (Occupation::has($player_id, 41)) {
				// Slowpoke - may buy oil with coin or mead
				Action::push([
					"setplayer-$player_id", 
					"state-slowpoke"
				]);
			}
			
			// Cork shed bonus
			foreach ($player["boards"] as $board) {
				if (isset($boards) && ! in_array($board["type"], $boards))
					continue;
				if (isset($board["_bonus"]) && count($board["_bonus"]) > 0) {
					foreach ($board["_bonus"] as $b) {
						if ($b == "shed") {
							
							// if artisan shed already build
							if (FfoPlayer::hasBuiltArtisanShed($player)) {
								if (FeastForOdin::$game->getGameStateValue( 'num_sheds' ) > 0) {
									Action::push([
										"setplayer-$player_id",
										"build-shed"
									]);
								}
							} else {
								Action::push([
									"setplayer-$player_id", 
									"state-corkshedbonus"
								]);
							}
						}
					}
				}
			}


			foreach ($player["boards"] as $board) {
				if (isset($boards) && ! in_array($board["type"], $boards))
					continue;
				if (isset($board["_bonus"]) && count($board["_bonus"]) > 0) {
					$goods_log = [];
					$goods_args = [
						'i18n' => []
					];
					
					$needs_save = true;
					$data_goods = self::getDataGoods();
					$gain = [];
					// give out longhouse and knarr only if there are 2 respective bonuses since this is two cell bonus (and only Waterford has them)
					// may need a more principated way to account for 2-cell bonuses later but this simple approach works for now
					$longhouseBonusCells = 0;
					$knarrBonusCells = 0;
					$altarBonusCells = 0;
					foreach ($board["_bonus"] as $b) {
						if ($b == "stonehouse") {
							// Newfoundland being awkward!
							if (FeastForOdin::$game->getGameStateValue( 'num_stonehouses' ) > 0) {
								Action::push([
									"setplayer-$player_id",
									"build-stonehouse"
								]);
							}
						} else if ($b == "weapon") {
							// Orkney being awkward! (and Norwegians boards)
							Action::push([
								"setplayer-$player_id",
								"draw-weapon"
							]);
						} else if (in_array($b, ["sword", "spear", "bow", "snare"])) {
							// Norwegians boards contain these
							Action::push([
								"setplayer-$player_id",
								"draw-weapon-$b"
							]);	
						} else if ($b == "whalingboat") {
							// Cork being awkward!
							$boats = 0;
							foreach ($player["ships"] as $s) {
								if ($s["type"] == "whalingboat") {
									$boats++;
								}
							}
							if ($boats < 3) {
								Action::push([
									"setplayer-$player_id",
									"build-whalingboat"
								]);
							}
						} else if ($b == "shed") {
							// Cork shed bonus handled above!
						} else if ($b == "longhouse") {
							// Waterford being awkward!
							$longhouseBonusCells++;
							// give only on second hit (to simulate double sized bonus)
							if ($longhouseBonusCells == 2) {
								if (FeastForOdin::$game->getGameStateValue( 'num_longhouses' ) > 0) {
									Action::push([
										"setplayer-$player_id",
										"build-longhouse"
									]);
								}
							}
						} else if ($b == "knarr") {
							// Waterford being awkward!
							$knarrBonusCells++;
							// give only on second hit (to simulate double sized bonus)
							if ($knarrBonusCells == 2) {
								$boats = 0;
								foreach ($player["ships"] as $s) {
									if ($s["type"] == "knarr" || $s["type"] == "longship") {
										$boats++;
									}
								}

								if ($boats < 4) {
									if (Occupation::has($player_id, 179)) {
										$this->setGameStateValue( 'waterford_bonus_knarr_skip_179_occ', 1);
									}
									Action::push([
										"setplayer-$player_id",
										"build-knarr"
									]);
								}
							}
						} else if ($b == "altar") {
							// IsleOfMull being awkward!
							$altarBonusCells++;
							// give only on second hit (to simulate double sized bonus)
							if ($altarBonusCells == 2) {
								$this->gain($player, "stone", 1);
								if ($this->getGameStateValue('altar_available')) {
									$this->setGameStateValue('altar_available', 0);
									$this->gain($player, "altar", 1);
								}
							}
						} else {
							$type = self::getAssetType($b);
							if ($type == "resource") {
								$player[$b] += 1;
								self::notifyAllPlayers( "diffResources", '', array(
									'player_id' => $player_id,
									'resource' => $b,
									'diff' => 1,
									'source' => 'board-'.$board["index"]
								));
								
								$key = "inject_good_$b";
								$goods_log[] = '${'.$key.'}';
								$goods_args[$key] = $this->getAssetName( $b );
								$goods_args["i18n"][] = $key;
							} else {
								// If the bonus is "cane" from newfoundland, it needs to be available
								if ($b == "cane") {
									$forge = Forge::getAvailable();
									$available = false;
									foreach ($forge as $f) {
										if ($f["name"] == "cane") {
											$available = true;
											$this->saveForge();
											Forge::remove("cane");
											self::notifyAllPlayers( "updateForge", '', array(
													'forge' => Forge::getAvailable(),
												));
											break;
										}
									}
									if (! $available) {
										// Cane has already been taken : (
										continue;
									}
								}
													
								$goodToGiveOut = $data_goods[$b];

								if (in_array($goodToGiveOut, ['berries', 'eggs', 'amber', 'gullgubber'])) {
									// easter egg thing 
								}

								$gain[] = $goodToGiveOut;
								$player["goods"][] = $goodToGiveOut;

								// $gain[] = $data_goods[$b];
								// $player["goods"][] = $data_goods[$b];
								
								$key = "inject_good_$b";
								$goods_log[] = '${'.$key.'}';
								$goods_args[$key] = $this->getAssetName( $b );
								$goods_args["i18n"][] = $key;
							}
						}
					}
					
					if (count($board["_bonus"]) == 1 && $board["category"] == "house" && Occupation::has($player_id, 177)) {
						// Maid
						$asset = $board["_bonus"][0];
						$type = self::getAssetType($asset);
						if ($type == "good" || $type == "resource") {
							Action::push([
								"setplayer-$player_id", 
								"exchange-$asset-silverware:occupation-177"
							]);
						}
					}
					
					if (Occupation::has($player_id, 28)) {
						// Locksmith - may buy 1 oil for 1 silver
						foreach ($gain as $good) {
							if ($good["type"] == "chest" || $good["type"] == "treasurechest") {
								Action::push([
									"setplayer-$player_id", 
									"exchange-coin-oil:occupation-28"
								]);
							}
						}
					}
					
					if (Occupation::has($player_id, 190)) {
						// Bosporus Merchant
						foreach ($gain as $good) {
							if ($good["type"] == "spices") {
								Action::push([
									"setplayer-$player_id", 
									"take-oil:occupation-190"
								]);
							}
						}
					}
					
					if (Occupation::has($player_id, 174)) {
						// Bosporus Traveller
						foreach ($gain as $good) {
							if ($good["type"] == "cattle") {
								Action::push([
									"setplayer-$player_id", 
									"state-bosporustraveller"
								]);
							}
						}
					}
					
					self::notifyAllPlayers( "takeGoods", clienttranslate('${player_name} earns ${goods_list} from ${board_name}'), array(
						'player_id' => $player_id,
						'player_name' => $p["player_name"],
						'goods' => $gain,
						'source' => 'board-'.$board["index"],
						'goods_list' => [
							'log' => implode(' ', $goods_log),
							'args' => $goods_args
						],
						'board_name' => $this->m_boards[$board["type"]]["name"],
						'i18n' => ['board_name'],
					));
				}
			}
			
			if ($needs_save) {
				$this->updatePlayerScore( $player, false );
				FfoPlayer::saveFullPlayer($player);
			}
		}
		
		$this->flushAssetDiffs();
		
		if (! isset($only_player_id)) {
			$this->setString("finishedTransition", "finished_bonus");
			$this->gamestate->nextState( '' );
		}
	}
	
	function stUpdateMountainStrips() {
		$strips = MountainStrip::getAvailable();
		
		// Remove left-most item from each Mountain strip
		$coins_gain = 0;
		foreach ($strips as $s) {
			$elements = explode(",", $s["resources"]);
			if (count($elements) > 0) {
				$type = array_shift($elements);
				if ($type == "coins") {
					$coins_gain += 2;
				} else if ($type == "ore") {
					$coins_gain += 1;
				}
			}
			MountainStrip::save($s["id"], $elements);
		}
		
		if ($coins_gain > 0) {
			$players = self::loadPlayersBasicInfos();
							
			foreach( $players as $player_id => $p ) {
				if (Occupation::has($player_id, 173)) {
					$player = FfoPlayer::getFullPlayer( $player_id );
					$this->gain($player, "coin", $coins_gain, "occupation-173");
					FfoPlayer::saveFullPlayer($player);
				}
			}
		}
		
		// Reveal a new full Mountain strip
		MountainStrip::draw();
			
		// Notify update strips
		self::notifyAllPlayers( "updateMountainStrips", '', array(
			"mountainstrips" => MountainStrip::getAvailable(),
		));
		
		$this->gamestate->nextState( '' );
	}
	
	function stRemovePlacedVikings() {
		// Move action vikings -> Thing
		if ($this->isSoloGame()) {
			// If it's a solo game, only move the previous round's meeples back
			$round = $this->getGameStateValue('round');
			if ($round % 2 == 0) {
				$meeples = array_merge(
					FfoPlayer::meeplesToBenchByColour( 1 ),
					FfoPlayer::meeplesToThingByColour( 0 )
				);
			} else {
				$meeples = array_merge(
					FfoPlayer::meeplesToBenchByColour( 0 ),
					FfoPlayer::meeplesToThingByColour( 1 )
				);
			}
		} else {
			$meeples = FfoPlayer::meeplesToThing( );
		}
		self::notifyAllPlayers( "meepleMove", '', array(
			'meeples' => $meeples,
		));
		$this->gamestate->nextState( '' );
	}
	
	function stNextRound() {
		// Increment round counter
		$round = $this->incGameStateValue( 'round', 1 );
		self::notifyAllPlayers( "setRound", '', array(
			'round' => $round,
		));
		
		$this->gamestate->nextState( '' );
	}
	
	function stTurnExplorationBoards() {
		$code = null;
		
		$round = $this->getGameStateValue( 'round' );
		$short_game = $this->isShortGame();
		if ($short_game) {
			if ($round == 2) $code = 'A';
			else if ($round == 3) $code = 'B';
			else if ($round == 4) $code = 'C';
			else if ($round == 5) $code = 'D';
		} else {
			if ($round == 3) $code = 'A';
			else if ($round == 4) $code = 'B';
			else if ($round == 5) $code = 'C';
			else if ($round == 6) $code = 'D';
		}
		// Turn the appropriate board if it's still available
		// Add coins to the others
		if (isset($code)) {
			Island::flip($code);
			self::notifyAllPlayers( "updateIslands", '', array(
				'islands' => Island::getAvailable(),
			));
		}
		
		$this->gamestate->nextState( '' );
	}
	
	function stGameEndScoring() {
		// Find the winner!
		$player_count = $this->getPlayersNumber();
		if ($player_count > 1) {
			// Starting order of winner...
			try {
				$winner_starting_order = FeastForOdin::getValue("SELECT starting_order FROM player ORDER BY player_score DESC, player_score_aux DESC LIMIT 1");
				$this->setStat( $winner_starting_order, 'starting_position_winner_' . $player_count . 'p' );
			} catch (Exception $e) {}
		}
		$this->gamestate->nextState( '' );
	}
	
	function stCheckSkip() {
		$state = $this->gamestate->state();
		
		$player_id = self::getActivePlayerId();
		$player = FfoPlayer::getFullPlayer( $player_id );
		if ($state['name'] == 'command-hornturner') {
			// No sheep, skip!
			if (! $this->canAfford($player, "sheep", 1)) {
				$this->gamestate->nextState( 'pass' );
			}
		} 
		else if ($state['name'] == 'command-earl') {
			$round = self::getGameStateValue( 'round' );
			if ($this->getGameStateValue( 'num_stonehouses' ) <= 0) {
				$this->gamestate->nextState( 'pass' );
			} else if (! $this->canAfford($player, "coin", $round)) {
				$this->gamestate->nextState( 'pass' );
			}
		} 
		else if ($state['name'] == 'command-hornturner2') {
			// No money, no horn, skip!
			if (! $this->canAfford($player, "coin", 3) || ! Forge::isAvailable("horn")) {
				$this->gamestate->nextState( 'pass' );
			}
		} 
		else if ($state['name'] == 'command-wharfowner') {
			// No longship, skip!
			$found = null;
			foreach ($player["ships"] as $k => $g) {
				if ($g["type"] == "longship") {
					$found = $g;
					break;
				}
			}
			if (! isset($found)) {
				$this->gamestate->nextState( 'pass' );
			}
		} 
		else if ($state['name'] == 'command-fineblacksmith') {
			// No ore + coin, skip!
			if (! $this->canAfford($player, "coin", 1) || ! $this->canAfford($player, "ore", 1) || (! Forge::isAvailable("crucifix") && ! Forge::isAvailable("cane"))) {
				$this->gamestate->nextState( 'pass' );
			}
		} 
		else if ($state['name'] == 'command-shipbuilder') {
			// No whaler + coin, skip!
			if (! $this->canAfford($player, "coin", 2)) {
				$this->gamestate->nextState( 'pass' );
			} else {
				$found = null;
				$num_big = 0;
				foreach ($player["ships"] as $k => $g) {
					if ($g["type"] == "whalingboat") {
						$found = $g;
					} else {
						$num_big++;
					}
				}
				if (! isset($found) || $num_big >= 4) {
					$this->gamestate->nextState( 'pass' );
				}
			}
		} 
		else if ($state['name'] == 'command-privatechef') {
			// No coin, skip!
			if (! $this->canAfford($player, "coin", 4)) {
				$this->gamestate->nextState( 'pass' );
			}
		} 
		else if ($state['name'] == 'command-dragonslayer') {
			// No weps, skip!
			$weps = Weapon::getHand($player_id);
			$count_per_types = [
				"bow" => 0, "snare" => 0, "spear" => 0, "sword" => 0
			];
			foreach ($weps as $w) {
				$count_per_types[$w["type"]]++;
			}
			if ($count_per_types["snare"] < 2 || $count_per_types["spear"] < 2) {
				$this->gamestate->nextState( 'pass' );
			}
		} 
		else if ($state['name'] == 'command-fieldfarmer') {
			// No oranges or coins, skip!
			if (! $this->canAfford($player, "coin", 1)) {
				$this->gamestate->nextState( 'pass' );
			} else {
				$found = null;
				foreach ($player["goods"] as $g) {
					if ($g["colour"] == "orange") {
						$found = $g;
						break;
					}
				}
				if (! isset($found)) {
					$this->gamestate->nextState( 'pass' );
				}
			}
		} 
		else if ($state['name'] == 'command-cowherd') {
			if (! $this->canAfford($player, "coin", 2) ||
				! $this->canAfford($player, "grain", 1)) {
				$this->gamestate->nextState( 'pass' );
			}
		} 
		else if ($state['name'] == 'command-bonecollector') {
			if (! $this->canAfford($player, "coin", 1)) {
				$this->gamestate->nextState( 'pass' );
			}
		}
		else if ($state['name'] == 'command-breeder') {
			// Don't use "canAfford" as that includes pregnant
			$found = null;
			foreach ($player["goods"] as $g) {
				if ($g["type"] == "cattle") {
					$found = $g;
					break;
				}
			}
			if (! isset($found)) {
				$this->gamestate->nextState( 'pass' );
			}
		} else if ($state['name'] == 'command-shepherdboy') {
			if (! $this->canAfford($player, "coin", 2)) {
				$this->gamestate->nextState( 'pass' );
			}
		} 
		else if ($state['name'] == 'command-hidebuyer') {
			if (! $this->canAfford($player, "coin", 2)) {
				$this->gamestate->nextState( 'pass' );
			}
		} 
		else if ($state['name'] == 'command-hornblower') {
			if (! $this->canAfford($player, "coin", 1)) {
				$this->gamestate->nextState( 'pass' );
			}
		} 
		else if ($state['name'] == 'command-hornblower2') {
			if (! $this->canAfford($player, "coin", 2)) {
				$this->gamestate->nextState( 'pass' );
			}
		} 
		else if ($state['name'] == 'command-quartermaster') {
			$cost = 8;
			$hand = Weapon::getHand($player_id);
			foreach ($hand as $w) {
				if ($w["type"] == 'sword') {
					$cost -= 1;
				} else if ($w["type"] == 'spear' || $w["type"] == 'bow') {
					$cost -= 0.5;
				}
			}
			if ($cost < 0) {
				$cost = 0;
			}
			if (! $this->canAfford($player, "coin", floor($cost))) {
				$this->gamestate->nextState( 'pass' );
			}
		} 
		else if ($state['name'] == 'command-longshipbuilder') {
			$cost = 8;
			$knarrs = 0;
			foreach ($player["ships"] as $s) {
				// Don't include printed ore
				if ($s["type"] == "knarr") {
					$knarrs++;
				}
			}
			if ($knarrs >= 3) {
				$cost -= 7;
			} else if ($knarrs >= 2) {
				$cost -= 5;
			} else if ($knarrs >= 1) {
				$cost -= 2;
			}
			if (! $this->canAfford($player, "coin", $cost)) {
				$this->gamestate->nextState( 'pass' );
			}
		} 
		else if ($state['name'] == 'command-harborguard') {
			$cost = self::getGameStateValue( 'round' );
			$bigs = 0;
			foreach ($player["ships"] as $s) {
				// Don't include printed ore
				if ($s["type"] == "knarr" || $s["type"] == "longship") {
					$bigs++;
				}
			}
			$cost -= $bigs;
			if ($cost < 0) {
				$cost = 0;
			}
			// Include emigration discounts here?
			if ($bigs == 0/* || ! $this->canAfford($player, "coin", $cost)*/) {
				$this->gamestate->nextState( 'pass' );
			}
		}
		else if ($state['name'] == 'command-swordfighter') {
			$longships_with_ore = 0;
			foreach ($player["ships"] as $s) {
				// Don't include printed ore
				if ($s["type"] == "longship" && $s["ore"] > 0) {
					$longships_with_ore++;
				}
			}
			if ($longships_with_ore == 0) {
				$this->gamestate->nextState( 'pass' );
			}
		}
		else if ($state['name'] == 'command-spicemerchant') {
			if (! $this->canAfford($player, "oil", 2)) {
				$this->gamestate->nextState( 'pass' );
			}
		} 
		else if ($state['name'] == 'command-flaxbaker') {
			if (! $this->canAfford($player, "flax", 1)) {
				$this->gamestate->nextState( 'pass' );
			}
		} 
		else if ($state['name'] == 'command-meatmerchant') {
			if (! $this->canAfford($player, "gamemeat", 1) &&
				! $this->canAfford($player, "whalemeat", 1)) {
				$this->gamestate->nextState( 'pass' );
			}
		}
		else if ($state['name'] == 'command-ironsmith') {
			if (! $this->canAfford($player, "coin", 1)) {
				$this->gamestate->nextState( 'pass' );
			}
		}
		else if ($state['name'] == 'command-silkstitcher') {
			if (! $this->canAfford($player, "coin", 3)) {
				$this->gamestate->nextState( 'pass' );
			}
		}
		else if ($state['name'] == 'command-laborer') {
			if (! $this->canAfford($player, "coin", 2)) {
				$this->gamestate->nextState( 'pass' );
			}
		}
		else if ($state['name'] == 'command-meatcurer') {
			$weps = Weapon::getHand($player_id);
			$snares = 0;
			foreach ($weps as $w) {
				if ($w["type"] == "snare") {
					$snares++;
				}
			}
			if ($snares < 1) {
				$this->gamestate->nextState( 'pass' );
			}
		}
		else if ($state['name'] == 'command-snarespecialist') {
			$weps = Weapon::getHand($player_id);
			$snares = 0;
			foreach ($weps as $w) {
				if ($w["type"] == "snare") {
					$snares++;
				}
			}
			if ($snares < 2) {
				$this->gamestate->nextState( 'pass' );
			}
		}
		else if ($state['name'] == 'command-armedfighter') {
			//$strips = MountainStrip::getAvailable();
			// TODO : If no stone/ore, you can't take!
		}
		else if ($state['name'] == 'command-spicetrader') {
			if (! $this->canAfford($player, "coin", 3)) {
				$this->gamestate->nextState( 'pass' );
			}
		}
		else if ($state['name'] == 'command-emigratepassable') {
			// You need a big boat, and enough cash
			
			$round = self::getGameStateValue( 'round' );
			$cost = $round;
			
			// Patron discount
			if (Occupation::has($player_id, 2)) {
				if ($cost >= 2) {
					$cost--;
				}
			}
			
			if ($player["coin"] < $cost) {
				// no cash
				$this->gamestate->nextState( 'pass' );
				return;
			}
			
			if (self::isShortGame()) {
				$round++;
			}

			$currentMax = $round + 5;
			$emigrated = 0;
			foreach ($player["emigrations"] as $e) {
				$e == 'whalingboat'
					? $emigrated += 1
					: $emigrated += 2;
			}

			if (($currentMax - $emigrated) < 2) {
				$this->gamestate->nextState( 'pass' );
				return;
			}

			$bigs = 0;
			foreach ($player["ships"] as $s) {
				// Don't include printed ore
				if ($s["type"] == "knarr" || $s["type"] == "longship") {
					$bigs++;
				}
			}
			
			if ($bigs == 0) {
				// no ships
				$this->gamestate->nextState( 'pass' );
				return;
			}
		}
		else if ($state['name'] == 'command-deerstalker') {
			if (! $this->canAfford($player, "coin", 2)) {
				$this->gamestate->nextState( 'pass' );
			}
		}
		else if ($state['name'] == 'command-sponsor') {
			if (! $this->canAfford($player, "ore", 3)) {
				$this->gamestate->nextState( 'pass' );
			} else {
				if (! $this->canAfford($player, "wood", 4) && ! $this->canAfford($player, "coin", 3)) {
					$this->gamestate->nextState( 'pass' );
				}
			}
		}
		else if ($state['name'] == 'command-slowpoke') {
			if (! $this->canAfford($player, "coin", 1) && ! $this->canAfford($player, "mead", 1)) {
				$this->gamestate->nextState( 'pass' );
			}
		}
		else if ($state['name'] == 'command-bosporustraveller') {
			if (! $this->canAfford($player, "coin", 3)) {
				$this->gamestate->nextState( 'pass' );
			}
		}
		else if ($state['name'] == 'command-emigrateminor') {
			if ($this->getGameStateValue('action_id') == 124) {
				$num_small = 0;
				foreach ($player["ships"] as $s) {
					if ($s["type"] == "whalingboat") {
						$num_small++;
					}
				}
				if ($num_small == 0) {
					$this->gamestate->nextState( 'pass' );
				}
				//TODO: space checked elswhere?
			}
		}
	}
	
	function stBeforeOrAfterPlay() {
		// If you have no occs in hand, then play after!
		$player_id = self::getActivePlayerId();
		$hand = Occupation::getHand($player_id);
			
		if (count($hand) == 0) {
			$this->actSelectMode( 1 );
		}
	}
	
	function stConfirmTurn() {
		if (! $this->getGameStateValue( 'can_undo' ) || ! $this->getGameStateValue( 'undo_supported' )) {
			$this->actAffirm( );
		}
	}
	
	function stExplore() {
		// Check an island is available
		$islands = Island::getAvailable();
		$island_names = array_column($islands, "name");
		$args = self::argExplore();
		$intersect = array_intersect($args['islands'], $island_names);

		if (count($intersect) == 0) {
			throw new BgaUserException( clienttranslate("None of those islands is available") );
		}
	}

	function stSlaughter() {

	}

	function stResources() {

	}

	function stDrawPersonalResource() {
		
	}

	function stDrawPersonalResource2() {
		
	}

	function stBestOfTwoStartingOcc() {
		
	}

	function stDrawTwoBrown() {
		$players = self::getCollectionFromDb("SELECT player_id id FROM player");

		$everyonePickedStartingOcc = true;

		foreach ($players as $p) {

			$player_id = $p['id'];

			$occupation1 = Occupation::draw( $player_id );
					
			self::notifyPlayer( $player_id, "drawOccupation", 'd1', array(
				'player_id' => $player_id,
				'occupation' => $occupation1,
			));
			$occupation2 = Occupation::draw( $player_id );
					
			self::notifyPlayer( $player_id, "drawOccupation", 'd2', array(
				'player_id' => $player_id,
				'occupation' => $occupation2,
			));
			FeastForOdin::$game->saveOccupations( $player_id );
		}

		$this->gamestate->nextState( 'discardOneBrown' );
	}

	function stPreGame() {
		if (FeastForOdin::$game->isBestOfTwoStartingOcc()) { // if harvest mini exp, or a dedicated option for that

			$players = self::getCollectionFromDb("SELECT player_id id FROM player");

			$everyonePickedStartingOcc = true;
	
			foreach ($players as $player) {
				if (count(Occupation::getHand($player['id'])) !== 1) {
					$everyonePickedStartingOcc = false;
					break;
				}
			}
	
			if ($everyonePickedStartingOcc) {
				if (FeastForOdin::$game->hasMiniExpansion2()) {
					$this->gamestate->nextState( 'harvestSetup' );
				} else {
					$this->gamestate->nextState( 'newViking' );
				}
			} else {
				$this->gamestate->setAllPlayersMultiactive();

				$this->gamestate->nextState( 'bestOfTwoStartingOcc' );
			}

		
		} else {
			$this->gamestate->nextState( 'newViking' );
		}
	}
}