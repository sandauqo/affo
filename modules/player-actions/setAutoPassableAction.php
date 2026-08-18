<?php 

trait SetAutoPassAction {
    function setAutoPassAction( $action_type ) {

		// TODO: check if setting enabled

		$player_id = self::getCurrentPlayerId();
		$player = FfoPlayer::getFullPlayer($player_id);

		FfoPlayer::setAutoPassAction($player_id, $action_type);
	}
}