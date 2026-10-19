<?php 

trait DeclareFailure {

    function declareFailure( $check = true ) {
        if ($check) {
            self::checkAction( 'declareFailure' );
        }
        $player_id = self::getActivePlayerId();
        
        $players = $this->loadPlayersBasicInfos();
        self::notifyAllPlayers( "message", clienttranslate('${player_name} declares the result a failure'), array(
            'player_id' => $player_id,
            'player_name' => $players[$player_id]["player_name"],
        ));
        
        $state = $this->gamestate->state();
        if ($state['name'] == 'command-hunt' || $state['name'] == 'command-attack') {
            $player = FfoPlayer::getFullPlayer($player_id);
            $action_id = $this->getGameStateValue('action_id');
            $args = $this->argHunt();
            
            $ore = $args["ore_value"];
            $multiplier = $args["multiplier"];
            $die_value = $args["die_value"];
            $hunt_type = $args["hunt_type"];
            $resource_type = $args["resource_type"];
            $weapon_types = $args["weapon_types"];
            
            $base_value = $die_value + $ore * $multiplier;
            
            if (in_array($hunt_type, ['game', 'snare', 'whales', 'whalesminor', 'game2'])) {
                if ($base_value <= 0) {
                    throw new BgaUserException( clienttranslate("You cannot declare a failure with a result of 0") );
                }
            }
            
            if ($hunt_type == 'raid') {
                if (Occupation::has($player_id, 134)) {
                    $this->gain($player, "mead", 1, "occupation-134");
                }
            }
            if ($hunt_type == 'raid' || $hunt_type == 'pillage') {
                // Stone Crusher
                if (Occupation::has($player_id, 156)) {
                    $this->gain($player, "coin", 1, "occupation-156");
                }
            }
    
            // Get the consolation prize
            $this->saveWeapons();
            foreach ($weapon_types as $weapon_type) {
                Weapon::draw($player_id, $weapon_type, 'action-'.$action_id);
            }
            
            if ($resource_type) {
                $this->gain($player, $resource_type, 1, 'action-'.$action_id);
            }
            
            // Meeples returned
            $refund = 0;
            
            $command = $this->getString( 'currentCommand' );
            $components = explode('-', $command);
            if ($this->getGameStateValue( 'no_hunt_refunds' ) || isset($components[2]) && $components[2] == "0") {
                // No refund!
            } else {
                // Snare, 1 back
                // Whaling, 2 back
                // Pillage, 1 back
                if (in_array($hunt_type, ['pillage', 'snare', 'game2'])) {
                    $refund = 1;
                } else if (in_array($hunt_type, ['whales', 'whalesminor'])) {
                    $refund = 2;
                }
                if ($refund > 0) {
                    $placed_action_id = $this->getGameStateValue('placed_action_id');
                    $meeples = FfoPlayer::meepleRefund($player_id, $placed_action_id, $refund);
                    self::notifyAllPlayers( "meepleMove", '', array(
                        'meeples' => $meeples,
                    ));
                    
                    if (Occupation::has($player_id, 151)) {
                        $this->gain($player, "coin", 1, "occupation-151");
                    }
                }
            }
            
            FfoPlayer::saveFullPlayer($player);
            
            if ($hunt_type == "raid" && Occupation::has($player_id, 104) && $args["num_rolls"] <= 2) {
                // Also gain a fish or an oil!
                Action::unshift(["state-disheartenedwarrior"]);
            }
        }
        
        $this->gamestate->nextState( 'declareFailure' );
    }

}

