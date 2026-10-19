<?php

use PHPUnit\Framework\TestCase;

/**
 * Characterization tests for action 1 (base game, "Build 1 shed for 2 wood").
 */
class Action1Test extends TestCase
{
    private FakeCommandGame $g;

    protected function setUp(): void
    {
        $this->g = new FakeCommandGame();
        FfoPlayer::$addedBoards = [];
    }

    public function testDefinitionIsOneMeepleBuildShedForTwoWood(): void
    {
        $actions = Action::getModifiedAction(1, 1);
        $this->assertCount(1, $actions);
        $a = $actions[0];
        $this->assertSame(1, $a->numberMeeples);
        $this->assertSame(['linkedactions'], $a->preCommands);
        $this->assertSame(['build-shed'], $a->commands);
        $this->assertSame(['wood' => 2], $a->cost);
        $this->assertSame([], $a->prerequisite);
    }

    public function testActionIsBaseGameOnly(): void
    {
        $this->g->norwegians = true;
        $this->expectException(BgaUserException::class);
        Action::getModifiedAction(1, 1);
    }

    public function testIsInHousesRowAndFirstColumn(): void
    {
        $this->assertContains(1, Action::filterActions('houses', 'row'));
        $this->assertSame(1, Action::getCol(1));
    }

    /** Occupations that discount or swap things elsewhere must not touch action 1. */
    public function testUnrelatedDiscountOccupationsDoNotChangeCost(): void
    {
        foreach ([1 /* Peddler */, 2 /* Patron */, 116 /* Master Bricklayer: stone only */, 99 /* Preceptor */] as $ability) {
            $this->g->addOccupation('played', 1, $ability);
        }
        $a = Action::getModifiedAction(1, 1)[0];
        $this->assertSame(['wood' => 2], $a->cost);
        $this->assertSame(['build-shed'], $a->commands);
        $this->assertSame(['linkedactions'], $a->preCommands);
    }

    public function testBuildShedUsesOneShedFromSupplyAndAddsBoard(): void
    {
        $this->g->state['num_sheds'] = 3;
        $this->g->queue('build-shed');
        [$queue, $next] = $this->g->step();
        $this->assertSame(2, $this->g->state['num_sheds']);
        $this->assertCount(1, FfoPlayer::$addedBoards);
        $this->assertSame('shed', FfoPlayer::$addedBoards[0]['type']);
        $this->assertSame([], $queue);
        $this->assertSame('next', $next);
    }

    public function testBuildShedWithEmptySupplyBuildsNothingAndDoesNotFail(): void
    {
        $this->g->state['num_sheds'] = 0;
        $this->g->queue('build-shed');
        [, $next] = $this->g->step();
        $this->assertSame(0, $this->g->state['num_sheds']);
        $this->assertSame([], FfoPlayer::$addedBoards);
        $this->assertSame('next', $next);
    }

    public function testBuildShedDoesNotTouchOtherSupplies(): void
    {
        $before = $this->g->state;
        $this->g->queue('build-shed');
        $this->g->step();
        $this->assertSame($before['num_stonehouses'], $this->g->state['num_stonehouses']);
        $this->assertSame($before['num_longhouses'], $this->g->state['num_longhouses']);
    }
}
