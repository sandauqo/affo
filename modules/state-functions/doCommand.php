<?php

trait DoCommand {
    function doCommand() {
		$players = FeastForOdin::$game->loadPlayersBasicInfos();
		$player_id = self::getActivePlayerId();
		$action_id = $this->getGameStateValue('action_id');
		
		if ($this->isPlayerZombie($player_id)) {
			// Clear the queue, and go straight to the next player!
			FeastForOdin::DbQuery("DELETE FROM command_queue WHERE 1");
		}

		$command = Action::pop(); 

		// TODO : Remove
		// self::notifyAllPlayers( "message", 'Running command: ' . $command, array());
		$nextState = 'next';
		
		if (! isset($command)) {
			// There are no commands left, we are done!
			$this->flushAssetDiffs();
			
			$state = $this->gamestate->state();
			// TODO: Only do this in the action phase?
			// Update player score
			$player = FfoPlayer::getFullPlayer($player_id);
			$this->updatePlayerScore( $player );


			 // deal with pre game occupation play (set flag so it does not get prompted again)
			if ($this->getString('preGameOccupation'.$player_id) !== '1' && self::getGameStateValue( 'usingAnyTimeOccupationOutOfAction')) {
				// if non-norwegians, harvest exp short game
				if (FeastForOdin::$game->hasMiniExpansion2() 
					&& self::isShortGame() 
					&& !FeastForOdin::$game->isNorwegiansExpansion()) {
					$this->setString('preGameOccupation'.$player_id, 1);
				}
			}

			if (self::getGameStateValue( 'usingAnyTimeOccupationOutOfAction')) {
				self::setGameStateValue( 'usingAnyTimeOccupationOutOfAction', 0);
				$this->gamestate->nextState( 'pickAction' );
				return;
			}

			$finished_transition = $this->getString("finishedTransition");
			// TODO : I think this can be deleted
			if (! isset($finished_transition)) $finished_transition = 'finished';

			$this->gamestate->nextState( $finished_transition );
		} 
		else {
			// TODO : Allow override source to be used!
			$parts = explode(':', $command);
			$command = $parts[0];
			$source = count($parts) > 1 ? $parts[1] : "action-$action_id";
			
			// TODO: save "reason" for action here i think?

			// Do the command!
			$this->setString( 'currentCommand', $command );
			$this->setGameStateValue('num_rolls', 0);
			$components = explode('-', $command);
			$primary = $components[0];

			if (in_array($primary, ['pickmodal', 'exchange', 'forge', 'forgebuy', 'mountain', 'trade', 'upgrade', 'flip', 'meatupgrade', 'payupgrade', 'sameupgrade', 'nwupgrade', 'hunt', 'attack', 'emigrate', 'emigratepassable', 'swapwhalingboatknarr', 'buylongshipfifthcol', 'buywhalingboatfifthcol', 'clone', 'explore', 'explorepassable', 'slaughter', 'resources', 'forgeminor', 'emigrateminor', 'emigrateminorpassable', 'anytwoanimals', 'discardocc', 'coinsoroccs', 'armsdealer', 'oncetwiceupgrade', 'doubleaction'])) {
				$nextState = 'command-' . $primary;
				if ($primary == 'pickmodal') {
					// If you played an irreversable occ, you need to check reqs here...
					if (! $this->getGameStateValue( 'can_undo' )) {
						$player = FfoPlayer::getFullPlayer( $player_id );
						try {
							Requirements::check($action_id, $player);
						} catch (Exception $e) {
							$this->setGameStateValue( 'can_pass_override', 1 );
						}
					}
				} else if ($primary == "hunt") {
					if ($components[1] == "snare") {
						if (Occupation::has($player_id, 131)) {
							Action::push(["exchange-stockfish-gamemeat"]);
						}
					} else if ($components[1] == "raid") {
						if (Occupation::has($player_id, 90)) {
							$nextState = 'command-swordfighter';
						}
					} else if ($components[1] == "pillage") {
						if (Occupation::has($player_id, 132)) {
							$nextState = 'command-berserker';
						}
					}
				} else if ($primary == "attack") {
					if ($components[1] == "raid") {
					   if (Occupation::has($player_id, 90)) {
						   $nextState = 'command-swordfighter';
					   }
				   } else if ($components[1] == "pillage") {
					   if (Occupation::has($player_id, 132)) {
						   $nextState = 'command-berserker';
					   }
				   }
				} else if ($primary == "doubleaction") {
					$player = FfoPlayer::getFullPlayer($player_id);
					if ($components[1] == 124) {
						$phase = $components[2];
						if ($phase == 1) {
							Action::unshift(["doubleaction-124-2"]);

							if (self::getAmount($player, 'longship') > 0 || (Occupation::has($player_id, 147) && self::getAmount($player, 'knarr') > 0)) {
								Action::unshift(["explorepassable-0"]);
								$nextState = "command-explorepassable-0";
							} else {
								$nextState = "next";
							}
							$nextState = "next";
						} else {
							$this->getGameStateValue( 'double_action_first_taken') 
								? Action::unshift(["emigrateminorpassable"])
								: Action::unshift(["emigrateminor"]);
							$nextState = "next";
						}
					}
					if ($components[1] == 128) {
						$phase = $components[2];
						if ($phase == 1) {
							if (self::getAmount($player, 'whalingboat') > 0) {
								$nextState = "command-emigrateminorpassable";
							} else {
								$nextState = "next";
							}
							Action::unshift(["doubleaction-128-2"]);
						} else {
							$this->getGameStateValue( 'double_action_first_taken') 
								? Action::unshift(["emigratepassable"])
								: Action::unshift(["emigrate"]);
							$nextState = "next";
						}
					}
					if ($components[1] == 5557) {
						$phase = $components[2];
						if ($phase == 1) {
							if (self::getAmount($player, 'whalingboat') < 3 
								&& $this->canAfford($player, 'coin', 1) 
								&& $this->canAfford($player, 'wood', 1)) {
								Action::push(["doubleaction-5557-2"]);
								$nextState = "command-buywhalingboatfifthcol";
							} else {
								$allAvailableIslands = array_column(Island::getAvailable(), "name");
								$eligibleIslands = FfoPlayer::getEligibleIslands($player_id, 1);
								$availableIslands = array_intersect($eligibleIslands, $allAvailableIslands);
								if (count($availableIslands) > 0) {
									Action::unshift(["explore-1"]);
								}
								$nextState = "next";
							}
						} else {
							$allAvailableIslands = array_column(Island::getAvailable(), "name");
							$eligibleIslands = FfoPlayer::getEligibleIslands($player_id, 1);
							$availableIslands = array_intersect($eligibleIslands, $allAvailableIslands);
							if (count($availableIslands) > 0) {
								$this->getGameStateValue( 'double_action_first_taken') 
									? Action::unshift(["explorepassable-1"])
									: Action::unshift(["explore-1"]);
							}

							$nextState = "next";
						}
					}
				}
			} else if ($primary == 'beforeorafter') {
				// Go to relevant state!
				if ($components[1] == 'draw') {
					// Either way, we need to check requirements here
					$player = FfoPlayer::getFullPlayer($player_id);
					$action_id = $this->getGameStateValue('action_id');
					
					Requirements::check($action_id, $player);
					// TODO : Show a warning if you don't currently meet the reqs?
					$nextState = 'command-beforeorafter-draw';
				} else {
					$nextState = 'command-beforeorafter-play';
				}
			} else if ($primary == 'setaction') {
				$this->setGameStateValue('action_id', $components[1]);
			} else if ($primary == 'linkedactions') {
				$action_id = $this->getGameStateValue('action_id');
				// Most of the 'linked actions' are in the pick modal phase, but some are here because they influence your ability to do the action
				// Occupation Actions - Before
				if (in_array($action_id, Action::filterActions('crafting', 'row'))) {
					// Crafting
					if (Occupation::has($player_id, 113)) {
						// Apprentice Craftsman
						Action::unshift(['mountain-1', 'mountainreset']);
					}
				}
				if (in_array($action_id, array_merge(Action::filterActions('houses', 'row'), Action::filterActions('ships', 'row')))) {
					// Build houses / Build ships
					if (Occupation::has($player_id, 121)) {
						// Carpenter
						Action::unshift(['mountain-1', 'mountainreset']);
					}
				}
				if ($action_id == 4 && Occupation::has($player_id, 164)) {
					// Armed Fighter
					Action::unshift(["state-armedfighter"]);
				}
			} else if ($primary == 'mountainreset') {
				$this->setString("takenStrips", "");
				$player = FfoPlayer::getFullPlayer($player_id);
				$needs_save = false;
				$types = self::getGameStateValue( 'mountainguard_types' );
				$wood_taken = self::getGameStateValue( 'mountain_wood_taken' );
				$silver_taken = self::getGameStateValue( 'mountain_silver_taken' );
				if (Occupation::has($player_id, 122)) {
					$different_types = 0;
					if ($types & 1) {
						$different_types++;
					}
					if ($types & 2) {
						$different_types++;
					}
					if ($types & 4) {
						$different_types++;
					}
					if ($types & 8) {
						$different_types++;
					}
					// Should this be actions queue instead?
					if ($different_types >= 4) {
						$this->gain($player, "chest", 1, "occupation-122");
						$needs_save = true;
					} else if ($different_types >= 3) {
						$this->gain($player, "hide", 1, "occupation-122");
						$needs_save = true;
					} else if ($different_types >= 2) {
						$this->gain($player, "mead", 1, "occupation-122");
						$needs_save = true;
					}
				}
				// Stone Crusher
				if (Occupation::has($player_id, 156)) {
					if ($types & 8) {
						$this->gain($player, "coin", 1, "occupation-156");
						$needs_save = true;
					}
				}
				if ($wood_taken >= 2 && Occupation::has($player_id, 154)) {
					$this->gain($player, "coin", 1, "occupation-154");
					$needs_save = true;
				}
				if ($wood_taken >= 2 && Occupation::has($player_id, 155)) {
					Action::push(["exchange-wood-stockfish"]);
				}
				if ($silver_taken == 2 && Occupation::has($player_id, 124)) {
					Action::push(["state-laborer"]);
				}
				if ($needs_save) {
					FfoPlayer::saveFullPlayer($player);
				}
			} else if ($primary == 'flush') {
				$this->flushAssetDiffs();
			} else if ($primary == 'state') {
				// Unsure if I should really be using this. A bit hamfisted?
				$nextState = 'command-' . $components[1];
			} else if ($primary == 'setplayer') {
				// Should only be used outside of the action phase...
				$this->gamestate->changeActivePlayer( $components[1] );
				if (isset($components[2]) && $components[2] == 'nopass') {
					$this->setCanUndo( 0 );
				}
			} else if ($primary == 'build') {
				$player = FfoPlayer::getFullPlayer($player_id);
				
				// Build stuff!
				$has_built_something = false;
				for ($i = 1; $i < count($components); $i++) {
					$tobuild = $components[$i];
					if (in_array($tobuild, ['shed', 'stonehouse', 'longhouse'])) {
						// Build building
						if ($this->getGameStateValue( 'num_' . $tobuild . 's' ) <= 0) {
							// None left!
							// Don't build...
						} else {
							$this->incGameStateValue( 'num_' . $tobuild . 's', -1 );
							$this->updateBuildingSupply();
							$has_built_something = true;
							$data_boards = self::getDataBoards();
							
							$new_board = FfoPlayer::addBoard($player, $data_boards[$tobuild]);
						}
						
					} else if ($tobuild == 'artisan0' || $tobuild == 'artisan1') {

						$side = (int) filter_var($tobuild, FILTER_SANITIZE_NUMBER_INT);
						$data_boards = self::getDataBoards();
						$player = FfoPlayer::getFullPlayer( $player_id );
						$shed = explode("-", $player['artisan_shed'])[$side];
						$has_built_something = true;
						FfoPlayer::addBoard($player, $data_boards[$shed]);

					} else if ($tobuild == 'whalingboat' || 
					           $tobuild == 'knarr' || 
					           $tobuild == 'longship')
					{
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
						if ($tobuild == "whalingboat") {
							if ($num_small >= 3) {
								// No room!
								throw new BgaUserException( clienttranslate("You do not have enough room to buy that boat") );
							}
						} else {
							if ($num_big >= 4) {
								if (! $has_built_something) {
									// No room!
									throw new BgaUserException( clienttranslate("You do not have enough room to buy that boat") );
								} else {
									continue;
								}
							}
						}
						// Build boat
						FfoPlayer::addShip( $player, $tobuild );
					} else {
						throw new BgaVisibleSystemException( "Cannot build that ($command)" );
					}
				}
				FfoPlayer::saveFullPlayer($player);
			} else if ($primary == 'draw') {
				// Draw a card (weapon or occupation)
				if ($components[1] == 'occupation') {
					// Can't undo after drawing
					$this->setCanUndo( 0 );
					$occupation = Occupation::draw( $player_id );
					
					self::notifyPlayer( $player_id, "drawOccupation", '', array(
						'player_id' => $player_id,
						'occupation' => $occupation,
						'source' => $source,
					));
					unset($occupation["ability"]);
					
					$cards_log = [];
					$cards_args = [
						'i18n' => []
					];
					$starter = $occupation["starter"] ? "true" : "false";
					$key = "inject_occupation_x_$starter";
					$cards_log[] = '${'.$key.'}';
					$cards_args[$key] = clienttranslate('occupation');
					$cards_args["i18n"][] = $key;
					
					self::notifyAllPlayers( "drawOccupation", clienttranslate('${player_name} draws: ${cards_list}'), array(
						'player_id' => $player_id,
						'player_name' => $players[$player_id]["player_name"],
						'ignore_player_id' => $player_id,
						'occupation' => $occupation,
						'cards_list' => [
							'log' => implode(' ', $cards_log),
							'args' => $cards_args
						],
						'source' => $source,
					));
				} else if ($components[1] == 'weapon') {
					$action_id = $this->getGameStateValue('action_id');
					if (Occupation::has($player_id, 123) && $action_id == 114) {
						// Oil Seller
						Action::unshift( ["exchange-oil-silverware"] );
					}
					if (Occupation::has($player_id, 102) && $action_id == 114) {
						// check if Fur Merchant
						$nextState = 'command-furmerchant';
					} else {
						$type = isset($components[2]) ? $components[2] : null;
						if ($type == 'x' || ! isset($type)) {
							$type = null;
							// Can't undo after drawing (random)
							$this->setCanUndo( 0 );
						} else {
							$this->saveWeapons();
						}
						$num = isset($components[3]) ? $components[3] : 1;
						Weapon::draw( $player_id, $type, $source, $num );
					}
				} else {
					throw new BgaVisibleSystemException( "Cannot draw from that deck ($command)" );
				}
			} else if ($primary == 'play') {
				// Play a card (occupation)
				$deck = $components[1];
				if ($deck == "occupation") {
					// Go to "play occupation" state
					$nextState = 'command-playOccupation';
				} else {
					throw new BgaVisibleSystemException( "Cannot play that card ($command)" );
				}
			} else if ($primary == 'take') {
				$player = FfoPlayer::getFullPlayer($player_id);
				$data_goods = self::getDataGoods();
				// Take the resource
				$asset = $components[1];
				$num = isset($components[2]) ? +$components[2] : 1;
				$type = self::getAssetType($asset);
				if ($type == "resource") {
					$this->gain($player, $asset, $num, $source);
					if ($asset == "wood" && $num >= 2) {
						if (Occupation::has($player_id, 154)) {
							// Woodcutter
							Action::push(["take-coin:occupation-154"]);
						}
						if (Occupation::has($player_id, 155)) {
							// Cooper
							Action::push(["exchange-wood-stockfish"]);
						}
					}
				} else if ($type == "good") {
					// Add good(s)
					$this->gain($player, $asset, $num, $source);
					// TODO : Can this go inside the gain function somehow?
					if ($asset == "chest" || $asset == "treasurechest") {
						if (Occupation::has($player_id, 28)) {
							// Locksmith - may buy 1 oil for 1 silver
							for ($i = 0; $i < $num; $i++) {
								Action::unshift(["exchange-coin-oil:occupation-28"]);
							}
						}
					}
					if (Occupation::has($player_id, 150)) {
						if ($asset == "stockfish" && $num == 2) {
							Action::unshift(['exchange-stockfish-gamemeat']);
						}
						if ($asset == "saltmeat" && $num == 2) {
							Action::unshift(['exchange-saltmeat-gamemeat']);
						}
					}

				} else {
					throw new BgaVisibleSystemException( "Cannot take that asset ($command)" );
				}
				FfoPlayer::saveFullPlayer($player);
			} else if ($primary == 'tpp') {
				$player = FfoPlayer::getFullPlayer($player_id);
				$data_goods = self::getDataGoods();
				// Take the resource
				$asset = $components[1];
				$num = count(self::loadPlayersBasicInfos());
				$type = self::getAssetType($asset);
				if ($type == "resource") {
					$this->gain($player, $asset, $num, $source);
					
					if ($asset == "wood" && $num >= 2) {
						if (Occupation::has($player_id, 154)) {
							Action::push(["take-coin:occupation-154"]);
						}
						if (Occupation::has($player_id, 155)) {
							Action::push(["exchange-wood-stockfish"]);
						}
					}
				} else if ($type == "good") {
					// Add good
					$this->gain($player, $asset, $num, $source);
				} else {
					throw new BgaVisibleSystemException( "Cannot tpp that asset ($command)" );
				}
				FfoPlayer::saveFullPlayer($player);
			} else if ($primary == 'takeif') {
				$player = FfoPlayer::getFullPlayer($player_id);
				$data_goods = self::getDataGoods();
				// Take the resource
				$requirement = $components[2];
				$required_amount = isset($components[3]) ? +$components[3] : 1;
				// Check the player has this!
				if (self::canAfford( $player, $requirement, $required_amount )) {
					$asset = $components[1];
					$type = self::getAssetType($asset);
					if ($type == "resource") {
						$this->gain($player, $asset, 1, $source);
					} else if ($type == "good") {
						$this->gain($player, $asset, 1, $source);
					} else {
						throw new BgaVisibleSystemException( "Cannot take that asset ($command)" );
					}
					FfoPlayer::saveFullPlayer($player);
				}
			} else if ($primary == 'takeper') {
				$player = FfoPlayer::getFullPlayer($player_id);
				$data_goods = self::getDataGoods();
				
				// Count the amount...
				$requirement = $components[2];
				$num = self::getAmount( $player, $requirement );
				
				if ($num > 0) {
					$asset = $components[1];
					$type = self::getAssetType($asset);
					if ($type == "resource") {
						$this->gain($player, $asset, $num, $source);
					} else if ($type == "good") {
						$this->gain($player, $asset, $num, $source);
					} else {
						throw new BgaVisibleSystemException( "Cannot take that asset ($command)" );
					}
					FfoPlayer::saveFullPlayer($player);
				}
			} else if ($primary == 'collect') {
				// used only for 29, 31, 97, 99 actions
				$player = FfoPlayer::getFullPlayer($player_id);
				$data_goods = self::getDataGoods();
				// Take the resource
				$asset = $components[1];
				$animal = "";
				if ($asset == "milk") {
					$animal = "cattle";
				} else if ($asset == "wool") {
					$animal = "sheep";
				} else {
					throw new BgaVisibleSystemException( "Collecting unknown resource ($action)" );
				}

				$num = 0;
				foreach ($player["goods"] as $g) {
					if ($g["type"] == $animal || $g["type"] == "pregnant$animal") {
						$num++;
					}
				}
				if ($num > 3) $num = 3;

				if (FeastForOdin::$game->isNorwegiansExpansion()) {
					if ($num == 1) {
						$num = 0;
					} 
					if (str_contains($animal, 'cattle')) {
						$num--;
					}
				}

				// in Norwegians, action is allowed without any requirements
				if ($num > 0) {
					$this->gain($player, $asset, $num, $source);
					FfoPlayer::saveFullPlayer($player);
				}

			} else if ($primary == 'occupation') {
				$ability_id = $components[1];
				$player = FfoPlayer::getFullPlayer($player_id);
				$possible_next_state = Occupation::getNextState( $ability_id );
				if (isset($possible_next_state)) {
					$nextState = $possible_next_state;
				} 
				else if (in_array($ability_id, [178, 180, 181])) {
					Occupation::checkAsSoonAsOccs( $player );
					FfoPlayer::saveFullPlayer( $player );
				}
				else if ($ability_id == 14) {
					// Ship Owner
					if (
						self::canAfford( $player, 'whalingboat', 2 ) &&
						self::canAfford( $player, 'knarr', 2 ) &&
						self::canAfford( $player, 'longship', 2 )
					) {
						// 2 whaling meat
						Action::unshift( [
							"take-whalemeat:occupation-$ability_id",
							"take-whalemeat:occupation-$ability_id",
						] );
					} else if (
						self::canAfford( $player, 'whalingboat', 1 ) &&
						self::canAfford( $player, 'knarr', 1 ) &&
						self::canAfford( $player, 'longship', 1 )
					) {
						// 1 whaling meat
						Action::unshift( [
							"take-whalemeat:occupation-$ability_id",
						] );
					} 
				} 
				else if ($ability_id == 15) {
					// Cottager
					// 2 mead per house
					$houses = 0;
					foreach ($player["boards"] as $b) {
						if ($b["type"] == "longhouse" || $b["type"] == "stonehouse") {
							$houses++;
						}
					}
					
					if ($houses > 0) {
						$meads = $houses * 2;
						Action::unshift( [
							"take-mead-$meads:occupation-$ability_id",
						] );
					}
				} 
				else if ($ability_id == 16) {
					// Captain
					$ore = 0;
					foreach ($player["ships"] as $s) {
						// Don't include printed ore
						$ore += +$s["ore"];
					}
					if ($ore - 2 > 0) {
						$coin = $ore - 2;
						Action::unshift( [
							"take-coin-$coin:occupation-$ability_id",
						] );
					}
				} 
				else if ($ability_id == 18) {
					// Fighter
					$ships = 0;
					foreach ($player["ships"] as $s) {
						// Don't include printed ore
						if ($s["type"] == "longship" && $s["ore"] >= 2) {
							$ships++;
						}
					}
					if ($ships >= 3) {
						Action::unshift( [
							"take-silverhoard:occupation-$ability_id",
						] );
					} else if ($ships >= 2) {
						Action::unshift( [
							"take-jewelry:occupation-$ability_id",
						] );
					} else if ($ships >= 1) {
						Action::unshift( [
							"take-silk:occupation-$ability_id",
						] );
					}
				} 
				else if ($ability_id == 19) {
					// Host
					// Count special tiles on board and in supply...
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
					
					if ($specials >= 5) {
						Action::unshift( [
							"take-jewelry:occupation-$ability_id",
						] );
					} else if ($specials >= 4) {
						Action::unshift( [
							"take-spices:occupation-$ability_id",
						] );
					} else if ($specials >= 3) {
						Action::unshift( [
							"take-silk:occupation-$ability_id",
						] );
					}
				}
				else if ($ability_id == 20) {
					// Builder
					$this->stBonus( $player_id, ["longhouse", "stonehouse"] );
				} 
				else if ($ability_id == 21) {
					// Collector
					$this->stBonus( $player_id, ["home"] );
				} 
				else if ($ability_id == 23) {
					// Custodian
					$houses = 0;
					foreach ($player["boards"] as $b) {
						if ($b["type"] == "longhouse" || $b["type"] == "stonehouse") {
							$houses++;
						}
					}
					
					if ($houses > 0) {
						$coin = $houses * 2;
						Action::unshift( [
							"take-coin-$coin:occupation-$ability_id",
						] );
					}
				}
				else if ($ability_id == 26) {
					// Meat Inspector
					$red_goods = [];
					foreach ($player["goods"] as $g) {
						if ($g["colour"] == "red") {
							$type = $g["type"];
							if ($type == "pregnantsheep") {
								$type = "sheep";
							} else if ($type == "pregnantcattle") {
								$type = "cattle";
							} 
							else if ($type == "pregnanthorse") {
								$type = "horse";
							}
							$red_goods[$type] = 1;
						}
					}
					
					if (count($red_goods) > 0) {
						$coin = count($red_goods);
						Action::unshift( [
							"take-coin-$coin:occupation-$ability_id",
						] );
					}
				} 
				else if ($ability_id == 29) {
					// Meat Preserver
					$gamemeats = 0;
					$whalemeats = 0;
					foreach ($player["goods"] as $g) {
						if ($g["type"] == "gamemeat") {
							$gamemeats++;
						} else if ($g["type"] == "whalemeat") {
							$whalemeats++;
						}
					}
					$pairs = min($gamemeats, $whalemeats);
					
					if ($pairs > 0) {
						Action::unshift( [
							"take-saltmeat-$pairs:occupation-$ability_id",
						] );
					}
				} 
				else if ($ability_id == 30) {
					// Silk Merchant
					$silks = 0;
					foreach ($player["goods"] as $g) {
						if ($g["type"] == "silk") {
							$silks++;
						}
					}
					
					if ($silks >= 3) {
						Action::unshift( [
							"take-coin-6:occupation-$ability_id",
						] );
					} else if ($silks >= 2) {
						Action::unshift( [
							"take-coin-3:occupation-$ability_id",
						] );
					} else if ($silks >= 1) {
						Action::unshift( [
							"take-coin-1:occupation-$ability_id",
						] );
					}
				} 
				else if ($ability_id == 32) {
					// Weapons Supplier
					$ships = 0;
					foreach ($player["ships"] as $s) {
						// Don't include printed ore
						if ($s["type"] == "longship") {
							$ships++;
						}
					}
					if ($ships >= 3) {
						Action::unshift( [
							"draw-weapon-x-10:occupation-$ability_id",
						] );
					} else if ($ships >= 2) {
						Action::unshift( [
							"draw-weapon-x-5:occupation-$ability_id",
						] );
					} else if ($ships >= 1) {
						Action::unshift( [
							"draw-weapon-x-2:occupation-$ability_id",
						] );
					}
				} 
				else if ($ability_id == 35) {
					// Cattle Breeder
					$this->stBreeding( $player_id );
				}
				else if ($ability_id == 69) {
					// Equipment supplier
					// Draw so that you have 2 of each weapon
					$weps = Weapon::getHand($player_id);
					$count_per_types = [
						"bow" => 0, "snare" => 0, "spear" => 0, "sword" => 0
					];
					foreach ($weps as $w) {
						$count_per_types[$w["type"]]++;
					}
					$this->saveWeapons();
					foreach ($count_per_types as $type => $amount) {
						if ($amount < 2) {
							// TODO : Put all weapons in one notification
							Weapon::draw($player_id, $type, 'occupation-69', 2 - $amount);
						}
					}
				}
				else if ($ability_id == 70 || $ability_id == 71) {
					// Courier / Innkeeper
					$tile_name = $ability_id == 70 ? "cane" : "horn";
					// Take the Cloakpin
					if (! Forge::isAvailable($tile_name)) {
						$found = null;
						$players = self::loadPlayersBasicInfos();
						foreach( $players as $pid => $p ) {
							$pp = FfoPlayer::getFullPlayer($pid);
							
							foreach ($pp["boards"] as $k => &$board) {
								foreach ($board["goods"] as $key => $good) {
									if ($good["type"] == $tile_name) {
										$found = $good;
										// Remove this
										unset($board["goods"][$key]);
										$board["goods"] = array_values($board["goods"]);
										
										// Add replacements
										$data_goods = self::getDataGoods();
										if ($tile_name == "cane") {
											$g1 = $data_goods["rune"];
											$g2 = $data_goods["silverware"];
											if ($found["rotation"] == 0) {
												if ($found["flipped"]) {
													$g1["coords"] = [$found["coords"][0], $found["coords"][1]];
													$g1["rotation"] = 1;
													
													$g2["coords"] = [$found["coords"][0] + 1, $found["coords"][1]];
													$g2["rotation"] = 0;
												} else {
													$g1["coords"] = [$found["coords"][0] + 3, $found["coords"][1]];
													$g1["rotation"] = 1;
													
													$g2["coords"] = [$found["coords"][0], $found["coords"][1]];
													$g2["rotation"] = 0;
												}
											} else if ($found["rotation"] == 1) {
												if ($found["flipped"]) {
													$g1["coords"] = [$found["coords"][0], $found["coords"][1] + 3];
													$g1["rotation"] = 0;
													
													$g2["coords"] = [$found["coords"][0], $found["coords"][1]];
													$g2["rotation"] = 1;
												} else {
													$g1["coords"] = [$found["coords"][0], $found["coords"][1]];
													$g1["rotation"] = 0;
													
													$g2["coords"] = [$found["coords"][0], $found["coords"][1] + 1];
													$g2["rotation"] = 1;
												}
											} else if ($found["rotation"] == 2) {
												if ($found["flipped"]) {
													$g1["coords"] = [$found["coords"][0] + 3, $found["coords"][1]];
													$g1["rotation"] = 1;
													
													$g2["coords"] = [$found["coords"][0], $found["coords"][1] + 1];
													$g2["rotation"] = 0;
												} else {
													$g1["coords"] = [$found["coords"][0], $found["coords"][1]];
													$g1["rotation"] = 1;
													
													$g2["coords"] = [$found["coords"][0] + 1, $found["coords"][1] + 1];
													$g2["rotation"] = 0;
												}
											} else if ($found["rotation"] == 3) {
												if ($found["flipped"]) {
													$g1["coords"] = [$found["coords"][0], $found["coords"][1]];
													$g1["rotation"] = 0;
													
													$g2["coords"] = [$found["coords"][0] + 1, $found["coords"][1] + 1];
													$g2["rotation"] = 1;
												} else {
													$g1["coords"] = [$found["coords"][0], $found["coords"][1] + 3];
													$g1["rotation"] = 0;
													
													$g2["coords"] = [$found["coords"][0] + 1, $found["coords"][1]];
													$g2["rotation"] = 1;
												}
											}
											$g1["occ:70"] = 1;
											$g2["occ:70"] = 1;
											$board["goods"][] = $g1;
											$board["goods"][] = $g2;
										} else {
											$g1 = $data_goods["rune"];
											$g2 = $data_goods["chest"];
											if ($found["rotation"] == 0) {
												if ($found["flipped"]) {
													$g1["coords"] = [$found["coords"][0] + 2, $found["coords"][1]];
													$g1["rotation"] = 1;
													
													$g2["coords"] = [$found["coords"][0], $found["coords"][1] + 1];
													$g2["rotation"] = 0;
												} else {
													$g1["coords"] = [$found["coords"][0], $found["coords"][1]];
													$g1["rotation"] = 1;
													
													$g2["coords"] = [$found["coords"][0] + 1, $found["coords"][1] + 1];
													$g2["rotation"] = 0;
												}
											} else if ($found["rotation"] == 1) {
												if ($found["flipped"]) {
													$g1["coords"] = [$found["coords"][0], $found["coords"][1]];
													$g1["rotation"] = 0;
													
													$g2["coords"] = [$found["coords"][0] + 1, $found["coords"][1] + 1];
													$g2["rotation"] = 0;
												} else {
													$g1["coords"] = [$found["coords"][0], $found["coords"][1] + 2];
													$g1["rotation"] = 0;
													
													$g2["coords"] = [$found["coords"][0] + 1, $found["coords"][1]];
													$g2["rotation"] = 0;
												}
											} else if ($found["rotation"] == 2) {
												if ($found["flipped"]) {
													$g1["coords"] = [$found["coords"][0], $found["coords"][1] + 1];
													$g1["rotation"] = 1;
													
													$g2["coords"] = [$found["coords"][0] + 1, $found["coords"][1]];
													$g2["rotation"] = 0;
												} else {
													$g1["coords"] = [$found["coords"][0] + 2, $found["coords"][1] + 1];
													$g1["rotation"] = 1;
													
													$g2["coords"] = [$found["coords"][0], $found["coords"][1]];
													$g2["rotation"] = 0;
												}
											} else if ($found["rotation"] == 3) {
												if ($found["flipped"]) {
													$g1["coords"] = [$found["coords"][0] + 1, $found["coords"][1] + 2];
													$g1["rotation"] = 0;
													
													$g2["coords"] = [$found["coords"][0], $found["coords"][1]];
													$g2["rotation"] = 0;
												} else {
													$g1["coords"] = [$found["coords"][0] + 1, $found["coords"][1]];
													$g1["rotation"] = 0;
													
													$g2["coords"] = [$found["coords"][0], $found["coords"][1] + 1];
													$g2["rotation"] = 0;
												}
											}
											$g1["occ:71"] = 1;
											$g2["occ:71"] = 1;
											$board["goods"][] = $g1;
											$board["goods"][] = $g2;
										}
										
										
										
										self::notifyAllPlayers( "updateBoards", '', array(
											'player_id' => $pid,
											'update' => [
												"board-$board[index]" => [
													$g1,
													$g2,
												]
											],
											'skip_animate' => true,
										));
										
										if ($pid != $player_id) {
											// Save position in case we need to undo...
											// currentwork
											$this->saveStringGlobals();
											$flipped = $found["flipped"] ? 'true' : 'false';
											$this->setString("occ:$ability_id", "$pid-{$found["coords"][0]}-{$found["coords"][1]}-$found[rotation]-{$flipped}-$board[index]");
											FfoPlayer::saveFullPlayer($pp);
										} else {
											$player["boards"] = $pp["boards"];
										}
										break 2;
									}
								}
							}
						}
						
						if ($found) {
							$this->gain($player, $tile_name, 1, "occupation-$ability_id");
							FfoPlayer::saveFullPlayer($player);
						}
					}
				}
				else if ($ability_id == 76) {
					// Homecomer
					// Refund one meeple from each 4th column action
					$actionIds = Action::filterActions(4, 'column');

					$condition = FfoPlayer::playerMeeplesCondition( $player_id );
					$pre_meeples = FeastForOdin::getObjectList( "SELECT action_id FROM meeple WHERE action_id IN (" . implode(',', $actionIds) . ") AND location = 'action' AND $condition GROUP BY action_id" );

					$meeples = [];
					foreach ($pre_meeples as $m) {
						// Return the meeple
						$meeples = array_merge($meeples, FfoPlayer::meepleRefund($player_id, $m["action_id"], 1));
					}
					
					if (count($meeples) > 0) {
						if (count($meeples) <= 2) {
							if (Occupation::has($player_id, 151)) {
								$player = FfoPlayer::getFullPlayer($player_id);
								$this->gain($player, "coin", 1, "occupation-151");
								FfoPlayer::saveFullPlayer($player);
							}
						}
						
						// Notification
						self::notifyAllPlayers( "meepleMove", '', array(
							'meeples' => $meeples,
						));
					}
				} 
				else if ($ability_id == 78) {
					// Traveling Merchant
					$ships = 0;
					foreach ($player["ships"] as $s) {
						// Don't include printed ore
						if ($s["type"] == "knarr") {
							$ships++;
						}
					}
					if ($ships >= 4) {
						Action::unshift( [
							"flip-7-1",
						] );
					} else if ($ships >= 3) {
						Action::unshift( [
							"flip-6-1",
						] );
					} else if ($ships >= 2) {
						Action::unshift( [
							"flip-4-1",
						] );
					} else if ($ships >= 1) {
						Action::unshift( [
							"flip-2-1",
						] );
					}
				}
				else if ($ability_id == 188) {
					// Mineworker
					$players = self::loadPlayersBasicInfos();
					$num_coins = 0;
					if (count($players) >= 4) {
						$num_coins = 3;
					} else if (count($players) >= 3) {
						$num_coins = 2;
					} else if (count($players) >= 2) {
						$num_coins = 1;
					}
					if ($num_coins > 0) {
						$player = FfoPlayer::getFullPlayer($player_id);
							$this->gain($player, "coin", $num_coins, "occupation-188");
							FfoPlayer::saveFullPlayer($player);
					}
				}
				else {
					// Occupation has no effect
					//throw new BgaVisibleSystemException( "Occupation not implemented: $ability_id" );
				}
			} else {
				throw new BgaVisibleSystemException( "Command not implemented: $command" );
			}
			
			// Go to next command:
			$this->gamestate->nextState( $nextState );
		}
	}
}
