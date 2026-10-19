<?php

class FeastForOdin
{
    // In-memory SQLite connection used by DB-backed tests (see tests/Support/FakeUndoGame.php)
    public static ?PDO $pdo = null;
    public static $game = null;

    public static function totranslate($text)
    {
        return $text;
    }

    public static function getDataActions()
    {
        return require dirname(__DIR__) . '/modules/data/actions.php';
    }

    public static function DbQuery($sql)
    {
        self::$pdo->exec($sql);
    }

    public static function getObject($sql)
    {
        $row = self::$pdo->query($sql)->fetch(PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }

    public static function getObjectList($sql)
    {
        return self::$pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // Copied from feastforodin.game.php (the real class cannot be loaded without the BGA framework)
    public static function getAssetType($asset)
    {
        if (in_array($asset, ["coin", "ore", "wood", "stone", "vp"])) {
            return "resource";
        } elseif (in_array($asset, ["bow", "snare", "spear", "sword"])) {
            return "weapon";
        } elseif (in_array($asset, ["whalingboat", "knarr", "longship", "boat", "bigboat"])) {
            return "boat";
        }
        return "good";
    }

    public static function getAmount($player, $asset)
    {
        $type = self::getAssetType($asset);
        if ($type == "resource") {
            return $player[$asset];
        } elseif ($type == "good") {
            $num = 0;
            foreach ($player["goods"] as $g) {
                if ($g["type"] == $asset || $g["type"] == "pregnant$asset") {
                    $num++;
                }
            }
            return $num;
        } elseif ($type == "boat") {
            $num = 0;
            foreach ($player["ships"] as $s) {
                if ($asset == "boat") {
                    $num++;
                } elseif ($asset == "bigboat") {
                    if ($s["type"] == "knarr" || $s["type"] == "longship") {
                        $num++;
                    }
                } elseif ($s["type"] == $asset) {
                    $num++;
                }
            }
            return $num;
        }
        return 0;
    }

    public static function canAfford($player, $asset, $amount)
    {
        return self::getAmount($player, $asset) >= $amount;
    }
}

function clienttranslate($text)
{
    return $text;
}

require_once dirname(__DIR__) . '/modules/class.action.php';
require_once dirname(__DIR__) . '/modules/class.occupation.php';
require_once dirname(__DIR__) . '/modules/class.requirements.php';
require_once __DIR__ . '/Support/stubs.php';
require_once dirname(__DIR__) . '/modules/helper.undo.php';
require_once dirname(__DIR__) . '/modules/state-functions/doCommand.php';
require_once dirname(__DIR__) . '/modules/player-actions/selectMode.php';
require_once dirname(__DIR__) . '/modules/player-actions/selectShip.php';
require_once __DIR__ . '/Support/FakeUndoGame.php';
require_once __DIR__ . '/Support/FakeCommandGame.php';
