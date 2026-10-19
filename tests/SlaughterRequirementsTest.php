<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Slaughter actions (85 sheep, 89 horse/cattle, 93 sheep/horse/cattle) must not be usable
 * without a matching animal. This matters most for Latecomer, which can start any
 * unoccupied first-column action regardless of what the player owns.
 */
class SlaughterRequirementsTest extends TestCase
{
    private FakeCommandGame $g;

    protected function setUp(): void
    {
        $this->g = new FakeCommandGame();
        $this->g->setPlayer(0, 0, []);
    }

    private function giveAnimals(string ...$types): void
    {
        $goods = array_map(fn($t) => ['type' => $t], $types);
        $this->g->exec("UPDATE player SET goods = '" . json_encode($goods) . "' WHERE player_id = 1");
    }

    private function assertMet(int $actionId, ?int $mode = null): void
    {
        Requirements::check($actionId, $this->g->player(), $mode);
        $this->addToAssertionCount(1);
    }

    private function assertNotMet(int $actionId, ?int $mode = null): void
    {
        try {
            Requirements::check($actionId, $this->g->player(), $mode);
        } catch (BgaUserException $e) {
            $this->addToAssertionCount(1);
            return;
        }
        $this->fail("Action $actionId (mode " . var_export($mode, true) . ") should not be available");
    }

    public function testNoAnimalsBlocksEverySlaughterAction(): void
    {
        $this->assertNotMet(85, 0);
        $this->assertNotMet(89, 0);
        $this->assertNotMet(89, 1);
        $this->assertNotMet(93, 0);
        $this->assertNotMet(93, 1);
        $this->assertNotMet(93, 2);
        $this->assertNotMet(85);
        $this->assertNotMet(89);
        $this->assertNotMet(93);
    }

    public static function animalsPerAction(): array
    {
        // action, mode, animal that satisfies it, animal that does not
        return [
            '85 sheep' => [85, 0, 'sheep', 'horse'],
            '85 pregnant sheep' => [85, 0, 'pregnantsheep', 'cattle'],
            '89 horse' => [89, 0, 'horse', 'sheep'],
            '89 pregnant horse' => [89, 0, 'pregnanthorse', 'cattle'],
            '89 cattle' => [89, 1, 'cattle', 'horse'],
            '89 pregnant cattle' => [89, 1, 'pregnantcattle', 'sheep'],
            '93 sheep' => [93, 0, 'sheep', 'horse'],
            '93 horse' => [93, 1, 'horse', 'cattle'],
            '93 cattle' => [93, 2, 'cattle', 'sheep'],
            '93 pregnant cattle' => [93, 2, 'pregnantcattle', 'pig'],
        ];
    }

    #[DataProvider('animalsPerAction')]
    public function testModeNeedsItsOwnAnimalType(int $action, int $mode, string $ok, string $wrong): void
    {
        $this->giveAnimals($ok);
        $this->assertMet($action, $mode);

        $this->giveAnimals($wrong);
        $this->assertNotMet($action, $mode);
    }

    public function testWithoutModeAnyMatchingAnimalIsEnough(): void
    {
        $this->giveAnimals('cattle');
        $this->assertMet(89);
        $this->assertMet(93);
        $this->assertNotMet(85);
    }

    public function testPigsCannotBeSlaughteredWithTheseActions(): void
    {
        $this->giveAnimals('pig');
        $this->assertNotMet(85);
        $this->assertNotMet(89);
        $this->assertNotMet(93);
    }

    public function testErrorNamesTheMissingAnimal(): void
    {
        foreach ([[85, 0, 'sheep'], [89, 0, 'horses'], [89, 1, 'cattle'], [93, 2, 'cattle']] as [$action, $mode, $word]) {
            try {
                Requirements::check($action, $this->g->player(), $mode);
                $this->fail("Action $action should throw");
            } catch (BgaUserException $e) {
                $this->assertStringContainsString($word, $e->getMessage());
            }
        }
    }

    public function testPickModalOnAction85WithoutSheepIsRejectedBeforeAnythingIsQueued(): void
    {
        $this->giveAnimals('horse');
        try {
            $this->g->pickMode('command-pickmodal', 85, 0);
            $this->fail('Expected exception');
        } catch (BgaUserException $e) {
            $this->assertStringContainsString('sheep', $e->getMessage());
        }
        $this->assertSame([], $this->g->queued());
    }

    public function testPickModalOnAction85WithSheepQueuesTheSlaughter(): void
    {
        $this->giveAnimals('pregnantsheep');
        $this->g->pickMode('command-pickmodal', 85, 0);
        $this->assertSame(['slaughter-sheep'], $this->g->queued());
    }

    public function testPickModalOnAction89And93UsesTheChosenModeAnimal(): void
    {
        $this->giveAnimals('horse');
        $this->g->pickMode('command-pickmodal', 89, 0);
        $this->assertSame(['slaughter-horse'], $this->g->queued());

        $this->expectException(BgaUserException::class);
        $this->g->pickMode('command-pickmodal', 93, 2);
    }
}
