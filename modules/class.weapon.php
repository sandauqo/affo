<?php

class Weapon {
	public static function setup($players) {
		$sql = "INSERT INTO weapon (
			type, location, player_id
		) VALUES ";
		$values = array();
		$weapons_deck = array();
		for ($i = 12 - count($players); $i > 0; $i--) {
			$weapons_deck[] = ["type" => "bow", "location" => "deck", "player_id" => "NULL"];
			$weapons_deck[] = ["type" => "snare", "location" => "deck", "player_id" => "NULL"];
			$weapons_deck[] = ["type" => "spear", "location" => "deck", "player_id" => "NULL"];
		}
		foreach ($players as $player_id => $player) {
			$weapons_deck[] = ["type" => "bow", "location" => "hand", "player_id" => $player_id];
			$weapons_deck[] = ["type" => "snare", "location" => "hand", "player_id" => $player_id];
			$weapons_deck[] = ["type" => "spear", "location" => "hand", "player_id" => $player_id];
		}
		for ($i = 11; $i > 0; $i--) {
			$weapons_deck[] = ["type" => "sword", "location" => "deck", "player_id" => "NULL"];
		}
		shuffle($weapons_deck);
		foreach ($weapons_deck as $w) {
			$values[] = "('$w[type]', '$w[location]', $w[player_id])";
		}
		$sql .= implode(',', $values);
		FeastForOdin::DbQuery( $sql );
	}
	
	public static function getHand($player_id) {
		return FeastForOdin::getObjectList( "SELECT * FROM weapon WHERE location = 'hand' AND player_id = $player_id" );
	}
	
	public static function discard($player_id, $to_spend, $source = null) {
		$ids = [];
		$values = [];
		foreach ($to_spend as $c) {
			$ids[] = $c["id"];
			$values[] = "($player_id, '$c[type]', -1, ".(isset($source) ? "'$source'" : 'NULL').")";
		}
		if (count($to_spend) == 0)
			return;
		
		FeastForOdin::DbQuery("INSERT INTO asset_diffs (
			player_id, asset, amount, source
		) VALUES ".implode(',', $values));
		
		$id_list = implode(",", $ids);
		FeastForOdin::DbQuery( "UPDATE weapon SET location = 'discard' WHERE id IN ($id_list)" );
	}
	
	public static function getDeck() {
		$deck = FeastForOdin::getObjectList( "SELECT * FROM weapon WHERE location = 'deck'" );
		shuffle($deck);
		return $deck;
	}
	
	public static function draw($player_id, $type = null, $source = null, $num = 1, $notify = true) {
		$weapons = [];
		for ($i = 0; $i < $num; $i++) {
			if (! isset($type)) {
				$weapon_deck = self::getDeck();
				if (count($weapon_deck) == 0) {
					// Shuffle discards into deck
					FeastForOdin::DbQuery( "UPDATE weapon SET location = 'deck' WHERE location = 'discard'" );
					$weapon_deck = self::getDeck();
					if (count($weapon_deck) == 0) {
						continue;
					}
				}
				$weapon = $weapon_deck[0];
			} else {
				// Get from discard, or from deck
				$weapon_discard = FeastForOdin::getObjectList( "SELECT * FROM weapon WHERE location = 'discard' AND `type` = '$type'" );
				if (count($weapon_discard) == 0) {
					// Get from deck
					$weapon_deck = FeastForOdin::getObjectList( "SELECT * FROM weapon WHERE location = 'deck' AND `type` = '$type'" );
					if (count($weapon_deck) == 0) {
						continue;
					}
					$weapon = $weapon_deck[0];
				} else {
					$weapon = $weapon_discard[0];
				}
			}
			$weapon["location"] = "hand";
			$weapon["player_id"] = $player_id;
			FeastForOdin::DbQuery( "UPDATE weapon SET location = 'hand', player_id = $player_id WHERE id = $weapon[id]" );
			$weapons[] = $weapon;
		}
		
		if (count($weapons) > 0 && $notify) {
			$cards_log = [];
			$cards_args = [
				'i18n' => []
			];
			
			foreach ($weapons as $weapon) {
				$key = "inject_weapon_$weapon[type]";
				$cards_log[] = '${'.$key.'}';
				$cards_args[$key] = FeastForOdin::$game->m_weapons[$weapon["type"]];
				$cards_args["i18n"][] = $key;
			}
			
			$players = FeastForOdin::$game->loadPlayersBasicInfos();
			
			// Notification
			FeastForOdin::$game->notifyAllPlayers( "drawWeapon", clienttranslate('${player_name} draws: ${cards_list}'), array(
				'player_id' => $player_id,
				'player_name' => $players[$player_id]["player_name"],
				'weapons' => $weapons,
				'cards_list' => [
					'log' => implode(' ', $cards_log),
					'args' => $cards_args
				],
				'source' => $source
			));
		}
		
		return $weapons;
	}
}