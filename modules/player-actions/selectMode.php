<?php 

trait SelectMode {
    function selectMode( $mode_id ) {
		self::checkAction( 'selectMode' );

		$player_id = self::getActivePlayerId();
		$player = FfoPlayer::getFullPlayer($player_id);
		
		$state = $this->gamestate->state();

		if ($state['name'] == 'command-beforeorafter-draw') {
			if ($mode_id == 0) {
				Action::unshift(["draw-occupation"]);
			} else {
				self::setGameStateValue( 'pending_draw', 1 );
			}
		}
		else if ($state['name'] == 'command-beforeorafter-play') {
			if ($mode_id == 0) {
				Action::unshift(["play-occupation"]);
			} else {
				self::setGameStateValue( 'pending_play', 1 );
			}
		}
		else if ($state['name'] == 'command-disheartenedwarrior') {
			if ($mode_id == 0) {
				$this->gain($player, "stockfish", 1, 'occupation-104');
			} else if ($mode_id == 1) {
				$this->gain($player, "oil", 1, 'occupation-104');
			} else {
				throw new BgaVisibleSystemException( "Invalid mode ($mode_id) for $state[name]" );
			}
			FfoPlayer::saveFullPlayer($player);
		} 
		else if ($state['name'] == 'command-bosporustraveller') {
			if ($mode_id == 0) {
				if (! $this->canAfford($player, "coin", 3)) {
					throw new BgaUserException( clienttranslate("You cannot afford that action") );
				}
				$this->spend($player, "coin", 3, 'occupation-174');
				$this->gain($player, "silk", 1, 'occupation-174');
			} else if ($mode_id == 1) {
				if (! $this->canAfford($player, "coin", 4)) {
					throw new BgaUserException( clienttranslate("You cannot afford that action") );
				}
				$this->spend($player, "coin", 4, 'occupation-174');
				$this->gain($player, "spices", 1, 'occupation-174');
			} else {
				throw new BgaVisibleSystemException( "Invalid mode ($mode_id) for $state[name]" );
			}
			FfoPlayer::saveFullPlayer($player);
		} 
		else if ($state['name'] == 'command-clerk') {
			// 1. You must have enough knarrs
			$knarrs = 0;
			foreach ($player["ships"] as $s) {
				// Don't include printed ore
				if ($s["type"] == "knarr") {
					$knarrs++;
				}
			}
			if ($knarrs <= $mode_id) {
				throw new BgaVisibleSystemException( "You don't have enough knarrs" );
			}
			
			// 2. You must have enough cash
			if (! self::canAfford( $player, "coin", $mode_id + 1 )) {
				throw new BgaUserException( clienttranslate("You cannot afford that action") );
			}
			$this->spend( $player, "coin", $mode_id + 1, 'occupation-88' );
			
			// 3. Push the commands (as individuals, so you can upgrade the same thing multiple times)
			$new_commands = [];
			for ($i = 0; $i < $mode_id + 1; $i++) {
				$new_commands[] = 'nwupgrade-1-1';
			}
			if (count($new_commands) > 0) {
				Action::unshift( $new_commands );
			}
			
			FfoPlayer::saveFullPlayer($player);
		} 
		else if ($state['name'] == 'command-upgrade') {
			if (Occupation::has($player_id, 102)) {
				if ($mode_id == 0) {
					if (! self::canAfford( $player, "gamemeat", 1 )) {
						throw new BgaUserException( clienttranslate("You cannot afford that action") );
					}
					
					$this->spend( $player, "gamemeat", 1, 'occupation-102' );
					$this->gain($player, "fur", 1, 'occupation-102');
				} else if ($mode_id == 1) {
					if (! self::canAfford( $player, "silk", 1 )) {
						throw new BgaUserException( clienttranslate("You cannot afford that action") );
					}
					
					$this->spend( $player, "silk", 1, 'occupation-102' );
					$this->gain($player, "fur", 1, 'occupation-102');
				} else {
					throw new BgaVisibleSystemException( "Invalid mode ($mode_id) for $state[name]" );
				}
				// 0 = gamemeat => fur
				// 1 = silk => fur
				FfoPlayer::saveFullPlayer($player);
			} else {
				throw new BgaVisibleSystemException( "No modes to select" );
			}
		} 
		else if ($state['name'] == 'command-metalsmith') {
			$player = FfoPlayer::getFullPlayer($player_id);
			
			if ($mode_id == 0) {
				$this->gain($player, "silverware", 1, 'occupation-73');
			} else if ($mode_id == 1) {
				if (! self::canAfford( $player, "coin", 2 )) {
					throw new BgaUserException( clienttranslate("You cannot afford that action") );
				}
				
				if (! Forge::isAvailable("crucifix")) {
					throw new BgaUserException( clienttranslate("That good is not available") );
				}
				$this->saveForge();
				Forge::remove("crucifix");
				self::notifyAllPlayers( "updateForge", '', array(
					'forge' => Forge::getAvailable(),
				));
				
				$this->spend( $player, "coin", 2, 'occupation-73' );
				$this->gain($player, "crucifix", 1, 'occupation-73');
			} else if ($mode_id == 2) {
				if (! self::canAfford( $player, "coin", 5 )) {
					throw new BgaUserException( clienttranslate("You cannot afford that action") );
				}
				
				if (! Forge::isAvailable("grail")) {
					throw new BgaUserException( clienttranslate("That good is not available") );
				}
				$this->saveForge();
				Forge::remove("grail");
				self::notifyAllPlayers( "updateForge", '', array(
					'forge' => Forge::getAvailable(),
				));
				
				$this->spend( $player, "coin", 5, 'occupation-73' );
				$this->gain($player, "grail", 1, 'occupation-73');
			}
			FfoPlayer::saveFullPlayer($player);
		}
		else if ($state['name'] == 'command-meatmerchant') {
			$player = FfoPlayer::getFullPlayer($player_id);
			
			if ($mode_id == 0) {
				if (! self::canAfford( $player, "gamemeat", 1 )) {
					throw new BgaUserException( clienttranslate("You cannot afford that action") );
				}
				
				$this->spend( $player, "gamemeat", 1, 'occupation-96' );
				$this->gain($player, "whalemeat", 1, 'occupation-96');
			} else if ($mode_id == 1) {
				if (! self::canAfford( $player, "whalemeat", 1 )) {
					throw new BgaUserException( clienttranslate("You cannot afford that action") );
				}
				
				$this->spend( $player, "whalemeat", 1, 'occupation-96' );
				$this->gain($player, "gamemeat", 2, 'occupation-96');
			}
			FfoPlayer::saveFullPlayer($player);
		}
		else if ($state['name'] == 'command-peacemaker') {
			$player = FfoPlayer::getFullPlayer($player_id);
			
			if ($mode_id == 0) {
				$this->gain($player, "wood", 1, 'occupation-38');
			} else if ($mode_id == 1) {
				$this->gain($player, "stone", 1, 'occupation-38');
			} else if ($mode_id == 2) {
				$this->gain($player, "ore", 1, 'occupation-38');
			}
			
			FfoPlayer::saveFullPlayer($player);
		}
		else if ($state['name'] == 'command-sower') {
			$player = FfoPlayer::getFullPlayer($player_id);
			
			if ($mode_id == 0) {
				$this->gain($player, "flax", 1, 'occupation-169');
			} else if ($mode_id == 1) {
				$this->gain($player, "grain", 1, 'occupation-169');
			}
			FfoPlayer::saveFullPlayer($player);
		}
		else if ($state['name'] == 'command-hidebuyer') {
			$player = FfoPlayer::getFullPlayer($player_id);
			
			if ($mode_id == 0) {
				if (! self::canAfford( $player, "coin", 2 )) {
					throw new BgaUserException( clienttranslate("You cannot afford that action") );
				}
				$this->spend( $player, "coin", 2, 'occupation-86' );
				$this->gain($player, "hide", 1, 'occupation-86');
			} else if ($mode_id == 1) {
				if (! self::canAfford( $player, "coin", 4 )) {
					throw new BgaUserException( clienttranslate("You cannot afford that action") );
				}
				$this->spend( $player, "coin", 4, 'occupation-86' );
				$this->gain($player, "hide", 2, 'occupation-86');
			} else if ($mode_id == 2) {
				if (! self::canAfford( $player, "coin", 6 )) {
					throw new BgaUserException( clienttranslate("You cannot afford that action") );
				}
				$this->spend( $player, "coin", 6, 'occupation-86' );
				$this->gain($player, "hide", 3, 'occupation-86');
			}
			FfoPlayer::saveFullPlayer($player);
		}
		else if ($state['name'] == 'command-preacher') {
			$player = FfoPlayer::getFullPlayer($player_id);
			
			if ($mode_id == 0) {
				if (! Forge::isAvailable("crucifix")) {
					throw new BgaUserException( clienttranslate("That good is not available") );
				}
				$this->saveForge();
				Forge::remove("crucifix");
				self::notifyAllPlayers( "updateForge", '', array(
					'forge' => Forge::getAvailable(),
				));
				
				$this->gain($player, "crucifix", 1, 'occupation-87');
			} else if ($mode_id == 1) {
				// Mountain strips
				Action::unshift( ["mountain-4", 'mountainreset'] );
			}
			FfoPlayer::saveFullPlayer($player);
		}
		else if ($state['name'] == 'command-preceptor') {
			$player = FfoPlayer::getFullPlayer($player_id);
			
			if ($mode_id == 0) {
				// Draw occupation
				// Must check at this point if you can afford...
				// TODO : Not quite right if you have linked actions.
					// Should linked actions go first?
				$action_id = $this->getGameStateValue('action_id');
				Requirements::check($action_id, $player);
				Action::unshift( ["draw-occupation"] );
			} else if ($mode_id == 1) {
				// Play occupation
				Action::unshift( ["play-occupation"] );
			} else {
				throw new BgaVisibleSystemException( "Invalid mode ($mode_id) for $state[name]" );
			}
		}
		else if ($state['name'] == 'command-lancebearer') {
			$player = FfoPlayer::getFullPlayer($player_id);
			
			if ($mode_id == 0) {
				$this->gain($player, "peas", 1, 'occupation-133');
			} else if ($mode_id == 1) {
				$this->gain($player, "coin", 1, 'occupation-133');
			}
			FfoPlayer::saveFullPlayer($player);
		}
		else if ($state['name'] == 'command-sponsor') {
			$player = FfoPlayer::getFullPlayer($player_id);
			
			if ($mode_id == 0) {
				if (! self::canAfford( $player, "wood", 4 ) || ! self::canAfford( $player, "ore", 3 )) {
					throw new BgaUserException( clienttranslate("You cannot afford that action") );
				}
				$this->spend( $player, "ore", 3, 'occupation-184' );
				$this->spend( $player, "wood", 4, 'occupation-184' );
			} else if ($mode_id == 1) {
				if (! self::canAfford( $player, "coin", 3 ) || ! self::canAfford( $player, "ore", 3 )) {
					throw new BgaUserException( clienttranslate("You cannot afford that action") );
				}
				
				$this->spend( $player, "ore", 3, 'occupation-184' );
				$this->spend( $player, "coin", 3, 'occupation-184' );
			} else {
				// Unknown mode
				throw new BgaVisibleSystemException( "Unknown mode ($mode_id for $state[name])" );
			}
			
			FfoPlayer::addShip( $player, "longship", 3 );
			
			FfoPlayer::saveFullPlayer($player);
		}
		else if ($state['name'] == 'command-slowpoke') {
			$player = FfoPlayer::getFullPlayer($player_id);
			
			if ($mode_id == 0) {
				if (! self::canAfford( $player, "coin", 1 )) {
					throw new BgaUserException( clienttranslate("You cannot afford that action") );
				}
				$this->spend( $player, "coin", 1, 'occupation-41' );
				$this->gain( $player, "oil", 1, 'occupation-41' );
			} else if ($mode_id == 1) {
				if (! self::canAfford( $player, "mead", 1 )) {
					throw new BgaUserException( clienttranslate("You cannot afford that action") );
				}
				$this->spend( $player, "mead", 1, 'occupation-41' );
				$this->gain( $player, "oil", 1, 'occupation-41' );
			} else {
				// Unknown mode
				throw new BgaVisibleSystemException( "Unknown mode ($mode_id for $state[name])" );
			}
			
			FfoPlayer::saveFullPlayer($player);
		}
		else if ($state['name'] == 'command-corkshedbonus') {
			$player = FfoPlayer::getFullPlayer($player_id);
			
			if ($mode_id == 0) {
				if ($this->getGameStateValue( 'num_sheds') <= 0) {
					throw new BgaUserException( clienttranslate("There are no more sheds left") );
				}
				$data_boards = self::getDataBoards();
				FfoPlayer::addBoard($player, $data_boards['shed']);
			} else if ($mode_id == 1) {
				if (FfoPlayer::hasBuiltArtisanShed($player)) {
					throw new BgaUserException( clienttranslate("You have already built your artisan shed") );
				}
				$data_boards = self::getDataBoards();
				$shed = explode("-", $player['artisan_shed'])[0];
				FfoPlayer::addBoard($player, $data_boards[$shed]);
			} else if ($mode_id == 2) {
				if (FfoPlayer::hasBuiltArtisanShed($player)) {
					throw new BgaUserException( clienttranslate("You have already built your artisan shed") );
				}
				$data_boards = self::getDataBoards();
				$shed = explode("-", $player['artisan_shed'])[1];
				FfoPlayer::addBoard($player, $data_boards[$shed]);
			} else {
				// Unknown mode
				throw new BgaVisibleSystemException( "Unknown mode ($mode_id for $state[name])" );
			}
			
			FfoPlayer::saveFullPlayer($player);
		}
		else if ($state['name'] == 'command-pickmodal') {

			$action_id = $this->getGameStateValue('action_id');
			$action = $this->getCurrentAction();
			
			if ($mode_id < 0 || $mode_id >= count($action)) {
				throw new BgaVisibleSystemException( "That mode ($mode_id) is not available" );
			}

			Requirements::check($action_id, $player, $mode_id);

			// Pick the mode!
			$mode = $action[$mode_id];
			
			// Pay costs + push commands
			$cost = $mode->cost;
			if (count($cost) > 0) {
				foreach ($cost as $asset => $amount) {
					$this->spend( $player, $asset, $amount, 'action-'.$action_id );
				}
				FfoPlayer::saveFullPlayer($player);
			}
			$before_action = [];
			$after_action = [];

			if ($this->getGameStateValue('takeImmediateActionToContinue')) {
				$this->setGameStateValue('takeImmediateActionToContinue', 0);
				$before_action[] = 'state-takeImmediateAction';
			}

			// TODO : These should go to linkedactions
			
			// Occupation Actions - After
			if (in_array($action_id, [60])) {
				// Raiding/pillaging/plundering
				if (Occupation::has($player_id, 12)) {
					// Immediately before using longship, draw a weapon
					$before_action[] = "draw-weapon:occupation-12";
				}
			}
			if (in_array($action_id, [53, 54, 121])) {
				// Overseas trading
				if (Occupation::has($player_id, 88)) {
					// Clerk
					$before_action[] = 'state-clerk';
				}
				if (Occupation::has($player_id, 36)) {
					// Ship's cook
					// Only for saltmeat, gamemeat or whalemeat
					$before_action[] = 'meatupgrade-1-1';
				}
				if (Occupation::has($player_id, 37)) {
					// Foreign trader
					$before_action[] = 'upgrade-1-1';
				}
				if ($action_id == 54 && Occupation::has($player_id, 109)) {
					// Master Count
					$after_action[] = 'takeper-coin-knarr:occupation-109';
				}
				if ($action_id == 54 && Occupation::has($player_id, 110)) {
					// Emigration (if you can afford it!)
					$after_action[] = 'emigratepassable';
				}
				if (Occupation::has($player_id, 149)) {
					// Priest
					$before_action[] = 'take-oil:occupation-149';
				}
			}
			
			if (in_array($action_id, [35, 103, 107, 5554]) || ($action_id == 38 && $mode_id == 1)) {
				// Exchanged ore
				if (Occupation::has($player_id, 103)) {
					// Forest blacksmith
					$before_action[] = 'mountain-1';
					$before_action[] = 'mountainreset';
				}
				if (Occupation::has($player_id, 108) && $action_id != 5554) {
					// Ironsmith
					$after_action[] = 'state-ironsmith';
				}
			}

			if (in_array($action_id, Action::filterActions('crafting', 'row'))) {
				if (Occupation::has($player_id, 111)) {
					// Artist
					if ($action_id == 39) {
						$after_action[] = 'take-coin-2:occupation-111';
					} else if ($action_id == 103 || $action_id == 107) {
						$after_action[] = 'take-coin-3:occupation-111';
					} else {
						$after_action[] = 'take-coin-1:occupation-111';
					}
				}
			}
			
			if (in_array($action_id, Action::filterActions('crafting', 'row'))) {
				// Crafting
				if (Occupation::has($player_id, 112)) {
					// Barkeep
					$after_action[] = 'take-mead:occupation-112';
				}
				if (Occupation::has($player_id, 114)) {
					// Silk Stitcher
					$after_action[] = 'state-silkstitcher';
				}
			}
			
			if (in_array($action_id, Action::filterActions('houses', 'row'))) {
				// Build houses
				if (Occupation::has($player_id, 115)) {
					// Princess
					$after_action[] = 'take-silverware:occupation-115';
				}
			}
			
			if (in_array($action_id, [4, 6, 7, 78]) || (in_array($action_id, [90, 91]) && $mode_id == 1)) {
				// Build knarr/longship
				if (Occupation::has($player_id, 117)) {
					// Shipowner
					$before_action[] = 'upgrade-1-1';
				}
			}
			
			if (in_array($action_id, [6, 78]) || (in_array($action_id, [4, 90]) && $mode_id == 1)) {
				// Build knarr
				if (Occupation::has($player_id, 148)) {
					// Spice trader
					$after_action[] = 'state-spicetrader';
				}
			}
			
			if (in_array($action_id, [30, 34, 98])) {
				// Gain exactly 2 silver
				if (Occupation::has($player_id, 124)) {
					// Laborer
					$after_action[] = 'state-laborer';
				}
			}
			
			if (in_array($action_id, Action::filterActions('weekly', 'row'))) {
				// Weekly market
				if (Occupation::has($player_id, 118)) {
					// Messenger
					$after_action[] = 'take-stone:occupation-118';
					$after_action[] = 'take-mead:occupation-118';
				}
				if (Occupation::has($player_id, 119)) {
					// Milkman
					$after_action[] = 'take-milk:occupation-119';
				}
				if (Occupation::has($player_id, 120)) {
					// Middleman
					$after_action[] = 'payupgrade-1-1';
				}
			}
			
			if (in_array($action_id, [45, 46])) {
				// Mountains + upgrade
				if (Occupation::has($player_id, 123)) {
					// Oil Seller
					$after_action[] = 'exchange-oil-silverware';
				}
			}
			
			if (in_array($action_id, [9, 10, 82])) {
				// Hunting game
				if (Occupation::has($player_id, 145)) {
					$before_action[] = 'mountain-1';
					$before_action[] = 'mountainreset';
				}
			}
			
			if (in_array($action_id, [13, 17, 26])) {
				// Getting stockfish
				if (Occupation::has($player_id, 175)) {
					$before_action[] = 'exchange-coin-oil';
				}
			}

			if ($action_id % 4 == 0 && $action_id != 4 && $action_id < 1000) {
				if (Occupation::has($player_id, 164)) {
					// Armed Fighter
					$before_action[] = 'state-armedfighter';
				}
			}

			if ($action_id == 20 || $action_id == 22 || ($action_id == 19 && $mode_id == 1)
				|| ($action_id == 86 && $mode_id == 3)) {
				// action 88 is command-anytwoanimals so not here (in selectGoods action)
				// Take cattle
				if (Occupation::has($player_id, 174)) {
					// Bosphorus traveller
					$after_action[] = 'state-bosporustraveller';
				}
			}
			
			if (in_array($action_id, [25, 26, 27, 28, 37, 38, 65, 69, 97, 100, 5553, 107, 126]) && Occupation::has($player_id, 3)) {
				// Draw snare with Furrier
				$after_action[] = 'draw-weapon-snare:occupation-3';
			}
			
			if ((in_array($action_id, [2, 3, 4]) || ( in_array($action_id, [90, 91]) && $mode_id == 0 ) ) && Occupation::has($player_id, 15)) {
				// Gain hide from Cottager
				$after_action[] = 'take-hide:occupation-15';
			}
			
			if (! in_array($action_id, [73, 74, 75, 76])) {
				// Don't do pending_play for clone action
				if (self::getGameStateValue( 'pending_play' )) {
					self::setGameStateValue( 'pending_play', 0 );
					$after_action[] = 'play-occupation';
				}
			}
			
			$actions = array_merge($before_action, $mode->commands, $after_action);
			Action::unshift($actions);
		} 
		else if ($state['name'] == 'command-coinsoroccs') {
			if ($mode_id == 0) {
				Action::push(["take-coin-2"]);
				if (Occupation::has($player_id, 124)) {
					Action::push(["state-laborer"]);
				}
			}
			if ($mode_id == 1) {
				Action::push(["draw-occupation", "draw-occupation", "draw-occupation"]);
			}
		}
		else if ($state['name'] == 'command-furmerchant') {
			if ($mode_id == 0) {
				if (! self::canAfford( $player, "gamemeat", 1 )) {
					throw new BgaUserException( clienttranslate("You cannot afford that action") );
				}
				$this->spend( $player, "gamemeat", 1, 'occupation-102' );
				$this->gain($player, "fur", 1, 'occupation-102');
			} else if ($mode_id == 1) {
				if (! self::canAfford( $player, "silk", 1 )) {
					throw new BgaUserException( clienttranslate("You cannot afford that action") );
				}
				$this->spend( $player, "silk", 1, 'occupation-102' );
				$this->gain($player, "fur", 1, 'occupation-102');
			} else if ($mode_id == 2){
				// Can't undo after drawing (random)
				$this->setCanUndo( 0 );
				Weapon::draw( $player_id, null, 'action-114', 2 );
			} else {
				throw new BgaVisibleSystemException( "Invalid mode ($mode_id) for $state[name]" );
			}
			FfoPlayer::saveFullPlayer($player);
		} 
		else if ($state['name'] == 'command-buywhalingboatfifthcol') {
			if ($mode_id == 0) {
				if (!$this->canAfford($player, 'coin', 1) || !$this->canAfford($player, 'wood', 1)) {
					throw new BgaUserException( clienttranslate("You cannot afford that action") );
				}
				if (self::getAmount($player, 'whalingboat') >= 3) {
					throw new BgaUserException( clienttranslate("You do not have enough room for that boat.") );
				}
				$this->spend( $player, "coin", 1, 'action-5557' );
				$this->spend( $player, "wood", 1, 'action-5557' );

				FfoPlayer::addShip( $player, "whalingboat", 0, 'action-5557' );
			
				$this->setGameStateValue( 'double_action_first_taken', 1);

				FfoPlayer::saveFullPlayer($player);
			}
		}
		else {
			throw new BgaVisibleSystemException( "Unrecognised state ($state[name])" );
		}
		
		$this->gamestate->nextState( 'done' );
	}
}