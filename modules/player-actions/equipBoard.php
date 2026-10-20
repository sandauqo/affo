<?php

trait EquipBoard {
    function equipBoard( $board_index, $asset ) {
		// You can do this at any time, no need for self::checkAction

		$state = $this->gamestate->state();
		
		$player_id = self::getCurrentPlayerId();
		$player = FfoPlayer::getFullPlayer($player_id);
		
		$type = self::getAssetType($asset);

		// Check you have the board (+ give it the resource)
		$found = null;
		foreach ($player["boards"] as $k => &$board) {
			if ($board["index"] == $board_index) {
				$max = 0;
				if ($board["type"] == "shed") {
					$max = 3;
				} else if ($board["type"] == "stonehouse") {
					$max = 1;
				} else if (in_array($board["type"], ["mason", "lumberjack"]) ) {
					$max = 2;
				} else if ($board["type"] == "armoury") {
					$max = 1;
				} else if (in_array($board["type"], ["spinnery", "beekeeper", "pigsty", "icehouse", "hunting"])) {
					$max = 1;
				}

				if (! isset($board[$asset])) {
					$board[$asset] = 0;
				}
				if ($board[$asset] >= $max) {
					throw new BgaVisibleSystemException( "There is no more room on that board" );
				}
				$board[$asset]++;
				
				$found = &$board;
				break;
			}
		}
		if (! isset($found)) {
			throw new BgaVisibleSystemException( "You do not have that board ($board_index)" );
		}
		
		if ($type == "resource") {
			// Check you have resource
			if ($player[$asset] <= 0) {
				throw new BgaUserException( clienttranslate("You do not have any of that resource") );
			}
		} else {
			// Check the user has the good
			$player_goods = &$player["goods"];
			$goodFound = false;
			foreach ($player_goods as $good) {
				if (self::baseGoodType($good["type"]) == self::baseGoodType($asset)) {
					$goodFound = true;
				}
			}
			if (!$goodFound) {
				throw new BgaUserException( "You do not own that good ($asset)" );
			}
		}

		// Spend the resource
		$this->spend($player, $asset, 1, "board-$board[index]", false);
		$this->updatePlayerScore( $player, false );
		
		if (Occupation::has($player_id, 42)) {
			if ($found["type"] == "shed" || $found["type"] == "lumberjack" || $found["type"] == "mason") {
				// Only do this during the income-placegoods phase? 
				// Actually it doesn't really matter since we reset at the start of the phase
				$this->setGameStateValue( 'occ_steward', 1 );
			}
		}
		
		// Notify
		self::notifyAllPlayers( "equipBoard", '', array(
			'player_id' => $player_id,
			'board_index' => $board_index,
			'resource' => $asset,
			'amount' => $board[$asset],
			'equip' => true,
		));
		
		FfoPlayer::saveFullPlayer($player);
	}
}