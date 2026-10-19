<?php

include "buildAction.php";

	// when clicking an action space
	// a number of things can occur:
	// 1. you play an action normally
	// 2. you click on 5th col (norwegians only), and are prompted how many vikings do you want to play
	// 3. this is an inspector action (where you return 1 or 2 vikings from the action space)
	// 4. or similar
	// 

trait ActAction {

    use BuildAction;

    function action( $action_id  ) {
		self::checkAction( 'action' );

		$state = $this->gamestate->state();

		$player_id = self::getActivePlayerId();

		$thing_meeples = FfoPlayer::getMeeples($player_id, 'thing');

		if ($state['name'] == 'command-inspector') {
			$this->setGameStateValue( 'inspector_action', $action_id );
		} else {
			$this->setGameStateValue('action_id', $action_id);	
		}

		if ($state['name'] == 'pickAction' && str_contains("$action_id", "555") && count($thing_meeples) > 1) {
			if (! FfoPlayer::isActionAvailable($action_id)) {
				throw new BgaUserException( clienttranslate("That action is not available") );
			}
			$this->gamestate->nextState( 'vikingsNumber' );
		} else {
			if (str_contains("$action_id", "555") && count($thing_meeples) == 1) {
				// do not ask and auto pick 1 viking
				$this->setGameStateValue('5th_col_how_many_vikings', 1);
			}
			$this->buildAction($state);
		}
	}
}