<?php

trait SelectResources {
    function selectResources($resources) {
		$actionId = $this->getGameStateValue('action_id');
		$command = $this->getString( 'currentCommand' );
		$components = explode('-', $command);
		$mustBeUnique = isset($components[1]) && $components[1] == "unique";

		if (count($resources) != $components[1]) {
			throw new BgaVisibleSystemException( "You must pick exactly two resources" );
		}
		if ($mustBeUnique && count($resources) !== count(array_unique($resources))) {
			throw new BgaVisibleSystemException( "You must pick two different resources" );
		}
		$player_id = self::getCurrentPlayerId();
		$player = FfoPlayer::getFullPlayer($player_id);

		// Stone Crusher
		if (in_array("stone", $resources) && Occupation::has($player_id, 156)) {
			$this->gain($player, "coin", 1, "occupation-156");
		}
		foreach ($resources as $resource) {
			$this->gain($player, $resource, 1, 'action-'.$actionId);
			$this->updatePlayerScore( $player );
			FfoPlayer::saveFullPlayer($player);
		}

		$this->gamestate->nextState( 'done' );
	}
}