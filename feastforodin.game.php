<?php

require_once( APP_GAMEMODULE_PATH.'module/table/table.game.php' );

require_once "modules/class.occupation.php";
require_once "modules/class.weapon.php";
require_once "modules/class.player.php";
require_once "modules/class.action.php";
require_once "modules/class.island.php";
require_once "modules/class.forge.php";
require_once "modules/class.mountainstrip.php";
require_once "modules/class.requirements.php";

include "modules/helper.state-functions.php";
include "modules/helper.state-arguments.php";
include "modules/helper.player-actions.php";
include "modules/helper.undo.php";


class FeastForOdin extends Table
{
	use StateFunctions;
	use StateArguments;
	use PlayerActions;
	use Undo;
	
	static $game;

	function __construct()
	{
		parent::__construct();
			
		self::$game = $this;

		self::initGameStateLabels([
			"round" => 10,
			"action_id" => 11,
			"num_rolls" => 12,
			"die_value" => 13,
			"raid_value" => 14,
			"first_player_id" => 15,
			"placed_player_id" => 16,
			"ship_id" => 17,
			"left_action_column" => 18,
			"right_action_column" => 19,
			
			// occupation things
			"patron_discount_forge" => 20,
			"ore_boatman" => 21,
			"inspector_action" => 22,
			'mountainguard_types' => 23,
			'mountain_wood_taken' => 24,
			
			'placed_action_id' => 25,
			'can_undo' => 26,
			'undo_supported' => 27,
			'pending_draw' => 28,
			'pending_play' => 29,
			'just_restored' => 30,
			
			'irreversable_occ' => 31, // UNUSED
			'can_pass_override' => 32,
			'solo_game' => 33,
			
			'num_sheds' => 34,
			'num_stonehouses' => 35,
			'num_longhouses' => 36,
			
			'resolved_effect' => 37,
			
			'occ_swordfighter' => 38,
			
			'num_sheds_saved' => 39,
			'num_stonehouses_saved' => 40,
			'num_longhouses_saved' => 41,
			
			'occ_steward' => 42,
			'no_hunt_refunds' => 43,
			'occ_berserker' => 44,
			'occ_fishcook' => 45,
			'occ_soberman' => 46,
			
			// Undo helpers
			'undo_occupation' => 50,
			'undo_weapon' => 51,
			'undo_forge' => 52,
			'undo_string_globals' => 53,
			'undo_islands' => 54,
			'undo_mountain_strips' => 55,
			
			'mountain_silver_taken' => 56,
			
			"game_length" => 100,
			"occupation_decks" => 101,
			"mini_expansion_1" => 102,
			"norwegians_expansion" => 103,
			"norwegians_all_islands" => 104,
			"occ_ban_list_1" => 105,
			"occ_ban_list_2" => 106,
			"no_pass" => 107,
			"confirm_timer" => 108,
			"autopassable_phases" => 109,
			"best_of_2_starting_occ" => 111,
			"mini_expansion_2" => 112,
			"yule_expansion" => 113,

			// Norwegians
			'num_vp_token_4' => 401,
			'num_vp_token_3' => 402,
			'num_vp_token_4_saved' => 403,
			'num_vp_token_3_saved' => 404,
			'5th_col_how_many_vikings' => 405,
			'double_action_first_taken' => 406,
			'takeImmediateActionToContinue' => 407,
			'usingAnyTimeOccupationOutOfAction' => 408,
			'privateParallelState' => 409,
			'etiquetteTeacherGivenPeasThisAction' => 410,
			'goBack' => 411,
			'waterford_bonus_knarr_skip_179_occ' => 412,
			'altar_available' => 413,
		]);
	}
	
    protected function getGameName( )
    {
		// Used for translations and stuff. Please do not modify.
        return "feastforodin";
    }	

    public static function getDataBoards() {
		$data_boards = include "modules/data/boards.php";
		return $data_boards;
	}
	
	public static function getDataGoods() {
		$data_goods = include "modules/data/goods.php";
		return $data_goods;
	}

	public static function getDataActions() {
		$data_action = include "modules/data/actions.php";
		return $data_action;
	}

	public static function getDataArtisanSheds() {
		$data_action = include "modules/data/artisan_sheds.php";
		return $data_action;
	}

	public static function getDataPersonalResourceMarkers() {
		$data_action = include "modules/data/personal_resource_markers.php";
		return $data_action;
	}
	
	public static function findGood($cols, $rows, $colour) {
		$data_goods = self::getDataGoods();
		foreach ($data_goods as $good) {
			if ($good["cols"] == $cols &&
				$good["rows"] == $rows &&
				$good["colour"] == $colour) {
				return $good;
			}
		}
		return null;
	}
	
	protected function hasOccupationDeck($deckLetter) {
		$decks = $this->getGameStateValue('occupation_decks');
		if ($deckLetter == 'a') return in_array($decks, [1, 4, 5, 7]);
		if ($deckLetter == 'b') return in_array($decks, [2, 4, 6, 7]);
		if ($deckLetter == 'c') return in_array($decks, [3, 5, 6, 7]);
	}
	
	public function hasMiniExpansion1() {
		$value = $this->getGameStateValue('mini_expansion_1');
		if ($value == 2) return true;
		return false;
	}

	public function hasMiniExpansion2() {
		$value = $this->getGameStateValue('mini_expansion_2');
		if ($value == 2) return true;
		return false;
	}

	public function isConfirmTimer() {
		$value = $this->getGameStateValue('confirm_timer');
		if ($value == 1) return true;
		return false;
	}

	public function isBestOfTwoStartingOcc() {
		$value = $this->getGameStateValue('best_of_2_starting_occ');
		if ($value == 2 || $this->hasMiniExpansion2()) return true;
		return false;
	}

	public function isAutoPassablePhases() {
		$value = $this->getGameStateValue('autopassable_phases');
		if ($value == 1) return true;
		return false;
	}

	public function isYuleExpansion() {
		$value = $this->getGameStateValue('yule_expansion');
		if ($value == 2) return true;
		return false;
	}

	public function isNorwegiansExpansion() {
		$value = $this->getGameStateValue('norwegians_expansion');
		if ($value == 2) return true;
		return false;
	}

	public function isNorwegiansAllIslands() {
		$value = $this->getGameStateValue('norwegians_all_islands');
		if ($value == 2) return true;
		return false;
	}

	public function isShortGame() {
		return $this->getGameStateValue('game_length') == 2;
	}
	
	public function isSoloGame() {
		return $this->getGameStateValue('solo_game');
	}
	
	protected function getCurrentAction() {
		$player_id = $this->getActivePlayerId();
		$action_id = $this->getGameStateValue('action_id');
		return Action::getModifiedAction( $action_id, $player_id );
	}
	
	static function getAmount( $player, $asset ) {
		$type = self::getAssetType($asset);
		if ($type == "resource") {
			return $player[$asset];
		} else if ($type == "good") {
			$num = 0;
			foreach ($player["goods"] as $g) {
				if ($g["type"] == $asset || $g["type"] == "pregnant$asset") {
					$num++;
				}
			}
			return $num;
		} else if ($type == "boat") {
			$num = 0;
			foreach ($player["ships"] as $s) {
				if ($asset == "boat") {
					$num++;
				} else if ($asset == "bigboat") {
					if ($s["type"] == "knarr" || $s["type"] == "longship") {
						$num++;
					}
				} else if ($s["type"] == $asset) {
					$num++;
				}
			}
			return $num;
		}
		return 0;
	}
	
	static function canAfford( $player, $asset, $amount ) {
		$num = self::getAmount( $player, $asset );
		return $num >= $amount;
	}
	
	function spend( &$player, $asset, $amount, $source = null, $flushable = true ) {

		$type = self::getAssetType($asset);
		if ($type == "resource") {
			$player[$asset] -= $amount;
			if ($player[$asset] < 0) {
				throw new BgaUserException( clienttranslate("You cannot afford that action") );
			}
			self::notifyAllPlayers( "diffResources", '', array(
				'player_id' => $player["id"],
				'resource' => $asset,
				'diff' => -$amount,
				'source' => $source
			));
		} else if ($type == "good") {
			$num = 0;
			$spent = [];
			foreach ($player["goods"] as $k => $g) {
				if ($g["type"] == $asset) {
					$num++;
					$spent[] = $g;
					unset($player["goods"][$k]);
					if ($num == $amount) {
						break;
					}
				}
			}
			if ($num < $amount) {
				foreach ($player["goods"] as $k => $g) {
					if ($g["type"] == "pregnant$asset") {
						$num++;
						$spent[] = $g;
						unset($player["goods"][$k]);
						if ($num == $amount) {
							break;
						}
					}
				}
			}
			if ($num < $amount) {
				throw new BgaUserException( clienttranslate("You cannot afford that action") );
			}
			self::notifyAllPlayers( "spendGoods", '', array(
				'player_id' => $player["id"],
				'goods' => $spent,
				'source' => $source
			));
		} else {
			throw new BgaVisibleSystemException( "Cannot spend $asset" );
		}
		if ($flushable) {
			self::DbQuery("INSERT INTO asset_diffs (
				player_id, asset, amount, source
			) VALUES ($player[id], '$asset', -$amount, ".(isset($source) ? "'$source'" : 'NULL').")");
		}
	}
	
	function gain( &$player, $asset, $amount, $source = null, $flushable = true  ) {
		$type = self::getAssetType($asset);
		if ($type == "resource") {
			$player[$asset] += $amount;
			self::notifyAllPlayers( "diffResources", '', array(
				'player_id' => $player["id"],
				'resource' => $asset,
				'diff' => $amount,
				'source' => $source
			));
		} else if ($type == "good") {
			$data_goods = self::getDataGoods();

			$num = 0;
			$new_goods = [];
			for ($i = 0; $i < $amount; $i++) {
				if (! isset($data_goods[$asset])) {
					throw new BgaVisibleSystemException( "Good $asset not found" );
				}
				$new_goods[] = $data_goods[$asset];
				$player["goods"][] = $data_goods[$asset];
			}
			self::notifyAllPlayers( "takeGoods", '', array(
				'player_id' => $player["id"],
				'goods' => $new_goods,
				'source' => $source,
			));
			if ($asset == "spices" && Occupation::has($player["id"], 190)) {
				$this->gain( $player, "oil", $amount, "occupation-190", $flushable );
			}
		} else {
			throw new BgaVisibleSystemException( "Cannot gain $asset" );
		}
		if ($flushable) {
			self::DbQuery("INSERT INTO asset_diffs (
				player_id, asset, amount, source
			) VALUES ($player[id], '$asset', $amount, ".(isset($source) ? "'$source'" : 'NULL').")");
		}
	}
	
	function getAssetName( $asset ) {
		$a = $this->m_goods[$asset];
		if (isset($a)) {
			return $a["name"];
		} else {
			return "Unknown";
		}
	}
	
	function sendAssetDiffsNotification($player_id, $source, $elements) {
		$players = self::loadPlayersBasicInfos();
		$jj = 0;
		
		$spends_goods_log = [];
		$spends_goods_args = [
			'i18n' => []
		];
		
		$gains_goods_log = [];
		$gains_goods_args = [
			'i18n' => []
		];
		
		foreach ($elements as $e) {
			$type = self::getAssetType($e["asset"]);
			if ($type == "resource") {
				if ($e["amount"] > 0) {
					for ($i = 0; $i < $e["amount"]; $i++) {
						$key = "inject_good_$e[asset]";
						$gains_goods_log[] = '${'.$key.'}';
						$gains_goods_args[$key] = $this->getAssetName( $e["asset"] );
						$gains_goods_args["i18n"][] = $key;
					}
				} else {
					for ($i = 0; $i < -$e["amount"]; $i++) {
						$key = "inject_good_$e[asset]";
						$spends_goods_log[] = '${'.$key.'}';
						$spends_goods_args[$key] = $this->getAssetName( $e["asset"] );
						$spends_goods_args["i18n"][] = $key;
					}
				}
			} else if ($type == "good") {
				if ($e["amount"] > 0) {
					for ($i = 0; $i < $e["amount"]; $i++) {
						$key = "inject_good_$e[asset]";
						$gains_goods_log[] = '${'.$key.'}';
						$gains_goods_args[$key] = $this->getAssetName( $e["asset"] );
						$gains_goods_args["i18n"][] = $key;
					}
				} else {
					for ($i = 0; $i < -$e["amount"]; $i++) {
						$key = "inject_good_$e[asset]";
						$spends_goods_log[] = '${'.$key.'}';
						$spends_goods_args[$key] = $this->getAssetName( $e["asset"] );
						$spends_goods_args["i18n"][] = $key;
					}
				}
			} else if ($type == "weapon") {
				if ($e["amount"] > 0) {
					for ($i = 0; $i < $e["amount"]; $i++) {
						$key = "inject_weapon_$e[asset]";
						$gains_goods_log[] = '${'.$key.'}';
						$gains_goods_args[$key] = $this->m_weapons[$e["asset"]];
						$gains_goods_args["i18n"][] = $key;
					}
				} else {
					for ($i = 0; $i < -$e["amount"]; $i++) {
						$key = "inject_weapon_$e[asset]";
						$spends_goods_log[] = '${'.$key.'}';
						$spends_goods_args[$key] = $this->m_weapons[$e["asset"]];
						$spends_goods_args["i18n"][] = $key;
					}
				}
			} else {
				// ...
			}
		}
		if ($spends_goods_log && $gains_goods_log) {
			self::notifyAllPlayers( "message", clienttranslate('${player_name} exchanges: ${spend_goods_list} ${inject_for} ${gain_goods_list}'), array(
				'player_id' => $player_id,
				'player_name' => $players[$player_id]["player_name"],
				'inject_for' => clienttranslate('for'),
				'spend_goods_list' => [
					'log' => implode(' ', $spends_goods_log),
					'args' => $spends_goods_args
				],
				'gain_goods_list' => [
					'log' => implode(' ', $gains_goods_log),
					'args' => $gains_goods_args
				]
			));
		} else if ($spends_goods_log) {
			self::notifyAllPlayers( "message", clienttranslate('${player_name} spends: ${goods_list}'), array(
				'player_id' => $player_id,
				'player_name' => $players[$player_id]["player_name"],
				'goods_list' => [
					'log' => implode(' ', $spends_goods_log),
					'args' => $spends_goods_args
				]
			));
		} else if ($gains_goods_log) {
			self::notifyAllPlayers( "message", clienttranslate('${player_name} takes: ${goods_list}'), array(
				'player_id' => $player_id,
				'player_name' => $players[$player_id]["player_name"],
				'goods_list' => [
					'log' => implode(' ', $gains_goods_log),
					'args' => $gains_goods_args
				]
			));
		}
	}
	
	public function flushAssetDiffs() {
		// Group by player and source
		$diffs = self::getObjectList( "SELECT * FROM asset_diffs ORDER BY player_id ASC, source ASC, id ASC" );
		$player_id = NULL;
		$source = NULL;
		$elements = [];
		foreach ($diffs as $diff) {
			if (count($elements) > 0 && ($player_id != $diff["player_id"] || $source != $diff["source"])) {
				// Send!
				$this->sendAssetDiffsNotification($player_id, $source, $elements);
				$elements = [];
			}
			{
				// Append
				$player_id = $diff["player_id"];
				$source = $diff["source"];
				$elements[] = ["asset" => $diff["asset"], "amount" => $diff["amount"]];
			}
		}
		if (count($elements) > 0) {
			// Send!
			$this->sendAssetDiffsNotification($player_id, $source, $elements);
			$elements = [];
		}
		self::DbQuery("DELETE FROM asset_diffs WHERE 1");
	}
	
	public function spendBoat( &$player, $ship_id, $source ) {
		$spent = null;
		foreach ($player["ships"] as $k => $g) {
			if ($g["id"] == $ship_id) {
				$spent = $g;
				unset($player["ships"][$k]);
			}
		}
		if (! isset($spent)) {
			return null;
		}
		self::notifyAllPlayers( "spendShips", '', array(
			'player_id' => $player["id"],
			'ships' => [$spent],
			'source' => $source
		));
		return $spent;
	}
	
    #[Debug(reload: false)]
    public function debug_lotsOfResources() {
		$player_id = self::getActivePlayerId();
		$player = FfoPlayer::getFullPlayer($player_id);
		$player["coin"] += 100;
		$player["wood"] += 10;
		$player["stone"] += 10;
		$player["ore"] += 10;
		FfoPlayer::saveFullPlayer($player);
	}
	
    #[Debug(reload: false)]
    public function debug_givewood() {
		$player_id = self::getActivePlayerId();
		$player = FfoPlayer::getFullPlayer($player_id);
		$player["wood"] += 1;
		FfoPlayer::saveFullPlayer($player);
	}

    #[Debug(reload: false)]
    public function debug_givestone() {
		$player_id = self::getActivePlayerId();
		$player = FfoPlayer::getFullPlayer($player_id);
		$player["stone"] += 1;
		FfoPlayer::saveFullPlayer($player);
	}

    #[Debug(reload: false)]
    public function debug_giveore() {
		$player_id = self::getActivePlayerId();
		$player = FfoPlayer::getFullPlayer($player_id);
		$player["ore"] += 1;
		FfoPlayer::saveFullPlayer($player);
	}

	public function debug_givecoin() {
		$player_id = self::getActivePlayerId();
		$player = FfoPlayer::getFullPlayer($player_id);
		$player["coin"] += 1;
		FfoPlayer::saveFullPlayer($player);
	}

	public function debug_good(string $good) {
		$player_id = self::getActivePlayerId();
		$player = FfoPlayer::getFullPlayer($player_id);
		$data_goods = self::getDataGoods();
		$player["goods"][] = $data_goods[$good];
		self::notifyAllPlayers( "takeGoods", '', array(
			'player_id' => $player_id,
			'goods' => [$data_goods[$good]],
			'source' => null
		));
		FfoPlayer::saveFullPlayer($player);
	}
	
	public function giveisland($island) {
		$islands = Island::getAvailable();
		$found = null;
		foreach ($islands as $i) {
			if ($i["name"] == $island) {
				$found = $i;
				break;
			}
		}
		$player_id = self::getActivePlayerId();
		$player = FfoPlayer::getFullPlayer($player_id);
		$new_board = FfoPlayer::addBoard($player, $found["data"]);
		$this->saveIslands();
		
		Island::remove($island);
		
		FfoPlayer::saveFullPlayer($player);
	}

	public function gameinfos() {
		$gameinfos = self::getGameinfos();
		$default_colors = $gameinfos["player_colors"];
		echo '<pre>' . var_export($gameinfos, true) . '</pre>';
	}

	public function players() {
		$players = self::loadPlayersBasicInfos();
		echo '<pre>' . var_export($players, true) . '</pre>';
	}

	public function callfn($fn) {
		echo var_export(self::{$fn}(), true);
	}

    #[Debug(reload: false)]
    public function debug_occ(int $ability) {
		$player_id = self::getActivePlayerId();
			
		$occupation = FeastForOdin::getObject( "SELECT * FROM occupation WHERE ability = '$ability' LIMIT 1" );
		
		if (isset($occupation)) {
			$occupation["location"] = "hand";
			$occupation["player_id"] = $player_id;
			
			FeastForOdin::DbQuery( "UPDATE occupation SET location = 'hand', player_id = $player_id WHERE id = $occupation[id]" );
		} else {
			$occupation = FeastForOdin::DbQuery( "INSERT INTO occupation 
			(ability, location, player_id) VALUES
			('$ability', 'hand', $player_id)" );
			
			$occupation = FeastForOdin::getObject( "SELECT * FROM occupation WHERE ability = '$ability' LIMIT 1" );
		}
		
		self::notifyPlayer( $player_id, "drawOccupation", '', array(
			'player_id' => $player_id,
			'occupation' => $occupation
		));
	}

    #[Debug(reload: false)]
    public function debug_pocc(int $ability) {
		$player_id = self::getActivePlayerId();
			
		$occupation = FeastForOdin::getObject( "SELECT * FROM occupation WHERE ability = '$ability' LIMIT 1" );
		
		$occupation = FeastForOdin::DbQuery( "INSERT INTO occupation 
		(ability, location, player_id) VALUES
		('$ability', 'played', $player_id)" );
	}
	
    #[Debug(reload: false)]
    public function debug_docc(int $ability) {
		FeastForOdin::DbQuery( "DELETE FROM occupation WHERE ability = '$ability'" );
	}
	
	protected function setupNewGame($players, $options = []) {
		// Set the colors of the players with HTML color code
		// The default below is red/green/blue/orange/brown
		// The number of colors defined here must correspond to the maximum number of players allowed for the gams
		$gameinfos = self::getGameinfos();
		$default_colors = $gameinfos["player_colors"];

		$data_goods = self::getDataGoods();
		$data_boards = self::getDataBoards();
		
		$drawTwoStartingOccs = $this->isBestOfTwoStartingOcc();
		
		// Setup decks
		Occupation::setup(
			$this->hasOccupationDeck('a'),
			$this->hasOccupationDeck('b'),
			$this->hasOccupationDeck('c'), 
			$players,
			$drawTwoStartingOccs);
		Weapon::setup($players);
			
		Island::setup( $this, $players );
		Forge::setup();
		MountainStrip::setup();
		
		// Draw 2 strips for 1-3 players, 3 strips for 4 players
		MountainStrip::draw();
		MountainStrip::draw();
		if (count($players) == 4) {
			MountainStrip::draw();
		}

		// Create players
		// Note: if you added some extra field on "player" table in the database (dbmodel.sql), you can initialize it there.
		$sql = "INSERT INTO player (
          player_id, player_color, player_canal, player_name, player_avatar, 
          coin, wood, stone, ore, penalty, vp,
          goods, boards, ships, emigrations, player_score
        ) VALUES ";
		$values = [];

		$starting_goods = [$data_goods["mead"]];
		$starting_boards = [$data_boards["home"], $data_boards["homefeast"]];
		$starting_boards[0]["index"] = 0;
		$starting_boards[0]["goods"] = [];
		$starting_boards[0]["_negatives"] = count($starting_boards[0]["negatives"]);
		$starting_boards[1]["index"] = 1;
		$starting_boards[1]["goods"] = [];
		$starting_boards[1]["_negatives"] = count($starting_boards[1]["negatives"]);

		foreach ($players as $player_id => $player) {
			FfoPlayer::setup($player_id, $this->isShortGame(), count($players) == 1);
			$color = array_shift($default_colors);

			$startingCoins = ($this->hasMiniExpansion2() && $this->isShortGame()) ? 3 : 0;

			$values[] =
				"(
				'" . $player_id .
				"','$color','" .
				$player["player_canal"] .
				"','" .
				addslashes($player["player_name"]) .
				"','" .
				addslashes($player["player_avatar"]) .
				"', " . $startingCoins . ", 0, 0, 0, 0, 0, '" .
				json_encode($starting_goods) .
				"', '" .
				json_encode($starting_boards) .
				"', '[]', '[]', -86 )";
		}

		$sql .= implode(",", $values);
		self::DbQuery($sql);
		self::reattributeColorsBasedOnPreferences(
			$players,
			$gameinfos["player_colors"]
		);

		if ($this->isNorwegiansExpansion()) {
			$artisan_sheds = self::getDataArtisanSheds();

			foreach ($players as $player_id => $player) {
				if ($this->hasMiniExpansion2() && self::isShortGame()) {
					// don't assign pre-built, players will draw themselves
					break;
				}
				shuffle($artisan_sheds);
				$artisan_shed = array_pop($artisan_sheds);
				$sql = "UPDATE player SET artisan_shed = '$artisan_shed' WHERE player_id = $player_id";
				self::DbQuery($sql);
			}
		}

		self::reloadPlayersBasicInfos();

		/************ Start the game initialization *****/

		// Init global values with their initial values
		self::setGameStateInitialValue( 'round', 1 );
		self::setGameStateInitialValue( 'action_id', 0 );
		self::setGameStateInitialValue( 'num_rolls', 0 );
		self::setGameStateInitialValue( 'die_value', 0 );
		self::setGameStateInitialValue( 'raid_value', 0 );
		self::setGameStateInitialValue( 'ship_id', 0 );
		self::setGameStateInitialValue( 'patron_discount_forge', 0 );
		self::setGameStateInitialValue( 'ore_boatman', 0 );
		self::setGameStateInitialValue( 'inspector_action', 0 );
		self::setGameStateInitialValue( 'mountainguard_types', 0 );
		self::setGameStateInitialValue( 'mountain_wood_taken', 0 );
		self::setGameStateInitialValue( 'mountain_silver_taken', 0 );
		self::setGameStateInitialValue( 'placed_action_id', 0 );
		self::setGameStateInitialValue( 'can_undo', 0 );
		self::setGameStateInitialValue( 'undo_supported', 1 );
		self::setGameStateInitialValue( 'pending_draw', 0 );
		self::setGameStateInitialValue( 'pending_play', 0 );
		self::setGameStateInitialValue( 'just_restored', 0 );
		self::setGameStateInitialValue( 'irreversable_occ', 0 );
		self::setGameStateInitialValue( 'can_pass_override', 0 );
		self::setGameStateInitialValue( 'solo_game', count($players) == 1 ? 1 : 0 );
		self::setGameStateInitialValue( 'num_sheds', 3 );
		self::setGameStateInitialValue( 'num_stonehouses', 3 );
		self::setGameStateInitialValue( 'num_longhouses', 5 );
		self::setGameStateInitialValue( 'resolved_effect', 0 );
		self::setGameStateInitialValue( 'no_hunt_refunds', 0 );
		
		self::setGameStateInitialValue( 'occ_swordfighter', 0 );
		self::setGameStateInitialValue( 'occ_steward', 0 );
		self::setGameStateInitialValue( 'occ_berserker', 0 );
		self::setGameStateInitialValue( 'occ_fishcook', 0 );
		self::setGameStateInitialValue( 'occ_soberman', 0 );
		
		self::setGameStateInitialValue( 'num_sheds_saved', 3 );
		self::setGameStateInitialValue( 'num_stonehouses_saved', 3 );
		self::setGameStateInitialValue( 'num_longhouses_saved', 5 );
		
		self::setGameStateInitialValue( 'undo_occupation', 0 );
		self::setGameStateInitialValue( 'undo_weapon', 0 );
		self::setGameStateInitialValue( 'undo_forge', 0 );
		self::setGameStateInitialValue( 'undo_string_globals', 0 );
		self::setGameStateInitialValue( 'undo_islands', 0 );
		self::setGameStateInitialValue( 'undo_mountain_strips', 0 );


		self::setGameStateInitialValue( 'takeImmediateActionToContinue', 0 );

		self::setGameStateInitialValue( 'usingAnyTimeOccupationOutOfAction', 0);

		self::setGameStateInitialValue( 'privateParallelState', 1);

		self::setGameStateInitialValue( 'etiquetteTeacherGivenPeasThisAction', 0 );

		self::setGameStateInitialValue( 'goBack', 0 );

		self::setGameStateInitialValue( 'waterford_bonus_knarr_skip_179_occ', 0 );

		self::setGameStateInitialValue( 'altar_available', 1 );

		if (count($players) == 4 && !$this->isNorwegiansExpansion()) {
			self::setGameStateInitialValue( 'left_action_column', bga_rand( 1, 2 ) );
			self::setGameStateInitialValue( 'right_action_column', bga_rand( 3, 4 ) );
		} else {
			self::setGameStateInitialValue( 'left_action_column', 0 );
			self::setGameStateInitialValue( 'right_action_column', 0 );
		}

		// Init game statistics
		self::initStat( 'table', 'actions_taken', 0 );
		self::initStat( 'table', 'starting_position_winner_2p', 0 ); // 0 if not applicable, otherwise 1 or 2
		self::initStat( 'table', 'starting_position_winner_3p', 0 ); // 0 if not applicable, otherwise 1, 2 or 3
		self::initStat( 'table', 'starting_position_winner_4p', 0 ); // 0 if not applicable, otherwise 1, 2, 3 or 4
		
		self::initStat( 'player', 'actions_taken', 0 );
		self::initStat( 'player', 'raid_pillage_actions_taken', 0 );
		self::initStat( 'player', 'hunt_actions_taken', 0 );
		self::initStat( 'player', 'snare_actions_taken', 0 );
		self::initStat( 'player', 'whaling_actions_taken', 0 );
		self::initStat( 'player', 'occupations_played', 0 );
		self::initStat( 'player', 'points_occupations', 0 );
		self::initStat( 'player', 'points_boards', 0 );
		self::initStat( 'player', 'points_emigrations', 0 );

		if ($this->isNorwegiansExpansion()) {
			self::setGameStateInitialValue( 'num_vp_token_4', 2 );
			self::setGameStateInitialValue( 'num_vp_token_3', 6 );
			self::setGameStateInitialValue( 'num_vp_token_4_saved', 2 );
			self::setGameStateInitialValue( 'num_vp_token_3_saved', 6 );
		}

		if ($this->hasMiniExpansion2()) {
			// dont wanna do a db update so put into gamestate 
			$pool = [1,2,3,4];
			$firstHarvestKey = array_rand($pool);
			$firstHarvest = $pool[$firstHarvestKey];
			array_splice($pool, $firstHarvestKey, 1);
			array_push($pool, 0, 0, 0, 0);
			shuffle($pool);
			$this->setString( 'custom_harvest', $firstHarvest . '-' . implode('-', $pool));

			if ($this->isShortGame()) {
				foreach ($players as $player_id => $player) {
					Weapon::draw($player_id);
				}
			}
		}

		// Activate first player (which is in general a good idea :) )
		$this->activeNextPlayer();
		
		// Set starting turn order indices
		$next_player_table = $this->getNextPlayerTable();
		$p = $this->getActivePlayerId();
		$i = 1;
		do {
			$sql = "UPDATE player SET starting_order = $i WHERE player_id = $p";
			$p = $next_player_table[$p];
			self::DbQuery($sql);
			$i++;
		} while ($i <= count($players));
		
		self::setGameStateInitialValue( 'first_player_id', $this->getActivePlayerId() );
		self::setGameStateInitialValue( 'placed_player_id', $this->getActivePlayerId() );

		if ($this->hasMiniExpansion2()) {
			// draw personal resource tokens starting with last player
			$this->activePrevPlayer();
		}
		/************ End of the game initialization *****/
	}
	
	protected function getAllDatas(): array {
		$current_player_id = self::getCurrentPlayerId(); // !! We must only return informations visible by this player !!

		// Get information about players
		$players = self::getCollectionFromDb("SELECT 
          player_id id, player_color colour, player_score score, 
		  passed, 
          coin, wood, stone, ore, penalty, vp,
          goods, boards, ships, emigrations, artisan_shed
        FROM player ");

		// TODO: Gather all information about current game situation (visible by player $current_player_id).

		// Goods and boards for each player
		$first_player_id = 0;
		foreach ($players as $player_id => &$player) {
			$first_player_id = $player_id;
			// Decode JSON fields (good, boards, ships)...
			$player["goods"] = json_decode($player["goods"], true);
			//DEBUG: remove null goods from older games where bug with hunting lodge appeared
			$player["goods"] = array_filter($player["goods"]);
			$player["boards"] = json_decode($player["boards"], true);
			$player["ships"] = json_decode($player["ships"], true);
			$player["emigrations"] = json_decode($player["emigrations"], true);
			$player["weapons"] = Weapon::getHand($player_id);
			$player["occupationsHand"] = Occupation::getHand($player_id);
			$player["occupationsPlayed"] = Occupation::getPlayed($player_id);
			$player["meeples"] = FfoPlayer::getMeeples($player_id);
			$player["score_breakdown"] = self::updatePlayerScore( $player, false, false );
			$player["autopass"] = FfoPlayer::getAutoPass($player_id);
			$player["personal_resource_marker"] = FfoPlayer::getPersonalResourceMarker($player_id);
			$player["personal_resource_marker2"] = FfoPlayer::getSecondPersonalResourceMarker($player_id);

			//DEBUG: adjust existing games
			// since boards were updated with "standalone":
			foreach ($player["boards"] as &$board) {
				if ($board["type"] == 'spinnery') {
					$board["standalone"] = [
						["item" => "linen", "value" => -4], 
					];
				}
				if ($board["type"] == 'beekeeper') {
					$board["standalone"] = [
						["item" => "mead", "value" => -2], 
					];
				}
				if ($board["type"] == 'pigsty') {
					$board["standalone"] = [
						["item" => "beans", "value" => -2], 
					];
				}
				if ($board["type"] == 'icehouse') {
					$board["standalone"] = [
						["item" => "stockfish", "value" => -3], 
					];
				}
				if ($board["type"] == 'hunting') {
					$board["standalone"] = [
						["item" => "hide", "value" => -3], 
					];
				}
				if ($board["type"] == 'shed') {
					$board["standalone"] = [
						["item" => "wood", "value" => -1], 
						["item" => "wood", "value" => -1], 
						["item" => "wood", "value" => -1], 
						["item" => "stone", "value" => -1], 
						["item" => "stone", "value" => -1], 
						["item" => "stone", "value" => -1], 
					];
				}
				if ($board["type"] == 'stonehouse') {
					$board["standalone"] = [
						["item" => "wood", "value" => -1], 
						["item" => "stone", "value" => -1], 
					];
				}
				if ($board["type"] == 'lumberjack') {
					$board["standalone"] = [
						["item" => "wood", "value" => -1], 
						["item" => "wood", "value" => -1], 
					];
				}
				if ($board["type"] == 'mason') {
					$board["standalone"] = [
						["item" => "stone", "value" => -1], 
						["item" => "stone", "value" => -1], 
					];
				}
				if ($board["type"] == 'armoury') {
					$board["standalone"] = [
						["item" => "ore", "value" => -2], 
					];
				}
			}

			// If for another player, remove the ability
			if ($current_player_id != $player_id) {
				foreach ($player["occupationsHand"] as &$occ) {
					unset($occ["ability"]);
				}
			}
			// Meeples?
		}
		
		$next_player_table = $this->getNextPlayerTable();
		$player_order = [
			$first_player_id
		];
		while (count($player_order) < count($players)) {
			$player_order[] = $next_player_table[end($player_order)];
		}

		$round = self::getGameStateValue( 'round' );
		$custom_harvest = null;

		if ($this->hasMiniExpansion2()) {

			$playersToCheckForMarkers = self::getCollectionFromDb("SELECT player_id id FROM player");

			$everyoneHasMarkers = true;
	
			foreach ($playersToCheckForMarkers as $ppp) {
				if (!FfoPlayer::getPersonalResourceMarker($ppp['id'])) {
					$everyoneHasMarkers = false;
					break;
				}
			}
	
			if ($everyoneHasMarkers) {
				// truncate to round nr + 1
				$custom_harvest = explode('-', $this->getString('custom_harvest'));
				$truncated = array_splice($custom_harvest, 0, $round + 1);
				$custom_harvest = implode('-', $truncated);
			} else {
				// take first only, second harvest will be revealed after the first harvest phase
				$custom_harvest = explode('-', $this->getString('custom_harvest'))[0];
			}

		}

		$data = [
			"players" => $players,
			"player_order" => $player_order,
			"islands" => Island::getAvailable(),
			"forge" => Forge::getAvailable(),
			"mountainstrips" => MountainStrip::getAvailable(),
			"first_player_id" => $this->getGameStateValue('first_player_id'),
			"can_undo" => $this->getGameStateValue('can_undo'),
			"num_sheds" => $this->getGameStateValue('num_sheds'),
			"num_stonehouses" => $this->getGameStateValue('num_stonehouses'),
			"num_longhouses" => $this->getGameStateValue('num_longhouses'),
			"round" => $round,
			"left_action_column" => self::getGameStateValue( 'left_action_column' ),
			"right_action_column" => self::getGameStateValue( 'right_action_column' ),
			"options" => [
				"short_game" => $this->isShortGame(),
				"solo_game" => $this->isSoloGame(),
				"norwegians_expansion" => $this->isNorwegiansExpansion(),
				"harvest_expansion" => $this->hasMiniExpansion2(),
				"confirm_timer" => $this->isConfirmTimer(),
				"yule_expansion" => $this->isYuleExpansion(),
			],
			"custom_harvest" => $custom_harvest,
			"custom_harvest_debug" => $this->getString('custom_harvest'),
		];

		if ($this->isNorwegiansExpansion()) {
			$data["num_vp_token_4"] = $this->getGameStateValue('num_vp_token_4');
			$data["num_vp_token_3"] = $this->getGameStateValue('num_vp_token_3');
		}

		return $data;
	}
	
	function debugSetColumns($l = 1, $r = 3) {
		self::setGameStateInitialValue( 'left_action_column', $l );
		self::setGameStateInitialValue( 'right_action_column', $r );
	}
	
	function updateBuildingSupply() {
		self::notifyAllPlayers( "updateBuildingSupply", '', array(
			"num_sheds" => $this->getGameStateValue('num_sheds'),
			"num_stonehouses" => $this->getGameStateValue('num_stonehouses'),
			"num_longhouses" => $this->getGameStateValue('num_longhouses'),
		));
	}
	
	function setCanUndo( $can_undo ) {
		if (! $this->getGameStateValue( 'undo_supported' )) {
			return;
		}
		
		$this->setGameStateValue('can_undo', $can_undo);
		self::notifyAllPlayers( "updateUndo", '', array(
			'can_undo' => $can_undo,
		));
	}

	/*
        getGameProgression:
        
        Compute and return the current game progression.
        The number returned must be an integer beween 0 (=the game just started) and
        100 (= the game is finished or almost finished).
    
        This method is called each time we are in a game state with the "updateGameProgression" property set to true 
        (see states.inc.php)
    */
	function getGameProgression()
	{
		$num_rounds = $this->isShortGame() ? 6 : 7;

		return floor(100 * (self::getGameStateValue( 'round' ) - 1) / $num_rounds);
	}
	
	static function getAssetType($asset) {
		if (in_array($asset, ["coin", "ore", "wood", "stone", "vp"])) {
			return "resource";
		} else if (in_array($asset, ["bow", "snare", "spear", "sword"])) {
			return "weapon";
		} else if (in_array($asset, ["whalingboat", "knarr", "longship", "boat", "bigboat"])) {
			return "boat";
		} else {
			// TODO : Check it's actually a good?
			return "good";
		}
	}
	
	//////////////////////////////////////////////////////////////////////////////
	//////////// Zombie
	////////////

	/*
        zombieTurn:
        
        This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
        You can do whatever you want in order to make sure the turn of this player ends appropriately
        (ex: pass).
        
        Important: your zombie code will be called when the player leaves the game. This action is triggered
        from the main site and propagated to the gameserver from a server, not from a browser.
        As a consequence, there is no current player associated to this action. In your zombieTurn function,
        you must _never_ use getCurrentPlayerId() or getCurrentPlayerName(), otherwise it will fail with a "Not logged" error message. 
    */
	
	protected function isPlayerZombie($player_id) {
		$players = self::loadPlayersBasicInfos();
		if (! isset($players[$player_id]))
		throw new feException("Player $player_id is not playing here");
	
		return ($players[$player_id]['player_zombie'] == 1);
	}

	function zombieTurn($state, $active_player)
	{
		$statename = $state["name"];

		if ($state["type"] === "activeplayer") {
			switch ($statename) {
				default:
					$this->gamestate->nextState("zombiepass");
					break;
			}

			return;
		}

		if ($state["type"] === "multipleactiveplayer") {
			// Make sure player is in a non blocking status for role turn
			$this->gamestate->setPlayerNonMultiactive($active_player, "pass");

			return;
		}

		throw new feException(
			"Zombie mode not supported at this game state: " . $statename
		);
	}
	
	public function updatePlayerScore( &$player, $save = true, $notify = true ) {
		$score_breakdown = [
			"ships" => 0,
			"emigrations" => 0,
			"exploration_boards" => 0,
			"sheds_and_houses" => 0,
			"sheep_and_cattle" => 0,
			"occupations" => 0,
			"silver" => 0,
			"english_crown" => 0,
			"neg_home_board" => 0,
			"neg_exploration_boards" => 0,
			"neg_sheds_and_houses" => 0,
			"neg_thing_penalty" => 0,
			"total" => 0,
			"positive_sub_total" => 0,
			"negative_sub_total" => 0,
		];
		
		// Ships
		foreach ($player["ships"] as $s) {
			if ($s["type"] == "whalingboat") $score_breakdown["ships"] += 3;
			else if ($s["type"] == "knarr") $score_breakdown["ships"] += 5;
			else if ($s["type"] == "longship") $score_breakdown["ships"] += 8;
		}
		
		// Emigrations
		foreach ($player["emigrations"] as $s) {
			if ($s == "knarr") $score_breakdown["emigrations"] += 18;
			else if ($s == "longship") $score_breakdown["emigrations"] += 21;
			else if ($s == "whalingboat") $score_breakdown["emigrations"] += 7;
		}
		
		// Boards
		foreach ($player["boards"] as $s) {
			if ($s["type"] == "homefeast") {
				continue;
			} else if (in_array($s["type"], ["longhouse", "shed", "stonehouse", "mason", "lumberjack", "armoury"])) {
				$score_breakdown["sheds_and_houses"] += $s["points"];
				$score_breakdown["neg_sheds_and_houses"] += $s["_negatives"];
				$num_stone = 0;
				$num_wood = 0;
				$num_ore = 0;
				if ($s["type"] == "shed") {
					// 3 stone, 3 wood
					$num_stone = 3 - (isset($s["stone"]) ? $s["stone"] : 0);
					$num_wood = 3 - (isset($s["wood"]) ? $s["wood"] : 0);
				} else if ($s["type"] == "stonehouse") {
					// 1 wood, 1 stone
					$num_stone = 1 - (isset($s["stone"]) ? $s["stone"] : 0);
					$num_wood = 1 - (isset($s["wood"]) ? $s["wood"] : 0);
				} else if ($s["type"] == "mason") {
					// 2 stone
					$num_stone = 2 - (isset($s["stone"]) ? $s["stone"] : 0);
				} else if ($s["type"] == "lumberjack") {
					// 2 wood
					$num_wood = 2 - (isset($s["wood"]) ? $s["wood"] : 0);
				} else if ($s["type"] == "armoury") {
					// 1 ore
					$num_ore = 1 - (isset($s["ore"]) ? $s["ore"] : 0);
				}
				$score_breakdown["neg_sheds_and_houses"] += $num_stone;
				$score_breakdown["neg_sheds_and_houses"] += $num_wood;
				$score_breakdown["neg_sheds_and_houses"] += $num_ore * 2;
			} else if (in_array($s["type"], ["beekeeper", "spinnery", "icehouse", "pigsty", "hunting", "barn", "hermit", "cowshed", "whaling"])) {
				$score_breakdown["sheds_and_houses"] += $s["points"];
				$score_breakdown["neg_sheds_and_houses"] += $s["_negatives"];
				$negative_points = 0;
				if ($s["type"] == "beekeeper") {
					$negative_points = (isset($s["mead"]) && $s["mead"]) ? 0 : 2;
				} else if ($s["type"] == "spinnery") {
					$negative_points = (isset($s["linen"]) && $s["linen"]) ? 0 : 4;
				} else if ($s["type"] == "icehouse") {
					$negative_points = (isset($s["stockfish"]) && $s["stockfish"]) ? 0 : 3;
				} else if ($s["type"] == "pigsty") {
					$negative_points = (isset($s["beans"]) && $s["beans"]) ? 0 : 2;
				} else if ($s["type"] == "hunting") {
					$negative_points = (isset($s["hide"]) && $s["hide"]) ? 0 : 3;
				}
				$score_breakdown["neg_sheds_and_houses"] += $negative_points;

			} else if ($s["type"] == "home") {
				$score_breakdown["neg_home_board"] += $s["_negatives"];
			} else {
				$score_breakdown["exploration_boards"] += $s["points"];
				$score_breakdown["neg_exploration_boards"] += $s["_negatives"];
			}
			if (! empty($s['_english_crown'])) {
				// English crown also gives points if placed
				$score_breakdown["english_crown"] += 2;
			}
		}
		
		// Goods
		foreach ($player["goods"] as $s) {
			if (!$s) {
				// HAX;
				break;
			}
			if ($s["type"] == "cattle") {
				$score_breakdown["sheep_and_cattle"] += 3;
			} else if ($s["type"] == "pregnantcattle") {
				$score_breakdown["sheep_and_cattle"] += 4;
			} else if ($s["type"] == "sheep") {
				$score_breakdown["sheep_and_cattle"] += 2;
			} else if ($s["type"] == "pregnantsheep") {
				$score_breakdown["sheep_and_cattle"] += 3;
			} else if ($s["type"] == "crown") {
				$score_breakdown["english_crown"] += 2;
			}
			// rename to 'animals'?
			if ($s["type"] == "pig") {
				$score_breakdown["sheep_and_cattle"] += 1;
			}
			if ($s["type"] == "horse") {
				$score_breakdown["sheep_and_cattle"] += 6;
			}
			if ($s["type"] == "pregnanthorse") {
				$score_breakdown["sheep_and_cattle"] += 8;
			}
		}
		
		$score_breakdown["neg_thing_penalty"] += $player["penalty"] * 3;
		$score_breakdown["silver"] += $player["coin"];
		
		$occs = Occupation::getPlayed($player["id"]);
		foreach ($occs as $o) {
			$score_breakdown["occupations"] += $o["points"];
		}
		
		$score_breakdown["occupations"] += $player["vp"];

		$score_breakdown["positive_sub_total"] = 
			$score_breakdown["ships"] + 
			$score_breakdown["emigrations"] + 
			$score_breakdown["exploration_boards"] + 
			$score_breakdown["sheds_and_houses"] + 
			$score_breakdown["sheep_and_cattle"] + 
			$score_breakdown["occupations"] + 
			$score_breakdown["silver"] + 
			$score_breakdown["english_crown"];
		$score_breakdown["negative_sub_total"] = 
			$score_breakdown["neg_home_board"] + 
			$score_breakdown["neg_exploration_boards"] + 
			$score_breakdown["neg_sheds_and_houses"] + 
			$score_breakdown["neg_thing_penalty"];
		$score_breakdown["total"] = $score_breakdown["positive_sub_total"] - $score_breakdown["negative_sub_total"];
		
		$player["score"] = $score_breakdown["total"];
		
		if ($notify) {
			$this->setStat( $score_breakdown["occupations"], 'points_occupations', $player["id"] );
			$this->setStat( $score_breakdown["exploration_boards"], 'points_boards', $player["id"] );
			$this->setStat( $score_breakdown["emigrations"], 'points_emigrations', $player["id"] );
			
			// Notify
			self::notifyAllPlayers( "updateScore", '', array(
				'player_id' => $player["id"],
				'score_breakdown' => $score_breakdown
			));
		}
		
		if ($save) {
			FfoPlayer::saveFullPlayer( $player );
		}
		
		return $score_breakdown;
	}

	///////////////////////////////////////////////////////////////////////////////////:
	////////// DB upgrade
	//////////

	/*
        upgradeTableDb:
        
        You don't have to care about this until your game has been published on BGA.
        Once your game is on BGA, this method is called everytime the system detects a game running with your old
        Database scheme.
        In this case, if you change your Database scheme, you just have to apply the needed changes in order to
        update the game database and allow the game to continue to run with your new version.
    
    */

	function upgradeTableDb($from_version)
	{
	  // $from_version is the current version of this game database, in numerical form.
	  // For example, if the game was running with a release of your game named "140430-1345",
	  // $from_version is equal to 1404301345
	  if( $from_version <= 2409041709 ) {
		$sql = "ALTER TABLE DBPREFIX_player 
		  ADD COLUMN `vp` INT UNSIGNED NOT NULL DEFAULT '0', 
		  ADD COLUMN `artisan_shed` TEXT, 
		  ADD COLUMN `extra` LONGTEXT; ";
		self::applyDbUpgradeToAllDB( $sql );
		$sql = "ALTER TABLE DBPREFIX_player_saved 
		  ADD COLUMN `vp` INT UNSIGNED NOT NULL DEFAULT '0', 
		  ADD COLUMN `artisan_shed` TEXT, 
		  ADD COLUMN `extra` LONGTEXT; ";
		self::applyDbUpgradeToAllDB( $sql );
	  }
	}
	
	// String globals
	public function setString( $key, $value ) {
		self::DbQuery("INSERT INTO string_globals
		(`key`, `value`) VALUES ('$key', '$value') ON DUPLICATE KEY UPDATE `value` = '$value'");
	}
	public function getString( $key ) {
		return self::getValue("SELECT `value` FROM string_globals
		 WHERE `key` = '$key'");
	}

	public static function get()
	{
	  return self::$game;
	}

	// Hacks
	public static function getCollection($sql)
	{
		return self::get()->getCollectionFromDb($sql);
	}
	public static function getObjectList($sql)
	{
		return self::get()->getObjectListFromDB($sql);
	}
	public static function getObject($sql)
	{
		return self::get()->getObjectFromDB($sql);
	}
	public static function getValue($sql)
	{
		return self::get()->getUniqueValueFromDB($sql);
	}

}
