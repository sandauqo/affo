# Recheck findings

This file supplements `ARCHITECTURE_REVIEW.md` with additional issues that appear in the current implementation and are worth addressing before broad refactors.

## 1. Undo snapshot tables are effectively global within the game

Evidence: `modules/helper.undo.php` (`saveState`, `restoreState`)

- `saveState()` executes `DELETE FROM player_saved WHERE 1` and `DELETE FROM meeple_saved WHERE 1` before inserting the current player's snapshot.
- `restoreState()` then reads only the row for the active player and restores from there.
- The same pattern is used for other saved tables (`occupation_saved`, `string_globals_saved`, etc.).

Impact:

- Snapshot state is not scoped by an explicit game-turn or player context beyond a single row in each table. This is fine only if the current flow is perfectly serialized.
- A missing or stale row can cause silent data loss or a hard PHP error on restore.
- The design relies on a very narrow invariant: "at most one active save per table at any time." That is easy to violate through a refactor or a late-join/undo edge case.

Recommendation:

- Replace the single-row global saves with a per-game/per-player keyed snapshot structure or with an explicit active snapshot token.
- Add a defensive guard in `restoreState()` before dereferencing the saved row.

## 2. The command queue is an SQL injection sink

Evidence: `modules/class.action.php` (`Action::push`, `Action::unshift`, `Action::contains`)

- `push()` builds SQL like `INSERT INTO command_queue (command) VALUES ('{$c}')`.
- `unshift()` rebuilds the queue using raw command strings and then re-inserts them without escaping.
- `contains()` uses `WHERE command LIKE '$primary%'` in a SQL string.

Impact:

- Every caller that can influence a command string becomes a SQL injection vector.
- The queue is not a safe boundary; it accepts raw strings from game logic and turns them into executable SQL.
- This is especially risky because the same strings are later parsed positionally in `doCommand()`.

Recommendation:

- Stop interpolating queue payloads directly into SQL.
- Use prepared statements or encode the command as a typed value before writing to the queue.
- Treat queue entries as untrusted input and validate them before execution.

## 3. Command parsing is positional and untyped

Evidence: `modules/state-functions/doCommand.php`

- `doCommand()` stores `currentCommand` as a raw string and immediately splits it with `explode(':', $command)` and `explode('-', $command)`.
- Later branches read positional slots such as `$components[1]`, `$components[2]`, and nested values from `$parts`.
- The whitelist at the top of `doCommand()` must stay in sync with the transition table in `states.inc.php` by convention.

Impact:

- A malformed or unexpected command silently chooses a different branch or causes an undefined transition instead of failing early.
- The design pushes correctness onto human convention rather than a machine-checked contract.
- This is exactly the sort of drift that produces the brittle "undefined transition" failures described in the architecture review.

Recommendation:

- Introduce a typed command object or a command schema instead of raw strings.
- Validate command shape at queue insertion time and reject malformed payloads before they reach `doCommand()`.
- Replace the whitelist-by-convention with a central registry that the transition table and parser share.

## 4. Save/restore state relies on a hidden global invariant rather than explicit state ownership

Evidence: `modules/helper.undo.php` and `modules/state-functions/doCommand.php`

- The queue, player snapshot, and meeple snapshot all rely on a single active game-state context.
- `saveState()` deletes the entire saved tables and writes fresh rows, while `restoreState()` selects by player and then takes the game back to that snapshot.
- There is no explicit ownership check to confirm that the snapshot belongs to the same in-flight operation being restored.

Impact:

- Undo can become order-dependent and non-deterministic when multiple players or multi-step actions interact.
- Refactors that slightly reorder save/restore calls can corrupt the current turn while leaving the code looking "correct" at a glance.
- This is a reliability issue even if it is not always exploitable as an attack.

Recommendation:

- Save snapshots with a unique operation ID or turn token and restore only the matching snapshot.
- Reject any restore whose token does not match the active game context.

## Bottom line

The project's core design is still defensible, but the saved-state and queue layers create a large amount of hidden coupling. The largest risks are not in the board rules themselves; they are in the code that serializes state and interprets command strings. These are exactly the places where a small refactor can cause a large, hard-to-debug regression.
