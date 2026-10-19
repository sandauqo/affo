<?php

//////////////////////////////////////////////////////////////////////////////
//////////// Game state actions
////////////

trait Undo {
	function saveState($player_id) {
		if (! $this->getGameStateValue( 'undo_supported' )) {
			return;
		}
		// Save state
		// Make this a bit quicker by only saving what needs to be saved...
		
		// Player
		$player = FfoPlayer::getFullPlayer($player_id);
		self::DbQuery("DELETE FROM player_saved WHERE 1");
		self::DbQuery("INSERT INTO player_saved
		  (player_id, player_score, player_score_aux, coin, wood, stone, ore, penalty, vp, goods, boards, ships, emigrations) VALUES (
			$player_id,
			$player[score],
			$player[score_aux],
			$player[coin], 
			$player[wood], 
			$player[stone], 
			$player[ore], 
			$player[penalty], 
			$player[vp], 
			'" . json_encode($player["goods"]) . "',
			'" . json_encode($player["boards"]) . "',
			'" . json_encode($player["ships"]) . "',
			'" . json_encode($player["emigrations"]) . "'
		  )");
		
		self::DbQuery("DELETE FROM meeple_saved WHERE 1");
		self::DbQuery("INSERT INTO meeple_saved
		  SELECT * FROM meeple
		  WHERE player_id = $player_id");
		
		// Globals
		$this->setGameStateValue('num_sheds_saved', $this->getGameStateValue('num_sheds'));
		$this->setGameStateValue('num_stonehouses_saved', $this->getGameStateValue('num_stonehouses'));
		$this->setGameStateValue('num_longhouses_saved', $this->getGameStateValue('num_longhouses'));

		$this->setGameStateValue('num_vp_token_4_saved', $this->getGameStateValue('num_vp_token_4'));
		$this->setGameStateValue('num_vp_token_3_saved', $this->getGameStateValue('num_vp_token_3'));
	}
	
	// Snapshots the whole occupation table (all players and the deck), so the
	// restore can never mix up players, and keeps card ids stable.
	// $player_id is kept for the existing callers; it no longer limits the snapshot.
	function saveOccupations( $player_id = null ) {
		if (! $this->getGameStateValue( 'undo_supported' ) || $this->getGameStateValue( 'undo_occupation' )) {
			return;
		}
		
		$this->setGameStateValue( 'undo_occupation', 1 );
		self::DbQuery("DELETE FROM occupation_saved WHERE 1");
		self::DbQuery("INSERT INTO occupation_saved
		  SELECT * FROM occupation");
	}
	
	function saveWeapons() {
		if (! $this->getGameStateValue( 'undo_supported' ) || $this->getGameStateValue( 'undo_weapon' )) {
			return;
		}
		
		$this->setGameStateValue( 'undo_weapon', 1 );
		self::DbQuery("DELETE FROM weapon_saved WHERE 1");
		self::DbQuery("INSERT INTO weapon_saved
		  SELECT * FROM weapon");
	}
	
	function saveForge() {
		if (! $this->getGameStateValue( 'undo_supported' ) || $this->getGameStateValue( 'undo_forge' )) {
			return;
		}
		
		$this->setGameStateValue( 'undo_forge', 1 );
		self::DbQuery("DELETE FROM forge_saved WHERE 1");
		self::DbQuery("INSERT INTO forge_saved
		  SELECT * FROM forge");
	}
	
	function saveStringGlobals() {
		if (! $this->getGameStateValue( 'undo_supported' ) || $this->getGameStateValue( 'undo_string_globals' )) {
			return;
		}
		
		$this->setGameStateValue( 'undo_string_globals', 1 );
		self::DbQuery("DELETE FROM string_globals_saved WHERE 1");
		self::DbQuery("INSERT INTO string_globals_saved
		  SELECT * FROM string_globals");
	}
	
	function saveIslands() {
		if (! $this->getGameStateValue( 'undo_supported' ) || $this->getGameStateValue( 'undo_islands' )) {
			return;
		}
		
		$this->setGameStateValue( 'undo_islands', 1 );
		self::DbQuery("DELETE FROM available_islands_saved WHERE 1");
		self::DbQuery("INSERT INTO available_islands_saved
		  SELECT * FROM available_islands");
	}
	
	function saveMountainStrips() {
		if (! $this->getGameStateValue( 'undo_supported' ) || $this->getGameStateValue( 'undo_mountain_strips' )) {
			return;
		}
		
		$this->setGameStateValue( 'undo_mountain_strips', 1 );
		self::DbQuery("DELETE FROM mountain_strips_saved WHERE 1");
		self::DbQuery("INSERT INTO mountain_strips_saved
		  SELECT * FROM mountain_strips");
	}
	
	// occ 70 and 71 only (where you move a special tile from another player's board to your own supply)
	function undoComplexOcc($occ) {
		$s = $this->getString("occ:$occ");
		if ($s) {
			$parts = explode("-", $s);
			$player_id = +$parts[0];
			$coords = [+$parts[1], +$parts[2]];
			$rotation = +$parts[3];
			$flipped = $parts[4] == 'true' ? true : false;
			$board_index = +$parts[5];
			
			$player = FfoPlayer::getFullPlayer( $player_id );
			
			// Remove all matching goods
			foreach ($player["boards"] as $k => &$board) {
				if ($board["index"] == $board_index) {
					foreach ($board["goods"] as $key => $good) {
						if (! empty($good["occ:$occ"])) {
							unset($board["goods"][$key]);
						}
					}
					$board["goods"] = array_values($board["goods"]);
					
					$data_goods = self::getDataGoods();
					$good = $data_goods[$occ == 70 ? "cane" : "horn"];
					$good["rotation"] = $rotation;
					$good["flipped"] = $flipped;
					$good["coords"] = $coords;
					$board["goods"][] = $good;
					
					self::notifyAllPlayers( "updateBoards", '', array(
						'player_id' => $player_id,
						'update' => [
							"board-$board[index]" => [
								$good
							]
						],
						'skip_animate' => true,
					));
					break;
				}
			}
			
			FfoPlayer::saveFullPlayer($player);
		}
	}
	
	function restoreState($player_id) {
		if (! self::getGameStateValue( 'undo_supported' )) {
			throw new BgaVisibleSystemException( "Undo is not supported in this game. (Game started before feature added.)" );
		}
		if (! self::getGameStateValue( 'can_undo' )) {
			throw new BgaUserException( self::_("You cannot undo at this time") );
		}
		// Restore state
		
		if ($this->getGameStateValue( 'undo_occupation' )) {
			self::DbQuery("DELETE FROM occupation WHERE 1");
			self::DbQuery("INSERT INTO occupation
			  SELECT * FROM occupation_saved");
		}
		
		if ($this->getGameStateValue( 'undo_weapon' )) {
			self::DbQuery("DELETE FROM weapon WHERE 1");
			self::DbQuery("INSERT INTO weapon
			  SELECT * FROM weapon_saved");
		}
		
		if ($this->getGameStateValue( 'undo_forge' )) {
			self::DbQuery("DELETE FROM forge WHERE 1");
			self::DbQuery("INSERT INTO forge
			  SELECT * FROM forge_saved");
		}
		
		if ($this->getGameStateValue( 'undo_string_globals' )) {
			// If occ:70 or occ:71 are set, undo them!
			$this->undoComplexOcc(70);
			$this->undoComplexOcc(71);
			
			self::DbQuery("DELETE FROM string_globals WHERE 1");
			self::DbQuery("INSERT INTO string_globals
			  SELECT * FROM string_globals_saved");
		}
		
		if ($this->getGameStateValue( 'undo_mountain_strips' )) {
			self::DbQuery("DELETE FROM mountain_strips WHERE 1");
			self::DbQuery("INSERT INTO mountain_strips
			  SELECT * FROM mountain_strips_saved");
		}
		
		if ($this->getGameStateValue( 'undo_islands' )) {
			self::DbQuery("DELETE FROM available_islands WHERE 1");
			self::DbQuery("INSERT INTO available_islands
			  SELECT * FROM available_islands_saved");
		}
		
		self::DbQuery("DELETE FROM meeple WHERE player_id = $player_id");
		self::DbQuery("INSERT INTO meeple
		  SELECT * FROM meeple_saved");
		  
		self::DbQuery("DELETE FROM command_queue WHERE 1");
		
		// Player
		$player_saved = self::getObject("SELECT * FROM player_saved WHERE player_id = $player_id");

		FeastForOdin::DbQuery("UPDATE player SET 
		  player_score = $player_saved[player_score], 
		  player_score_aux = $player_saved[player_score_aux], 
		  coin = $player_saved[coin], 
		  wood = $player_saved[wood], 
		  stone = $player_saved[stone], 
		  ore = $player_saved[ore], 
		  penalty = $player_saved[penalty], 
		  vp = $player_saved[vp], 
		  goods = '" . ($player_saved["goods"]) . "',
		  boards = '" . ($player_saved["boards"]) . "',
		  ships = '" . ($player_saved["ships"]) . "',
		  emigrations = '" . ($player_saved["emigrations"]) . "'
		WHERE player_id = $player_id");
		
		$player = FfoPlayer::getFullPlayer($player_id);
		$player["weapons"] = Weapon::getHand($player_id);
		$player["occupationsHand"] = Occupation::getHand($player_id);
		$player["occupationsPlayed"] = Occupation::getPlayed($player_id);
		$player["meeples"] = FfoPlayer::getMeeples($player_id);
		$player["score_breakdown"] = self::updatePlayerScore( $player, false, false );
			
		// Globals
		$this->setGameStateValue('num_sheds', $this->getGameStateValue('num_sheds_saved'));
		$this->setGameStateValue('num_stonehouses', $this->getGameStateValue('num_stonehouses_saved'));
		$this->setGameStateValue('num_longhouses', $this->getGameStateValue('num_longhouses_saved'));

		$this->setGameStateValue('num_vp_token_4', $this->getGameStateValue('num_vp_token_4_saved'));
		$this->setGameStateValue('num_vp_token_3', $this->getGameStateValue('num_vp_token_3_saved'));

		// Notif to update player!
		$players = self::loadPlayersBasicInfos();
		self::notifyAllPlayers( "undoTurn", clienttranslate('${player_name} undoes their turn'), array(
			'player_id' => $player_id,
			'player_name' => $players[$player_id]["player_name"],
			'player' => $player,
			"islands" => Island::getAvailable(),
			"forge" => Forge::getAvailable(),
			"mountainstrips" => MountainStrip::getAvailable(),
			"num_sheds" => $this->getGameStateValue('num_sheds'),
			"num_stonehouses" => $this->getGameStateValue('num_stonehouses'),
			"num_longhouses" => $this->getGameStateValue('num_longhouses'),
			"num_vp_token_4" => $this->getGameStateValue("num_vp_token_4"),
			"num_vp_token_3" => $this->getGameStateValue("num_vp_token_3"),
		));
		
		self::setGameStateValue( 'just_restored', 1 );
	}
}