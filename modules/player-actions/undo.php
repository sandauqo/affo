<?php

trait ActUndo {

	// resets whole turn
	// purchased boats too
	// items placed on boards and confirmed also
	function undo() {
		self::checkAction( 'undo' );
		
		$player_id = self::getActivePlayerId();
			
		// Check the "can undo" global...
		$this->restoreState( $player_id );
		
		// Set state back to player action
		$this->gamestate->nextState( 'undo' );
	}
}