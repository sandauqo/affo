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
    public static function getFullPlayer($player_id)
    {
        $p = FeastForOdin::getObject("SELECT * FROM player WHERE player_id = $player_id");
        $p["id"] = $p["player_id"];
        $p["score"] = $p["player_score"];
        $p["score_aux"] = $p["player_score_aux"];
        foreach (["goods", "boards", "ships", "emigrations"] as $k) {
            $p[$k] = json_decode($p[$k], true) ?: [];
        }
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
    public static function getAvailable()
    {
        return [];
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
