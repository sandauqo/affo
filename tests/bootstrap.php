<?php

class FeastForOdin
{
    // In-memory SQLite connection used by DB-backed tests (see tests/Support/FakeUndoGame.php)
    public static ?PDO $pdo = null;

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
}

function clienttranslate($text)
{
    return $text;
}

require_once dirname(__DIR__) . '/modules/class.action.php';
require_once dirname(__DIR__) . '/modules/class.occupation.php';
require_once __DIR__ . '/Support/stubs.php';
require_once dirname(__DIR__) . '/modules/helper.undo.php';
require_once __DIR__ . '/Support/FakeUndoGame.php';
