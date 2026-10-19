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

        $this->expectException(Exception::class);
        $this->game->restoreState(1);
    }
}
