<?php

trait DiscardOccupation {
    function discardOccupation( $occupation_id, $playOrToken = null ) {
		self::checkAction( 'discardOccupation' );
		
		$player_id = self::getActivePlayerId();
		
		$state = $this->gamestate->state();
		if ($state['name'] == 'command-discardocc') {
			
			// 1. Check the occ is in player's hand
			$hand = Occupation::getHand($player_id);
			$found = null;
			foreach ($hand as $h) {
				if ($h["id"] == $occupation_id) {
					$found = $h;
					break;
				}
			}
			if (! isset($found)) {
				throw new BgaVisibleSystemException( "That occupation ($occupation_id) is not in your hand" );
			}
			// 2. Discard it
			$this->saveOccupations( $player_id );
			Occupation::discard($occupation_id);
			$this->incStat( 1, 'occupations_played', $player_id );

			// 3. Notify
			$found["location"] = "discard";
			$players = FeastForOdin::$game->loadPlayersBasicInfos();
			$cards_log = [];
			$cards_args = [
				'i18n' => []
			];
			$starter = $found["starter"] ? "true" : "false";
			$key = "inject_occupation_$found[ability]_$starter";
			$cards_log[] = '${'.$key.'}';
			$cards_args[$key] = FeastForOdin::$game->m_occupations[$found["ability"]]["name"];
			$cards_args["i18n"][] = $key;
			self::notifyAllPlayers( "discardOccupation", clienttranslate('${player_name} discards: ${cards_list}'), array(
				'player_id' => $player_id,
				'player_name' => $players[$player_id]["player_name"],
				'occupation' => $found,
				'cards_list' => [
					'log' => implode(' ', $cards_log),
					'args' => $cards_args
				],
			));
			
		} else {
			throw new BgaVisibleSystemException( "Unexpected state to discard occupation" );
		}
		
		$this->gamestate->nextState( 'done' );
	}
}