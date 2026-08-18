<?php

trait GoBack {
    function goBack() {
		self::checkAction( 'goBack' );
		$this->gamestate->nextState( 'goBack' );
	}
}