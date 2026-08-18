<?php

trait StateArguments {
	function argPickModal() {
		$action_id = $this->getGameStateValue('action_id');
		$action = $this->getCurrentAction();
		
		return array(
			'action_id' => $action_id,
			'num_modes' => count($action),
			'can_pass_override' => $this->getGameStateValue( 'can_pass_override' ),
		);
	}
	function argExchange() {
		$command = $this->getString( 'currentCommand' );
		
		$components = explode('-', $command);
		return array(
			'from' => $components[1],
			'to' => $components[2],
		);
	}
	function argExplore() {
		$player_id = self::getActivePlayerId();
		$command = $this->getString( 'currentCommand' );
		
		$components = explode('-', $command);
		$vikings = $components[1];
		
		// Hard coding the list probably isn't the most sensible
		$islands = [];
		$all_islands = $vikings == 0 ? true : false;
		
		if ($vikings == 1 || $all_islands) {
			$islands[] = 'shetland';
			$islands[] = 'faroeislands';
			$islands[] = 'lofoten';
			$islands[] = 'orkney';
			$islands[] = 'isleofman';
			$islands[] = 'isleofskye';
			$islands[] = 'limerick';
			$islands[] = 'isleofmull';
			
			if (Occupation::has($player_id, 107)) {
				$all_islands = true;
			}
		}
		
		if ($vikings == 2 || $all_islands) {
			$islands[] = 'iceland';
			$islands[] = 'greenland';
			$islands[] = 'bearisland';
			$islands[] = 'wexford';
			$islands[] = 'outerhebrides';
			$islands[] = 'islay';
		}
		
		if ($vikings == 3 || $all_islands) {
			$islands[] = 'tierradelfuego';
			$islands[] = 'baffinisland';
			$islands[] = 'labrador';
			$islands[] = 'newfoundland';
			$islands[] = 'cork';
			$islands[] = 'waterford';
			$islands[] = 'caithness';
		}
		
		return array(
			'islands' => $islands,
		);
	}
	function argHunt() {
		$command = $this->getString( 'currentCommand' );
		$player_id = self::getActivePlayerId();
		$player = FfoPlayer::getFullPlayer($player_id);
		
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
		$resource_types = [
			"game" => "wood",
			"snare" => "wood",
			"whales" => "wood",
			"whalesminor" => "wood",
			"raid" => "stone",
			"pillage" => "stone",
			"elk" => null,
			"fish" => null,
			"game2" => "wood",
			"theft" => null,
		];
		$weapon_types = [
			"game" => ["bow"],
			"snare" => ["snare"],
			"whales" => ["spear"],
			"whalesminor" => ["spear"],
			"raid" => ["sword"],
			"pillage" => ["sword"],
			"elk" => ["snare", "spear", "bow"],
			"fish" => ["snare", "spear"],
			"game2" => ["bow"],
			"theft" => ["sword", "bow", "spear"],
		];
		$description = [
			"game" => clienttranslate('hunting game'),
			"snare" => clienttranslate('laying a snare'),
			"whales" => clienttranslate('whaling'),
			"whalesminor" => clienttranslate('whaling'),
			"raid" => clienttranslate('raiding'),
			"pillage" => clienttranslate('pillaging'),
			"elk" => clienttranslate('elk hunt'),
			"fish" => clienttranslate('catching fish'),
			"game2" => clienttranslate('hunting game'),
			"theft" => clienttranslate('theft'),
		];
		
		$bonus = 0;
		if (in_array($hunt_type, ["game", "game2"]) && Occupation::has($player_id, 13)) {
			$bonus -= 1;
		}
		
		if (in_array($hunt_type, ["game", "game2", "snare"]) && Occupation::has($player_id, 146)) {
			$bonus -= 1;
		}
		
		if (in_array($hunt_type, ["raid", "pillage"]) && Occupation::has($player_id, 136)) {
			$bonus += 1;
		}
		
		$ore = 0;
		$multiplier = -1;
		if ($hunt_type == 'raid' || $hunt_type == 'theft') {
			$multiplier = 1;
		} else if ($hunt_type == 'pillage') {
			// 'ore' from one longship
			$max_ore = 0;
			$ship_id = $this->getGameStateValue( 'occ_berserker' );
			if ($ship_id == 0) {
				foreach ($player["ships"] as $s) {
					if ($s["type"] == 'longship') {
						$o = +$s["ore"];
						if ($o > $max_ore) {
							$max_ore = $o;
						}
					}
				}
			} else {
				foreach ($player["ships"] as $s) {
					if ($s["id"] == $ship_id) {
						$max_ore = +$s["ore"];
						break;
					}
				}
				$bonus += 3;
			}
			$ore = $max_ore;
			$multiplier = 1;
		} else if ($hunt_type == 'whales') {
			// 'ore' from all whalingboats
			foreach ($player["ships"] as $s) {
				if ($s["type"] == 'whalingboat') {
					$ore += +$s["ore"] + 1;
				}
			}
		} else if ($hunt_type == 'whalesminor') {
			// 'ore' from one whalingboat
			$max_ore = 0;
			foreach ($player["ships"] as $s) {
				if ($s["type"] == 'whalingboat') {
					$o = +$s["ore"] + 1;
					if ($o > $max_ore) {
						$max_ore = $o;
					}
				}
			}
			$ore = $max_ore;
		}
		
		if (Occupation::has($player_id, 4)) {
			// Hunter gives -1
			$bonus--;
		}
		
		if ($this->getGameStateValue( 'occ_swordfighter' )) {
			$bonus += 2;
		}
		
		// num_rolls and die_value may not be set yet... come up with a workaround
		// extra state?
		return array(
			'hunt_type' => $hunt_type,
			'hunt_type_desc' => $description[$hunt_type],
			'i18n' => ['hunt_type_desc'],
			'num_rolls' => $this->getGameStateValue('num_rolls'),
			'die_value' => $this->getGameStateValue('die_value'),
			'die_type' => $dice_types[$hunt_type],
			'resource_type' => $resource_types[$hunt_type],
			'weapon_types' => $weapon_types[$hunt_type],
			'ore_value' => $ore,
			'multiplier' => $multiplier,
			'bonus' => $bonus,
		);
	}
	
	function argHuntReward() {
		$command = $this->getString( 'currentCommand' );
		$components = explode('-', $command);
		$hunt_type = $components[1];
		
		return array(
			'hunt_type' => $hunt_type,
			'raid_value' => $this->getGameStateValue('raid_value'),
		);
	}
	
	function argOreBoatman() {
		return array(
			'num' => $this->getGameStateValue('ore_boatman'),
		);
	}
	
	function argFishCook() {
		return array(
			'num' => $this->getGameStateValue('occ_fishcook'),
		);
	}
	
	function argUpgrade() {
		$command = $this->getString( 'currentCommand' );
		
		$components = explode('-', $command);
		$num_tiles = $components[1];
		$num_steps = $components[2];
		$action_id = $this->getGameStateValue('action_id');
		$allowed_types = $this->getString('allowed_upgrade_types');
		if (isset($allowed_types) && $allowed_types) {
			$allowed_types = explode(',', $allowed_types);
		} else {
			$allowed_types = null;
		}
		
		return [
			"num_tiles" => $num_tiles,
			"num_steps" => $num_steps,
			"action_id" => $action_id,
			"allowed_types" => $allowed_types,
		];
	}
	
	function argOnceTwiceUpgrade() {
		$command = $this->getString( 'currentCommand' );
		$components = explode('-', $command);
		$num_tiles_once = $components[1];
		$num_tiles_twice = $components[2];
		$action_id = $this->getGameStateValue('action_id');
		
		return [
			"num_tiles_once" => $num_tiles_once,
			"num_tiles_twice" => $num_tiles_twice,
			"action_id" => $action_id,
		];
	}

	function argMountain() {
		$command = $this->getString( 'currentCommand' );
		
		$components = explode('-', $command);
		$num = $components[1];
		$action_id = $this->getGameStateValue('action_id');
		
		return [
			"num" => $num,
			"action_id" => $action_id,
		];
	}
	
	function argConfirmTurn() {
		return [
			"pending_draw" => $this->getGameStateValue('pending_draw'),
		];
	}


	function argSlaughter() {
		$command = $this->getString( 'currentCommand' );
		
		$components = explode('-', $command);
		$slaughterType = $components[1];
		$action_id = $this->getGameStateValue('action_id');
		
		return [
			"slaughterType" => $slaughterType,
			"action_id" => $action_id,
		];
	}

	function argFurMerchant() {
		$action_id = $this->getGameStateValue('action_id');
		
		return [
			"action_id" => $action_id,
		];
	}

	function argHarvestGoods() {
		
		return array(
			
		);
	}

	function argDrawPersonalResource() {
		
		$markers = FeastForOdin::getDataPersonalResourceMarkers();
		
		$players = self::getCollectionFromDb("SELECT player_id id FROM player");

		$disabled = [];

		foreach($markers as $marker) {
			foreach ($players as $player) {
				$playerMarker = FfoPlayer::getPersonalResourceMarker($player['id']);
				if ($marker === $playerMarker) {
					$disabled[] = $marker;
				}
				$playerMarker2 = FfoPlayer::getSecondPersonalResourceMarker($player['id']);
				if ($marker === $playerMarker2) {
					$disabled[] = $playerMarker2;
				}
			}
		} 

		return array(
			'markers' => $markers,
			'disabled' => $disabled,
		);
	}

	function argBestOfTwoStartingOcc() {
		$player_id = self::getActivePlayerId();

		return array(
			'cards' => Occupation::getHand($player_id),
		);
	}

	function argBuildArtisanShed() {
		$artisan_sheds = FeastForOdin::getDataArtisanSheds();

		$players = self::getCollectionFromDb("SELECT player_id id, boards FROM player");

		$disabled = [];

        foreach ($players as $player) {
			$player['boards'] = json_decode($player['boards'], true);
            $playerShed = FfoPlayer::getArtisanShed($player);

            if ($playerShed) {

				foreach ($artisan_sheds as $shed) {
					$exploded = explode('-', $shed);
					if (in_array($playerShed['type'], $exploded)) {
						$disabled[] = $exploded[0];
						$disabled[] = $exploded[1];
					}
				}
			}
        }

		return array(
			'artisan_sheds' => $artisan_sheds,
			'disabled' => $disabled,
		);
	}

	function argDrawAndDiscardBrown() {
		$player_id = self::getCurrentPlayerId();

		$cards = Occupation::getHand($player_id);
		$brownCardsOnly = [];

		foreach($cards as $card) {
			if ($card['starter'] === '0') {
				$brownCardsOnly[] = $card;
			}
		}

		return array(
			'cards' => $brownCardsOnly,
		);
	}

	function argTEST() {
        return array(
            'nbr' => 2  // In this case ${nbr} in the description will be replaced by "2"
        );    
    }
}