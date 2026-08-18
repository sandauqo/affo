<?php

trait SaveBoards {
    function saveBoards( $request ) {
		// For now - don't check. You can do it whenever you want?
		// self::checkAction( 'saveBoards' );
		
		// Cannot save feast board outside of feast phase
		$state = $this->gamestate->state();
		
		// Current player = request player
		$player_id = self::getCurrentPlayerId();
		$player = FfoPlayer::getFullPlayer($player_id);
		
		$has_master_joiner = Occupation::has($player_id, 40);
		$has_undertaker = Occupation::has($player_id, 100);
		$has_judge = Occupation::has($player_id, 101);
		
		// Place them in the board
		$goods_spend = [];
		$resources_spend = [];
		$boards_to_check = [];
		$gamewhale_spent = 0;
		foreach ($request as $board_index_string => $goods) {
			if ($board_index_string == "pass") {
				// Some magic keywords which are not boards...
				continue;
			}
			$parts = explode("-", $board_index_string);
			$board_index = $parts[1];
			if (! isset($player["boards"][$board_index])) {
				// Invalid board
				throw new BgaVisibleSystemException( "Invalid board update request (invalid board)" );
			}
			if ($player["boards"][$board_index]["type"] == "homefeast" && 
				count($goods) > 0) {
				if (($state['name'] != 'feast-placegoods' && $state['name'] != 'command-personalfeast')) {
					throw new BgaVisibleSystemException( "Cannot place goods on feast board at this time" . $state['name'] );
				}
			}
			foreach ($goods as $good) {
				if ($player["boards"][$board_index]["type"] == "homefeast") {
					if ($good["type"] == "gamemeat" || $good["type"] == "whalemeat") {
						$gamewhale_spent++;
					}
				}
				
				if (! in_array($good["colour"], $player["boards"][$board_index]["allowedColours"])) {
					if ($has_master_joiner && in_array($player["boards"][$board_index]["type"], ["stonehouse", "longhouse"]) && $good["colour"] == "wood") {
						// Allow after all
					} else {
						// Disallowed type
						throw new BgaVisibleSystemException( "Invalid board update request (disallowed type)" );
					}
				}
				if ($good["type"] == "rune") {
					if ($has_undertaker) {
						// Exploration board = + coin
						if ($player["boards"][$board_index]["category"] == "island") {
							$this->gain($player, "coin", 1, 'occupation-100');
						}
					}
					if ($has_judge) {
						// House = + coin + mead
						if (in_array($player["boards"][$board_index]["type"], ["stonehouse", "longhouse"])) {
							$this->gain($player, "coin", 1, 'occupation-101');
							$this->gain($player, "mead", 1, 'occupation-101');
						}
					}
				}
				// Check the good is allowed on this board
				$asset_type = self::getAssetType($good["type"]);
				if ($asset_type == "good") {
					$goods_spend[] = $good["type"];
				} else if ($asset_type == "resource") {
					$resources_spend[] = $good["type"];
					// $goods_spend[] = $good["type"]; //TODO: why was this here
				} else if ($good["type"] === "whalingboat" && $player["boards"][$board_index]["type"] == "whaling") {
					$this->spendBoat($player, $good['id'], 'whalinghut');
				} else {
					// Unrecognised type
					throw new BgaVisibleSystemException( "Invalid board update request (unrecognised asset type)" );
				}
				$player["boards"][$board_index]["goods"][] = $good;
			}
			if (count($goods) > 0) {
				$boards_to_check[] = $board_index;
			}
		}
		
		// Spend goods
		$stockfish_spent = 0;
		foreach ($goods_spend as $good_type) {
			if ($good_type == "stockfish") {
				$stockfish_spent++;
			}
			foreach ($player["goods"] as $k => $good) {
				if ($good["type"] == $good_type) {
					// Spend!
					unset($player["goods"][$k]);
					continue 2;
				}
			}
			throw new BgaVisibleSystemException( "Invalid board update request (insufficient goods)" );
		}
		
		// Spend resources
		$ore_spent = 0;
		foreach ($resources_spend as $resource_type) {
			if ($player[$resource_type] <= 0) {
				throw new BgaVisibleSystemException( "Invalid board update request (insufficient resources)" );
			}
			if ($resource_type == "ore") {
				$ore_spent++;
			}
			$player[$resource_type]--;
		}
		
		if ($ore_spent > 0 && Occupation::has($player_id, 9) && $state['name'] == 'command-oreboatman') {
			self::incGameStateValue( 'ore_boatman', $ore_spent * -1 );
		}
		
		if ($stockfish_spent > 0 && Occupation::has($player_id, 172) && $state['name'] == 'command-fishcook') {
			self::incGameStateValue( 'occ_fishcook', $stockfish_spent * -1 );
		}
		
		if ($gamewhale_spent > 0 && Occupation::has($player_id, 158)) {
			// Gain silver
			$this->gain($player, "coin", $gamewhale_spent, 'occupation-158');
			// Draw weapons
			Weapon::draw($player_id, null, 'occupation-158', 3 * $gamewhale_spent);
		}
		
		// Check boards... quite long to put here
		$incomes = [];
		foreach ($boards_to_check as $board_index) {
			$board = &$player["boards"][$board_index];
			$emigration_columns = 0;
			if ($board["type"] == "homefeast") {
				// $emigration_columns = count($player["emigrations"]) * 2;
				foreach ($player["emigrations"] as $emigration) {
					$emigration_columns += $emigration === "whalingboat" ? 1 : 2;
				}
			}
			FfoPlayer::checkProvisionals( $this, $board, $player_id, $emigration_columns );

			$incomes[$board["index"]] = $board["_income"];
		}
		
		self::notifyAllPlayers( "updateBoards", '', array(
			'player_id' => $player_id,
			'update' => $request,
			'incomes' => $incomes,
		));
		
		$this->updatePlayerScore( $player, false );
		FfoPlayer::saveFullPlayer($player);
	}
}