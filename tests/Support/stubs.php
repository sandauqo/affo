<?php

// Minimal stand-ins for the game classes that Undo::restoreState() touches.
// Occupation is the real class (modules/class.occupation.php).

class BgaUserException extends Exception
{
}

class BgaVisibleSystemException extends Exception
{
}

class FfoPlayer
{
    /** Island names the player may explore; set by tests. */
    public static array $eligibleIslands = [];

    /** Boards passed to addBoard(), for assertions. */
    public static array $addedBoards = [];

    public static function addBoard(&$player, $board)
    {
        self::$addedBoards[] = $board;
        return $board;
    }

    /** Ship types passed to addShip(), for assertions. */
    public static array $addedShips = [];
    public static bool $builtArtisanShed = false;
    public static string $artisanShed = 'lumberjack-mason';

    public static function addShip(&$player, $type, $x = 0, $source = null)
    {
        self::$addedShips[] = $type;
        $ids = array_column($player['ships'], 'id');
        $player['ships'][] = ['id' => ($ids ? max($ids) : 0) + 1, 'type' => $type];
    }

    public static function reset(): void
    {
        self::$eligibleIslands = [];
        self::$addedBoards = [];
        self::$addedShips = [];
        self::$builtArtisanShed = false;
    }

    public static function hasBuiltArtisanShed($player)
    {
        return self::$builtArtisanShed;
    }

    public static function countBigBoats($player)
    {
        $n = 0;
        foreach ($player['ships'] as $s) {
            if ($s['type'] == 'knarr' || $s['type'] == 'longship') {
                $n++;
            }
        }
        return $n;
    }

    public static function saveFullPlayer($player)
    {
        $st = FeastForOdin::$pdo->prepare("UPDATE player SET coin = ?, wood = ?, stone = ?, ore = ?, ships = ?, emigrations = ? WHERE player_id = ?");
        $st->execute([$player['coin'], $player['wood'], $player['stone'], $player['ore'],
            json_encode(array_values($player['ships'])), json_encode($player['emigrations']), $player['id']]);
    }

    public static function getEligibleIslands($player_id, $level)
    {
        return self::$eligibleIslands;
    }

    public static function getFullPlayer($player_id)
    {
        $p = FeastForOdin::getObject("SELECT * FROM player WHERE player_id = $player_id");
        $p["id"] = $p["player_id"];
        $p["score"] = $p["player_score"];
        $p["score_aux"] = $p["player_score_aux"];
        foreach (["goods", "boards", "ships", "emigrations"] as $k) {
            $p[$k] = json_decode($p[$k], true) ?: [];
        }
        $p["artisan_shed"] = self::$artisanShed;
        return $p;
    }

    public static function getMeeples($player_id)
    {
        return FeastForOdin::getObjectList("SELECT * FROM meeple WHERE player_id = $player_id");
    }
}

class Weapon
{
    public static function getHand($player_id)
    {
        return FeastForOdin::getObjectList("SELECT * FROM weapon WHERE location = 'hand' AND player_id = $player_id");
    }
}

class Island
{
    /** Island names on the board; set by tests. */
    public static array $available = [];

    public static function getAvailable()
    {
        return array_map(fn($n) => ['name' => $n], self::$available);
    }
}

class Forge
{
    public static function getAvailable()
    {
        return [];
    }
}

class MountainStrip
{
    public static function getAvailable()
    {
        return [];
    }
}
