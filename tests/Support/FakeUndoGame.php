<?php

/**
 * Runs the real Undo trait (modules/helper.undo.php) against an in-memory SQLite DB.
 * Table layouts mirror dbmodel.sql (enums become TEXT).
 */
class FakeUndoGame
{
    use Undo;
    use EasterEgg;

    public array $state = [
        'ee_issued' => 0,
        'ee_issued_saved' => 0,
        'undo_supported' => 1,
        'can_undo' => 1,
        'just_restored' => 0,
        'undo_occupation' => 0,
        'undo_weapon' => 0,
        'undo_forge' => 0,
        'undo_string_globals' => 0,
        'undo_islands' => 0,
        'undo_mountain_strips' => 0,
        'num_sheds' => 3,
        'num_stonehouses' => 3,
        'num_longhouses' => 5,
        'num_vp_token_4' => 2,
        'num_vp_token_3' => 2,
        'num_sheds_saved' => 0,
        'num_stonehouses_saved' => 0,
        'num_longhouses_saved' => 0,
        'num_vp_token_4_saved' => 0,
        'num_vp_token_3_saved' => 0,
    ];
    public array $notifications = [];
    public array $strings = [];

    private const PLAYER_COLS = "player_id INTEGER PRIMARY KEY, player_score INTEGER DEFAULT 0, player_score_aux INTEGER DEFAULT 0,
        coin INTEGER DEFAULT 0, wood INTEGER DEFAULT 0, stone INTEGER DEFAULT 0, ore INTEGER DEFAULT 0,
        penalty INTEGER DEFAULT 0, vp INTEGER DEFAULT 0, goods TEXT, boards TEXT, ships TEXT, emigrations TEXT";
    private const OCC_COLS = "id INTEGER PRIMARY KEY AUTOINCREMENT, deck TEXT DEFAULT 'a', starter INTEGER, ability INTEGER,
        points INTEGER, location TEXT DEFAULT 'deck', player_id INTEGER, used INTEGER DEFAULT 0";
    private const WEAPON_COLS = "id INTEGER PRIMARY KEY AUTOINCREMENT, type TEXT DEFAULT 'bow', location TEXT DEFAULT 'deck', player_id INTEGER";
    private const MEEPLE_COLS = "id INTEGER PRIMARY KEY AUTOINCREMENT, player_id INTEGER, location TEXT DEFAULT 'feast',
        action_id INTEGER, slot INTEGER DEFAULT 0, colour INTEGER DEFAULT 0";

    public function __construct()
    {
        FeastForOdin::$pdo = new PDO('sqlite::memory:');
        FeastForOdin::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        foreach (['player' => self::PLAYER_COLS, 'occupation' => self::OCC_COLS,
                     'weapon' => self::WEAPON_COLS, 'meeple' => self::MEEPLE_COLS] as $table => $cols) {
            $this->exec("CREATE TABLE $table ($cols)");
            // player_saved lists its columns explicitly, so it can share the live layout
            $this->exec("CREATE TABLE {$table}_saved ($cols)");
        }
        foreach (['forge', 'string_globals', 'available_islands', 'mountain_strips'] as $table) {
            $this->exec("CREATE TABLE $table (k TEXT, v TEXT)");
            $this->exec("CREATE TABLE {$table}_saved (k TEXT, v TEXT)");
        }
        $this->exec("CREATE TABLE command_queue (id INTEGER PRIMARY KEY AUTOINCREMENT, command TEXT)");
    }

    // ---- helpers for tests ----

    public function exec(string $sql): void
    {
        FeastForOdin::$pdo->exec($sql);
    }

    public function rows(string $sql): array
    {
        return FeastForOdin::getObjectList($sql);
    }

    public function addPlayer(int $id, int $coin = 0): void
    {
        $this->exec("INSERT INTO player (player_id, coin, goods, boards, ships, emigrations)
            VALUES ($id, $coin, '[]', '[]', '[]', '[]')");
    }

    public function addOccupation(string $location, ?int $playerId, int $ability, int $used = 0): int
    {
        $pid = $playerId === null ? 'NULL' : $playerId;
        $this->exec("INSERT INTO occupation (deck, starter, ability, points, location, player_id, used)
            VALUES ('a', 0, $ability, 0, '$location', $pid, $used)");
        return (int) FeastForOdin::$pdo->lastInsertId();
    }

    public function addMeeple(int $playerId, string $location, int $slot = 0): int
    {
        $this->exec("INSERT INTO meeple (player_id, location, slot) VALUES ($playerId, '$location', $slot)");
        return (int) FeastForOdin::$pdo->lastInsertId();
    }

    /** All occupation rows in a stable order, for whole-state comparison. */
    public function occupations(): array
    {
        return $this->rows("SELECT * FROM occupation ORDER BY id");
    }

    // ---- stand-ins for BGA framework methods used by the Undo trait ----

    public function getGameStateValue($key)
    {
        return $this->state[$key];
    }

    public function setGameStateValue($key, $value)
    {
        $this->state[$key] = $value;
    }

    public static function DbQuery($sql)
    {
        FeastForOdin::DbQuery($sql);
    }

    public static function getObject($sql)
    {
        return FeastForOdin::getObject($sql);
    }

    public static function _($text)
    {
        return $text;
    }

    public function getString($key)
    {
        return $this->strings[$key] ?? '';
    }

    public function setString($key, $value)
    {
        $this->strings[$key] = $value;
    }

    public static function getDataGoods()
    {
        return require dirname(__DIR__, 2) . '/modules/data/goods.php';
    }

    public function updatePlayerScore($player, $a, $b)
    {
        return [];
    }

    public static function loadPlayersBasicInfos()
    {
        $players = [];
        foreach (FeastForOdin::getObjectList("SELECT player_id FROM player") as $r) {
            $players[$r['player_id']] = ['player_id' => $r['player_id'], 'player_name' => 'P' . $r['player_id']];
        }
        return $players;
    }

    public function notifyAllPlayers($type, $log, $args)
    {
        $this->notifications[] = ['type' => $type, 'args' => $args];
    }
}
