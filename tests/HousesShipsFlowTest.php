<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Characterization tests for the surrounding flow of houses/ships row actions:
 *  - Requirements::check (can the action be taken at all?)
 *  - selectMode in command-pickmodal (cost payment, queued commands, occupation hooks)
 *  - selectShip for the whaling boat -> knarr/longship swaps (5559)
 */
class HousesShipsFlowTest extends TestCase
{
    private FakeCommandGame $g;

    protected function setUp(): void
    {
        $this->g = new FakeCommandGame();
    }

    private function check(int $actionId, ?int $mode = null): void
    {
        Requirements::check($actionId, $this->g->player(), $mode);
    }

    private function assertMet(int $actionId, ?int $mode = null): void
    {
        $this->check($actionId, $mode);
        $this->addToAssertionCount(1);
    }

    private function assertNotMet(int $actionId, ?int $mode = null): void
    {
        try {
            $this->check($actionId, $mode);
        } catch (BgaUserException $e) {
            $this->addToAssertionCount(1);
            return;
        }
        $this->fail("Action $actionId (mode " . var_export($mode, true) . ") should not be available");
    }

    // ---- Requirements::check ----

    public static function simpleCosts(): array
    {
        // id, norwegians, affordable [coin, wood, stone], unaffordable
        return [
            '1' => [1, false, [0, 2, 0], [0, 1, 0]],
            '2' => [2, false, [0, 0, 1], [0, 5, 0]],
            '3' => [3, false, [0, 0, 2], [0, 5, 1]],
            '5' => [5, false, [0, 1, 0], [5, 0, 5]],
            '6' => [6, false, [0, 2, 0], [0, 1, 0]],
            '7' => [7, false, [0, 2, 0], [0, 1, 0]],
            '77' => [77, true, [0, 1, 0], [5, 0, 5]],
            '78' => [78, true, [0, 1, 0], [5, 0, 5]],
        ];
    }

    #[DataProvider('simpleCosts')]
    public function testRequirementsCost(int $id, bool $nw, array $ok, array $bad): void
    {
        $this->g->norwegians = $nw;
        $this->g->setPlayer($ok[0], $ok[1], [], $ok[2]);
        $this->assertMet($id);
        $this->g->setPlayer($bad[0], $bad[1], [], $bad[2]);
        $this->assertNotMet($id);
    }

    public function testAction4NeedsTwoWoodAndTwoStone(): void
    {
        $this->g->setPlayer(0, 2, [], 2);
        $this->assertMet(4);
        $this->g->setPlayer(0, 2, [], 1);
        $this->assertNotMet(4);
        $this->g->setPlayer(0, 1, [], 2);
        $this->assertNotMet(4);
    }

    public function testMasterBricklayerLowersStoneRequirementOfAction4(): void
    {
        $this->g->addOccupation('played', 1, 116);
        $this->g->setPlayer(0, 2, [], 1);
        $this->assertMet(4);
    }

    public function testAction5550IsFree(): void
    {
        $this->g->norwegians = true;
        $this->g->setPlayer(0, 0, []);
        $this->assertMet(5550);
    }

    public function testEmptyHouseSupplyBlocksActions1To3(): void
    {
        $this->g->setPlayer(0, 9, [], 9);
        foreach ([1 => 'num_sheds', 2 => 'num_stonehouses', 3 => 'num_longhouses'] as $id => $supply) {
            $this->assertMet($id);
            $this->g->state[$supply] = 0;
            $this->assertNotMet($id);
        }
    }

    public function testEmptySupplyDoesNotBlockOtherHouseActions(): void
    {
        // Only 1, 2, 3 check the supply up front; 4 / 90 / 91 / 77 do not
        $this->g->norwegians = true;
        $this->g->setPlayer(0, 9, [], 9);
        foreach (['num_sheds', 'num_stonehouses', 'num_longhouses'] as $supply) {
            $this->g->state[$supply] = 0;
        }
        foreach ([4, 77, 90, 91] as $id) {
            $this->assertMet($id);
        }
    }

    public function testBigBoatActionsNeedRoomEvenIfAffordable(): void
    {
        $this->g->setPlayer(0, 9, ['knarr', 'knarr', 'longship', 'longship']);
        $this->assertNotMet(6);
        $this->assertNotMet(7);
        $this->g->setPlayer(0, 9, ['knarr', 'knarr', 'longship', 'whalingboat']);
        $this->assertMet(6);
        $this->assertMet(7);
    }

    public function testActions90And91CheckOnlyTheChosenModeWhenGiven(): void
    {
        $this->g->norwegians = true;
        $this->g->setPlayer(0, 1, [], 0); // only the boat option is affordable on 90
        $this->assertMet(90);
        $this->assertMet(90, 1);
        $this->assertNotMet(90, 0);

        $this->g->setPlayer(0, 0, [], 1); // only the house option
        $this->assertMet(90);
        $this->assertMet(90, 0);
        $this->assertNotMet(90, 1);

        $this->g->setPlayer(0, 2, [], 1); // 91: boat only (house needs 2 stone)
        $this->assertMet(91);
        $this->assertMet(91, 1);
        $this->assertNotMet(91, 0);
    }

    public function testAction5559NeedsOneCoinAndAnyBoat(): void
    {
        $this->g->norwegians = true;
        $this->g->setPlayer(1, 0, ['whalingboat']);
        $this->assertMet(5559);
        $this->g->setPlayer(0, 0, ['whalingboat']);
        $this->assertNotMet(5559);
        $this->g->setPlayer(1, 0, []);
        $this->assertNotMet(5559);
    }

    public function testResolvedEffectChangesTheRefusalMessage(): void
    {
        $this->g->setPlayer(0, 0, []);
        try {
            $this->check(1);
            $this->fail('expected exception');
        } catch (BgaUserException $e) {
            $this->assertSame('You do not meet the requirements for the action space', $e->getMessage());
        }
        $this->g->state['resolved_effect'] = 1;
        try {
            $this->check(1);
            $this->fail('expected exception');
        } catch (BgaUserException $e) {
            $this->assertStringContainsString("After resolving this effect", $e->getMessage());
        }
    }

    // ---- selectMode in command-pickmodal ----

    private function pick(int $actionId, int $mode = 0): void
    {
        $this->g->pickMode('command-pickmodal', $actionId, $mode);
    }

    public function testPickModalPaysCostAndQueuesCommands(): void
    {
        $this->g->setPlayer(0, 5, []);
        $this->pick(6);
        $this->assertSame(3, $this->g->player()['wood']);
        $this->assertSame(['build-knarr'], $this->g->queued());
    }

    public function testPickModalSpendsEveryCostAsset(): void
    {
        $this->g->setPlayer(0, 5, [], 5);
        $this->pick(4, 1);
        $p = $this->g->player();
        $this->assertSame(3, $p['wood']);
        $this->assertSame(3, $p['stone']);
        $this->assertSame(['build-longhouse-knarr'], $this->g->queued());
    }

    public function testPickModalUsesTheDiscountedCost(): void
    {
        $this->g->addOccupation('played', 1, 116);
        $this->g->setPlayer(0, 5, [], 5);
        $this->pick(3);
        $this->assertSame(4, $this->g->player()['stone']);
    }

    public function testPickModalFailsWithoutPayingWhenUnaffordable(): void
    {
        $this->g->setPlayer(0, 1, []);
        try {
            $this->pick(6);
            $this->fail('expected exception');
        } catch (BgaUserException $e) {
            $this->assertSame(1, $this->g->player()['wood']);
            $this->assertSame([], $this->g->queued());
        }
    }

    public function testPickModalRejectsUnknownMode(): void
    {
        $this->g->setPlayer(0, 5, []);
        $this->expectException(BgaVisibleSystemException::class);
        $this->pick(6, 1);
    }

    public function testPickModalOnMultiVariantActionCostsOnlyChosenVariant(): void
    {
        $this->g->norwegians = true;
        $this->g->setPlayer(0, 5, [], 5);
        $this->pick(90, 1);
        $p = $this->g->player();
        $this->assertSame(4, $p['wood']);
        $this->assertSame(5, $p['stone']);
        $this->assertSame(['build-knarr'], $this->g->queued());
    }

    public static function occupationHooks(): array
    {
        // [action, norwegians, mode, occupations played, expected queue]
        return [
            'knarr + Shipowner' => [6, false, 0, [117], ['upgrade-1-1', 'build-knarr']],
            'knarr + Spice trader' => [6, false, 0, [148], ['build-knarr', 'state-spicetrader']],
            'knarr + both' => [6, false, 0, [117, 148], ['upgrade-1-1', 'build-knarr', 'state-spicetrader']],
            'longship + Shipowner' => [7, false, 0, [117], ['upgrade-1-1', 'build-longship']],
            'longship ignores Spice trader' => [7, false, 0, [148], ['build-longship']],
            'whalingboat has no hooks' => [5, false, 0, [117, 148, 115, 15], ['build-whalingboat']],
            '78 knarr + both' => [78, true, 0, [117, 148], ['upgrade-1-1', 'build-knarr', 'state-spicetrader']],
            'shed + Princess' => [1, false, 0, [115], ['build-shed', 'take-silverware:occupation-115']],
            'shed ignores Cottager' => [1, false, 0, [15], ['build-shed']],
            'stonehouse + Cottager' => [2, false, 0, [15], ['build-stonehouse', 'take-hide:occupation-15']],
            'longhouse + Princess + Cottager' => [3, false, 0, [115, 15], ['build-longhouse', 'take-silverware:occupation-115', 'take-hide:occupation-15']],
            '4 stonehouse-longship + Shipowner' => [4, false, 0, [117], ['upgrade-1-1', 'build-stonehouse-longship']],
            '4 stonehouse-longship ignores Spice trader' => [4, false, 0, [148], ['build-stonehouse-longship']],
            '4 longhouse-knarr + all' => [4, false, 1, [117, 148, 115, 15], [
                'upgrade-1-1', 'build-longhouse-knarr', 'take-silverware:occupation-115', 'state-spicetrader', 'take-hide:occupation-15',
            ]],
            '90 house mode: Cottager + Princess' => [90, true, 0, [117, 148, 115, 15], ['build-stonehouse', 'take-silverware:occupation-115', 'take-hide:occupation-15']],
            '90 knarr mode: Shipowner + Spice trader + Princess' => [90, true, 1, [117, 148, 115, 15], [
                'upgrade-1-1', 'build-knarr', 'take-silverware:occupation-115', 'state-spicetrader',
            ]],
            '91 house mode: Cottager' => [91, true, 0, [117, 148, 15], ['build-longhouse', 'take-hide:occupation-15']],
            // Characterized as-is: Spice trader is not wired to the 91 longship option
            '91 longship mode: Shipowner only' => [91, true, 1, [117, 148, 15], ['upgrade-1-1', 'build-longship']],
            '77 shed + Princess' => [77, true, 0, [115], ['build-shed', 'take-silverware:occupation-115']],
            '5550 artisan shed + Princess' => [5550, true, 1, [115], ['build-artisan0', 'take-silverware:occupation-115']],
        ];
    }

    #[DataProvider('occupationHooks')]
    public function testOccupationHooksOnPickModal(int $action, bool $nw, int $mode, array $occs, array $expected): void
    {
        $this->g->norwegians = $nw;
        $this->g->setPlayer(9, 9, [], 9);
        foreach ($occs as $o) {
            $this->g->addOccupation('played', 1, $o);
        }
        $this->pick($action, $mode);
        $this->assertSame($expected, $this->g->queued());
    }

    public function testOccupationsOfAnotherPlayerOrNotPlayedDoNotTrigger(): void
    {
        $this->g->addPlayer(2);
        $this->g->setPlayer(9, 9, []);
        $this->g->addOccupation('played', 2, 117);
        $this->g->addOccupation('hand', 1, 148);
        $this->pick(6);
        $this->assertSame(['build-knarr'], $this->g->queued());
    }

    public function testPendingPlayOccupationIsAppendedAndConsumed(): void
    {
        $this->g->setPlayer(0, 5, []);
        $this->g->state['pending_play'] = 1;
        $this->pick(6);
        $this->assertSame(['build-knarr', 'play-occupation'], $this->g->queued());
        $this->assertSame(0, $this->g->state['pending_play']);
    }

    public function testPickModalPutsNewCommandsInFrontOfExistingQueue(): void
    {
        $this->g->setPlayer(0, 5, []);
        $this->g->queue('earlier-command');
        $this->pick(6);
        $this->assertSame(['build-knarr', 'earlier-command'], $this->g->queued());
    }

    public function testAction5559PaysOneCoinAndQueuesTheSwapState(): void
    {
        $this->g->norwegians = true;
        $this->g->setPlayer(3, 0, ['whalingboat']);
        $this->pick(5559);
        $this->assertSame(2, $this->g->player()['coin']);
        $this->assertSame(['buylongshipfifthcol'], $this->g->queued());
    }

    // ---- selectShip: whaling boat swaps ----

    public function testSwapWhalingboatForKnarr(): void
    {
        $this->g->setPlayer(0, 0, ['whalingboat', 'longship']);
        $this->g->pickShip('command-swapwhalingboatknarr', 1);
        $this->assertEqualsCanonicalizing(['longship', 'knarr'], $this->g->shipTypes());
        $this->assertSame('done', $this->g->transitions[0]);
    }

    public function testBuyLongshipSwapsWhalingboatAndDoesNotChargeAgain(): void
    {
        $this->g->setPlayer(2, 0, ['whalingboat', 'whalingboat']);
        $this->g->pickShip('command-buylongshipfifthcol', 2);
        $this->assertEqualsCanonicalizing(['whalingboat', 'longship'], $this->g->shipTypes());
        // the coin is paid by pickmodal, not here (see TODO in selectShip.php)
        $this->assertSame(2, $this->g->player()['coin']);
        $this->assertSame('done', $this->g->transitions[0]);
    }

    public static function swapStates(): array
    {
        return ['knarr swap' => ['command-swapwhalingboatknarr'], 'longship purchase' => ['command-buylongshipfifthcol']];
    }

    #[DataProvider('swapStates')]
    public function testSwapRefusedWhenNoRoomForBigBoat(string $state): void
    {
        $this->g->setPlayer(0, 0, ['whalingboat', 'knarr', 'knarr', 'longship', 'longship']);
        try {
            $this->g->pickShip($state, 1);
            $this->fail('expected exception');
        } catch (BgaUserException $e) {
            $this->assertCount(5, $this->g->shipTypes());
        }
    }

    #[DataProvider('swapStates')]
    public function testSwapRefusesNonWhalingboat(string $state): void
    {
        $this->g->setPlayer(0, 0, ['knarr']);
        $this->expectException(BgaVisibleSystemException::class);
        $this->g->pickShip($state, 1);
    }

    #[DataProvider('swapStates')]
    public function testSwapRefusesShipYouDoNotHave(string $state): void
    {
        $this->g->setPlayer(0, 0, ['whalingboat']);
        $this->expectException(BgaVisibleSystemException::class);
        $this->g->pickShip($state, 99);
    }

    public function testAction5559EndToEnd(): void
    {
        $this->g->norwegians = true;
        $this->g->setPlayer(3, 0, ['whalingboat', 'knarr']);
        $this->pick(5559);
        $this->g->pickShip('command-buylongshipfifthcol', 1);
        $p = $this->g->player();
        $this->assertSame(2, $p['coin']);
        $this->assertEqualsCanonicalizing(['knarr', 'longship'], $this->g->shipTypes());
    }
}
