<?php

class FfoPlayer {
	public static function setup($player_id, $short_game, $solo_game) {
		$values = array();
		
		if ($solo_game) {
			$sql = "INSERT INTO meeple (
				player_id, location, slot, colour
			) VALUES ";
			
			$values[] = "($player_id, 'feast', 1, 0)";
			$values[] = "($player_id, 'feast', 3, 0)";
			$values[] = "($player_id, 'feast', 3, 0)";
			$values[] = "($player_id, 'feast', 5, 0)";
			$values[] = "($player_id, 'feast', 5, 0)";
			if ($short_game) {
				$values[] = "($player_id, 'feast', 1, 0)";
			} else {
				$values[] = "($player_id, 'feast', 7, 0)";
				$values[] = "($player_id, 'feast', 7, 0)";
			}
			
			$values[] = "($player_id, 'feast', 2, 1)";
			$values[] = "($player_id, 'feast', 2, 1)";
			$values[] = "($player_id, 'feast', 4, 1)";
			$values[] = "($player_id, 'feast', 4, 1)";
			$values[] = "($player_id, 'feast', 6, 1)";
			$values[] = "($player_id, 'feast', 6, 1)";
			
			for ($i = 0; $i < 5; $i++) {
				$values[] = "($player_id, 'thing', NULL, 0)";
			}
			
			$number_on_bench = $short_game ? 6 : 5;
			for ($i = 0; $i < $number_on_bench; $i++) {
				$values[] = "($player_id, 'bench', NULL, 1)";
			}
		} else {
			// 12 for each player. Some on round spaces (depends on short/long), rest on Thing.
			$sql = "INSERT INTO meeple (
				player_id, location, slot
			) VALUES ";
			
			$number_on_table = $short_game ? 6 : 7;
			for ($i = 0; $i < 12; $i++) {
				if ($i < $number_on_table) {
					$slot = $i + 1;
					$values[] = "($player_id, 'feast', $slot)";
				} else {
					$values[] = "($player_id, 'thing', 0)";
				}
			}
		}
	
		$sql .= implode(',', $values);
		FeastForOdin::DbQuery( $sql );
	}
	
	public static function getMeeples($player_id, $location = null) {
		$condition = '';
		if (isset($location)) {
			$condition = "AND location = '$location'";
		}
		return FeastForOdin::getObjectList( "SELECT * FROM meeple WHERE player_id = $player_id $condition" );
	}
	
	public static function playerMeeplesCondition($player_id) {
		if (FeastForOdin::$game->isSoloGame()) {
			$round = FeastForOdin::$game->getGameStateValue('round');
			$active_colour = ($round % 2 == 0) ? 1 : 0;
			return "player_id = $player_id AND colour = $active_colour";
		} else {
			return "player_id = $player_id";
		}
	}
	
	public static function getMeeplesInColumn($column_num, $player_id = NULL) {
		//TODO: think of better var name
		if ($column_num === 5) {
			$action_id_clause = "action_id >= 5550";
		} else {
			$target_mod = $column_num % 4;
			$action_id_clause = "action_id % 4 = $target_mod"; 
		}

		if (isset($player_id)) {
			$condition = self::playerMeeplesCondition($player_id);
			return FeastForOdin::getObjectList( "SELECT * FROM meeple WHERE $action_id_clause AND location = 'action' AND $condition" );
		} else {
			return FeastForOdin::getObjectList( "SELECT * FROM meeple WHERE $action_id_clause AND location = 'action'" );
		}
	}
	
	public static function isActionAvailable($action_id) {
		$meeples = FeastForOdin::getObjectList( "SELECT * FROM meeple WHERE action_id = $action_id AND location = 'action'" );
		return count($meeples) == 0;
	}
	
	public static function isOccupiedByAnotherPlayer($action_id, $player_id) {
		$condition = self::playerMeeplesCondition($player_id);
		$meeples = FeastForOdin::getObjectList( "SELECT * FROM meeple WHERE action_id = $action_id AND location = 'action' AND NOT ($condition)" );
		return count($meeples) > 0;
	}
	
	public static function getMeeplesOnAction($action_id) {
		$meeples = FeastForOdin::getObjectList( "SELECT * FROM meeple WHERE action_id = $action_id AND location = 'action'" );
		return $meeples;
	}
	
	public static function newMeeple($player_id) {
		// take last Feast meeple(s) and move to the Thing
		$meeples = self::getMeeples($player_id);
		$min_meeples = [];
		$min_slot = 0;
		foreach ($meeples as $m) {
			if ($m['location'] == 'feast') {
				if (count($min_meeples) == 0 || $min_slot > $m['slot']) {
					$min_slot = $m['slot'];
					$min_meeples = [$m];
				} else if ($min_slot == $m['slot']) {
					$min_meeples[] = $m;
				}
			}
		}
		if (count($min_meeples) == 0) return null;
		foreach ($min_meeples as &$m) {
			$m['location'] = 'thing';
		}
		$id_list = implode(',', array_column($min_meeples, "id"));
		FeastForOdin::DbQuery( "UPDATE meeple SET location = 'thing' WHERE player_id = $player_id AND id IN ($id_list)" );
		return $min_meeples;
	}
	
	public static function moveMeepleToAction($player_id, $action_id, $num) {
		// take last Feast meeple and move it to the Thing
		$meeples = self::getMeeples($player_id);
		$moved_meeples = [];
		$ids = [];
		foreach ($meeples as &$m) {
			if ($m['location'] == 'thing') {
				$m['location'] = 'action';
				$m['action_id'] = $action_id;
				$moved_meeples[] = $m;
				$ids[] = $m["id"];
				if (count($moved_meeples) == $num) {
					break;
				}
			}
		}
		$id_list = implode(",", $ids);
		FeastForOdin::DbQuery( "UPDATE meeple SET location = 'action', action_id = $action_id WHERE player_id = $player_id AND id IN ($id_list)" );
		return $moved_meeples;
	}
	
	public static function meepleRefund($player_id, $action_id, $num) {
		// take last Feast meeple and move it to the Thing
		$meeples = FeastForOdin::getObjectList( "SELECT * FROM meeple WHERE player_id = $player_id AND location = 'action' AND action_id = $action_id" );
		$moved_meeples = [];
		$ids = [];
		foreach ($meeples as &$m) {
			$m['location'] = 'thing';
			$moved_meeples[] = $m;
			$ids[] = $m["id"];
			if (count($moved_meeples) == $num) {
				break;
			}
		}
		if (count($ids) > 0) {
			$id_list = implode(",", $ids);
			FeastForOdin::DbQuery( "UPDATE meeple SET location = 'thing' WHERE player_id = $player_id AND id IN ($id_list)" );
			if (Occupation::has($player_id, 168)) {
				// Earl of Lade
				$thing_meeples = self::getMeeples($player_id, 'thing');
				if (count($thing_meeples) == 3) {
					Action::push(["take-coin:occupation-168"]);
				}
			}
		}
		
		return $moved_meeples;
	}
	
	public static function meeplesToThing() {
		$meeples = FeastForOdin::getObjectList( "SELECT * FROM meeple WHERE location = 'action'" );
		foreach ($meeples as &$m) {
			$m['location'] = 'thing';
		}
		FeastForOdin::DbQuery( "UPDATE meeple SET location = 'thing' WHERE location = 'action'" );
		return $meeples;
	}
	
	public static function meeplesToThingByColour( $colour ) {
		$meeples = FeastForOdin::getObjectList( "SELECT * FROM meeple WHERE colour = $colour AND location != 'feast'" );
		foreach ($meeples as &$m) {
			$m['location'] = 'thing';
		}
		FeastForOdin::DbQuery( "UPDATE meeple SET location = 'thing' WHERE colour = $colour AND location != 'feast'" );
		return $meeples;
	}
	
	public static function meeplesToBenchByColour( $colour ) {
		$meeples = FeastForOdin::getObjectList( "SELECT * FROM meeple WHERE colour = $colour AND location = 'thing'" );
		foreach ($meeples as &$m) {
			$m['location'] = 'bench';
		}
		FeastForOdin::DbQuery( "UPDATE meeple SET location = 'bench' WHERE colour = $colour AND location = 'thing'" );
		return $meeples;
	}
	
	public static function getFullPlayer($player_id) {
		$player = FeastForOdin::getObject("SELECT 
		  player_id id, player_color colour, player_score score, 
		  player_score_aux score_aux,
		  coin, wood, stone, ore, penalty, vp,
		  goods, boards, artisan_shed, ships, emigrations 
		FROM player WHERE player_id = $player_id");
		
		// Auto decode json
		$player["goods"] = json_decode($player["goods"], true);
		//DEBUG: remove null goods from older games where bug with hunting lodge appeared
		$player["goods"] = array_filter($player["goods"]);
		$player["boards"] = json_decode($player["boards"], true);
		$player["ships"] = json_decode($player["ships"], true);
		$player["emigrations"] = json_decode($player["emigrations"], true);
		
		return $player;
	}
	
	public static function saveFullPlayer($player, $fields = null) {
		// Re-key anything that can get spent
		$player["goods"] = array_values($player["goods"]);
		$player["ships"] = array_values($player["ships"]);
		
		//DEBUG: remove null goods from older games where bug with hunting lodge appeared
		$player["goods"] = array_filter($player["goods"]);

		if (isset($fields)) {
			$query = "UPDATE player SET ";
			$updates = [];
			if (isset($fields["resources"])) {
				$updates[] = "coin = $player[coin]";
				$updates[] = "wood = $player[wood]";
				$updates[] = "stone = $player[stone]";
				$updates[] = "ore = $player[ore]";
				$updates[] = "penalty = $player[penalty]";
				$updates[] = "vp = $player[vp]";
			}
			if (isset($fields["goods"])) {
				$updates[] = "goods = '" . json_encode($player["goods"]) . "'";
			}
			if (isset($fields["boards"])) {
				$updates[] = "boards = '" . json_encode($player["boards"]) . "'";
			}
			if (isset($fields["ships"])) {
				$updates[] = "ships = '" . json_encode($player["ships"]) . "'";
			}
			if (isset($fields["emigrations"])) {
				$updates[] = "emigrations = '" . json_encode($player["emigrations"]) . "'";
			}
			$query .= implode(',', $updates);
			$query .= " WHERE player_id = $player[id]";
			FeastForOdin::DbQuery($query);
		} else {
			FeastForOdin::DbQuery("UPDATE player SET 
			  player_score = $player[score], 
			  player_score_aux = $player[score_aux], 
			  coin = $player[coin], 
			  wood = $player[wood], 
			  stone = $player[stone], 
			  ore = $player[ore], 
			  penalty = $player[penalty], 
			  vp = $player[vp],
			  goods = '" . json_encode($player["goods"]) . "',
			  boards = '" . json_encode($player["boards"]) . "',
			  ships = '" . json_encode($player["ships"]) . "',
			  emigrations = '" . json_encode($player["emigrations"]) . "'
			WHERE player_id = $player[id]");
		}
	}
	
	public static function addBoard( &$player, $board ) {
		$board["index"] = count($player["boards"]);
		$board["goods"] = [];
		// Cached values
		$board["_negatives"] = count($board["negatives"]);
		$player["boards"][] = $board;
		
		$players = FeastForOdin::$game->loadPlayersBasicInfos();
		
		$board_type = $board['type'];
		if ($board_type == 'shed') {
			$text = clienttranslate('${player_name} takes a shed');
		} else if ($board_type == 'stonehouse') {
			$text = clienttranslate('${player_name} takes a stone house');
		} else if ($board_type == 'longhouse') {
			$text = clienttranslate('${player_name} takes a long house');
		} else {
			$text = clienttranslate('${player_name} explores ${board_name}');
		}
		
		FeastForOdin::$game->notifyAllPlayers( "newBoard", $text, array(
			'player_id' => $player["id"],
			'player_name' => $players[$player["id"]]["player_name"],
			'board' => $board,
			'board_name' => FeastForOdin::$game->m_boards[$board_type]['name'],
			'i18n' => ['board_name'],
		));
		
		return $board;
	}
	
	public static function addShip( &$player, $ship_type, $ore = 0, $source = null, $with_cost = null, $error_if_no_room = true ) {
		$ship_id = FeastForOdin::$game->incGameStateValue( 'ship_id', 1 );
		
		// Check you have room for the ship
		$num_small = 0;
		$num_big = 0;
		foreach ($player["ships"] as $s) {
			if ($s["type"] == "whalingboat") {
				$num_small++;
			} else {
				$num_big++;
			}
		}
		if ($ship_type == "whalingboat") {
			if ($num_small >= 3) {
				// No room!
				if (! $error_if_no_room)
					return null;
				throw new BgaUserException( clienttranslate("You do not have enough room for that boat") );
			}
		} else {
			if ($num_big >= 4) {
				// No room!
				if (! $error_if_no_room)
					return null;
				throw new BgaUserException( clienttranslate("You do not have enough room for that boat") );
			}
		}
		
		$ship = ["type" => $ship_type, "ore" => $ore, "id" => $ship_id];
		$player["ships"][] = $ship;
		
		$players = FeastForOdin::$game->loadPlayersBasicInfos();
		
		if (! isset($with_cost)) {
			if ($ship_type == 'knarr') {
				$text = clienttranslate('${player_name} takes a knarr');
			} else if ($ship_type == 'longship') {
				$text = clienttranslate('${player_name} takes a longship');
			} else if ($ship_type == 'whalingboat') {
				$text = clienttranslate('${player_name} takes a whaling boat');
			}
		} else {
			if ($ship_type == 'knarr') {
				$text = clienttranslate('${player_name} takes a knarr for ${cost} silver');
			} else if ($ship_type == 'longship') {
				$text = clienttranslate('${player_name} takes a longship for ${cost} silver');
			} else if ($ship_type == 'whalingboat') {
				$text = clienttranslate('${player_name} takes a whaling boat for ${cost} silver');
			}
		}
		
		FeastForOdin::$game->notifyAllPlayers( "newShip", $text, array(
			'player_id' => $player["id"],
			'player_name' => $players[$player["id"]]["player_name"],
			'ship' => $ship,
			'source' => $source,
			'cost' => $with_cost,
		));

		// Check each of the boat building occs (178-181)
		if ($ship_type != "whalingboat") {
			Occupation::checkAsSoonAsOccs( $player );
			if ($ship_type == "knarr") {
				$playeds = Occupation::getPlayed($player["id"], 179);
				if (count($playeds) > 0) {
					if (!FeastForOdin::$game->getGameStateValue( 'waterford_bonus_knarr_skip_179_occ')) {
						// Check it's unused
						$played = $playeds[0];
						if (empty($played["used"])) {
							FeastForOdin::$game->saveOccupations( $player["id"] );
							Occupation::use($played["id"]);
							// 1 stockfish + coin for each knarr
							$knarrs = 0;
							foreach ($player["ships"] as $s) {
								if ($s["type"] == "knarr") {
									$knarrs++;
								}
							}
							FeastForOdin::$game->gain($player, "stockfish", $knarrs, "occupation-179");
							FeastForOdin::$game->gain($player, "coin", $knarrs, "occupation-179");
						}
					} else {
						FeastForOdin::$game->setGameStateValue( 'waterford_bonus_knarr_skip_179_occ', 0);
					}

				}
			}
		}
		
		return $ship;
	}
	
	private static function isValidCoords($game, $board, $coords, $check_voids = true, $good = null) {
		if ($coords[0] < 0) return false;
		if ($coords[1] < 0) return false;
		if ($coords[0] >= $board["cols"]) return false;
		if ($coords[1] >= $board["rows"]) return false;
		if ($check_voids) {
			foreach ($board["cells"] as $cell) {
				if ($cell["coords"][0] == $coords[0] && $cell["coords"][1] == $coords[1]) {
					if ($cell["type"] == "void") {
						// This is on a void cell
						return false;
					}
					if ($good && $cell["type"] == "unique") {
						if ($cell["type"] !== $good["type"]) {
							return false;
						}
					}
				}
			}
		}
		if ($board["feast"]) {
			// Current round and rightwards is not valid
			$round = $game->getGameStateValue( 'round' );
			$short_game = $game->isShortGame();
			$spaces = 5 + $round;
			if ($short_game) {
				$spaces++;
			}
			if ($coords[0] >= $spaces) {
				return false;
			}
		}
		return true;
	}
	
	private static function goodCells( $good ) {
		$coords = [];
		for ($c = 0; $c < $good["cols"]; $c++) {
			for ($r = 0; $r < $good["rows"]; $r++) {
				// If this cell is void, skip it!
				$skip = false;
				foreach ($good["cells"] as $cell) {
					if ($cell["coords"][0] == $c && $cell["coords"][1] == $r) {
						if ($cell["type"] == "void") {
							$skip = true;
							break;
						} else {
							throw new BgaVisibleSystemException( "Invalid good cell type: ".$cell["type"] );
						}
					}
				}
				if ($skip) continue;
				
				// Find this cell on the Target
				// Tranform according to flipped & rotated
				$effective_rotation = $good["rotation"];
				if ($good["flipped"]) {
					// If flipped, and rotation is odd, rotate twice
					if ($effective_rotation == 1 || $effective_rotation == 3) {
						$effective_rotation = ($effective_rotation + 2) % 4;
					}
				}
				$theta = $effective_rotation * M_PI/2;
				$rotation_matrix = [
					[cos($theta), -sin($theta)],
					[sin($theta), cos($theta)],
				];
				$transformed_coords = [
					$rotation_matrix[0][0] * $c + $rotation_matrix[1][0] * $r,
					$rotation_matrix[0][1] * $c + $rotation_matrix[1][1] * $r,
				];
				
				// Translate to be from bottom-left corner, not from transformed origin
				if ($effective_rotation == 1) {
					$transformed_coords[1] += $good["cols"] - 1;
				} else if ($effective_rotation == 2) {
					$transformed_coords[0] += $good["cols"] - 1;
					$transformed_coords[1] += $good["rows"] - 1;
				} else if ($effective_rotation == 3) {
					$transformed_coords[0] += $good["rows"] - 1;
				}
				
				$transformed_coords[0] = round($transformed_coords[0]);
				$transformed_coords[1] = round($transformed_coords[1]);
				
				if ($good["flipped"]) {
					// Swap to the other side x-direction
					if ($effective_rotation == 0 || $effective_rotation == 2) {
						$transformed_coords[0] = $good["cols"] - $transformed_coords[0] - 1;
					} else {
						$transformed_coords[0] = $good["rows"] - $transformed_coords[0] - 1;
					}
				}
				
				$coords[] = $transformed_coords;
			}
		}
		return $coords;
	}
	
	private static function neighbours($coords, $orthog = true) {
		if ($orthog)
			return [
				[$coords[0] - 1, $coords[1]],
				[$coords[0] + 1, $coords[1]],
				[$coords[0], $coords[1] - 1],
				[$coords[0], $coords[1] + 1],
			];
		else
			return [
				[$coords[0] - 1, $coords[1]],
				[$coords[0] + 1, $coords[1]],
				[$coords[0], $coords[1] - 1],
				[$coords[0], $coords[1] + 1],
				[$coords[0] + 1, $coords[1] + 1],
				[$coords[0] - 1, $coords[1] + 1],
				[$coords[0] + 1, $coords[1] - 1],
				[$coords[0] - 1, $coords[1] - 1],
			];
	}
	
	public static function checkProvisionals( $game, &$board, $player_id, $emigration_columns = 0 ) {
		// Same function name as JS, but it actually checks the whole board
		$data = [];
		for ($i = 0; $i < $board["cols"]; $i++) {
			$column = [];
			for ($j = 0; $j < $board["rows"]; $j++) {
				$type = null;
				$income = 0;
				$bonus = [];
				foreach ($board["cells"] as $cell) {
					// {"coords":[0,0],"type":"income","value":0}
					if ($cell["coords"][0] == $i && $cell["coords"][1] == $j) {
						// If a cell is multiple types, we only care about void
						if (! isset($type) || $type != 'void') {
							$type = $cell["type"];
						}
						if ($cell["type"]  == "income") {
							$income = $cell["value"];
						} else if ($cell["type"]  == "bonus") {
							$bonus = array_merge($bonus, $cell["resources"]);
						}
					}
				}
				$column[] = [
					"coords" => [$i, $j],
					"goods" => [],
					"type" => $type,
					"income" => $income,
					"bonus" => $bonus,
				];
			}
			$data[] = $column;
		}

		// Loop through all goods, and add them to data
		foreach ($board["goods"] as $good) {
			if ($good["type"] == "crown") {
				$board['_english_crown'] = 2;
			}
			// Loop through each cell of this good
			$cells = self::goodCells($good);
			foreach ($cells as $coords) {
				$bottom_left_coords = $good["coords"];
				$target_coords = [
					$bottom_left_coords[0] + $coords[0],
					$bottom_left_coords[1] + $coords[1]
				];
				
				// If there are emigrations...
				if ($target_coords[0] < $emigration_columns) {
					// TODO : Move this check to javascript?
					throw new BgaUserException( clienttranslate("You cannot overlap an Emigration tile") );
				}
				
				// If they overlap other goods: red
				$valid = self::isValidCoords($game, $board, $target_coords, false, $good);
				if ($valid) {
					$datum = $data[$target_coords[0]][$target_coords[1]];
					if (count($datum["goods"]) > 0) {
						// Collision!
						throw new BgaVisibleSystemException( "Error in makeBoardArray (collision)" );
					}
					$data[$target_coords[0]][$target_coords[1]]["goods"][] = $good;
				}
				
				// If they overhang the edge or a void: red
				if (! $valid) {
					// Overlaps void!
					throw new BgaVisibleSystemException( "Error in makeBoardArray (overhang void)" );
				}
				
				// If they touch a forbidden colour: red
				// Check 4 adjacent cells for different, same-coloured goods
				$neighbours = self::neighbours($target_coords);
				if (in_array($good["colour"], $board["nonTouchingColours"])) {
					foreach ($neighbours as $nn) {
						if (self::isValidCoords($game, $board, $nn, false, $good)) {
							$datum = $data[$nn[0]][$nn[1]];
							if (count($datum["goods"]) > 0) {
								foreach ($datum["goods"] as $neighbour) {
									if ($neighbour != $good) {
										// Mark both red
										if ($neighbour["colour"] == $good["colour"]) {
											throw new BgaVisibleSystemException( "Error in makeBoardArray (bad touching)" );
										}
									}
								}
							}
						}
					}
				}
			}
		}
		
		// Can't cover an income space which has non-void blanks down and left
		foreach ($board["cells"] as $cell) {
			// Can this be made quicker with incomeStarts?
			if ($cell["type"] == "income") {
				// Only bother checking if covered by a provisional
				$datum = $data[$cell["coords"][0]][$cell["coords"][1]];
				if (count($datum["goods"]) > 0) {
					foreach ($datum["goods"] as $good) {
						// TODO : Only bother checking _new_ tiles!
						// Check all cells down and left of this!
						$gap = false;
						for ($i = 0; $i <= $cell["coords"][0]; $i++) {
							for ($j = 0; $j <= $cell["coords"][1]; $j++) {
								// Don't check the income space itself
								if ($i == $cell["coords"][0] && $j == $cell["coords"][1])
									continue;
								// If the cell is covered, fine!
								if (count($data[$i][$j]["goods"]) > 0)
									continue;
								// If the cell is a void or bonus, fine!
								if ($data[$i][$j]["type"] == "void" || $data[$i][$j]["type"] == "bonus")
									continue;
								$gap = true;
								break 2;
							}
						}
						if ($gap) {
							throw new BgaVisibleSystemException( "Error in makeBoardArray (income naughty)" );
						}
					}
				}
			}
		}
		

		// Save income and bonus for this board!
		$income = 0;
		foreach ($board["incomeStarts"] as $coord) {
			// Start here, and go up until you get to an uncovered one
			$c = $coord;
			
			$max_income = $data[$c[0]][$c[1]]["income"];
			// TODO : If I implement the special islands, this algorithm will need adapting!
			// (Can't skip over voids, because that would include the last income space...)
			// (Skip over income -1, perhaps?)
			// While the current income space is covered, move onto the next one
			while (count($data[$c[0]][$c[1]]["goods"]) > 0 || $data[$c[0]][$c[1]]["income"] < 0) {
				$c = [$c[0] + 1, $c[1] + 1];
				$max_income = $data[$c[0]][$c[1]]["income"];
			}
			$income += $max_income;
		}
		$board["_income"] = $income;
		
		// Save bonuses for this board!
		$bonus = [];
		for ($i = 0; $i < $board["cols"]; $i++) {
			for ($j = 0; $j < $board["rows"]; $j++) {
				$b = $data[$i][$j]["bonus"];
				if (count($b) > 0 && count($data[$i][$j]["goods"]) == 0) {
					// Surrounded?
					$neighbours = self::neighbours([$i, $j], false);
					foreach ($neighbours as $nn) {
						
						if (self::isValidCoords($game, $board, $nn, true)) { // here not passing $good, fine?
							$datum = $data[$nn[0]][$nn[1]];

							if (count($datum["goods"]) == 0) {
								if ($datum["type"] != "bonus") { // check for 2-cell type bonuses (waterford)
									// Not surrounded!
									continue 2;
								}
							}
						}
					}
					$bonus = array_merge($bonus, $b);
				}
			}
		}
		$board["_bonus"] = $bonus;

		// Cache points
		$negatives = 0;
		foreach ($board["negatives"] as $coord) {
			$datum = $data[$coord[0]][$coord[1]];
			if (count($datum["goods"]) == 0) {
				// Not covered!
				$negatives++;
			}
		}
		$board["_negatives"] = $negatives;
		
		if ($board["feast"]) {
			// Must touch the bottom row
			// (server-side only)
			// Can't overlap the current round, or further right
			// (server-side only)
			// Only one good of each type can be horizontal
			$horizontal_goods = [];
			$has_186 = Occupation::has($player_id, 186);
			$has_160 = Occupation::has($player_id, 160);
			foreach ($board["goods"] as $good) {
				if ($has_160 && $game->getGameStateValue('occ_soberman') && $good["type"] == "mead") {
					throw new BgaUserException( clienttranslate("You cannot place mead because you activated the Sober Man") );
				}
				if ($good["coords"][1] > 0) {
					throw new BgaVisibleSystemException( "Error in makeBoardArray (not on bottom row)" );
				}
				if ($good["cols"] == $good["rows"]) {
					// Square goods are exempt
					continue;
				}
				if ($good["rotation"] == 0 || $good["rotation"] == 2) {
					if (! isset($horizontal_goods[$good["type"]])) {
						$horizontal_goods[$good["type"]] = 0;
					}
					$horionztal_limit = 1;
					if ($good["type"] == "peas" && $has_186) {
						$horionztal_limit = 2;
					}
					if ($horizontal_goods[$good["type"]] > $horionztal_limit) {
						throw new BgaVisibleSystemException( "Error in makeBoardArray (same good horizontal)" );
					}
					$horizontal_goods[$good["type"]] += 1;
				}
			}
			
			// Cache the feast penalties
			$round = $game->getGameStateValue( 'round' );
			$short_game = $game->isShortGame();
			$spaces = 5 + $round;
			$penalties = 0;
			if ($short_game) {
				$spaces++;
			}
			for ($i = $emigration_columns; $i < $spaces; $i++) {
				$datum = $data[$i][0];
				if (count($datum["goods"]) == 0) {
					// Not covered!
					$penalties++;
				}
			}
			$board["_penalties"] = $penalties;
		}
	}
	
	public static function getEligibleIslands($player_id, $vikings) {
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

		return $islands;
	}

	public static function filterIslands($vikings) {
		$boards = FeastForOdin::getDataBoards();
		$islands = [];
		foreach ( $boards as $board) {
			if ($board['category'] == 'island' && $board['vikings'] == $vikings) {
				$islands[] = $board;
			}
		}
		return $islands;

	}

	public static function countBigBoats($player) {
		$num_big = 0;
		foreach ($player["ships"] as $s) {
			if ($s["type"] != "whalingboat") {
				$num_big++;
			}
		}
		return $num_big;
	}

	public static function hasShipsWithOre($player) {
		foreach ($player["ships"] as $k => $s) {
			if ($player["ships"][$k]["ore"] > 0) {
				return true;
			}
		}
		return false;
	}

	public static function hasBuiltArtisanShed($player) {
		foreach ($player["boards"] as $board) {
			if ($board["category"] === 'shed' && $board["type"] !== 'shed') {
				return true;
			}
		}
		return false;
	}

	public static function getArtisanShed($player) {
		foreach ($player["boards"] as $board) {
			if ($board["category"] === 'shed' && $board["type"] !== 'shed') {
				return $board;
			}
		}
		return false;
	}

	public static function getPersonalResourceMarker($player_id) {
		$player = FeastForOdin::GetObject("SELECT extra FROM player WHERE player_id = $player_id");

		if (!$player['extra']) {
			return null;
		}

		return json_decode($player['extra'], true)['personal_resource_marker'];
	}

	public static function getSecondPersonalResourceMarker($player_id) {
		$player = FeastForOdin::GetObject("SELECT extra FROM player WHERE player_id = $player_id");

		if (!$player['extra']) {
			return null;
		}

		return json_decode($player['extra'], true)['personal_resource_marker2'];
	}

	public static function setPersonalResourceMarker($player_id, $personal_resource_marker) {
		
		$player = FeastForOdin::GetObject("SELECT extra FROM player WHERE player_id = $player_id");

		if (!$player['extra']) {
			$player['extra'] = self::getExtraDefault();
		} else {
			$player['extra'] = json_decode($player['extra'], true);
		}

		$extra = json_encode([...$player['extra'], 'personal_resource_marker' => $personal_resource_marker]);

		FeastForOdin::DbQuery("UPDATE player SET extra = '$extra' WHERE player_id = $player_id");
	}

	public static function setSecondPersonalResourceMarker($player_id, $personal_resource_marker) {
		
		$player = FeastForOdin::GetObject("SELECT extra FROM player WHERE player_id = $player_id");

		if (!$player['extra']) {
			$player['extra'] = self::getExtraDefault();
		} else {
			$player['extra'] = json_decode($player['extra'], true);
		}

		$extra = json_encode([...$player['extra'], 'personal_resource_marker2' => $personal_resource_marker]);

		FeastForOdin::DbQuery("UPDATE player SET extra = '$extra' WHERE player_id = $player_id");
	}

	public static function getAutoPass($player_id) {
		$player = FeastForOdin::GetObject("SELECT extra FROM player WHERE player_id = $player_id");

		if (!$player['extra']) {
			return null;
		}

		return json_decode($player['extra'], true)['autopass'];
	}

	public static function setAutoPassAction($player_id, $action_type) {
		
		$player = FeastForOdin::GetObject("SELECT extra FROM player WHERE player_id = $player_id");

		if (!$player['extra']) {
			$player['extra'] = self::getExtraDefault();
		} else {
			$player['extra'] = json_decode($player['extra'], true);
		}

		$autopass = $player['extra']['autopass'];

		$autopass = [...$autopass, $action_type => 1];

		$extra = json_encode([...$player['extra'], 'autopass' => $autopass]);

		FeastForOdin::DbQuery("UPDATE player SET extra = '$extra' WHERE player_id = $player_id");
	}

	private static function getExtraDefault() {
		// here goes things that I don't want to update db for, ideally they should have their own cols 
		return [
			'autopass' => ['income' => 0, 'feast' => 0, 'bonus' => 0, 'remember' => 0],
			'personal_resource_marker' => null,
			'personal_resource_marker2' => null,
		];
	}
}