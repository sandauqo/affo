<?php

trait SelectForSlaughter {
    function selectForSlaughter($animal) {

		// self::checkAction( 'selectForSlaughter' );
		$actionId = $this->getGameStateValue('action_id');

		if(!in_array($animal, ['sheep', 'pregnantsheep', 'horse', 'pregnanthorse', 'cattle', 'pregnantcattle', 'pig'])) {
			throw new BgaUserException( clienttranslate("Wrong selection for slaughter") );
		}
		$player_id = self::getCurrentPlayerId();
		$player = FfoPlayer::getFullPlayer($player_id);
		$this->spend($player, $animal, 1, 'action-'.$actionId);
		$this->gain($player, "saltmeat", 1, 'action-'.$actionId);

		if (str_contains($animal, 'sheep')) {
			$this->gain($player, "wool", 1, 'action-'.$actionId);
			$this->gain($player, "hide", 1, 'action-'.$actionId);

		} else if ($animal === 'pig') {
			$this->gain( $player, "skinandbones", 1, 'action-'.$actionId);
		} else {
			$this->gain($player, "leather", 1, 'action-'.$actionId);
		}

		$this->updatePlayerScore( $player, false );
		FfoPlayer::saveFullPlayer($player);

		$this->gamestate->nextState( 'done' );
	}
}