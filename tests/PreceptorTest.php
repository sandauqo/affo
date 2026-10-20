<?php

use PHPUnit\Framework\TestCase;

/**
 * Preceptor (99): each time you would receive an occupation, you may play one instead.
 */
class PreceptorTest extends TestCase
{
    private FakeCommandGame $g;

    protected function setUp(): void
    {
        $this->g = new FakeCommandGame();
    }

    private function withPreceptor(): void
    {
        $this->g->addOccupation('played', 1, 99);
    }

    public function testSingleDrawIsSwappedForPreceptorPrompt(): void
    {
        $this->withPreceptor();
        $a = Action::getModifiedAction(65, 1);
        $this->assertSame(['take-coin', 'state-preceptor'], $a[0]->commands);
    }

    public function testBeforeOrAfterChoiceIsKeptForThreeMeepleActions(): void
    {
        $this->withPreceptor();
        $this->assertContains('beforeorafter-draw-occupation', Action::getModifiedAction(3, 1)[0]->preCommands);
        $this->assertNotContains('state-preceptor', Action::getModifiedAction(3, 1)[0]->preCommands);
    }

    public function testWithoutPreceptorNothingIsSwapped(): void
    {
        $this->assertContains('beforeorafter-draw-occupation', Action::getModifiedAction(3, 1)[0]->preCommands);
        $this->assertNotContains('state-preceptor', Action::getModifiedAction(3, 1)[0]->preCommands);
    }

    public function testBeforeChoiceQueuesPreceptorPromptNow(): void
    {
        $this->withPreceptor();
        $this->g->pickMode('command-beforeorafter-draw', 3, 0);
        $this->assertSame(['state-preceptor'], $this->g->queued());
    }

    public function testBeforeChoiceWithoutPreceptorQueuesPlainDraw(): void
    {
        $this->g->pickMode('command-beforeorafter-draw', 3, 0);
        $this->assertSame(['draw-occupation'], $this->g->queued());
    }

    public function testAfterChoiceSetsPendingDraw(): void
    {
        $this->withPreceptor();
        $this->g->pickMode('command-beforeorafter-draw', 3, 1);
        $this->assertSame(1, $this->g->state['pending_draw']);
        $this->assertSame([], $this->g->queued());
    }

    public function testPendingDrawBecomesPreceptorPromptAfterTheAction(): void
    {
        $this->withPreceptor();
        $this->g->setPlayer(0, 0, [], 5);
        $this->g->state['pending_draw'] = 1;
        $this->g->pickMode('command-pickmodal', 3, 0);
        $queue = $this->g->queued();
        $this->assertSame('state-preceptor', end($queue));
        $this->assertSame(0, $this->g->state['pending_draw']);
    }

    public function testPendingDrawIsLeftForConfirmTurnWithoutPreceptor(): void
    {
        $this->g->setPlayer(0, 0, [], 5);
        $this->g->state['pending_draw'] = 1;
        $this->g->pickMode('command-pickmodal', 3, 0);
        $this->assertNotContains('state-preceptor', $this->g->queued());
        $this->assertSame(1, $this->g->state['pending_draw']);
    }

    public function testPreceptorPromptStateIsReachedFromStateCommand(): void
    {
        $this->g->queue('state-preceptor');
        [$queue, $next] = $this->g->step();
        $this->assertSame([], $queue);
        $this->assertSame('command-preceptor', $next);
    }

    public function testPreceptorDrawModeQueuesDraw(): void
    {
        $this->withPreceptor();
        $this->g->pickMode('command-preceptor', 3, 0);
        $this->assertSame(['draw-occupation'], $this->g->queued());
    }

    public function testPreceptorPlayModeQueuesPlayWhenHandHasCards(): void
    {
        $this->withPreceptor();
        $this->g->addOccupation('hand', 1, 5);
        $this->g->pickMode('command-preceptor', 3, 1);
        $this->assertSame(['play-occupation'], $this->g->queued());
    }

    public function testPreceptorPlayModeWithEmptyHandIsRejectedAndKeepsQueue(): void
    {
        $this->withPreceptor();
        $this->g->queue('state-preceptor', 'state-preceptor');
        try {
            $this->g->pickMode('command-preceptor', 125, 1);
            $this->fail('expected exception');
        } catch (BgaUserException $e) {
            $this->assertSame(['state-preceptor', 'state-preceptor'], $this->g->queued());
        }
    }

    public function testPreceptorRejectsUnknownMode(): void
    {
        $this->withPreceptor();
        $this->expectException(BgaVisibleSystemException::class);
        $this->g->pickMode('command-preceptor', 3, 2);
    }

    public function testCoinsOrOccsQueuesOnePromptPerOccupationWithPreceptor(): void
    {
        $this->withPreceptor();
        $this->g->pickMode('command-coinsoroccs', 125, 1);
        $this->assertSame(['state-preceptor', 'state-preceptor', 'state-preceptor'], $this->g->queued());
    }

    public function testCoinsOrOccsQueuesThreePlainDrawsWithoutPreceptor(): void
    {
        $this->g->pickMode('command-coinsoroccs', 125, 1);
        $this->assertSame(['draw-occupation', 'draw-occupation', 'draw-occupation'], $this->g->queued());
    }

    public function testCoinsOrOccsCoinOptionIsUnaffectedByPreceptor(): void
    {
        $this->withPreceptor();
        $this->g->pickMode('command-coinsoroccs', 125, 0);
        $this->assertSame(['take-coin-2'], $this->g->queued());
    }
}
