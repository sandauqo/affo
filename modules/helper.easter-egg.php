<?php

trait EasterEgg
{
	static function baseGoodType($type) {
		return preg_replace('/_ee$/', '', $type);
	}

	// Called when a good leaves a player: an easter egg goes back into the pool.
	public function releaseEasterEgg($good) {
		$bases = ['berries', 'eggs', 'amber', 'gullgubber'];
		if (self::baseGoodType($good["type"]) === $good["type"]) {
			return;
		}
		$index = array_search(self::baseGoodType($good["type"]), $bases, true);
		if ($index === false) {
			return;
		}
		$this->setGameStateValue('ee_issued', (int) $this->getGameStateValue('ee_issued') & ~(1 << $index));
	}

	// A newly created berries/eggs/amber/gullgubber may turn into its (unique) easter egg version.
	// Easter egg goods passed in (e.g. given explicitly) are marked as issued.
	public function rollEasterEgg($good) {
		$bases = ['berries', 'eggs', 'amber', 'gullgubber'];
		$isEasterEgg = self::baseGoodType($good["type"]) !== $good["type"];
		$index = array_search(self::baseGoodType($good["type"]), $bases, true);
		if ($index === false) {
			return $good;
		}
		$bit = 1 << $index;
		$issued = (int) $this->getGameStateValue('ee_issued');
		if ($isEasterEgg) {
			$this->setGameStateValue('ee_issued', $issued | $bit);
			return $good;
		}
		if ($issued & $bit) {
			return $good;
		}
//		$chance = in_array($good["colour"], ['orange', 'red']) ? 12 : 9;
		$chance = in_array($good["colour"], ['orange', 'red']) ? 3 : 2;
		if (bga_rand(1, $chance) !== 1) {
			return $good;
		}
		$this->setGameStateValue('ee_issued', $issued | $bit);
		$data_goods = self::getDataGoods();
		return $data_goods[$good["type"] . "_ee"];
	}
}
