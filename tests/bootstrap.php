<?php

class FeastForOdin
{
    public static function getDataActions()
    {
        return require dirname(__DIR__) . '/modules/data/actions.php';
    }
}

require_once dirname(__DIR__) . '/modules/class.action.php';
