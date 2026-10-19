<?php

use PHPUnit\Framework\TestCase;

/**
 * Characterization tests for every action in the "houses" and/or "ships" rows:
 * 1, 2, 3, 4, 5, 6, 7, 77, 78, 90, 91, 5550, 5559.
 */
class HousesShipsActionsTest extends TestCase
{
    private const PRE_1 = ['linkedactions'];
    private const PRE_3 = ['linkedactions', 'beforeorafter-draw-occupation'];
    private const PRE_4 = ['beforeorafter-play-occupation', 'linkedactions'];

    private FakeCommandGame $g;

    protected function setUp(): void
    {
        $this->g = new FakeCommandGame();
    }

    /** id => [norwegians?, meeples, preCommands, variants [commands, cost, prerequisite]] */
    public static function definitions(): array
    {
        return [
            '1 shed' => [1, false, 1, self::PRE_1, [[['build-shed'], ['wood' => 2], []]]],
            '2 stonehouse' => [2, false, 2, self::PRE_1, [[['build-stonehouse'], ['stone' => 1], []]]],
            '3 longhouse' => [3, false, 3, self::PRE_3, [[['build-longhouse'], ['stone' => 2], []]]],
            '4 house+ship' => [4, false, 4, self::PRE_4, [
                [['build-stonehouse-longship'], ['wood' => 2, 'stone' => 2], []],
                [['build-longhouse-knarr'], ['wood' => 2, 'stone' => 2], []],
            ]],
            '5 whalingboat' => [5, false, 1, self::PRE_1, [[['build-whalingboat'], ['wood' => 1], []]]],
            '6 knarr' => [6, false, 2, self::PRE_1, [[['build-knarr'], ['wood' => 2], []]]],
            '7 longship' => [7, false, 3, self::PRE_3, [[['build-longship'], ['wood' => 2], []]]],
            '77 shed/artisan' => [77, true, 1, self::PRE_1, [
                [['build-shed'], ['wood' => 1], []],
                [['build-artisan0'], ['wood' => 1], []],
                [['build-artisan1'], ['wood' => 1], []],
            ]],
            '78 knarr' => [78, true, 2, self::PRE_1, [[['build-knarr'], ['wood' => 1], []]]],
            '90 stonehouse|knarr' => [90, true, 2, self::PRE_1, [
                [['build-stonehouse'], ['stone' => 1], []],
                [['build-knarr'], ['wood' => 1], []],
            ]],
            '91 longhouse|longship' => [91, true, 3, self::PRE_3, [
                [['build-longhouse'], ['stone' => 2], []],
                [['build-longship'], ['wood' => 2], []],
            ]],
            '5550 shed/artisan' => [5550, true, 2, self::PRE_1, [
                [['build-shed'], [], []],
                [['build-artisan0'], [], []],
                [['build-artisan1'], [], []],
            ]],
            '5559 whalingboat->longship' => [5559, true, 3, self::PRE_3, [
                [['buylongshipfifthcol'], ['coin' => 1], ['boat' => 1]],
            ]],
        ];
    }

    /** @dataProvider definitions */
    #[\PHPUnit\Framework\Attributes\DataProvider('definitions')]
    public function testDefinition(int $id, bool $norwegians, int $meeples, array $pre, array $variants): void
    {
        $this->g->norwegians = $norwegians;
        $actions = Action::getModifiedAction($id, 1);
        $this->assertCount(count($variants), $actions);
        foreach ($variants as $i => [$commands, $cost, $prereq]) {
            $this->assertSame($meeples, $actions[$i]->numberMeeples, "meeples #$i");
            $this->assertSame($pre, $actions[$i]->preCommands, "preCommands #$i");
            $this->assertSame($commands, $actions[$i]->commands, "commands #$i");
            $this->assertSame($cost, $actions[$i]->cost, "cost #$i");
            $this->assertSame($prereq, $actions[$i]->prerequisite, "prerequisite #$i");
        }
    }

    public function testRowMembership(): void
    {
        $houses = array_values(Action::filterActions('houses', 'row'));
        $ships = array_values(Action::filterActions('ships', 'row'));
        $this->assertEqualsCanonicalizing([1, 2, 3, 4, 77, 90, 91, 5550, 5559], $houses);
        $this->assertEqualsCanonicalizing([4, 5, 6, 7, 78, 90, 91, 5550, 5559], $ships);
    }

    public function testGameVersionAvailability(): void
    {
        foreach ([1, 6] as $id) {
            $this->assertContains($id, Action::getBaseGameActions());
            $this->assertNotContains($id, Action::getNorwegiansActions());
        }
        foreach ([77, 78, 90, 91, 5550, 5559] as $id) {
            $this->assertNotContains($id, Action::getBaseGameActions());
            $this->assertContains($id, Action::getNorwegiansActions());
        }
        foreach ([2, 3, 4, 5, 7] as $id) {
            $this->assertContains($id, Action::getBaseGameActions());
            $this->assertContains($id, Action::getNorwegiansActions());
        }
    }

    public function testBaseOnlyActionRejectedInNorwegiansAndViceVersa(): void
    {
        $this->g->norwegians = true;
        try {
            Action::getModifiedAction(6, 1);
            $this->fail('6 should be unavailable with Norwegians');
        } catch (BgaUserException $e) {
            $this->addToAssertionCount(1);
        }
        $this->g->norwegians = false;
        $this->expectException(BgaUserException::class);
        Action::getModifiedAction(78, 1);
    }

    // ---- modifiers ----

    public static function masterBricklayerCosts(): array
    {
        return [
            '2' => [2, false, [['stone' => 0]]],
            '3' => [3, false, [['stone' => 1]]],
            '4' => [4, false, [['wood' => 2, 'stone' => 1], ['wood' => 2, 'stone' => 1]]],
            '90' => [90, true, [['stone' => 0], ['wood' => 1]]],
            '91' => [91, true, [['stone' => 1], ['wood' => 2]]],
            '77 unchanged' => [77, true, [['wood' => 1], ['wood' => 1], ['wood' => 1]]],
            '5559 unchanged' => [5559, true, [['coin' => 1]]],
        ];
    }

    /** @dataProvider masterBricklayerCosts */
    #[\PHPUnit\Framework\Attributes\DataProvider('masterBricklayerCosts')]
    public function testMasterBricklayerReducesStoneOnHousesRowOnly(int $id, bool $nw, array $costs): void
    {
        $this->g->norwegians = $nw;
        $this->g->addOccupation('played', 1, 116);
        $actions = Action::getModifiedAction($id, 1);
        $this->assertSame($costs, array_map(fn($a) => $a->cost, $actions));
    }

    public function testMasterBricklayerOnlyAppliesWhenPlayedByThatPlayer(): void
    {
        $this->g->addPlayer(2);
        $this->g->addOccupation('played', 2, 116);
        $this->g->addOccupation('hand', 1, 116);
        $this->assertSame(['stone' => 1], Action::getModifiedAction(2, 1)[0]->cost);
    }

    public function testPatronAndPeddlerDoNotAffectHousesOrShips(): void
    {
        $this->g->addOccupation('played', 1, 1);
        $this->g->addOccupation('played', 1, 2);
        foreach ([1, 2, 3, 4, 5, 6, 7] as $id) {
            $before = array_map(fn($a) => $a->cost, Action::getModifiedAction($id, 3));
            $after = array_map(fn($a) => $a->cost, Action::getModifiedAction($id, 1));
            $this->assertSame($before, $after, "action $id");
        }
    }

    public function testPreceptorReplacesDrawOccupationOnThreeMeepleActions(): void
    {
        $this->g->addOccupation('played', 1, 99);
        foreach ([3, 7] as $id) {
            $this->assertSame(['linkedactions', 'state-preceptor'], Action::getModifiedAction($id, 1)[0]->preCommands);
        }
        $this->g->norwegians = true;
        foreach ([91, 5559] as $id) {
            $this->assertSame(['linkedactions', 'state-preceptor'], Action::getModifiedAction($id, 1)[0]->preCommands);
        }
        // not a draw action: unchanged
        $this->assertSame(self::PRE_4, Action::getModifiedAction(4, 1)[0]->preCommands);
    }

    public function testKnarrBuilderAddsThreeWoodKnarrOptionToAction5(): void
    {
        $this->g->addOccupation('played', 1, 105);
        $actions = Action::getModifiedAction(5, 1);
        $this->assertCount(2, $actions);
        $this->assertSame(['build-whalingboat'], $actions[0]->commands);
        $this->assertSame(['build-knarr'], $actions[1]->commands);
        $this->assertSame(['wood' => 3], $actions[1]->cost);
        $this->assertSame(1, $actions[1]->numberMeeples);
    }

    public function testKnarrBuilderWithSpiceTraderAndShipowner(): void
    {
        foreach ([105, 148, 117] as $a) {
            $this->g->addOccupation('played', 1, $a);
        }
        $extra = Action::getModifiedAction(5, 1)[1];
        $this->assertSame(['build-knarr', 'state-spicetrader', 'upgrade-1-1'], $extra->commands);
    }

    public function testKnarrBuilderOnlyAffectsAction5(): void
    {
        $this->g->addOccupation('played', 1, 105);
        $this->assertCount(1, Action::getModifiedAction(6, 1));
        $this->assertCount(1, Action::getModifiedAction(7, 1));
    }

    public function testArtisanShedBuiltLeavesOnlyShedOptionOn77And5550(): void
    {
        $this->g->norwegians = true;
        foreach ([77, 5550] as $id) {
            $this->assertCount(3, Action::getModifiedAction($id, 1));
        }
        FfoPlayer::$builtArtisanShed = true;
        foreach ([77, 5550] as $id) {
            $actions = Action::getModifiedAction($id, 1);
            $this->assertCount(1, $actions);
            $this->assertSame(['build-shed'], $actions[0]->commands);
        }
    }

    // ---- build command execution ----

    private function build(string $cmd): string
    {
        $this->g->queue($cmd);
        [$queue, $next] = $this->g->step();
        $this->assertSame([], $queue);
        return $next;
    }

    public function testBuildStonehouse(): void
    {
        $this->assertSame('next', $this->build('build-stonehouse'));
        $this->assertSame(2, $this->g->state['num_stonehouses']);
        $this->assertSame([['type' => 'stonehouse']], FfoPlayer::$addedBoards);
    }

    public function testBuildLonghouse(): void
    {
        $this->build('build-longhouse');
        $this->assertSame(4, $this->g->state['num_longhouses']);
        $this->assertSame([['type' => 'longhouse']], FfoPlayer::$addedBoards);
    }

    public function testBuildHouseWithEmptySupplyIsSilentlySkipped(): void
    {
        $this->g->state['num_longhouses'] = 0;
        $this->build('build-longhouse');
        $this->assertSame(0, $this->g->state['num_longhouses']);
        $this->assertSame([], FfoPlayer::$addedBoards);
    }

    public function testBuildWhalingboatAddsShip(): void
    {
        $this->g->setPlayer(0, 0, ['whalingboat', 'whalingboat', 'longship']);
        $this->build('build-whalingboat');
        $this->assertSame(['whalingboat'], FfoPlayer::$addedShips);
    }

    public function testBuildWhalingboatThrowsWhenThreeWhalingboats(): void
    {
        $this->g->setPlayer(0, 0, ['whalingboat', 'whalingboat', 'whalingboat']);
        $this->expectException(BgaUserException::class);
        $this->build('build-whalingboat');
    }

    /** @dataProvider bigBoats */
    #[\PHPUnit\Framework\Attributes\DataProvider('bigBoats')]
    public function testBuildBigBoatAddsShipWithRoom(string $cmd, string $type): void
    {
        $this->g->setPlayer(0, 0, ['knarr', 'longship', 'knarr', 'whalingboat', 'whalingboat', 'whalingboat']);
        $this->build($cmd);
        $this->assertSame([$type], FfoPlayer::$addedShips);
    }

    /** @dataProvider bigBoats */
    #[\PHPUnit\Framework\Attributes\DataProvider('bigBoats')]
    public function testBuildBigBoatThrowsWhenFourBigBoats(string $cmd, string $type): void
    {
        $this->g->setPlayer(0, 0, ['knarr', 'longship', 'knarr', 'knarr']);
        $this->expectException(BgaUserException::class);
        $this->build($cmd);
    }

    public static function bigBoats(): array
    {
        return ['knarr' => ['build-knarr', 'knarr'], 'longship' => ['build-longship', 'longship']];
    }

    public function testBuildStonehouseLongshipBuildsBoth(): void
    {
        $this->build('build-stonehouse-longship');
        $this->assertSame(2, $this->g->state['num_stonehouses']);
        $this->assertSame(['longship'], FfoPlayer::$addedShips);
    }

    public function testBuildLonghouseKnarrBuildsBoth(): void
    {
        $this->build('build-longhouse-knarr');
        $this->assertSame(4, $this->g->state['num_longhouses']);
        $this->assertSame(['knarr'], FfoPlayer::$addedShips);
    }

    public function testCombinedBuildStillBuildsShipWhenHouseSupplyEmpty(): void
    {
        $this->g->state['num_stonehouses'] = 0;
        $this->build('build-stonehouse-longship');
        $this->assertSame([], FfoPlayer::$addedBoards);
        $this->assertSame(['longship'], FfoPlayer::$addedShips);
    }

    public function testCombinedBuildSkipsShipWithoutErrorWhenNoRoomButHouseBuilt(): void
    {
        $this->g->setPlayer(0, 0, ['knarr', 'knarr', 'longship', 'longship']);
        $this->build('build-longhouse-knarr');
        $this->assertSame(4, $this->g->state['num_longhouses']);
        $this->assertSame([], FfoPlayer::$addedShips);
    }

    public function testCombinedBuildThrowsWhenNoRoomAndNoHouseBuilt(): void
    {
        $this->g->setPlayer(0, 0, ['knarr', 'knarr', 'longship', 'longship']);
        $this->g->state['num_longhouses'] = 0;
        $this->expectException(BgaUserException::class);
        $this->build('build-longhouse-knarr');
    }

    public function testBuildArtisanShedsUseThePlayersArtisanShed(): void
    {
        $this->build('build-artisan0');
        $this->build('build-artisan1');
        $this->assertSame([['type' => 'lumberjack'], ['type' => 'mason']], FfoPlayer::$addedBoards);
        $this->assertSame(3, $this->g->state['num_sheds']);
    }

    public function testBuildLongshipFifthColGoesToItsOwnState(): void
    {
        $this->assertSame('command-buylongshipfifthcol', $this->build('buylongshipfifthcol'));
    }

    public function testUnknownBuildTargetIsRejected(): void
    {
        $this->expectException(BgaVisibleSystemException::class);
        $this->build('build-castle');
    }
}
