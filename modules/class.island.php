<?php

class Island {
	public static function setup($game, $players) {
		$sql = "INSERT INTO available_islands (
			code, name, coins
		) VALUES ";
		
		$islands = array();
		
		if (FeastForOdin::$game->isNorwegiansExpansion()) {
			$islands[] = "('A', 'isleofman', 0)";
			$islands[] = "('B', 'isleofskye', 0)";
			$islands[] = "('C', 'outerhebrides', 0)";
			$islands[] = "('D', 'islay', 0)";
			if (count($players) == 3 || FeastForOdin::$game->isNorwegiansAllIslands()) {
				$islands[] = "('A', 'shetland', 0)";
				$islands[] = "('B', 'faroeislands', 0)";
			}
			if (count($players) == 4 || FeastForOdin::$game->isNorwegiansAllIslands()) {
				$islands[] = "('C', 'iceland', 0)";
				$islands[] = "('D', 'greenland', 0)";
			}
		} else {
			$islands[] = "('A', 'shetland', 0)";
			$islands[] = "('B', 'faroeislands', 0)";
			$islands[] = "('C', 'iceland', 0)";
			$islands[] = "('D', 'greenland', 0)";
		}

		if ($game->hasMiniExpansion1()) {
			$islands[] = "('A', 'lofoten', 0)";
			$islands[] = "(NULL, 'tierradelfuego', 0)";
		}

		if ($game->hasMiniExpansion2()) {
			$islands[] = "('B', 'isleofmull', 0)";
		}
		
		$sql .= implode(',', $islands);
		FeastForOdin::DbQuery( $sql );
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
		
		// Flip the code (and set coins to 0)
		if ($code == 'A') {
			FeastForOdin::DbQuery( "UPDATE available_islands SET coins = 0, code = NULL, `name` = 'bearisland' WHERE name = 'shetland'" );
			FeastForOdin::DbQuery( "UPDATE available_islands SET coins = 0, code = NULL, `name` = 'orkney' WHERE name = 'lofoten'" );
			FeastForOdin::DbQuery( "UPDATE available_islands SET coins = 0, code = NULL, `name` = 'limerick' WHERE name = 'isleofman'" );
		}
		else if ($code == 'B') {
			FeastForOdin::DbQuery( "UPDATE available_islands SET coins = 0, code = NULL, `name` = 'baffinisland' WHERE name = 'faroeislands'" );
			FeastForOdin::DbQuery( "UPDATE available_islands SET coins = 0, code = NULL, `name` = 'wexford' WHERE name = 'isleofskye'" );
			FeastForOdin::DbQuery( "UPDATE available_islands SET coins = 0, code = NULL, `name` = 'caithness' WHERE name = 'isleofmull'" );
		}
		else if ($code == 'C') {
			FeastForOdin::DbQuery( "UPDATE available_islands SET coins = 0, code = NULL, `name` = 'labrador' WHERE name = 'iceland'" );
			FeastForOdin::DbQuery( "UPDATE available_islands SET coins = 0, code = NULL, `name` = 'cork' WHERE name = 'outerhebrides'" );
		}
		else if ($code == 'D') {
			FeastForOdin::DbQuery( "UPDATE available_islands SET coins = 0, code = NULL, `name` = 'newfoundland' WHERE name = 'greenland'" );
			FeastForOdin::DbQuery( "UPDATE available_islands SET coins = 0, code = NULL, `name` = 'waterford' WHERE name = 'islay'" );
		}
	}
	
	public static function remove($name) {
		FeastForOdin::DbQuery( "DELETE FROM available_islands WHERE `name` = '$name'" );
	}
}