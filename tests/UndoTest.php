<?php

use PHPUnit\Framework\TestCase;

/**
 * Simulates a turn (snapshot -> mutate -> undo) and checks the restored state.
 */
class UndoTest extends TestCase
{
    private FakeUndoGame $game;
    /** @var int[] occupation ids by name */
    private array $occ = [];

    protected function setUp(): void
    {
        $g = $this->game = new FakeUndoGame();
        $g->addPlayer(1, 5);
        $g->addPlayer(2, 7);

        // Player 1: two in hand, one played. Player 2: one in hand, one played. Two left in the deck.
        $this->occ['p1_hand_a'] = $g->addOccupation('hand', 1, 11);
        $this->occ['p1_hand_b'] = $g->addOccupation('hand', 1, 12);
        $this->occ['p1_played'] = $g->addOccupation('played', 1, 13);
        $this->occ['p2_hand'] = $g->addOccupation('hand', 2, 21);
        $this->occ['p2_played'] = $g->addOccupation('played', 2, 22);
        $this->occ['deck_a'] = $g->addOccupation('deck', null, 31);
        $this->occ['deck_b'] = $g->addOccupation('deck', null, 32);
    }

    /** What stPickAction does at the start of a turn. */
    private function startTurn(int $playerId): void
    {
        $this->game->saveState($playerId);
    }

    public function testPlayingAnOccupationIsUndone(): void
    {
        $before = $this->game->occupations();
        $this->startTurn(1);

        $this->game->saveOccupations(1);
        Occupation::play($this->occ['p1_hand_a']);

        $this->game->restoreState(1);

        $this->assertSame($before, $this->game->occupations());
    }

    public function testDiscardingAnOccupationIsUndone(): void
    {
        $before = $this->game->occupations();
        $this->startTurn(1);

        $this->game->saveOccupations(1);
        Occupation::discard($this->occ['p1_hand_b']);

        $this->game->restoreState(1);

        $this->assertSame($before, $this->game->occupations());
    }

    public function testUsedFlagIsUndone(): void
    {
        $before = $this->game->occupations();
        $this->startTurn(1);

        $this->game->saveOccupations(1);
        Occupation::use($this->occ['p1_played']);

        $this->game->restoreState(1);

        $this->assertSame($before, $this->game->occupations());
    }

    /**
     * Regression: "own cards doubled, opponent's cards disappeared".
     * The occupation snapshot used to be taken for whichever player saved first and
     * restored without a player filter. If it was taken for player 2 (e.g. an effect
     * checking player 2's occupations) and player 1 undid, player 1's cards were deleted
     * and player 2's cards were inserted a second time.
     */
    public function testSnapshotSavedForAnotherPlayerDoesNotCorruptUndo(): void
    {
        $before = $this->game->occupations();
        $this->startTurn(1);

        $this->game->saveOccupations(2);
        Occupation::play($this->occ['p1_hand_a']);

        $this->game->restoreState(1);

        $this->assertSame($before, $this->game->occupations());
        $this->assertCount(2, Occupation::getHand(1));
        $this->assertCount(1, Occupation::getHand(2));
        $this->assertCount(1, Occupation::getPlayed(1));
        $this->assertCount(1, Occupation::getPlayed(2));
    }

    /** Same regression in the other direction (player 2 undoes, snapshot taken for player 1). */
    public function testSnapshotSavedForAnotherPlayerDoesNotCorruptUndoReverse(): void
    {
        $before = $this->game->occupations();
        $this->startTurn(2);

        $this->game->saveOccupations(1);
        Occupation::play($this->occ['p2_hand']);

        $this->game->restoreState(2);

        $this->assertSame($before, $this->game->occupations());
    }

    public function testSecondSaveInSameTurnKeepsTheTurnStartSnapshot(): void
    {
        $before = $this->game->occupations();
        $this->startTurn(1);

        $this->game->saveOccupations(1);
        Occupation::play($this->occ['p1_hand_a']);
        $this->game->saveOccupations(1); // must not overwrite the snapshot
        Occupation::discard($this->occ['p1_hand_b']);

        $this->game->restoreState(1);

        $this->assertSame($before, $this->game->occupations());
    }

    public function testCardIdsAreKeptAndDrawnCardsReturnToTheDeck(): void
    {
        $before = $this->game->occupations();
        $this->startTurn(1);

        $this->game->saveOccupations(1);
        $drawn = Occupation::draw(1);
        $this->assertSame('hand', Occupation::getHand(1)[2]['location']);

        $this->game->restoreState(1);

        $this->assertSame($before, $this->game->occupations());
        $this->assertSame('deck', $this->game->rows("SELECT location FROM occupation WHERE id = $drawn[id]")[0]['location']);
    }

    public function testDrawingFromAnEmptyDeckIsAReversibleNoOp(): void
    {
        $this->game->exec("UPDATE occupation SET location = 'discard' WHERE location = 'deck'");
        $before = $this->game->occupations();
        $this->startTurn(1);
        $this->game->saveOccupations(1);

        $this->assertNull(Occupation::draw(1));
        $this->assertSame($before, $this->game->occupations());

        $this->game->restoreState(1);

        $this->assertSame($before, $this->game->occupations());
    }

    public function testUndoWithoutOccupationSnapshotLeavesOccupationsUntouched(): void
    {
        $this->startTurn(1);
        Occupation::play($this->occ['p1_hand_a']); // no saveOccupations() call, so no snapshot
        $after = $this->game->occupations();

        $this->game->restoreState(1);

        $this->assertSame($after, $this->game->occupations());
    }

    public function testPlayerResourcesAndMeeplesAreRestoredForTheUndoingPlayerOnly(): void
    {
        $m1 = $this->game->addMeeple(1, 'thing', 0);
        $m2 = $this->game->addMeeple(2, 'thing', 0);
        $this->startTurn(1);

        $this->game->exec("UPDATE player SET coin = 99, wood = 4 WHERE player_id = 1");
        $this->game->exec("UPDATE meeple SET location = 'action', slot = 3 WHERE id = $m1");
        $this->game->exec("UPDATE player SET coin = 50 WHERE player_id = 2"); // not part of the undo

        $this->game->restoreState(1);

        $p1 = $this->game->rows("SELECT coin, wood FROM player WHERE player_id = 1")[0];
        $this->assertEquals(['coin' => 5, 'wood' => 0], $p1);
        $this->assertSame('thing', $this->game->rows("SELECT location FROM meeple WHERE id = $m1")[0]['location']);
        $this->assertEquals(50, $this->game->rows("SELECT coin FROM player WHERE player_id = 2")[0]['coin']);
        $this->assertCount(1, $this->game->rows("SELECT * FROM meeple WHERE player_id = 2"));
        $this->assertSame($m2, (int) $this->game->rows("SELECT id FROM meeple WHERE player_id = 2")[0]['id']);
    }

    public function testUndoRestoresPlayerCollectionsAndReplacesMeeplesExactly(): void
    {
        $originalMeeple = $this->game->addMeeple(1, 'thing', 2);
        $beforeMeeples = $this->game->rows("SELECT * FROM meeple WHERE player_id = 1 ORDER BY id");
        $this->game->exec("UPDATE player SET goods = '[{\"type\":\"cane\"}]', boards = '[{\"index\":4}]',
            ships = '[{\"id\":1,\"type\":\"knarr\"}]', emigrations = '[{\"id\":2}]', player_score = 12 WHERE player_id = 1");
        $beforePlayer = $this->game->rows("SELECT goods, boards, ships, emigrations, player_score FROM player WHERE player_id = 1");

        $this->startTurn(1);
        $this->game->exec("DELETE FROM meeple WHERE id = $originalMeeple");
        $this->game->addMeeple(1, 'action', 7);
        $this->game->exec("UPDATE player SET goods = '[]', boards = '[]', ships = '[]',
            emigrations = '[]', player_score = 99 WHERE player_id = 1");

        $this->game->restoreState(1);

        $this->assertSame($beforePlayer, $this->game->rows("SELECT goods, boards, ships, emigrations, player_score FROM player WHERE player_id = 1"));
        $this->assertSame($beforeMeeples, $this->game->rows("SELECT * FROM meeple WHERE player_id = 1 ORDER BY id"));
    }

    public function testUndoRestoresAllSavedGlobalCounters(): void
    {
        $this->game->state['num_sheds'] = 8;
        $this->game->state['num_stonehouses'] = 6;
        $this->game->state['num_longhouses'] = 4;
        $this->game->state['num_vp_token_4'] = 3;
        $this->game->state['num_vp_token_3'] = 1;
        $this->startTurn(1);

        $this->game->state['num_sheds'] = 0;
        $this->game->state['num_stonehouses'] = 0;
        $this->game->state['num_longhouses'] = 0;
        $this->game->state['num_vp_token_4'] = 0;
        $this->game->state['num_vp_token_3'] = 0;

        $this->game->restoreState(1);

        $this->assertSame(8, $this->game->state['num_sheds']);
        $this->assertSame(6, $this->game->state['num_stonehouses']);
        $this->assertSame(4, $this->game->state['num_longhouses']);
        $this->assertSame(3, $this->game->state['num_vp_token_4']);
        $this->assertSame(1, $this->game->state['num_vp_token_3']);
    }

    public function testUndoRestoresEveryOptInSharedTableAndClearsQueuedCommands(): void
    {
        $tables = [
            'forge' => 'undo_forge',
            'string_globals' => 'undo_string_globals',
            'available_islands' => 'undo_islands',
            'mountain_strips' => 'undo_mountain_strips',
        ];
        foreach (array_keys($tables) as $table) {
            $this->game->exec("INSERT INTO $table (k, v) VALUES ('original', '$table')");
        }
        $this->game->exec("INSERT INTO command_queue (command) VALUES ('pending')");
        $this->startTurn(1);

        foreach ($tables as $table => $flag) {
            $saveMethod = match ($table) {
                'forge' => 'saveForge',
                'string_globals' => 'saveStringGlobals',
                'available_islands' => 'saveIslands',
                'mountain_strips' => 'saveMountainStrips',
            };
            $this->game->$saveMethod();
            $this->game->exec("DELETE FROM $table");
            $this->game->exec("INSERT INTO $table (k, v) VALUES ('changed', '$table')");
            $this->game->$saveMethod();
            $this->assertSame(1, $this->game->state[$flag]);
        }

        $this->game->restoreState(1);

        foreach (array_keys($tables) as $table) {
            $this->assertSame(
                [['k' => 'original', 'v' => $table]],
                $this->game->rows("SELECT k, v FROM $table")
            );
        }
        $this->assertSame([], $this->game->rows("SELECT * FROM command_queue"));
    }

    public function testUndoRestoresSpecialTileOnOpponentBoardForBothComplexOccupations(): void
    {
        $this->startTurn(1);
        $boards = [
            [
                'index' => 4,
                'goods' => [
                    ['type' => 'rune', 'occ:70' => 1],
                    ['type' => 'silverware', 'occ:70' => 1],
                    ['type' => 'sheep', 'coords' => [0, 0]],
                ],
            ],
            [
                'index' => 5,
                'goods' => [
                    ['type' => 'rune', 'occ:71' => 1],
                    ['type' => 'chest', 'occ:71' => 1],
                    ['type' => 'cattle', 'coords' => [1, 1]],
                ],
            ],
        ];
        $this->game->exec("UPDATE player SET boards = '" . json_encode($boards) . "' WHERE player_id = 2");
        $this->game->saveStringGlobals();
        $this->game->setString('occ:70', '2-3-1-2-true-4');
        $this->game->setString('occ:71', '2-1-2-3-false-5');

        $this->game->restoreState(1);

        $restoredBoards = json_decode(
            $this->game->rows("SELECT boards FROM player WHERE player_id = 2")[0]['boards'],
            true
        );
        $this->assertSame(
            [
                ['type' => 'sheep', 'coords' => [0, 0]],
                [
                    'type' => 'cane',
                    'upgrade' => null,
                    'colour' => 'blue',
                    'strength' => 8,
                    'cols' => 4,
                    'rows' => 2,
                    'flipped' => true,
                    'rotation' => 2,
                    'special' => true,
                    'cells' => [
                        ['coords' => [0, 1], 'type' => 'void'],
                        ['coords' => [1, 1], 'type' => 'void'],
                        ['coords' => [2, 1], 'type' => 'void'],
                    ],
                    'coords' => [3, 1],
                ],
            ],
            $restoredBoards[0]['goods']
        );
        $this->assertSame(
            [
                ['type' => 'cattle', 'coords' => [1, 1]],
                [
                    'type' => 'horn',
                    'upgrade' => null,
                    'colour' => 'blue',
                    'strength' => 8,
                    'cols' => 3,
                    'rows' => 3,
                    'flipped' => false,
                    'rotation' => 3,
                    'special' => true,
                    'cells' => [
                        ['coords' => [0, 2], 'type' => 'void'],
                        ['coords' => [1, 0], 'type' => 'void'],
                        ['coords' => [2, 0], 'type' => 'void'],
                    ],
                    'coords' => [1, 2],
                ],
            ],
            $restoredBoards[1]['goods']
        );
        $this->assertSame('updateBoards', $this->game->notifications[0]['type']);
        $this->assertSame(2, $this->game->notifications[0]['args']['player_id']);
    }

    public function testStringGlobalsUndoWithNoComplexOccupationMetadataDoesNotChangeBoards(): void
    {
        $this->startTurn(1);
        $boards = [['index' => 4, 'goods' => [['type' => 'sheep', 'coords' => [0, 0]]]]];
        $this->game->exec("UPDATE player SET boards = '" . json_encode($boards) . "' WHERE player_id = 2");
        $this->game->exec("INSERT INTO string_globals (k, v) VALUES ('example', 'before')");
        $this->game->saveStringGlobals();
        $this->game->exec("UPDATE string_globals SET v = 'after' WHERE k = 'example'");
        $this->game->setString('occ:70', '');
        $this->game->setString('occ:71', '');

        $this->game->restoreState(1);

        $this->assertSame($boards, json_decode(
            $this->game->rows("SELECT boards FROM player WHERE player_id = 2")[0]['boards'],
            true
        ));
        $this->assertSame([['k' => 'example', 'v' => 'before']], $this->game->rows("SELECT * FROM string_globals"));
        $this->assertSame('undoTurn', $this->game->notifications[0]['type']);
        $this->assertCount(1, $this->game->notifications);
    }

    public function testUndoRestoresAnEmptySharedTableSnapshot(): void
    {
        $this->startTurn(1);
        $this->game->saveIslands();
        $this->game->exec("INSERT INTO available_islands (k, v) VALUES ('island', 'occupied')");

        $this->game->restoreState(1);

        $this->assertSame([], $this->game->rows("SELECT * FROM available_islands"));
    }

    public function testUnsnappedTablesAndCardsRemainUntouched(): void
    {
        $this->game->exec("INSERT INTO weapon (type, location, player_id) VALUES ('bow', 'hand', 1)");
        $this->game->exec("INSERT INTO forge (k, v) VALUES ('original', 'value')");
        $this->game->exec("INSERT INTO available_islands (k, v) VALUES ('original', 'value')");
        $this->startTurn(1);

        $this->game->saveForge();
        $this->game->exec("DELETE FROM forge");
        $this->game->exec("INSERT INTO forge (k, v) VALUES ('changed', 'value')");
        $this->game->exec("DELETE FROM available_islands");
        $this->game->exec("INSERT INTO available_islands (k, v) VALUES ('changed', 'value')");
        Occupation::play($this->occ['p1_hand_a']);
        $this->game->exec("UPDATE weapon SET location = 'discard' WHERE id = 1");
        $afterUnsnappedChanges = $this->game->occupations();
        $afterWeaponChange = $this->game->rows("SELECT * FROM weapon ORDER BY id");

        $this->game->restoreState(1);

        $this->assertSame([['k' => 'original', 'v' => 'value']], $this->game->rows("SELECT * FROM forge"));
        $this->assertSame([['k' => 'changed', 'v' => 'value']], $this->game->rows("SELECT * FROM available_islands"));
        $this->assertSame($afterUnsnappedChanges, $this->game->occupations());
        $this->assertSame($afterWeaponChange, $this->game->rows("SELECT * FROM weapon ORDER BY id"));
    }

    public function testSecondWeaponSaveDoesNotOverwriteTurnStartSnapshot(): void
    {
        $this->game->exec("INSERT INTO weapon (type, location, player_id) VALUES ('bow', 'hand', 1)");
        $before = $this->game->rows("SELECT * FROM weapon ORDER BY id");
        $this->startTurn(1);

        $this->game->saveWeapons();
        $this->game->exec("UPDATE weapon SET location = 'discard' WHERE id = 1");
        $this->game->saveWeapons();
        $this->game->exec("INSERT INTO weapon (type, location, player_id) VALUES ('sword', 'hand', 1)");

        $this->game->restoreState(1);

        $this->assertSame($before, $this->game->rows("SELECT * FROM weapon ORDER BY id"));
    }

    public function testRejectedUndoDoesNotModifyGameState(): void
    {
        $this->game->addMeeple(1, 'thing', 0);
        $this->startTurn(1);
        $this->game->saveOccupations(1);
        $this->game->exec("UPDATE player SET coin = 99 WHERE player_id = 1");
        $this->game->exec("INSERT INTO command_queue (command) VALUES ('pending')");
        $this->game->state['can_undo'] = 0;
        $playerBefore = $this->game->rows("SELECT * FROM player WHERE player_id = 1");
        $meeplesBefore = $this->game->rows("SELECT * FROM meeple ORDER BY id");
        $occupationsBefore = $this->game->occupations();
        $queueBefore = $this->game->rows("SELECT * FROM command_queue");

        try {
            $this->game->restoreState(1);
            $this->fail('Expected undo to be rejected.');
        } catch (BgaUserException $exception) {
            $this->assertSame('You cannot undo at this time', $exception->getMessage());
        }

        $this->assertSame($playerBefore, $this->game->rows("SELECT * FROM player WHERE player_id = 1"));
        $this->assertSame($meeplesBefore, $this->game->rows("SELECT * FROM meeple ORDER BY id"));
        $this->assertSame($occupationsBefore, $this->game->occupations());
        $this->assertSame($queueBefore, $this->game->rows("SELECT * FROM command_queue"));
        $this->assertSame([], $this->game->notifications);
        $this->assertSame(0, $this->game->state['just_restored']);
    }

    public function testSavingUndoStateIsDisabledWhenUndoIsUnsupported(): void
    {
        $before = $this->game->occupations();
        $this->game->state['undo_supported'] = 0;

        $this->game->saveState(1);
        $this->game->saveOccupations(1);
        $this->game->saveWeapons();

        $this->assertSame(0, $this->game->state['undo_occupation']);
        $this->assertSame(0, $this->game->state['undo_weapon']);
        $this->assertSame([], $this->game->rows("SELECT * FROM player_saved"));
        $this->assertSame([], $this->game->rows("SELECT * FROM occupation_saved"));
        $this->assertSame([], $this->game->rows("SELECT * FROM weapon_saved"));
        $this->assertSame($before, $this->game->occupations());
    }

    public function testCannotUndoWhenUndoIsUnsupported(): void
    {
        $this->game->state['undo_supported'] = 0;

        $this->expectException(BgaVisibleSystemException::class);
        $this->game->restoreState(1);
    }

    public function testWeaponsAreRestored(): void
    {
        $this->game->exec("INSERT INTO weapon (type, location, player_id) VALUES ('bow', 'hand', 1)");
        $before = $this->game->rows("SELECT * FROM weapon ORDER BY id");
        $this->startTurn(1);

        $this->game->saveWeapons();
        $this->game->exec("INSERT INTO weapon (type, location, player_id) VALUES ('sword', 'hand', 1)");
        $this->game->exec("UPDATE weapon SET location = 'discard' WHERE id = 1");

        $this->game->restoreState(1);

        $this->assertSame($before, $this->game->rows("SELECT * FROM weapon ORDER BY id"));
    }

    public function testRestoreSendsUndoNotificationAndMarksJustRestored(): void
    {
        $this->startTurn(1);
        $this->game->restoreState(1);

        $this->assertSame(1, $this->game->state['just_restored']);
        $this->assertSame('undoTurn', $this->game->notifications[0]['type']);
        $this->assertSame(1, $this->game->notifications[0]['args']['player_id']);
    }

    public function testCannotUndoWhenNotAllowed(): void
    {
        $this->startTurn(1);
        $this->game->state['can_undo'] = 0;

        $this->expectException(BgaUserException::class);
        $this->game->restoreState(1);
    }
}
