# Undo flow

"Undo turn" lets the active player roll back everything done since their turn started (state `pickAction`, id 50), as long as nothing random/irreversible happened. Code lives mainly in `modules/helper.undo.php`, with entry points in `modules/player-actions/undo.php`, `modules/helper.state-functions.php` and `states.inc.php`.

## 1. Storage model

### What is a "shadow table"?

A shadow table is a backup copy of a live game table, with the same columns, named `<table>_saved`. It is not used during normal play. Its only job is to hold the state as it was at the start of the turn (or just before the first change to that data), so `restoreState()` can copy it back over the live table when the player clicks "Undo turn".

Each shadow table is a **single, global** copy: it is wiped (`DELETE ... WHERE 1`) and refilled on each save. It is not per player or per turn, except where noted below.

All eight shadow tables are defined in `dbmodel.sql`:

| Live data | Shadow table | Saved by | Guard flag (game state value) | Scope of copy |
|---|---|---|---|---|
| `player` row (score, coin, wood, stone, ore, penalty, vp, goods, boards, ships, emigrations) | `player_saved` | `saveState` | none (always saved) | 1 row, active player |
| `meeple` | `meeple_saved` | `saveState` | none | active player's rows only |
| `occupation` | `occupation_saved` | `saveOccupations` | `undo_occupation` | all players and the deck |
| `weapon` | `weapon_saved` | `saveWeapons` | `undo_weapon` | all players |
| `forge` | `forge_saved` | `saveForge` | `undo_forge` | all |
| `string_globals` | `string_globals_saved` | `saveStringGlobals` | `undo_string_globals` | all |
| `available_islands` | `available_islands_saved` | `saveIslands` | `undo_islands` | all |
| `mountain_strips` | `mountain_strips_saved` | `saveMountainStrips` | `undo_mountain_strips` | all |

Not stored in a table: `num_sheds`, `num_stonehouses`, `num_longhouses`, `num_vp_token_4` and `num_vp_token_3` are copied into the game state values `num_sheds_saved`, `num_stonehouses_saved`, `num_longhouses_saved`, `num_vp_token_4_saved` and `num_vp_token_3_saved`.

Only `weapon_saved`, `forge_saved`, `string_globals_saved`, `available_islands_saved`, `mountain_strips_saved` and `occupation_saved` hold whole tables. `player_saved` and `meeple_saved` are always written for the active player and restored with `WHERE player_id = ...`. Restoring a whole-table shadow overwrites other players' changes if undo ever runs in a multi-active state (it can't today).

Other relevant game state values:
- `undo_supported` (init 1): if 0 (games started before the feature), all save/restore is skipped or throws.
- `can_undo`: whether the Undo button is currently allowed.
- `just_restored`: set after a restore so the next `stPickAction` does not take a fresh snapshot.

## 2. When state is saved

### 2.1 Full snapshot, `saveState($player_id)`
Called from `stPickAction` (`helper.state-functions.php`, near line 403) when the active player has a meeple to place:

1. `setCanUndo(1)`
2. If `just_restored` is set, clear it and **skip** snapshotting (the shadows still hold the turn-start state).
3. Otherwise `saveState($player_id)` and `giveExtraTime()`.

`saveState` replaces `player_saved` (1 row) and `meeple_saved` (this player's meeples). It also copies the building and VP-token globals into `*_saved`.

If the player has no free meeple (or has meeples in the 5th column, Norwegians), `stPickAction` auto-passes. `setCanUndo(1)` is not called and nothing is saved.

### 2.2 Lazy, one-shot saves
The other tables are saved lazily, just before the first mutation in the turn. Each `saveX()` returns immediately if its guard flag is already 1. Otherwise it sets the flag to 1, wipes the shadow table and copies the live table in.

| Function | Typical callers |
|---|---|
| `saveOccupations($player_id)` | `playOccupation`, `discardOccupation`, `useOccupation` (l.382), `Occupation::checkAsSoonAsOccs` (178/180/181), `chooseBestOfTwoStartingOcc`, `stDrawTwoBrown`, `class.player.php` (l.383) |
| `saveWeapons()` | `doCommand` (draw weapon of a known type, l.389, 801), `affirm`, `selectWeapons`, `declareSuccess`, `declareFailure`, `selectAttackReward`, `useOccupation` |
| `saveForge()` | `selectMode`, `selectSpecialTile`, `selectAttackReward`, `affirm`, `useOccupation`, `doCommand` |
| `saveStringGlobals()` | `buildAction`, `doCommand` (l.975) |
| `saveIslands()` | `selectIsland`, `giveisland` (debug) |
| `saveMountainStrips()` | `selectStrip` |

**Guard flag reset:** `stPickAction` (about lines 383-388 of `helper.state-functions.php`) sets `undo_occupation`, `undo_weapon`, `undo_forge`, `undo_string_globals`, `undo_islands` and `undo_mountain_strips` back to 0. This happens on every entry to `pickAction`, including the re-entry after an undo. A new turn or a restore therefore starts with "nothing saved in this turn" for the lazy tables.

Note that `undo_occupation` is initialised to 0 in `setupNewGame` (`feastforodin.game.php`, line 785) like the other flags.

## 3. When undo is available

`setCanUndo($n)` (`feastforodin.game.php` l.1067) stores `can_undo` and sends the `updateUndo` notification to all players.

- Set to 1 in `stPickAction`.
- Set to 0 ("irreversible") when randomness or hidden information is revealed:
  - rolling a die (`doCommand` ~l.844)
  - drawing an occupation (`doCommand` l.342)
  - drawing a random weapon (`doCommand` l.270, `affirm` l.523, `selectMode` l.608)
  - `setplayer-...-nopass`
  - `doCommand` l.387
  - `helper.state-functions.php` l.462 (`stDetermineStartPlayer`)
- If `can_undo` is 0 when the player reaches `confirmTurn` (state 59), `stConfirmTurn` auto-affirms, so the turn ends immediately.

Client: `ffo.states.js` (~l.1434) adds the gray "Undo turn" button only if the current state lists `undo` in `possibleactions` **and** `gamedatas.can_undo` is truthy. `ffo.notifications.js` l.864 keeps `gamedatas.can_undo` updated from `updateUndo`.

States that allow `undo` (`possibleactions` and a `"undo" => 50` transition) include `chooseVikingsNumber` (52), `confirmTurn` (59) and nearly every `command-*` state (1xxx) in `states.inc.php`. Not included: `pickAction` itself, `doCommand` and `nextPlayer`.

## 4. The undo action

Client click -> `onUndo` (`ffo.action.js`) -> ajax `/feastforodin/feastforodin/undo.html` -> `actUndo()` (`helper.player-actions.php`) -> `undo()` (`player-actions/undo.php`):

1. `checkAction('undo')`.
2. `$player_id = getActivePlayerId()`.
3. `restoreState($player_id)`.
4. `gamestate->nextState('undo')`, which goes to state 50 `pickAction`.

### 4.1 `restoreState($player_id)`, step by step

1. Throws if `undo_supported` is 0 or `can_undo` is 0.
2. **Occupations**, only if `undo_occupation` = 1:
   - `DELETE FROM occupation WHERE 1`
   - `INSERT INTO occupation SELECT * FROM occupation_saved` (all players, the deck and the original ids).
3. Weapons, forge, string globals, mountain strips, islands: if the flag is 1, `DELETE ... WHERE 1`, then copy back everything from the shadow. For string globals, `undoComplexOcc(70)` and `undoComplexOcc(71)` run first. They put a moved special tile back on the source player's board and send an `updateBoards` notification.
4. Meeples: `DELETE FROM meeple WHERE player_id = <player>`, then insert everything from `meeple_saved`.
5. `DELETE FROM command_queue WHERE 1`.
6. `UPDATE player` from the `player_saved` row. This includes score and aux score, resources, penalty, vp, goods, boards, ships and emigrations.
7. Rebuild the player object: `getFullPlayer` plus weapons, `occupationsHand`, `occupationsPlayed`, meeples and the score breakdown.
8. Restore the building and VP-token globals from `*_saved`.
9. `notifyAllPlayers("undoTurn", ...)` with the full player object, islands, forge, mountain strips and building counts.
10. `just_restored = 1`.

Then `pickAction` is re-entered. It resets the lazy guard flags (see 2.2), calls `setCanUndo(1)` and skips taking a new snapshot because `just_restored` is set.

### 4.2 Client handling of `undoTurn` (`ffo.notifications.js`, `handleUndoTurn`)

1. Replaces `playersById[playerId].playerObj` with the payload.
2. For a player who is **not** the local player, deletes `ability` from every card in `occupationsHand`, so hidden cards stay hidden.
3. Applies the island, forge, strip and building-supply updates.
4. Calls `handlePlayOccupation` with the same notification. It has no `occupation` or `vp` args, so it only calls `updateOccupationsDisplay()`.
5. Redraws resources, weapons, occupations, meeples, goods, ships, boards, emigrations and artisan sheds, then the action-used markers.

## 5. Known weaknesses

### Fixed
Occupation undo used to snapshot only one player's rows (the player passed to `saveOccupations()`) and restore without a `player_id` filter. If the snapshot belonged to another player, the undoing player's cards were deleted and the other player's cards were inserted a second time ("own cards doubled, opponent's disappeared"). Restore also dropped `id`, and cards drawn during the turn were never returned to the deck. `occupation_saved` now holds the **whole `occupation` table** and restore is a full `DELETE` plus `INSERT ... SELECT *`, so ids are kept and players can't be mixed up. Covered by `tests/UndoTest.php`.

### Still open
1. Undo is only reachable from `activeplayer` states (no `undo` in the `multipleactiveplayer` states), so the `saveOccupations` calls in `stDrawTwoBrown` and `chooseBestOfTwoStartingOcc` are never followed by an undo. They are harmless but pointless.
2. `weapon`, `forge`, `string_globals`, islands and strips are restored globally, so any other player's change to those tables since the snapshot would be lost. This is fine in single-active turns.
3. The client `drawOccupation` and `playOccupation` handlers push without checking for an existing id.
