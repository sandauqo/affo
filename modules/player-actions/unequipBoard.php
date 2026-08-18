<?php

trait UnequipBoard {
    function unequipBoard( $board_index, $asset ) {
		// You can do this at any time, no need for self::checkAction
		
		$player_id = self::getCurrentPlayerId();
		$player = FfoPlayer::getFullPlayer($player_id);
		
		// Check you have the board (+ give it the resource)
		$found = null;
		foreach ($player["boards"] as $k => &$board) {
			if ($board["index"] == $board_index) {

				if ($board["category"] == "shed") {
					if (in_array($asset, ["wood", "stone"])) {
						// This is because of Steward:42B occupation
						throw new BgaUserException( "You cannot remove wood or stone from sheds." );
						$this->setGameStateValue( 'occ_steward', 0 );
					}
				}

				if (! isset($board[$asset])) {
					$board[$asset] = 0;
				}
				if ($board[$asset] == 0) {
					throw new BgaVisibleSystemException( "The board does not have any of that resource to remove" );
				}
				$board[$asset]--;
				
				$found = &$board;
				break;
			}
		}
		if (! isset($found)) {
			throw new BgaVisibleSystemException( "You do not have that board ($board_index)" );
		}

		// Gain the resource
		$this->gain($player, $asset, 1, "board-$board[index]", false);
		$this->updatePlayerScore( $player, false );
				
		// Notify
		self::notifyAllPlayers( "equipBoard", '', array(
			'player_id' => $player_id,
			'board_index' => $board_index,
			'resource' => $asset,
			'amount' => $board[$asset],
			'equip' => false,
		));
		
		FfoPlayer::saveFullPlayer($player);
	}
}