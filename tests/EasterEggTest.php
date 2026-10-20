<?php

use PHPUnit\Framework\TestCase;

class EasterEggTest extends TestCase
{
    private const BASES = ['berries' => 1, 'eggs' => 2, 'amber' => 4, 'gullgubber' => 8];

    private FakeUndoGame $game;
    private array $goods;

    protected function setUp(): void
    {
        $this->game = new FakeUndoGame();
        $this->game->addPlayer(1);
        $this->goods = FakeUndoGame::getDataGoods();
        $GLOBALS['bga_rand_queue'] = [];
        $GLOBALS['bga_rand_calls'] = [];
    }

    private function nextRolls(int ...$values): void
    {
        $GLOBALS['bga_rand_queue'] = $values;
    }

    public function testEasterEggGoodsMirrorTheirBaseGoods(): void
    {
        foreach (array_keys(self::BASES) as $base) {
            $ee = $this->goods[$base . '_ee'];
            $expected = $this->goods[$base];
            $expected['type'] = $base . '_ee';
            $this->assertSame($expected, $ee, "$base _ee differs from $base");
        }
    }

    public function testBaseGoodType(): void
    {
        $this->assertSame('eggs', FakeUndoGame::baseGoodType('eggs_ee'));
        $this->assertSame('eggs', FakeUndoGame::baseGoodType('eggs'));
        $this->assertSame('gullgubber', FakeUndoGame::baseGoodType('gullgubber_ee'));
        $this->assertSame('mead', FakeUndoGame::baseGoodType('mead'));
    }

    public function testOtherGoodsAreNeverRolled(): void
    {
        $this->nextRolls(1);
        $this->assertSame($this->goods['mead'], $this->game->rollEasterEgg($this->goods['mead']));
        $this->assertSame([], $GLOBALS['bga_rand_calls']);
        $this->assertSame(0, $this->game->state['ee_issued']);
    }

    public function testEasterEggItselfIsNotRerolledButIsMarkedIssued(): void
    {
        $this->nextRolls(1);
        $this->assertSame($this->goods['eggs_ee'], $this->game->rollEasterEgg($this->goods['eggs_ee']));
        $this->assertSame([], $GLOBALS['bga_rand_calls']);
        $this->assertSame(2, $this->game->state['ee_issued']);

        // so a later base roll can no longer produce a second one
        $this->assertSame('eggs', $this->game->rollEasterEgg($this->goods['eggs'])['type']);
        $this->assertSame([], $GLOBALS['bga_rand_calls']);
    }

    public function testWinningRollIssuesTheEasterEggAndMarksIt(): void
    {
        foreach (self::BASES as $base => $bit) {
            $this->game->state['ee_issued'] = 0;
            $this->nextRolls(1);
            $result = $this->game->rollEasterEgg($this->goods[$base]);
            $this->assertSame($base . '_ee', $result['type']);
            $this->assertSame($bit, $this->game->state['ee_issued']);
        }
    }

    public function testLosingRollKeepsTheBaseGood(): void
    {
        $this->nextRolls(2);
        $this->assertSame($this->goods['berries'], $this->game->rollEasterEgg($this->goods['berries']));
        $this->assertSame(0, $this->game->state['ee_issued']);
    }

    public function testOnlyOneOfEachEasterEggCanExist(): void
    {
        $this->nextRolls(1, 1);
        $first = $this->game->rollEasterEgg($this->goods['eggs']);
        $second = $this->game->rollEasterEgg($this->goods['eggs']);

        $this->assertSame('eggs_ee', $first['type']);
        $this->assertSame('eggs', $second['type']);
        // Once issued, no further roll is even made
        $this->assertCount(1, $GLOBALS['bga_rand_calls']);
    }

    public function testEasterEggsAreTrackedIndependently(): void
    {
        $this->nextRolls(1, 1);
        $this->game->rollEasterEgg($this->goods['eggs']);
        $result = $this->game->rollEasterEgg($this->goods['amber']);

        $this->assertSame('amber_ee', $result['type']);
        $this->assertSame(2 | 4, $this->game->state['ee_issued']);
    }

    public function testOddsDependOnColour(): void
    {
        $this->nextRolls(2, 2, 2, 2);
        foreach (['berries', 'eggs', 'amber', 'gullgubber'] as $base) {
            $this->game->rollEasterEgg($this->goods[$base]);
        }
        // orange/red 1 in 12, green/blue 1 in 9
        $this->assertSame([[1, 12], [1, 12], [1, 9], [1, 9]], $GLOBALS['bga_rand_calls']);
    }

    public function testIssuedEasterEggsAreRestoredByUndo(): void
    {
        $this->game->state['ee_issued'] = 2;
        $this->game->saveState(1);

        $this->nextRolls(1);
        $this->game->rollEasterEgg($this->goods['berries']);
        $this->assertSame(3, $this->game->state['ee_issued']);

        $this->game->restoreState(1);
        $this->assertSame(2, $this->game->state['ee_issued']);
    }

    public function testSpentEasterEggReturnsToThePool(): void
    {
        $this->game->state['ee_issued'] = 2 | 4;
        $this->game->releaseEasterEgg($this->goods['eggs_ee']);
        $this->assertSame(4, $this->game->state['ee_issued']);

        $this->nextRolls(1);
        $this->assertSame('eggs_ee', $this->game->rollEasterEgg($this->goods['eggs'])['type']);
    }

    public function testReleasingNormalGoodsChangesNothing(): void
    {
        $this->game->state['ee_issued'] = 2;
        $this->game->releaseEasterEgg($this->goods['eggs']);
        $this->game->releaseEasterEgg($this->goods['mead']);
        $this->assertSame(2, $this->game->state['ee_issued']);
    }

    public function testEasterEggCountsAsItsBaseGood(): void
    {
        $player = ['goods' => [$this->goods['eggs_ee'], $this->goods['eggs'], $this->goods['amber_ee']]];

        $this->assertSame(2, FeastForOdin::getAmount($player, 'eggs'));
        $this->assertSame(1, FeastForOdin::getAmount($player, 'amber'));
        $this->assertTrue(FeastForOdin::canAfford($player, 'eggs', 2));
        $this->assertFalse(FeastForOdin::canAfford($player, 'berries', 1));
    }
}
