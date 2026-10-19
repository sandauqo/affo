<?php

class Requirements {
	
	public static function check($action_id, $player, $mode_id = null) {
		
		$player_id = $player["id"];
		if (in_array($action_id, [66, 67, 68, 128])) {
			if (!self::canAffordToEmigrate($player)) {
				throw new BgaUserException( clienttranslate("You cannot afford an emigration") );
			}
            if (!self::hasSpaceToEmigrate($player, 2)) {
				throw new BgaUserException( clienttranslate("You do not have room for an emigration") );
            }
		}
		
		$actions = Action::getModifiedAction($action_id, $player_id);
		
		// If clone, hard-code check that there are meeples and that the slot is available
		if (in_array($action_id, [73, 74, 75, 76])) {
			// Is the action slot available?
			$available_bonus = [
				72 + FeastForOdin::$game->getGameStateValue( 'left_action_column' ),
				72 + FeastForOdin::$game->getGameStateValue( 'right_action_column' )
			];
			if (! in_array($action_id, $available_bonus)) {
				throw new BgaVisibleSystemException( "Action does not exist" );
			}
			
			$column_num = $action_id - 72;
			// Is there a meeple to copy?
			$meeples = FfoPlayer::getMeeplesInColumn($column_num);
			$found = false;
			foreach ($meeples as $m) {
				if ($m["player_id"] != $player_id) {
					$found = true;
					break;
				}
			}
			if(! $found) {
				throw new BgaUserException( clienttranslate("There are no actions in that column to copy") );
			}
		}
		
		if ($action_id == 1) {
			if (FeastForOdin::$game->getGameStateValue( 'num_sheds' ) <= 0) {
				throw new BgaUserException( clienttranslate("There are no sheds left") );
			}
		}
		
		if ($action_id == 2) {
			if (FeastForOdin::$game->getGameStateValue( 'num_stonehouses' ) <= 0) {
				throw new BgaUserException( clienttranslate("There are no stone houses left") );
			}
		}
		
		if ($action_id == 3) {
			if (FeastForOdin::$game->getGameStateValue( 'num_longhouses' ) <= 0) {
				throw new BgaUserException( clienttranslate("There are no long houses left") );
			}
		}
		
		if ($action_id == 6 || $action_id == 7) {
			// Must have room for big boat
			// This will be checked later, but doing it now avoids issues with drawing occs and not being able to undo
			if (FfoPlayer::countBigBoats($player) >= 4) {
				throw new BgaUserException( clienttranslate("You do not have enough room to buy that boat") );
			}
		}
		if ($action_id == 124) {
			// double action
			$hasLongship = FeastForOdin::getAmount($player, 'longship') > 0;
			if (!$hasLongship && Occupation::has($player_id, 147)) {
				// Steerman
				$hasLongship = FeastForOdin::getAmount($player, 'knarr') > 0;
			}
			$allAvailableIslands = array_column(Island::getAvailable(), "name");
			$eligibleIslands = FfoPlayer::getEligibleIslands($player_id, 0);
			$availableIslands = array_intersect($eligibleIslands, $allAvailableIslands);

			// TODO: account for discount occs ( 2 and 170 )
			$canAffordEmigrate = FeastForOdin::canAfford($player, 'coin', 2);
			// $canAffordEmigrate = FeastForOdin::canAffordToEmigrate($player);
			$hasWhalingBoat = FeastForOdin::getAmount($player, 'whalingboat') > 0;
			$hasSpaceToEmigrate = self::hasSpaceToEmigrate($player, 1);

			$canDoFirst = $hasLongship && count($availableIslands) > 0;
			$canDoSecond = $canAffordEmigrate && $hasWhalingBoat && $hasSpaceToEmigrate;
			if (!$canDoFirst && !$canDoSecond) {
                self::requirementsNotMetExeption();
			}
		}
		if ($action_id == 125) {
			$occsInHand = Occupation::getHand($player_id);
			if (!count($occsInHand)) {
                self::requirementsNotMetExeption();
			}
		}
		if ($action_id == 128) {
			// double action
			//TODO: test with 170 occ (esp if played before-action)
			$hasWhalingBoat = FeastForOdin::getAmount($player, 'whalingboat') > 0;
			$hasRoomToEmigrate1 = self::hasSpaceToEmigrate($player, 1);

			$hasBigBoat = FeastForOdin::getAmount($player, 'bigboat') > 0;
			$canAffordEmigrate = self::canAffordToEmigrate($player);
			$hasRoomToEmigrate2 = self::hasSpaceToEmigrate($player, 2);

            $canDoFirst = $hasWhalingBoat && $hasRoomToEmigrate1;
            $canDoSecond = $hasBigBoat && $canAffordEmigrate && $hasRoomToEmigrate2;
			if (!$canDoFirst && !$canDoSecond) {
                self::requirementsNotMetExeption();
			}
		}
		if ($action_id == 5557) {
			//double action
			$canAffordBoat = FeastForOdin::canAfford($player, 'coin', 1) && FeastForOdin::canAfford($player, 'wood', 1);
			$hasRoomForBoat = FeastForOdin::getAmount($player, 'whalingboat') < 3;

			$hasBoat = FeastForOdin::getAmount($player, 'boat') > 0; // any boat is fine or 1 viking exploration
			$allAvailableIslands = array_column(Island::getAvailable(), "name");
			$eligibleIslands = FfoPlayer::getEligibleIslands($player_id, 1);
			$availableIslands = array_intersect($eligibleIslands, $allAvailableIslands);

			$canDoFirst = $canAffordBoat && $hasRoomForBoat;
			$canDoSecond = ($hasBoat || $canAffordBoat) && count($availableIslands) > 0;
			if (!$canDoFirst && !$canDoSecond) {
                self::requirementsNotMetExeption();
			}
		}

		// Slaughter actions: modes map to animal types
		$slaughterTypes = [85 => ['sheep'], 89 => ['horse', 'cattle'], 93 => ['sheep', 'horse', 'cattle']];
		if (isset($slaughterTypes[$action_id])) {
			$types = $slaughterTypes[$action_id];
			if (isset($mode_id)) {
				$types = isset($types[$mode_id]) ? [$types[$mode_id]] : [];
			}
			$hasAnimal = false;
			foreach ($types as $type) {
				if (FeastForOdin::canAfford($player, $type, 1) || FeastForOdin::canAfford($player, "pregnant$type", 1)) {
					$hasAnimal = true;
					break;
				}
			}
			if (!$hasAnimal) {
				if (count($types) == 1 && $types[0] == 'sheep') {
					throw new BgaUserException( clienttranslate("You do not have any sheep to slaughter!") );
				} else if (count($types) == 1 && $types[0] == 'horse') {
					throw new BgaUserException( clienttranslate("You do not have any horses to slaughter!") );
				} else if (count($types) == 1 && $types[0] == 'cattle') {
					throw new BgaUserException( clienttranslate("You do not have any cattle to slaughter!") );
				}
				throw new BgaUserException( clienttranslate("You do not have any animals to slaughter!") );
			}
		}

		// You must meet the requirements and costs of at least one of the actions
		$met = false;
		if (isset($mode_id)) {
			$actions = [$actions[$mode_id]];
		}

		foreach ($actions as $action) {
			// Reqs/costs
			foreach ($action->cost as $asset => $amt) {
				// Modifier
				if ($asset == "ore" && Occupation::has($player_id, 39)) {
					if (FfoPlayer::hasShipsWithOre($player) && !FeastForOdin::canAfford( $player, "ore", 1 )) {
						FeastForOdin::$game->setGameStateValue('takeImmediateActionToContinue', 1);
					}
				}
				if (! FeastForOdin::canAfford( $player, $asset, $amt )) {
					continue 2;
				}
			}
			foreach ($action->prerequisite as $asset => $amt) {
				// Steersman
				if ($asset == "longship" && Occupation::has($player_id, 147)) {
					$asset = "bigboat";
				}
				if (! FeastForOdin::canAfford( $player, $asset, $amt )) {
					continue 2;
				}
			}
			$met = true;
			break;
		}
		if (!$met) {
			self::requirementsNotMetExeption();
		}
	}
	
    private static function requirementsNotMetExeption() {
        // If an effect occurred before
        if (FeastForOdin::$game->getGameStateValue( 'resolved_effect' )) {
            throw new BgaUserException( clienttranslate("After resolving this effect, you still won't meet the requirements for the action space") );
        } else {
            throw new BgaUserException( clienttranslate("You do not meet the requirements for the action space") );
        }
    }

    private static function hasSpaceToEmigrate($player, $requiredSpace) {
        $round = FeastForOdin::$game->getGameStateValue( 'round' );

        if (FeastForOdin::$game->isShortGame()) {
            $round++;
        }

        $currentMax = $round + 5;

        $emigrated = 0;
        foreach ($player["emigrations"] as $e) {
            $e == 'whalingboat'
                ? $emigrated += 1
                : $emigrated += 2;
        }

        return ($currentMax - $emigrated) >= $requiredSpace;
    }

    private static function canAffordToEmigrate($player) {
        // checks big emigration only
        $cost = FeastForOdin::$game->getGameStateValue( 'round' );
		$player_id = $player["id"];

        // Patron discount
        if (Occupation::has($player_id, 2)) {
            if ($cost >= 2) {
                $cost--;
            }
        }
        
        // Refugee helper discount
        if (Occupation::has($player_id, 170)) {
            $cost -= 2;
            if ($cost < 0) {
                $cost = 0;
            }
        }
        
        return $player["coin"] >= $cost;
    }

}