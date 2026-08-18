<?php

trait Reroll {
    function reroll( $check = true ) {
		self::checkAction( 'reroll' );
		
		$player_id = self::getActivePlayerId();
		
		$num_rolls = self::getGameStateValue( 'num_rolls' );
		
		$max_num_rolls = 3;
		if (Occupation::has($player_id, 153)) {
			$max_num_rolls++;
		}
		
		if ($num_rolls >= $max_num_rolls) {
			// Can't do more than 3 rerolls!
			throw new BgaUserException( 
				str_replace(
					'${num}',
					$max_num_rolls,
					self::_('You only have ${num} rolls')
				)
			);
		}
		
		$this->gamestate->nextState( 'reroll' );
	}
}