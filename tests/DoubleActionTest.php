<?php

use PHPUnit\Framework\TestCase;

/**
 * Characterization tests: lock in what the "doubleaction-<id>-<phase>" commands do today.
 * Rule: a double action means "do at least one of the two things".
 */
class DoubleActionTest extends TestCase
{
    private FakeCommandGame $g;

    protected function setUp(): void
    {
        $this->g = new FakeCommandGame();
    }

    // ---- 124: explore, then emigrate minor ----

    public function testAction124Phase1WithoutBigBoatQueuesPhase2Only(): void
    {
        $this->g->queue('doubleaction-124-1');
        [$queue, $next] = $this->g->step();
        $this->assertSame(['doubleaction-124-2'], $queue);
        $this->assertSame('next', $next);
    }

    public function testAction124Phase1WithLongshipQueuesExploreButNeverEntersItsState(): void
    {
        $this->g->setPlayer(0, 0, ['longship']);
        $this->g->queue('doubleaction-124-1');
        [$queue, $next] = $this->g->step();
        $this->assertSame(['explorepassable-0', 'doubleaction-124-2'], $queue);
        // Dead code today: the explore state is overwritten by "next"
        $this->assertSame('next', $next);
    }

    public function testAction124Phase1KnarrOnlyCountsWithOccupation147(): void
    {
        $this->g->setPlayer(0, 0, ['knarr']);
        $this->g->queue('doubleaction-124-1');
        [$queue] = $this->g->step();
        $this->assertSame(['doubleaction-124-2'], $queue);

        $this->setUp();
        $this->g->setPlayer(0, 0, ['knarr']);
        $this->g->addOccupation('played', 1, 147);
        $this->g->queue('doubleaction-124-1');
        [$queue] = $this->g->step();
        $this->assertSame(['explorepassable-0', 'doubleaction-124-2'], $queue);
    }

    public function testAction124Phase2MandatoryEmigrateWhenFirstHalfSkipped(): void
    {
        $this->g->queue('doubleaction-124-2');
        [$queue, $next] = $this->g->step();
        $this->assertSame(['emigrateminor'], $queue);
        $this->assertSame('next', $next);
    }

    public function testAction124Phase2OptionalEmigrateWhenFirstHalfTaken(): void
    {
        $this->g->state['double_action_first_taken'] = 1;
        $this->g->queue('doubleaction-124-2');
        [$queue] = $this->g->step();
        $this->assertSame(['emigrateminorpassable'], $queue);
    }

    // ---- 128: emigrate minor, then emigrate ----

    public function testAction128Phase1WithWhalingboatPromptsOptionalEmigrateMinor(): void
    {
        $this->g->setPlayer(0, 0, ['whalingboat']);
        $this->g->queue('doubleaction-128-1');
        [$queue, $next] = $this->g->step();
        $this->assertSame(['doubleaction-128-2'], $queue);
        $this->assertSame('command-emigrateminorpassable', $next);
    }

    public function testAction128Phase1WithoutWhalingboatSkipsToPhase2(): void
    {
        $this->g->setPlayer(0, 0, ['knarr']);
        $this->g->queue('doubleaction-128-1');
        [$queue, $next] = $this->g->step();
        $this->assertSame(['doubleaction-128-2'], $queue);
        $this->assertSame('next', $next);
    }

    public function testAction128Phase2MandatoryWhenFirstHalfSkipped(): void
    {
        $this->g->queue('doubleaction-128-2');
        [$queue] = $this->g->step();
        $this->assertSame(['emigrate'], $queue);
    }

    public function testAction128Phase2OptionalWhenFirstHalfTaken(): void
    {
        $this->g->state['double_action_first_taken'] = 1;
        $this->g->queue('doubleaction-128-2');
        [$queue] = $this->g->step();
        $this->assertSame(['emigratepassable'], $queue);
    }

    // ---- 5557: buy whaling boat, then explore ----

    public function testAction5557Phase1AffordableGoesToBuyState(): void
    {
        $this->g->setPlayer(1, 1, []);
        $this->g->queue('doubleaction-5557-1', 'resources-x');
        [$queue, $next] = $this->g->step();
        // push (not unshift): phase 2 lands behind anything already queued
        $this->assertSame(['resources-x', 'doubleaction-5557-2'], $queue);
        $this->assertSame('command-buywhalingboatfifthcol', $next);
    }

    public function testAction5557Phase1CannotAffordGoesStraightToExplore(): void
    {
        $this->g->setPlayer(0, 5, []);
        Island::$available = ['A'];
        FfoPlayer::$eligibleIslands = ['A'];
        $this->g->queue('doubleaction-5557-1');
        [$queue, $next] = $this->g->step();
        $this->assertSame(['explore-1'], $queue);
        $this->assertSame('next', $next);
    }

    public function testAction5557Phase1ThreeWhalingboatsCannotBuy(): void
    {
        $this->g->setPlayer(5, 5, ['whalingboat', 'whalingboat', 'whalingboat']);
        Island::$available = ['A'];
        FfoPlayer::$eligibleIslands = ['A'];
        $this->g->queue('doubleaction-5557-1');
        [$queue, $next] = $this->g->step();
        $this->assertSame(['explore-1'], $queue);
        $this->assertSame('next', $next);
    }

    public function testAction5557Phase1NoEligibleIslandQueuesNothing(): void
    {
        $this->g->setPlayer(0, 0, []);
        Island::$available = ['A'];
        FfoPlayer::$eligibleIslands = ['B'];
        $this->g->queue('doubleaction-5557-1');
        [$queue, $next] = $this->g->step();
        $this->assertSame([], $queue);
        $this->assertSame('next', $next);
    }

    public function testAction5557Phase2MandatoryExploreWhenNothingBought(): void
    {
        Island::$available = ['A'];
        FfoPlayer::$eligibleIslands = ['A'];
        $this->g->queue('doubleaction-5557-2');
        [$queue] = $this->g->step();
        $this->assertSame(['explore-1'], $queue);
    }

    public function testAction5557Phase2OptionalExploreWhenBoatBought(): void
    {
        $this->g->state['double_action_first_taken'] = 1;
        Island::$available = ['A'];
        FfoPlayer::$eligibleIslands = ['A'];
        $this->g->queue('doubleaction-5557-2');
        [$queue] = $this->g->step();
        $this->assertSame(['explorepassable-1'], $queue);
    }

    public function testAction5557Phase2NoEligibleIslandQueuesNothing(): void
    {
        $this->g->queue('doubleaction-5557-2');
        [$queue] = $this->g->step();
        $this->assertSame([], $queue);
    }
}
