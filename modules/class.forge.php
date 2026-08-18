<?php

class Forge {
	public static function setup() {
		$sql = "INSERT INTO forge (
			name, cost, strength, tongs
		) VALUES ";
		
		$values = array();
		$values[] = "('necklace', 0, 7, 0)";
		$values[] = "('chesspiece', 2, 9, 0)";
		$values[] = "('belt', 2, 8, 0)";
		$values[] = "('horn', 2, 8, 0)";
		$values[] = "('ravenstaff', 3, 9, 0)";
		$values[] = "('grail', 5, 12, 0)";
		$values[] = "('crown', 1000, 16, 0)";
		$values[] = "('shield', 6, 13, 0)";
		
		$values[] = "('helmet', 1, 8, 1)";
		$values[] = "('crucifix', 2, 8, 1)";
		$values[] = "('hammer', 4, 10, 1)";
		$values[] = "('anchor', 4, 10, 1)";
		$values[] = "('cane', 1, 8, 1)";
		$values[] = "('horseshoe', 2, 9, 1)";
		$values[] = "('axe', 4, 11, 1)";
		
		if (FeastForOdin::$game->isNorwegiansExpansion()) {
			$values[] = "('pan', 2, 8, 1)";
			$values[] = "('scales', 2, 999, 0)";
			$values[] = "('vikingsword', 2, 7, 1)";
			$values[] = "('sickle', 2, 8, 1)";
			$values[] = "('mail', 5, 12, 1)";
			$values[] = "('anvil', 6, 13, 1)";
		}

		if (FeastForOdin::$game->isYuleExpansion()) {
			$values[] = "('tree', 7, 999, 0)";
			$values[] = "('table', 6, 12, 0)";
			$values[] = "('sleigh', 1000, 15, 0)";
			$values[] = "('skates', 0, 6, 0)";
			$values[] = "('skiis', 2, 9, 0)";
		}

		$sql .= implode(',', $values);
		FeastForOdin::DbQuery( $sql );
	}
	
	public static function getAvailable() {
		$items = FeastForOdin::getObjectList( "SELECT * FROM forge" );
		return $items;
	}
	
	public static function isAvailable($name) {
		$items = FeastForOdin::getObjectList( "SELECT * FROM forge WHERE `name` = '$name'" );
		return (count($items) > 0);
	}
	
	public static function remove($name) {
		FeastForOdin::DbQuery( "DELETE FROM forge WHERE `name` = '$name'" );
	}
}