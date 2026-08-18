<?php 

trait DrawPersonalResource {
    function drawPersonalResource( $personal_resource_marker, $isSecond = false ) {
		self::checkAction( 'drawPersonalResource' );

		$player_id = self::getActivePlayerId();
		$player = FfoPlayer::getFullPlayer($player_id);

        $markers = FeastForOdin::getDataPersonalResourceMarkers();
        if (!in_array($personal_resource_marker, $markers)) {
            throw new BgaVisibleSystemException( "Such resource marker does not exist" );
        }

        if ($this->alreadyTaken($personal_resource_marker)) {
            throw new BgaVisibleSystemException( "Resource marker already taken" );
        }

        if ($isSecond) {
            FfoPlayer::setSecondPersonalResourceMarker($player_id, $personal_resource_marker);
        } else {
            FfoPlayer::setPersonalResourceMarker($player_id, $personal_resource_marker);
        }

		$players = FeastForOdin::$game->loadPlayersBasicInfos();

		self::notifyAllPlayers( "drawPersonalResourceMarker", clienttranslate('${player_name} draws: ${marker} marker'), array(
			'player_id' => $player_id,
			'marker' => $personal_resource_marker,
            'player_name' => $players[$player["id"]]["player_name"],
		));

        $this->gamestate->nextState('checkNextPlayer');
	}

    private function alreadyTaken($markerToCheck) {
		$players = self::getCollectionFromDb("SELECT player_id id FROM player");

        foreach ($players as $player) {
            $marker = FfoPlayer::getPersonalResourceMarker($player['id']);
            $marker2 = FfoPlayer::getSecondPersonalResourceMarker($player['id']);
            if (in_array($markerToCheck, [$marker, $marker2])) {
                return true;
            }
        }
        return false;
    }
}