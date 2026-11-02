<?php

class Island {
	public static function setup($game, $players) {
		$originalAB = [
			['A', 'shetland'],
			['B', 'faroeislands'],
		];
		$originalCD = [
			['C', 'iceland'],
			['D', 'greenland'],
		];

		if (FeastForOdin::$game->isNorwegiansExpansion()) {
			$islands = [
				['A', 'isleofman'],
				['B', 'isleofskye'],
				['C', 'outerhebrides'],
				['D', 'islay'],
			];
			if (count($players) == 4 || FeastForOdin::$game->isNorwegiansAllIslands()) {
				$islands = array_merge($islands, $originalAB, $originalCD);
			} else if (count($players) == 3) {
				$islands[] = $originalAB[bga_rand(0, 1)];
				$islands[] = $originalCD[bga_rand(0, 1)];
			}
		} else {
			$islands = array_merge($originalAB, $originalCD);
		}

		if ($game->hasMiniExpansion1()) {
			$islands[] = ['A', 'lofoten'];
			$islands[] = [null, 'tierradelfuego'];
		}

		if ($game->hasMiniExpansion2()) {
			$islands[] = ['B', 'isleofmull'];
		}

		$values = [];
		foreach ($islands as [$code, $name]) {
			$sqlCode = $code === null ? 'NULL' : "'$code'";
			$values[] = "($sqlCode, '$name', 0)";
		}
		FeastForOdin::DbQuery("INSERT INTO available_islands (code, name, coins) VALUES " . implode(',', $values));
	}
	
	public static function getAvailable() {
		$data_boards = FeastForOdin::getDataBoards();
		$islands = FeastForOdin::getObjectList( "SELECT * FROM available_islands ORDER BY code ASC" );
		foreach ($islands as &$island) {
			$island["data"] = $data_boards[$island["name"]];
		}
		return $islands;
	}
	
	public static function flip($code) {
		// Add 2 coins to all islands
		FeastForOdin::DbQuery( "UPDATE available_islands SET coins = coins + 2 WHERE 1" );

		$flips = [
			'A' => ['shetland' => 'bearisland', 'lofoten' => 'orkney', 'isleofman' => 'limerick'],
			'B' => ['faroeislands' => 'baffinisland', 'isleofskye' => 'wexford', 'isleofmull' => 'caithness'],
			'C' => ['iceland' => 'labrador', 'outerhebrides' => 'cork'],
			'D' => ['greenland' => 'newfoundland', 'islay' => 'waterford'],
		];
		if (isset($flips[$code])) {
			foreach ($flips[$code] as $front => $back) {
				FeastForOdin::DbQuery( "UPDATE available_islands SET coins = 0, code = NULL, `name` = '$back' WHERE name = '$front'" );
			}
		}
	}
	
	public static function remove($name) {
		FeastForOdin::DbQuery( "DELETE FROM available_islands WHERE `name` = '$name'" );
	}
}