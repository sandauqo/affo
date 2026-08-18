<?php

trait SelectIsland {

    function selectIsland($island) {
        self::checkAction( 'selectIsland' );
		
		$command = $this->getString( 'currentCommand' );
		$components = explode('-', $command);
		$num = +$components[1];
		
		$player_id = self::getActivePlayerId();
		$action_id = $this->getGameStateValue('action_id');
		$player = FfoPlayer::getFullPlayer($player_id);
			
		$args = self::argExplore();
	
		// find the current explore column (how many vikings)
		// see if the selected island is:
			// - available
			// - related
		// take it!
		
		$islands = Island::getAvailable();
		if (! in_array($island, $args['islands'])) {
			throw new BgaUserException( self::_("That island is not available") );
		}
		
		// 1. Island must be available
		$found = null;
		foreach ($islands as $i) {
			if ($i["name"] == $island) {
				$found = $i;
				break;
			}
		}
		
		if (! isset($found)) {
			throw new BgaUserException( self::_("That island is not available") );
		}
		
		// 2. If there are coins on it, take the coins
		if ($found["coins"] > 0) {
			$this->gain($player, "coin", $found["coins"], 'action-'.$action_id);
			
			if ($found["coins"] == 2 && Occupation::has($player_id, 124)) {
				Action::push(["state-laborer"]);
			}
		}
		
		// If you have a longship, draw a weapon
		if (FeastForOdin::canAfford($player, "longship", 1) && Occupation::has($player_id, 12)) {
			Action::push(["draw-weapon:occupation-12"]);
		}
		
		// 3. Take the island, and remove from availability
		$new_board = FfoPlayer::addBoard($player, $found["data"]);
		// Cache the initial values (for e.g. Iceland)
		FfoPlayer::checkProvisionals($this, $player["boards"][$new_board["index"]], $player_id);
		$this->saveIslands();
		
		Island::remove($island);
		
		FfoPlayer::saveFullPlayer($player);
			
		$state = $this->gamestate->state();
		if ( $state['name'] == 'command-explore' || $state['name'] == 'command-explorepassable') {
			if ($action_id == 124) {
				$this->setGameStateValue( 'double_action_first_taken', 1);
			}
		}

		self::notifyAllPlayers( "updateIslands", '', array(
			'islands' => Island::getAvailable(),
		));
		
		if (Occupation::has($player_id, 125)) {
			Action::unshift(["take-rune:occupation-125"]);
		}
		
		// Set state back to player action
		$this->gamestate->nextState( 'done' );
    }

}