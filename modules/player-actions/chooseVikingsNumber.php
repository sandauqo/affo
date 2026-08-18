<?php

trait ChooseVikingsNumber {

    function chooseVikingsNumber( $number ) {
		$this->setGameStateValue('5th_col_how_many_vikings', $number);
		$state = $this->gamestate->state();
		$this->buildAction($state);
	}

}