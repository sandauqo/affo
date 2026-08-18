<?php

class Occupation {
	public static function setup($a, $b, $c, $players, $drawTwoStartingOccs) {
		$sql = "INSERT INTO occupation (
			deck, starter, ability, points, location, player_id
		) VALUES ";
		$values = array();
		
		// 45 starters
		$occupations_starter_a = array(
			['ability' => 8, 'points' => 1, 'deck' => 'a', 'starter' => true, 'ban2' => true],
			['ability' => 34, 'points' => 0, 'deck' => 'a', 'starter' => true],
			['ability' => 41, 'points' => 1, 'deck' => 'a', 'starter' => true, 'ban2' => true],
			['ability' => 47, 'points' => 2, 'deck' => 'a', 'starter' => true],
			['ability' => 52, 'points' => 0, 'deck' => 'a', 'starter' => true, 'ban1' => true],
			['ability' => 89, 'points' => 1, 'deck' => 'a', 'starter' => true],
			['ability' => 113, 'points' => 0, 'deck' => 'a', 'starter' => true],
			['ability' => 137, 'points' => 3, 'deck' => 'a', 'starter' => true],
			['ability' => 145, 'points' => 2, 'deck' => 'a', 'starter' => true],
			['ability' => 153, 'points' => 2, 'deck' => 'a', 'starter' => true],
			['ability' => 154, 'points' => 2, 'deck' => 'a', 'starter' => true],
			['ability' => 160, 'points' => 2, 'deck' => 'a', 'starter' => true],
			['ability' => 163, 'points' => 1, 'deck' => 'a', 'starter' => true, 'ban1' => true],
			['ability' => 170, 'points' => 0, 'deck' => 'a', 'starter' => true, 'ban1' => true],
			['ability' => 174, 'points' => 1, 'deck' => 'a', 'starter' => true, 'ban2' => true]
		); // 15
		$occupations_starter_b = array(
			['ability' => 21, 'points' => 1, 'deck' => 'b', 'starter' => true, 'ban2' => true],
			['ability' => 42, 'points' => 2, 'deck' => 'b', 'starter' => true, 'ban2' => true],
			['ability' => 53, 'points' => 1, 'deck' => 'b', 'starter' => true],
			['ability' => 57, 'points' => 1, 'deck' => 'b', 'starter' => true],
			['ability' => 62, 'points' => 3, 'deck' => 'b', 'starter' => true],
			['ability' => 100, 'points' => -1, 'deck' => 'b', 'starter' => true],
			['ability' => 104, 'points' => 0, 'deck' => 'b', 'starter' => true, 'ban2' => true],
			['ability' => 112, 'points' => 0, 'deck' => 'b', 'starter' => true],
			['ability' => 115, 'points' => 1, 'deck' => 'b', 'starter' => true, 'ban1' => true],
			['ability' => 119, 'points' => 1, 'deck' => 'b', 'starter' => true],
			['ability' => 120, 'points' => 2, 'deck' => 'b', 'starter' => true],
			['ability' => 127, 'points' => 1, 'deck' => 'b', 'starter' => true],
			['ability' => 129, 'points' => 1, 'deck' => 'b', 'starter' => true],
			['ability' => 132, 'points' => 2, 'deck' => 'b', 'starter' => true],
			['ability' => 155, 'points' => 2, 'deck' => 'b', 'starter' => true]
		); // 15
		$occupations_starter_c = array(
			['ability' => 4, 'points' => 0, 'deck' => 'c', 'starter' => true],
			['ability' => 11, 'points' => 1, 'deck' => 'c', 'starter' => true],
			['ability' => 26, 'points' => 0, 'deck' => 'c', 'starter' => true],
			['ability' => 69, 'points' => 1, 'deck' => 'c', 'starter' => true],
			['ability' => 91, 'points' => 0, 'deck' => 'c', 'starter' => true],
			['ability' => 108, 'points' => 2, 'deck' => 'c', 'starter' => true],
			['ability' => 116, 'points' => 2, 'deck' => 'c', 'starter' => true],
			['ability' => 118, 'points' => 0, 'deck' => 'c', 'starter' => true],
			['ability' => 125, 'points' => 0, 'deck' => 'c', 'starter' => true, 'ban1' => true],
			['ability' => 131, 'points' => 1, 'deck' => 'c', 'starter' => true],
			['ability' => 136, 'points' => 2, 'deck' => 'c', 'starter' => true],
			['ability' => 138, 'points' => 1, 'deck' => 'c', 'starter' => true, 'ban2' => true],
			['ability' => 141, 'points' => 2, 'deck' => 'c', 'starter' => true],
			['ability' => 150, 'points' => 0, 'deck' => 'c', 'starter' => true, 'ban1' => true],
			['ability' => 151, 'points' => 3, 'deck' => 'c', 'starter' => true]
		); // 15
		
		// Only merge the relevant decks
		$occupations_starters = array_merge(
			$a ? $occupations_starter_a : [],
			$b ? $occupations_starter_b : [],
			$c ? $occupations_starter_c : []
		);
		foreach ($occupations_starters as $k => $occ) {
			if (self::ignore($occ, $players))
				unset($occupations_starters[$k]);
		}
		
		shuffle($occupations_starters);
		foreach ($players as $player_id => $player) {
			$occ = array_shift($occupations_starters);
			// deck, starter, ability, points, location, player_id
			$s = $occ['starter'] ? 1 : 0;
			$values[] = "('$occ[deck]', $s, $occ[ability], $occ[points], 'hand', $player_id)";

			if ($drawTwoStartingOccs) {
				// draw another one
				$occ = array_shift($occupations_starters);
				$s = $occ['starter'] ? 1 : 0;
				$values[] = "('$occ[deck]', $s, $occ[ability], $occ[points], 'hand', $player_id)";
			}
		}
		
		// 145 normals
		$occupations_a = array(
			['ability' => 1, 'points' => 0, 'deck' => 'a', 'starter' => false, 'ban2' => true],
			['ability' => 3, 'points' => 1, 'deck' => 'a', 'starter' => false],
			['ability' => 5, 'points' => 7, 'deck' => 'a', 'starter' => false],
			['ability' => 7, 'points' => 3, 'deck' => 'a', 'starter' => false],
			['ability' => 9, 'points' => 0, 'deck' => 'a', 'starter' => false],
			['ability' => 10, 'points' => 1, 'deck' => 'a', 'starter' => false],
			['ability' => 12, 'points' => 0, 'deck' => 'a', 'starter' => false],
			['ability' => 17, 'points' => 0, 'deck' => 'a', 'starter' => false],
			['ability' => 20, 'points' => 0, 'deck' => 'a', 'starter' => false],
			['ability' => 22, 'points' => 1, 'deck' => 'a', 'starter' => false],
			['ability' => 23, 'points' => 1, 'deck' => 'a', 'starter' => false],
			['ability' => 24, 'points' => 2, 'deck' => 'a', 'starter' => false],
			['ability' => 27, 'points' => 1, 'deck' => 'a', 'starter' => false],
			['ability' => 32, 'points' => 2, 'deck' => 'a', 'starter' => false],
			['ability' => 33, 'points' => 2, 'deck' => 'a', 'starter' => false],
			['ability' => 35, 'points' => 0, 'deck' => 'a', 'starter' => false],
			['ability' => 36, 'points' => 2, 'deck' => 'a', 'starter' => false],
			['ability' => 37, 'points' => 1, 'deck' => 'a', 'starter' => false],
			['ability' => 38, 'points' => 2, 'deck' => 'a', 'starter' => false],
			['ability' => 43, 'points' => 0, 'deck' => 'a', 'starter' => false],
			['ability' => 48, 'points' => 3, 'deck' => 'a', 'starter' => false],
			['ability' => 50, 'points' => 2, 'deck' => 'a', 'starter' => false],
			['ability' => 54, 'points' => 3, 'deck' => 'a', 'starter' => false],
			['ability' => 56, 'points' => 1, 'deck' => 'a', 'starter' => false, 'ban2' => true],
			['ability' => 58, 'points' => 3, 'deck' => 'a', 'starter' => false],
			['ability' => 59, 'points' => 0, 'deck' => 'a', 'starter' => false],
			['ability' => 64, 'points' => 1, 'deck' => 'a', 'starter' => false],
			['ability' => 65, 'points' => 0, 'deck' => 'a', 'starter' => false],
			['ability' => 66, 'points' => 0, 'deck' => 'a', 'starter' => false],
			['ability' => 68, 'points' => 2, 'deck' => 'a', 'starter' => false],
			['ability' => 74, 'points' => 1, 'deck' => 'a', 'starter' => false],
			['ability' => 76, 'points' => 1, 'deck' => 'a', 'starter' => false],
			['ability' => 77, 'points' => 0, 'deck' => 'a', 'starter' => false],
			['ability' => 79, 'points' => 1, 'deck' => 'a', 'starter' => false],
			['ability' => 83, 'points' => 1, 'deck' => 'a', 'starter' => false],
			['ability' => 86, 'points' => 3, 'deck' => 'a', 'starter' => false],
			['ability' => 87, 'points' => 0, 'deck' => 'a', 'starter' => false],
			['ability' => 94, 'points' => 2, 'deck' => 'a', 'starter' => false],
			['ability' => 95, 'points' => 1, 'deck' => 'a', 'starter' => false],
			['ability' => 96, 'points' => 1, 'deck' => 'a', 'starter' => false, 'ban2' => true],
			['ability' => 98, 'points' => 2, 'deck' => 'a', 'starter' => false],
			['ability' => 99, 'points' => 2, 'deck' => 'a', 'starter' => false, 'ban2' => true],
			['ability' => 117, 'points' => 3, 'deck' => 'a', 'starter' => false],
			['ability' => 122, 'points' => 0, 'deck' => 'a', 'starter' => false],
			['ability' => 126, 'points' => 2, 'deck' => 'a', 'starter' => false],
			['ability' => 128, 'points' => 2, 'deck' => 'a', 'starter' => false],
			['ability' => 134, 'points' => 0, 'deck' => 'a', 'starter' => false, 'ban2' => true],
			['ability' => 135, 'points' => 2, 'deck' => 'a', 'starter' => false, 'ban2' => true],
			['ability' => 140, 'points' => 2, 'deck' => 'a', 'starter' => false],
			['ability' => 147, 'points' => 1, 'deck' => 'a', 'starter' => false],
			['ability' => 149, 'points' => 1, 'deck' => 'a', 'starter' => false],
			['ability' => 152, 'points' => 1, 'deck' => 'a', 'starter' => false],
			['ability' => 158, 'points' => 1, 'deck' => 'a', 'starter' => false, 'ban2' => true],
			['ability' => 164, 'points' => 3, 'deck' => 'a', 'starter' => false],
			['ability' => 165, 'points' => 3, 'deck' => 'a', 'starter' => false],
			['ability' => 171, 'points' => 2, 'deck' => 'a', 'starter' => false],
			['ability' => 186, 'points' => 3, 'deck' => 'a', 'starter' => false],
		); // 57
		$occupations_b = array(
			['ability' => 6, 'points' => 1, 'deck' => 'b', 'starter' => false],
			['ability' => 13, 'points' => 1, 'deck' => 'b', 'starter' => false],
			['ability' => 25, 'points' => 1, 'deck' => 'b', 'starter' => false],
			['ability' => 28, 'points' => 1, 'deck' => 'b', 'starter' => false, 'ban2' => true],
			['ability' => 29, 'points' => 3, 'deck' => 'b', 'starter' => false],
			['ability' => 30, 'points' => 3, 'deck' => 'b', 'starter' => false],
			['ability' => 40, 'points' => 3, 'deck' => 'b', 'starter' => false],
			['ability' => 44, 'points' => 1, 'deck' => 'b', 'starter' => false],
			['ability' => 46, 'points' => 1, 'deck' => 'b', 'starter' => false, 'ban2' => true],
			['ability' => 51, 'points' => 1, 'deck' => 'b', 'starter' => false],
			['ability' => 70, 'points' => 0, 'deck' => 'b', 'starter' => false],
			['ability' => 72, 'points' => 2, 'deck' => 'b', 'starter' => false],
			['ability' => 78, 'points' => 0, 'deck' => 'b', 'starter' => false],
			['ability' => 80, 'points' => 0, 'deck' => 'b', 'starter' => false],
			['ability' => 82, 'points' => 1, 'deck' => 'b', 'starter' => false],
			['ability' => 84, 'points' => 0, 'deck' => 'b', 'starter' => false],
			['ability' => 97, 'points' => 1, 'deck' => 'b', 'starter' => false, 'ban2' => true],
			['ability' => 101, 'points' => 1, 'deck' => 'b', 'starter' => false, 'ban2' => true],
			['ability' => 102, 'points' => 2, 'deck' => 'b', 'starter' => false],
			['ability' => 105, 'points' => 3, 'deck' => 'b', 'starter' => false],
			['ability' => 106, 'points' => -1, 'deck' => 'b', 'starter' => false, 'ban2' => true],
			['ability' => 107, 'points' => 1, 'deck' => 'b', 'starter' => false, 'ban1' => true],
			['ability' => 109, 'points' => 2, 'deck' => 'b', 'starter' => false],
			['ability' => 121, 'points' => 0, 'deck' => 'b', 'starter' => false],
			['ability' => 124, 'points' => 1, 'deck' => 'b', 'starter' => false, 'ban2' => true],
			['ability' => 133, 'points' => 1, 'deck' => 'b', 'starter' => false, 'ban2' => true],
			['ability' => 142, 'points' => 3, 'deck' => 'b', 'starter' => false],
			['ability' => 143, 'points' => 2, 'deck' => 'b', 'starter' => false],
			['ability' => 144, 'points' => 0, 'deck' => 'b', 'starter' => false],
			['ability' => 156, 'points' => 2, 'deck' => 'b', 'starter' => false],
			['ability' => 159, 'points' => 1, 'deck' => 'b', 'starter' => false],
			['ability' => 161, 'points' => 0, 'deck' => 'b', 'starter' => false, 'ban2' => true],
			['ability' => 166, 'points' => 1, 'deck' => 'b', 'starter' => false],
			['ability' => 167, 'points' => 3, 'deck' => 'b', 'starter' => false],
			['ability' => 173, 'points' => 1, 'deck' => 'b', 'starter' => false],
			['ability' => 175, 'points' => 2, 'deck' => 'b', 'starter' => false, 'ban2' => true],
			['ability' => 176, 'points' => 3, 'deck' => 'b', 'starter' => false],
			['ability' => 177, 'points' => 0, 'deck' => 'b', 'starter' => false, 'ban2' => true],
			['ability' => 178, 'points' => 0, 'deck' => 'b', 'starter' => false],
			['ability' => 179, 'points' => 2, 'deck' => 'b', 'starter' => false],
			['ability' => 185, 'points' => 2, 'deck' => 'b', 'starter' => false],
			['ability' => 187, 'points' => 2, 'deck' => 'b', 'starter' => false],
			['ability' => 189, 'points' => 1, 'deck' => 'b', 'starter' => false, 'ban1' => true],
			['ability' => 190, 'points' => 0, 'deck' => 'b', 'starter' => false, 'ban2' => true]
		); // 44
		$occupations_c = array(
			['ability' => 2, 'points' => 1, 'deck' => 'c', 'starter' => false],
			['ability' => 14, 'points' => 2, 'deck' => 'c', 'starter' => false],
			['ability' => 15, 'points' => 1, 'deck' => 'c', 'starter' => false],
			['ability' => 16, 'points' => 2, 'deck' => 'c', 'starter' => false],
			['ability' => 18, 'points' => 0, 'deck' => 'c', 'starter' => false],
			['ability' => 19, 'points' => 0, 'deck' => 'c', 'starter' => false],
			['ability' => 31, 'points' => 2, 'deck' => 'c', 'starter' => false],
			['ability' => 39, 'points' => 2, 'deck' => 'c', 'starter' => false],
			['ability' => 45, 'points' => -1, 'deck' => 'c', 'starter' => false, 'ban2' => true],
			['ability' => 49, 'points' => 3, 'deck' => 'c', 'starter' => false],
			['ability' => 55, 'points' => 2, 'deck' => 'c', 'starter' => false],
			['ability' => 60, 'points' => 2, 'deck' => 'c', 'starter' => false],
			['ability' => 61, 'points' => 3, 'deck' => 'c', 'starter' => false],
			['ability' => 63, 'points' => 2, 'deck' => 'c', 'starter' => false],
			['ability' => 67, 'points' => 1, 'deck' => 'c', 'starter' => false],
			['ability' => 71, 'points' => -1, 'deck' => 'c', 'starter' => false],
			['ability' => 73, 'points' => 2, 'deck' => 'c', 'starter' => false],
			['ability' => 75, 'points' => 0, 'deck' => 'c', 'starter' => false],
			['ability' => 81, 'points' => 2, 'deck' => 'c', 'starter' => false],
			['ability' => 85, 'points' => 0, 'deck' => 'c', 'starter' => false],
			['ability' => 88, 'points' => 2, 'deck' => 'c', 'starter' => false],
			['ability' => 90, 'points' => 2, 'deck' => 'c', 'starter' => false],
			['ability' => 92, 'points' => 1, 'deck' => 'c', 'starter' => false],
			['ability' => 93, 'points' => 3, 'deck' => 'c', 'starter' => false],
			['ability' => 103, 'points' => 3, 'deck' => 'c', 'starter' => false],
			['ability' => 110, 'points' => 1, 'deck' => 'c', 'starter' => false],
			['ability' => 111, 'points' => 1, 'deck' => 'c', 'starter' => false],
			['ability' => 114, 'points' => 3, 'deck' => 'c', 'starter' => false],
			['ability' => 123, 'points' => 1, 'deck' => 'c', 'starter' => false],
			['ability' => 130, 'points' => 1, 'deck' => 'c', 'starter' => false],
			['ability' => 139, 'points' => 1, 'deck' => 'c', 'starter' => false, 'ban2' => true],
			['ability' => 146, 'points' => 0, 'deck' => 'c', 'starter' => false],
			['ability' => 148, 'points' => 1, 'deck' => 'c', 'starter' => false],
			['ability' => 157, 'points' => 0, 'deck' => 'c', 'starter' => false, 'ban2' => true],
			['ability' => 162, 'points' => 1, 'deck' => 'c', 'starter' => false, 'ban2' => true],
			['ability' => 168, 'points' => 2, 'deck' => 'c', 'starter' => false],
			['ability' => 169, 'points' => 1, 'deck' => 'c', 'starter' => false],
			['ability' => 172, 'points' => 0, 'deck' => 'c', 'starter' => false],
			['ability' => 180, 'points' => -2, 'deck' => 'c', 'starter' => false, 'ban2' => true],
			['ability' => 181, 'points' => 1, 'deck' => 'c', 'starter' => false],
			['ability' => 182, 'points' => 1, 'deck' => 'c', 'starter' => false],
			['ability' => 183, 'points' => 2, 'deck' => 'c', 'starter' => false],
			['ability' => 184, 'points' => 0, 'deck' => 'c', 'starter' => false],
			['ability' => 188, 'points' => 0, 'deck' => 'c', 'starter' => false]
		); // 44
		
		$occupations = array_merge(
			$a ? $occupations_a : [],
			$b ? $occupations_b : [],
			$c ? $occupations_c : []
		);
		shuffle($occupations);
		
		foreach ($occupations as $occ) {
			// deck, starter, ability, points, location, player_id
			if (self::ignore($occ, $players))
				continue;
			$s = $occ['starter'] ? 1 : 0;
			$values[] = "('$occ[deck]', $s, $occ[ability], $occ[points], 'deck', NULL)";
		}

		$sql .= implode(',', $values);
		FeastForOdin::DbQuery( $sql );
	}
	
	public static function draw($player_id) {
		$occupation = FeastForOdin::getObject( "SELECT * FROM occupation WHERE location = 'deck' LIMIT 1" );
		if (! isset($occupation)) {
			return null;
		}
		$occupation["location"] = "hand";
		$occupation["player_id"] = $player_id;
		FeastForOdin::DbQuery( "UPDATE occupation SET 
			location = 'hand', 
			player_id = $player_id 
		WHERE id = $occupation[id]" );
		return $occupation;
	}

	// for testing purposes
	public static function drawSpecificOccupation($player_id, $occupation_id) {
		$occupation = FeastForOdin::getObject( "SELECT * FROM occupation WHERE ability = $occupation_id" );
		if (! isset($occupation)) {
			return null;
		}
		$occupation["location"] = "hand";
		$occupation["player_id"] = $player_id;
		FeastForOdin::DbQuery( "UPDATE occupation SET 
			location = 'hand', 
			player_id = $player_id 
		WHERE id = $occupation[id]" );
		return $occupation;
	}
	// for testing purposes
	public static function playSpecificOccupation($player_id, $occupation_id) {
		$occupation = FeastForOdin::getObject( "SELECT * FROM occupation WHERE ability = $occupation_id" );
		if (! isset($occupation)) {
			return null;
		}
		$occupation["location"] = "played";
		$occupation["player_id"] = $player_id;
		FeastForOdin::DbQuery( "UPDATE occupation SET 
			location = 'played', 
			player_id = $player_id 
		WHERE id = $occupation[id]" );
		return $occupation;
	}

	public static function play($occupation_id) {
		FeastForOdin::DbQuery( "UPDATE occupation SET 
			location = 'played'
		WHERE id = $occupation_id" );
	}
	
	public static function discard($occupation_id) {
		FeastForOdin::DbQuery( "UPDATE occupation SET 
			location = 'discard'
		WHERE id = $occupation_id" );
	}

	public static function getHand($player_id) {
		return FeastForOdin::getObjectList( "SELECT * FROM occupation WHERE location = 'hand' AND player_id = $player_id" );
	}
	
	public static function getPlayed($player_id, $ability_id = null) {
		if (! isset($ability_id)) {
			return FeastForOdin::getObjectList( "SELECT * FROM occupation WHERE location = 'played' AND player_id = $player_id" );
		} else {
			return FeastForOdin::getObjectList( "SELECT * FROM occupation WHERE location = 'played' AND player_id = $player_id AND ability = $ability_id" );
		}
	}
	
	public static function has($player_id, $ability_id) {
		return (count(FeastForOdin::getObjectList( "SELECT * FROM occupation WHERE location = 'played' AND player_id = $player_id AND ability = $ability_id" )) > 0);
	}
	
	public static function use($occupation_id) {
		FeastForOdin::DbQuery( "UPDATE occupation SET 
			used = 1
		WHERE id = $occupation_id" );
	}
	
	public static function checkAsSoonAsOccs( &$player ) {
		// 178, 180, 181
		$big_ships = 0;
		$longships = 0;
		foreach ($player["ships"] as $s) {
			if ($s["type"] == "longship") {
				$big_ships++;
				$longships++;
			} else if ($s["type"] == "knarr") {
				$big_ships++;
			}
		}
		
		if ($big_ships >= 3) {
			$played = self::getPlayed($player["id"]);
			$played_unused_abilities = [];
			foreach ($played as $occ) {
				if (empty($occ["used"])) {
					$played_unused_abilities[$occ["ability"]] = $occ["id"];
				}
			}
			if ($big_ships >= 4 && ! empty($played_unused_abilities[178])) {
				// 2 whaling boats
				FeastForOdin::$game->saveOccupations( $player["id"] );
				Occupation::use($played_unused_abilities[178]);
				FfoPlayer::addShip( $player, "whalingboat", 0, "occupation-178", null, false );
				FfoPlayer::addShip( $player, "whalingboat", 0, "occupation-178", null, false );
			}
			if ($big_ships >= 3) {
				$round_income = 0;
				foreach ($player["boards"] as $board) {
					if (isset($board["_income"]) && $board["_income"] > 0) {
						$round_income += $board["_income"];
					}
				}
				if ($longships >= 3 && ! empty($played_unused_abilities[180])) {
					// Check round income
					FeastForOdin::$game->saveOccupations( $player["id"] );
					Occupation::use($played_unused_abilities[180]);
					if ($round_income <= 5) {
						FeastForOdin::$game->gain($player, "silverware", 4, "occupation-180");
					} else if ($round_income <= 11) {
						FeastForOdin::$game->gain($player, "silverware", 3, "occupation-180");
					} else if ($round_income <= 30) {
						FeastForOdin::$game->gain($player, "silverware", 2, "occupation-180");
					}
				}
				if ($big_ships >= 3 && ! empty($played_unused_abilities[181])) {
					FeastForOdin::$game->saveOccupations( $player["id"] );
					Occupation::use($played_unused_abilities[181]);
					if ($round_income <= 4) {
						FeastForOdin::$game->gain($player, "wool", 3, "occupation-181");
					} else if ($round_income <= 9) {
						FeastForOdin::$game->gain($player, "wool", 2, "occupation-181");
					} else if ($round_income <= 30) {
						FeastForOdin::$game->gain($player, "wool", 1, "occupation-181");
					}
				}	
			}
		}
	} 
	
	public static function getImmediateCommands( $ability_id ) {
		switch ($ability_id) {
			case 10: // Milker
				return ["takeif-coin-sheep", "takeif-milk-sheep", "takeif-coin-cattle", "takeif-milk-cattle", "takeif-coin-horse", "takeif-milk-horse", "takeif-coin-pig", "takeif-milk-pig"];
			case 11: // Trident Hunter
				return ["draw-weapon-spear"];
			case 12: // Helmsman
				return ["take-chest"];
			case 13: // Archer
				return ["draw-weapon-bow"];
			case 17: // Orient Shipper
				return ["upgrade-1-3"];
			case 22: // Miner
				return ["takeper-ore-longship", "takeper-stone-longship", "takeper-coin-longship"];
			case 24: // Whaling Equipper
				return ["takeper-oil-knarr", "takeper-wood-whalingboat"];
			case 25: // Meat Trader
				return ["takeper-saltmeat-knarr"];
			case 27: // Fisherman
				return ["takeper-stockfish-whalingboat"];
			case 33: // Sheep Shearer
				return [
					"takeif-wool-sheep-3",
					"takeif-wool-sheep-4",
					"takeif-wool-sheep-6",
					];
			case 34: // Shepherd
				return ["takeif-milk-sheep-2", "takeif-wool-sheep-2", "takeif-wool-sheep-4"];
			case 43: // Fruit Picker
				return ["take-fruits"];
			case 68: // Supplier of Weapons
				return ["draw-weapon-x-4"];
			case 83: // Wholesaler
				return ["sameupgrade-4-1"];
			case 85:
				return [
					"state-hornblower",
					"state-hornblower2",
				];
			case 183: // Grain Deliveryman
				return [
					"takeif-peas-knarr-1",
					"takeif-beans-knarr-1",
					"takeif-flax-knarr-1",
					"takeif-grain-knarr-2",
					"takeif-cabbage-knarr-3",
					"takeif-fruits-knarr-4",
					];
			default:
				return ["occupation-$ability_id"];
		}
	}
	
	public static function getNextState( $ability_id ) {
		switch ($ability_id) {
			case 5:
				return 'command-personalfeast';
			case 31:
				return 'command-storeman';
			case 60:
				return 'command-inspector';
			case 61:
				return 'command-wharfowner';
			case 62:
				return 'command-fineblacksmith';
			case 63:
				return 'command-hornturner';
			case 64:
				return 'command-shipbuilder';
			case 65:
				return 'command-dragonslayer';
			case 66:
				return 'command-fieldfarmer';
			case 67:
				return 'command-privatechef';
			case 72:
				return 'command-earl';
			case 73:
				return 'command-metalsmith';
			case 74:
				return 'command-cowherd';
			case 75:
				return 'command-bonecollector';
			case 79:
				return 'command-breeder';
			case 80:
				return 'command-shepherdboy';
			case 81:
				return 'command-quartermaster';
			case 82:
				return 'command-longshipbuilder';
			case 84:
				return 'command-harborguard';
			case 86:
				return 'command-hidebuyer';
			case 87:
				return 'command-preacher';
			case 184:
				return 'command-sponsor';
			case 77:
				return 'command-follower';
		}
		return null;
	}

	private static function ignore($occupation, $players) {
		if (in_array($occupation['ability'], [
			 98, 52,
			 28, 53, 56, 150, 175// old
		])) {
			return true;
		}

		if (FeastForOdin::$game->getGameStateValue('occ_ban_list_1') == 2 && isset($occupation['ban1'])) {
			return true;
		}

		if (FeastForOdin::$game->getGameStateValue('occ_ban_list_2') == 2 && isset($occupation['ban2'])) {
			return true;
		}

		// Adjust occs for Norwegians
		if (FeastForOdin::$game->isNorwegiansExpansion()) {
			// if overseas trade action space with 2 vikings is not available (game with 1 to 2 players)
			// remove Master Count (109) and Birka Settler (110)
			if (count($players) < 3 && in_array($occupation['ability'], [109, 110]) ) {
				return true;
			}

			// if hunting action space with 2 vikings is not available (game with 1 to 3 players)
			// remove Farmhand (163)
			if (count($players) < 4 && $occupation['ability'] == 163 ) {
				return true;
			}

			// remove Meat Buyer (150) as it's never applicable (well at least via actions)
			if ($occupation['ability'] == 150) {
				return true;
			}
		}

		return false;
	}

}