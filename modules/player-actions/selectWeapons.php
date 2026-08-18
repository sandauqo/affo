<?php

trait SelectWeapons {
    function selectWeapons($weapons) {
		if (count($weapons) != 2) {
			throw new BgaVisibleSystemException( "You must pick exactly two weapons" );
		}
		$player_id = self::getCurrentPlayerId();
		$hand = Weapon::getHand($player_id);

		foreach ($weapons as $w) {
			$found = false;
			foreach ($hand as $h) {
				if ($h["id"] == $w) {
					$found = true;
				}
			}
			if (!$found) {
				throw new BgaVisibleSystemException( "You do not have ${w}" );
			}
		}

		$discard = [];
		foreach ($weapons as $w) {
			foreach ($hand as $h) {
				if ($h["id"] == $w) {
					$discard[] = $h;
				}
			}
		}

		$this->saveWeapons();
		Weapon::discard($player_id, $discard, 'occupation-56');
		self::notifyAllPlayers( "discardWeapons", '', array(
			'player_id' => $player_id,
			'weapons' => $discard,
			'source' => 'occupation-56'
		));

		$player = FfoPlayer::getFullPlayer($player_id);
		$this->gain($player, 'ore', 1, 'occupation-56');
		FfoPlayer::saveFullPlayer($player);

		$this->gamestate->nextState( 'done' );
	}
}