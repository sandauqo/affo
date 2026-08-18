<?php 

trait BuildArtisanShed {
    function buildArtisanShed( $shedToBuild ) {
		self::checkAction( 'buildArtisanShed' );

		if (!$this->doesExist($shedToBuild)) {
            throw new BgaVisibleSystemException( "Such artisan shed does not exist" );
		}

		if ($this->shedAlreadyTaken($shedToBuild)) {
            throw new BgaVisibleSystemException( "Artisan shed already taken" );
        }

		$data_boards = self::getDataBoards();
		$player_id = self::getActivePlayerId();
		$player = FfoPlayer::getFullPlayer( $player_id );
		FfoPlayer::addBoard($player, $data_boards[$shedToBuild]);
		FfoPlayer::saveFullPlayer($player);

		$this->gamestate->nextState('checkNextPlayer');
	}

	private function shedAlreadyTaken($shedToBuild) {
		$players = self::getCollectionFromDb("SELECT player_id id, boards FROM player");

        foreach ($players as $player) {
			$player['boards'] = json_decode($player['boards'], true);
            $playerShed = FfoPlayer::getArtisanShed($player);

            if ($playerShed) {

				if ($shedToBuild === $playerShed['type']) {
					return true;
				}

				$sheds = FeastForOdin::getDataArtisanSheds();

				foreach ($sheds as $shed) {
					$exploded = explode('-', $shed);
					if (in_array($playerShed['type'], $exploded)) {
						if (in_array($shedToBuild, $exploded)) {
							// found "counterpart" (flipped side is built)
							return true;
						}
					}
				}
			}
        }
        return false;
    }

	private function doesExist($shedToBuild) {
        $sheds = FeastForOdin::getDataArtisanSheds();

		$found = false;

		foreach ($sheds as $shed) {
			if (in_array($shedToBuild, explode('-', $shed))) {
				$found = true;
			}
		}

		return $found;
	}
}