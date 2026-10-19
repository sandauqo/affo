<?php

/**
 * Runs the real command/action traits (DoCommand, SelectMode, SelectShip) and the real
 * Requirements/Action/Occupation classes against in-memory SQLite.
 * Everything else (player persistence, spending, notifications) is a minimal stand-in.
 */
class FakeCommandGame extends FakeUndoGame
{
    use DoCommand;
    use SelectMode;
    use SelectShip;

    public array $transitions = [];
    public object $gamestate;
    public int $activePlayer = 1;
    public bool $norwegians = false;
    public string $stateName = '';

    public function __construct()
    {
        parent::__construct();
        FeastForOdin::$game = $this;
        FfoPlayer::reset();
        Island::$available = [];
        $this->state['action_id'] = 0;
        $this->state['round'] = 1;
        $this->state['double_action_first_taken'] = 0;
        $this->state['num_rolls'] = 0;
        $this->state['usingAnyTimeOccupationOutOfAction'] = 0;
        $this->state['pending_play'] = 0;
        $this->state['pending_draw'] = 0;
        $this->state['resolved_effect'] = 0;
        $this->state['takeImmediateActionToContinue'] = 0;
        $this->addPlayer(1);
        $this->gamestate = new class($this) {
            public function __construct(private FakeCommandGame $g)
            {
            }

            public function nextState($t)
            {
                $this->g->transitions[] = $t;
            }

            public function state()
            {
                return ['name' => $this->g->stateName];
            }
        };
    }

    // ---- test setup helpers ----

    /** @param string[] $shipTypes e.g. ['whalingboat', 'longship']; ships get ids 1..n */
    public function setPlayer(int $coin, int $wood, array $shipTypes, int $stone = 0): void
    {
        $ships = [];
        foreach (array_values($shipTypes) as $i => $t) {
            $ships[] = ['id' => $i + 1, 'type' => $t];
        }
        $this->exec("UPDATE player SET coin = $coin, wood = $wood, stone = $stone, ships = '" . json_encode($ships) . "' WHERE player_id = 1");
    }

    public function player(): array
    {
        return FfoPlayer::getFullPlayer(1);
    }

    public function shipTypes(): array
    {
        return array_column($this->player()['ships'], 'type');
    }

    public function queue(string ...$commands): void
    {
        Action::push($commands);
    }

    public function queued(): array
    {
        return array_column(Action::dump(), 'command');
    }

    /** Pops and runs one command; returns [queue after, transition taken]. */
    public function step(): array
    {
        $this->transitions = [];
        $this->doCommand();
        return [$this->queued(), $this->transitions[0] ?? null];
    }

    /** Runs selectMode in the given state for the given action. */
    public function pickMode(string $stateName, int $actionId, int $mode): void
    {
        $this->stateName = $stateName;
        $this->state['action_id'] = $actionId;
        $this->selectMode($mode);
    }

    public function pickShip(string $stateName, int $shipId): void
    {
        $this->stateName = $stateName;
        $this->selectShip($shipId);
    }

    // ---- stand-ins for BGA framework / game methods ----

    public function checkAction($name)
    {
    }

    public function getActivePlayerId()
    {
        return $this->activePlayer;
    }

    public function isNorwegiansExpansion()
    {
        return $this->norwegians;
    }

    public function isShortGame()
    {
        return false;
    }

    public function isPlayerZombie($id)
    {
        return false;
    }

    public function setString($key, $value)
    {
    }

    public function flushAssetDiffs()
    {
    }

    public function incGameStateValue($key, $delta)
    {
        $this->state[$key] += $delta;
    }

    public function updateBuildingSupply()
    {
    }

    public function getCurrentAction()
    {
        return Action::getModifiedAction($this->state['action_id'], $this->activePlayer);
    }

    public static function getDataBoards()
    {
        $boards = [];
        foreach (['shed', 'stonehouse', 'longhouse', 'lumberjack', 'mason'] as $t) {
            $boards[$t] = ['type' => $t];
        }
        return $boards;
    }

    public function spend(&$player, $asset, $amount, $source = null, $flushable = true)
    {
        $player[$asset] -= $amount;
        if ($player[$asset] < 0) {
            throw new BgaUserException("You cannot afford that action");
        }
    }

    public function spendBoat(&$player, $ship_id, $source)
    {
        foreach ($player['ships'] as $k => $s) {
            if ($s['id'] == $ship_id) {
                unset($player['ships'][$k]);
                $player['ships'] = array_values($player['ships']);
                return $s;
            }
        }
        return null;
    }

    public static function getAmount($player, $asset)
    {
        return FeastForOdin::getAmount($player, $asset);
    }

    public static function canAfford($player, $asset, $amount)
    {
        return FeastForOdin::canAfford($player, $asset, $amount);
    }
}
