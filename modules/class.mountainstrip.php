<?php

class MountainStrip {
	public static function setup() {
		$sql = "INSERT INTO mountain_strips (
			code, resources
		) VALUES ";
		
		$values = array();
		$values[] = "(1, 'wood,wood,stone,stone,ore,ore,coins')";
		$values[] = "(2, 'wood,wood,stone,ore,ore,coins,coins')";
		$values[] = "(3, 'wood,wood,wood,stone,stone,stone,coins')";
		$values[] = "(4, 'wood,wood,wood,stone,stone,ore,coins')";
		$values[] = "(5, 'wood,wood,wood,stone,ore,ore,coins')";
		
		$values[] = "(6, 'wood,wood,stone,stone,stone,ore,coins')";
		$values[] = "(7, 'wood,wood,wood,wood,stone,ore,coins')";
		$values[] = "(8, 'wood,wood,wood,wood,stone,stone,coins')";		

		shuffle($values);

		if (FeastForOdin::$game->isNorwegiansExpansion()) {
			// Add Norwegians mountain strip to the end
			// https://boardgamegeek.com/thread/2084644/new-mountain-stripe-2-4-players-only
			$values[] = "(9, 'wood,wood,stone,stone,ore,coins,coins,orepair')";
		}
		
		$sql .= implode(',', $values);
		FeastForOdin::DbQuery( $sql );
	}
	
	public static function getDeck() {
		$values = FeastForOdin::getObjectList( "SELECT * FROM mountain_strips WHERE location = 'deck'" );
		return $values;
	}
	
	public static function getAvailable() {
		$values = FeastForOdin::getObjectList( "SELECT * FROM mountain_strips WHERE location = 'available'" );
		return $values;
	}
	
	public static function draw() {
		// If there is an available strip, draw it!
		$deck = self::getDeck();
		if (count($deck) == 0) return null;
		$s = $deck[0];
		FeastForOdin::DbQuery( "UPDATE mountain_strips SET location = 'available' WHERE id = $s[id]" );
		$s["location"] = "available";
		return $s;
	}
	
	public static function save($strip_id, $elements) {
		$resources = implode(",", $elements);
		$location = count($elements) == 0 ? 'discard' : 'available';
		FeastForOdin::DbQuery( "UPDATE mountain_strips SET resources = '$resources', location = '$location' WHERE id = $strip_id" );
	}
}