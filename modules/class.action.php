<?php

class Action
{
	// For each action, I want to record:
	/*
	- the board it is on (base or norwegians?)
	- number of meeples
	- what it does in some easy format?
	- pre-actions - draw/play occ
	*/
	public $board;
	public $numberMeeples;
	public $preCommands;
	public $commands;
	public $cost;
	public $prerequisite;

	function __construct(
		$board,
		$numberMeeples,
		$preCommands,
		$commands,
		$cost,
		$prerequisite
	) {
		$this->board = $board;
		$this->numberMeeples = $numberMeeples;
		$this->preCommands = $preCommands;
		$this->commands = $commands;
		$this->cost = $cost;
		$this->prerequisite = $prerequisite;
	}

	private static function makeBaseAction(
		$numberMeeples,
		$commands,
		$cost = [],
		$prerequisite = [],
		$norwegiansLastColTwoVikings = false
	) {
		$preCommands = [];
		if ($numberMeeples == 3) {
			// Do linked actions before maybe drawing occ, so player can undo after we can check reqs
			$preCommands[] = "linkedactions";
			$preCommands[] = "beforeorafter-draw-occupation";
		} elseif ($numberMeeples == 4 || $norwegiansLastColTwoVikings) {
			$preCommands[] = "beforeorafter-play-occupation";
			$preCommands[] = "linkedactions";
		} else {
			$preCommands[] = "linkedactions";
		}
		return new Action(
			"base",
			$numberMeeples,
			$preCommands,
			$commands,
			$cost,
			$prerequisite
		);
	}
	
	public static function getModifiedAction($actionId, $playerId, $norwegiansLastColVikingNumber = null) {
		if (FeastForOdin::$game->isNorwegiansExpansion()) {
			// not a Norwegians action!
			if (!in_array($actionId, self::getNorwegiansActions())) {
				throw new BgaUserException( FeastForOdin::totranslate("That action is not available.") );

			}
		} else {
			// not a base game action!
			if (!in_array($actionId, self::getBaseGameActions())) {
				throw new BgaUserException( FeastForOdin::totranslate("That action is not available.") );
			}

		}

		$actions = self::getAction($actionId, $norwegiansLastColVikingNumber);

		if (in_array($actionId, [73, 74, 75, 76])) {
			// No pre-commands for clone actions
			foreach ($actions as &$a) {
				$a->preCommands = [];
			}
		}
		
		if (Occupation::has($playerId, 99)) {
			// Swap draw occupation for command-preceptor
			foreach ($actions as &$a) {
				foreach ($a->commands as $key => $command) {
					if ($command == "draw-occupation" || $command == "beforeorafter-draw-occupation") {
						$a->commands[$key] = "state-preceptor";
					}
				}
				
				foreach ($a->preCommands as $key => $command) {
					if ($command == "draw-occupation" || $command == "beforeorafter-draw-occupation") {
						$a->preCommands[$key] = "state-preceptor";
					}
				}
			}
		}

		if (in_array($actionId, self::filterActions('livestock', 'row'))) {
			// Peddler discount for Livestock Market
			if (Occupation::has($playerId, 1)) {
				foreach ($actions as &$a) {
					foreach ($a->cost as $asset => $amount) {
						if ($asset == 'coin' && $amount > 0) {
							$a->cost[$asset]--;
						}
					}
				}
			}
		}
		
		if (in_array($actionId, [21, 22, 20, 86, 87, 88, 121, 124])) {
			// Patron discount
			if (Occupation::has($playerId, 2)) {
				foreach ($actions as &$a) {
					foreach ($a->cost as $asset => $amount) {
						if ($asset == "coin" && $amount > 0) {
							$a->cost[$asset]--;
						}
					}
				}
			}
		}
		
		if (in_array($actionId, Action::filterActions('houses', 'row'))) {
			// Build houses
			if (Occupation::has($playerId, 116)) {
				// Master Bricklayer
				foreach ($actions as &$a) {
					foreach ($a->cost as $asset => $amount) {
						if ($asset == "stone" && $amount > 0) {
							$a->cost[$asset]--;
						}
					}
				}
			}
		}
		
		if (in_array($actionId, [5])) {
			// Knarr builder option
			if (Occupation::has($playerId, 105)) {
				$innerActions = ["build-knarr"];
				if (Occupation::has($playerId, 148)) {
					// Spice trader
					$innerActions[] = 'state-spicetrader';
				}
				if (Occupation::has($playerId, 117)) {
					// Shipowner
					$innerActions[] = 'upgrade-1-1';
				}
				$actions[] = self::makeBaseAction(1, $innerActions, ["wood" => 3]);
			}
		}
		
		if (in_array($actionId, [57])) {
			// Dorestad Traveller option
			if (Occupation::has($playerId, 185)) {
				$actions[] = self::makeBaseAction(1, ["upgrade-2-1-true"]);
			}
		}

		if (in_array($actionId, [77, 5550])) {
			$player = FfoPlayer::getFullPlayer($playerId);
			if (FfoPlayer::hasBuiltArtisanShed($player)) {
				$actions = [$actions[0]];
			}
		}

		// Modifier
		if (in_array($actionId, [5554])) {
			$player = FfoPlayer::getFullPlayer($playerId);
			if (Occupation::has($playerId, 39)) {
			}
		}

		return $actions;
	}
	
	public static function getCol($action_id) {
		if ($action_id >= 5550) { // norwegians 5th col ids are 555x
			return 5;
		}
		return (($action_id - 1) % 4) + 1;
	}

	public static function getMeepleCount($action_id, $norwegiansLastColVikingNumber) {
		if ($norwegiansLastColVikingNumber) {
			return $norwegiansLastColVikingNumber;
		}
		return (($action_id - 1) % 4) + 1;
	}

	public static function getAction($actionId, $norwegiansLastColVikingNumber = null) {
		$n = self::getMeepleCount($actionId, $norwegiansLastColVikingNumber);
		switch ($actionId) {
			case 1:
				return [self::makeBaseAction($n, ["build-shed"], ["wood" => 2])];
			case 2:
				return [self::makeBaseAction($n, ["build-stonehouse"], ["stone" => 1])];
			case 3:
				return [self::makeBaseAction($n, ["build-longhouse"], ["stone" => 2])];
			case 4:
				return [
					self::makeBaseAction(
						$n,
						["build-stonehouse-longship"],
						["wood" => 2, "stone" => 2]
					),
					self::makeBaseAction(
						$n,
						["build-longhouse-knarr"],
						["wood" => 2, "stone" => 2]
					),
				];
			case 5:
				return [self::makeBaseAction($n, ["build-whalingboat"], ["wood" => 1])];
			case 6:
				return [self::makeBaseAction($n, ["build-knarr"], ["wood" => 2])];
			case 7:
				return [self::makeBaseAction($n, ["build-longship"], ["wood" => 2])];
			case 9:
				return [self::makeBaseAction($n, ["hunt-game"])];
			case 10:
				return [self::makeBaseAction($n, ["hunt-game"])];
			case 11:
				return [self::makeBaseAction($n, ["hunt-whales"], [], ["whalingboat" => 1])];
			case 12:
				return [
					self::makeBaseAction($n, ["hunt-whalesminor"], [], ["whalingboat" => 1]),
				];
			case 13:
				return [self::makeBaseAction($n, ["take-stockfish"])];
			case 14:
				return [self::makeBaseAction($n, ["hunt-snare"])];
			case 17:
				return [
					self::makeBaseAction(
						$n,
						["take-stockfish-2"],
						["coin" => 1]
					),
				];
			case 18:
				return [self::makeBaseAction($n, ["take-sheep"], ["coin" => 1])];
			case 19:
				return [
					self::makeBaseAction($n, ["take-sheep"]),
					self::makeBaseAction($n, ["take-cattle"], ["coin" => 1]),
				];
			case 20:
				return [
					self::makeBaseAction(
						$n,
						["take-sheep", "take-cattle"],
						["coin" => 3]
					),
				];
			case 21:
				return [
					self::makeBaseAction(
						$n,
						["take-saltmeat-2"],
						["coin" => 2]
					),
				];
			case 22:
				return [
					self::makeBaseAction($n, ["take-cattle", "take-milk"], ["coin" => 3]),
				];
			case 25:
				return [self::makeBaseAction($n, ["take-beans", "take-coin"])];
			case 26:
				return [
					self::makeBaseAction($n, [
						"take-flax",
						"take-stockfish",
						"take-coin",
					]),
				];
			case 27:
				return [
					self::makeBaseAction($n, [
						"take-fruits",
						"take-oil",
						"take-saltmeat",
						"take-coin",
					]),
				];
			case 28:
				return [
					self::makeBaseAction($n, [
						"take-spices",
						"take-coin",
						"takeif-milk-cattle",
						"takeif-milk-cattle",
						"takeif-wool-sheep",
					]),
				];
			case 29:
				return [self::makeBaseAction($n, ["collect-milk"], [], ["cattle" => 1])];
			case 30:
				return [
					self::makeBaseAction($n, [
						"take-mead-2",
						"take-coin-2",
					]),
				];
			case 31:
				return [self::makeBaseAction($n, ["collect-wool"], [], ["sheep" => 1])];
			case 33:
				return [self::makeBaseAction($n, ["take-linen"], ["flax" => 1])];
			case 34:
				return [
					self::makeBaseAction(
						$n,
						["take-clothing", "take-coin", "take-coin"],
						["linen" => 1, "hide" => 1]
					),
				];
			case 35:
				return [self::makeBaseAction($n, ["forge"], ["ore" => 1])];
			case 36:
				return [
					self::makeBaseAction($n, [
						"take-coin-4",
						"exchange-wool-robe",
						"exchange-silverware-jewelry",
					]),
				];
			case 37:
				return [
					self::makeBaseAction($n, ["take-coin", "take-rune"], ["stone" => 1]),
				];
			case 38:
				return [
					self::makeBaseAction($n, ["take-coin", "take-chest"], ["wood" => 1]),
					self::makeBaseAction($n, ["take-coin", "take-chest"], ["ore" => 1]),
				];
			case 39:
				return [
					self::makeBaseAction(
						$n,
						["take-rune-2", "take-chest-2"],
						["wood" => 2, "stone" => 2]
					),
				];
			case 41:
				return [self::makeBaseAction($n, ["mountain-2", 'mountainreset'])];
			case 42:
				return [self::makeBaseAction($n, ["tpp-wood", "take-ore"])];
			case 43:
				return [self::makeBaseAction($n, ["mountain-3", "mountain-2", 'mountainreset'])];
			case 44:
				return [self::makeBaseAction($n, ["mountain-4", 'mountainreset', "upgrade-2-2"])];
			case 45:
				return [self::makeBaseAction($n, ["mountain-1", 'mountainreset', "upgrade-1-1"])];
			case 46:
				return [self::makeBaseAction($n, ["mountain-3", 'mountainreset', "upgrade-1-1"])];
			case 47:
				return [
					self::makeBaseAction($n, [
						"upgrade-3-1",
						"draw-weapon-x-4",
					]),
				];
			case 49:
				return [self::makeBaseAction($n, ["upgrade-2-1-true"])];
			case 50:
				return [self::makeBaseAction($n, ["upgrade-3-1-true"])];
			case 51:
				return [self::makeBaseAction($n, ["upgrade-4-1-true"])];
			case 52:
				return [
					self::makeBaseAction($n, [
						"mountain-2",
						"mountain-2",
						"mountain-2",
						"mountain-2", 
						'mountainreset',
					]),
					self::makeBaseAction($n, ["upgrade-3-2-true"]),
				];
			case 53:
				return [self::makeBaseAction($n, ["trade"], ["coin" => 1], ["knarr" => 1])];
			case 54:
				return [self::makeBaseAction($n, ["trade"], ["coin" => 1], ["knarr" => 1])];
			case 55:
				return [self::makeBaseAction($n, ["forgebuy", "forgebuy"], [], ["knarr" => 1])];
			case 57:
				return [self::makeBaseAction($n, ["attack-raid"], [], ["longship" => 1])];
			case 58:
				return [self::makeBaseAction($n, ["attack-pillage"], [], ["longship" => 1])];
			case 59:
				return [self::makeBaseAction($n, ["attack-pillage"], [], ["longship" => 1])];
			case 60:
				// Take care
				return [
					self::makeBaseAction(
						$n,
						["take-silverhoard"],
						[],
						["longship" => 2]
					),
				];
			case 61:
				return [
					self::makeBaseAction($n, ["explore-1"], [], ["boat" => 1]),
				];
			case 62:
				return [
					self::makeBaseAction($n, ["explore-2"], [], ["bigboat" => 1]),
				];
			case 63:
				return [
					self::makeBaseAction($n, ["explore-3"], [], ["longship" => 1]),
				];
			case 65:
				return [self::makeBaseAction($n, ["take-coin", "draw-occupation"])];
			case 66:
				return [self::makeBaseAction($n, ["emigrate"], [], ["bigboat" => 1])];
			case 67:
				return [self::makeBaseAction($n, ["emigrate"], [], ["bigboat" => 1])];
			case 68:
				return [
					self::makeBaseAction(
						$n,
						["swapwhalingboatknarr", "emigrate"],
						[],
						["boat" => 1]
					),
				];
			case 69:
				return [
					self::makeBaseAction(
						$n,
						["take-coin", "play-occupation"],
						["stone" => 1]
					),
					self::makeBaseAction(
						$n,
						["take-coin", "play-occupation"],
						["ore" => 1]
					),
				];
			case 70:
				return [
					self::makeBaseAction($n, ["play-occupation", "play-occupation"]),
				];
			case 71:
				return [
					self::makeBaseAction($n, [
						"play-occupation",
						"play-occupation",
						"play-occupation",
						"play-occupation",
					]),
				];
			case 73:
				return [
					self::makeBaseAction($n, [
						"clone-1",
					]),
				];
			case 74:
				return [
					self::makeBaseAction($n, [
						"clone-2",
					]),
				];
			case 75:
				return [
					self::makeBaseAction($n, [
						"clone-3",
					]),
				];
			case 76:
				return [
					self::makeBaseAction($n, [
						"clone-4",
					]),
				];
			// Norwegians	
			case 77:
				return [
					self::makeBaseAction($n, ["build-shed"], ["wood" => 1]),
					self::makeBaseAction($n, ["build-artisan0"], ["wood" => 1]),
					self::makeBaseAction($n, ["build-artisan1"], ["wood" => 1]),
				];
			case 78:
				return [self::makeBaseAction($n, ["build-knarr"], ["wood" => 1])];
			case 90:
				return [
					self::makeBaseAction(
						$n,
						["build-stonehouse"],
						["stone" => 1]
					),
					self::makeBaseAction(
						$n,
						["build-knarr"],
						["wood" => 1]
					),
				];
			case 91:
				return [
					self::makeBaseAction(
						$n,
						["build-longhouse"],
						["stone" => 2]
					),
					self::makeBaseAction(
						$n,
						["build-longship"],
						["wood" => 2]
					),
				];
			case 5559:
				return [
					self::makeBaseAction(
						$n, 
						["buylongshipfifthcol"], 
						["coin" => 1], 
						["boat"=> 1], 
						$norwegiansLastColVikingNumber === 2
					),
				];
			case 5550:
				return [
					self::makeBaseAction($n, ["build-shed"],[], [], $norwegiansLastColVikingNumber == 2),
					self::makeBaseAction($n, ["build-artisan0"], [], [], $norwegiansLastColVikingNumber == 2),
					self::makeBaseAction($n, ["build-artisan1"], [], [], $norwegiansLastColVikingNumber == 2),
				];
			case 81:
				return [self::makeBaseAction($n, ["hunt-fish"])];
			case 82:
				return [self::makeBaseAction($n, ["hunt-game2"])];				
			case 5551:
				return [self::makeBaseAction($n, ["hunt-elk"], [], [], $norwegiansLastColVikingNumber === 2)];
			case 93:
				return [
					self::makeBaseAction($n, ["slaughter-sheep"]),
					self::makeBaseAction($n, ["slaughter-horse"]),
					self::makeBaseAction($n, ["slaughter-cattle"]),
				];	
			case 86: 
				return [
					self::makeBaseAction($n, ["take-sheep", "take-grain"], ["coin" => 3]),
					self::makeBaseAction($n, ["take-horse", "take-grain"], ["coin" => 3]),
					self::makeBaseAction($n, ["take-pig", "take-grain"], ["coin" => 3]),
					self::makeBaseAction($n, ["take-cattle", "take-grain"], ["coin" => 3]),
				];
			case 87: 
				return [
					self::makeBaseAction($n, ["take-sheep", "take-sheep"], ["coin" => 2, "beans"=> 1]),
					self::makeBaseAction($n, ["take-pig", "take-pig"], ["coin" => 2, "beans" => 1]),
					self::makeBaseAction($n, ["take-sheep", "take-pig"], ["coin" => 2, "beans"=> 1]),
				];
			case 88: 
				return [self::makeBaseAction($n, ["anytwoanimals"], ["grain" => 1, "coin" => 3])];
			case 5552: 
				return [self::makeBaseAction($n, ["take-skinandbones", "take-saltmeat"], ["pig" => 1], [], $norwegiansLastColVikingNumber === 2)];	//pigs cannot be pregnant, so no need for slaughter command
			case 85: 
				return [self::makeBaseAction($n, ["slaughter-sheep"])];
			case 89: 
				return [
					self::makeBaseAction($n, ["slaughter-horse"]),
					self::makeBaseAction($n, ["slaughter-cattle"]),
			];
			case 97: 
				return [self::makeBaseAction($n, ["take-beans", "take-coin", "collect-milk"])];
			case 98: 
				return [self::makeBaseAction($n, ["take-coin-2", "take-flax", "take-mead", "takeif-grain-horse"])];
			case 99: 
				return [self::makeBaseAction($n, ["take-fruits", "collect-wool"])];
			case 100:
				return [self::makeBaseAction($n, ["take-coin", "take-spices", "take-herbs", "takeif-saltmeat-pig", "takeif-wool-sheep"])];
			case 5553: 
				return [self::makeBaseAction($n, ["take-coin", "take-herbs", "take-mead"], [], [], $norwegiansLastColVikingNumber === 2)];
			case 103: 
				return [self::makeBaseAction($n, ["take-tools-2", "take-rune"], ["wood" => 2, "stone" => 1, "ore" => 1])];	
			case 107: 
				return [self::makeBaseAction($n, ["take-coin", "take-tools", "take-rune", "take-chest"], ["wood" => 2, "stone" => 1, "ore" => 1])];
			case 104:
				return [self::makeBaseAction($n, ["take-coin-2", "exchange-wool-wadmal", "exchange-silverware-jewelry"])];
			case 5554: 
				return [self::makeBaseAction($n, ["forgeminor"], ["ore" => 1], [], $norwegiansLastColVikingNumber === 2)];
			case 119:
				return [self::makeBaseAction($n, ["mountain-3", 'mountainreset', "upgrade-2-1"])];
			case 110:
				return [self::makeBaseAction($n, ["take-wood-3", "take-ore"])];
			case 114:
				return [self::makeBaseAction($n, ["mountain-3", 'mountainreset', "draw-weapon-x-2",])];
			case 115:
				return [self::makeBaseAction($n, ["oncetwiceupgrade-2-1"])];
			case 116:
				return [self::makeBaseAction($n, ["upgrade-3-2"])];
			case 5555:
				return [self::makeBaseAction($n, ["resources-2-unique", "upgrade-1-1"], [], [], $norwegiansLastColVikingNumber === 2)]; //TODO: check naming
			case 121:
				return [self::makeBaseAction($n, ["trade"], ["coin" => 2], ["knarr" => 1])];	
			case 5556:
				return [self::makeBaseAction($n, ["attack-theft"], [], ["knarr" => 1], $norwegiansLastColVikingNumber === 2)];
			case 124:
				return [self::makeBaseAction($n, ["doubleaction-124-1"])];
			case 5557:
				return [self::makeBaseAction($n, ["doubleaction-5557-1"], [], [], $norwegiansLastColVikingNumber === 2)];
			case 125:
				return [self::makeBaseAction($n, ["discardocc", "coinsoroccs"])];
			case 126:
				return [self::makeBaseAction($n, ["take-coin", "draw-occupation", "draw-occupation", "play-occupation"])];
			case 128:
				return [self::makeBaseAction($n, ["doubleaction-128-1"])];
			case 5558:
				return [self::makeBaseAction($n, ["emigrateminor"], [], ["whalingboat" => 1], $norwegiansLastColVikingNumber === 2)];
		}
		return null;
	}

	public static function pop() {
		$commands = FeastForOdin::getObjectList("SELECT 
		  id, command
		FROM command_queue ORDER BY id ASC");
		
		// Auto decode json
		if (count($commands) == 0)
			return null;
		
		FeastForOdin::DbQuery("DELETE FROM command_queue
			WHERE id = {$commands[0]['id']}");
		
		return $commands[0]['command'];
	}
	
	public static function push($commands) {
		$values = [];
		foreach ($commands as $c) {
			$values[] = "('$c')";
		}
		FeastForOdin::DbQuery("INSERT INTO command_queue
			(command) VALUES ".implode(',', $values)."");
	}
	
	public static function unshift($commands) {
		$values = [];
		foreach ($commands as $c) {
			$values[] = "('$c')";
		}
		$old_commands = FeastForOdin::getObjectList("SELECT 
		  id, command
		FROM command_queue ORDER BY id ASC");
		foreach ($old_commands as $c) {
			$values[] = "('$c[command]')";
		}
		FeastForOdin::DbQuery("DELETE FROM command_queue
			WHERE 1");
		FeastForOdin::DbQuery("INSERT INTO command_queue
			(command) VALUES ".implode(',', $values)."");
	}
	
	public static function dump() {
		$commands = FeastForOdin::getObjectList("SELECT 
		  id, command
		FROM command_queue ORDER BY id ASC");
		
		return $commands;
	}

	public static function contains($primary) {
		// Should look for primary- or primary$, but I don't know how...
		$old_commands = FeastForOdin::getObjectList("SELECT id, command
		FROM command_queue WHERE command LIKE '$primary%' ORDER BY id ASC");
		return count($old_commands) > 0;
	}
	
	public static function getBaseGameActions() {
		return self::filterActions('base', 'game');
	}

	public static function getNorwegiansActions() {
		return self::filterActions('norwegians', 'game');
	}

	public static function filterActions($filter, $paramToFilter) {
		$actions = FeastForOdin::getDataActions();

		$filteredActions = array_filter($actions, function($action) use ($filter, $paramToFilter) {
			if (gettype($action[$paramToFilter]) == 'array') {
				return in_array($filter, $action[$paramToFilter]); 
			} else {
				return $filter == $action[$paramToFilter]; 
			} 
		});

		return array_map(function($action) {return $action['id']; }, $filteredActions);
	}
}
