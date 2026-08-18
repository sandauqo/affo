<?php 

trait ChooseBestOfTwoStartingOcc {
    function chooseBestOfTwoStartingOcc( $occupation_id ) {
        self::checkAction( 'pickOneOccupationToKeep' );

        $player_id = self::getCurrentPlayerId();

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

        $discarded = [];

        $state = $this->gamestate->state();

        if ($state['name'] == 'bestOfTwoStartingOcc') {
            foreach ($hand as $h) {
                if ($h["id"] !== $occupation_id) {
                    $this->saveOccupations( $player_id );
                    $discarded = $h;
                    Occupation::discard($h["id"]);
                }
            }
        } else if ($state['name'] == 'discardOneBrown') {
            foreach ($hand as $h) {
                if ($h["id"] !== $occupation_id && $h['starter'] === '0') {
                    $this->saveOccupations( $player_id );
                    $discarded = $h;
                    Occupation::discard($h["id"]);
                }
            }
        }

        $this->saveOccupations( $player_id );
    
        //TODO: clean this up
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

        self::notifyAllPlayers( "discardOccupation", clienttranslate('${player_name} picks the occupation to keep'), array(
            'player_id' => $player_id,
            'player_name' => $players[$player_id]["player_name"],
            'occupation' => $discarded,
            'cards_list' => [
                'log' => implode(' ', $cards_log),
                'args' => $cards_args
            ],
        ));

        $this->gamestate->setPlayerNonMultiactive( $player_id, 'nextPhase' );
	}
}