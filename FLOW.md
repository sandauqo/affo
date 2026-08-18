# A Feast for Odin — Code Flow

A [Board Game Arena](https://boardgamearena.com) (BGA Studio) implementation of Uwe Rosenberg's
*A Feast for Odin*, including the **Norwegians** expansion and three mini-expansions.

Game metadata lives in `gameinfos.inc.php` (1–4 players, solo supported, `is_beta = 1`).

**Companion documents**

| File | Contents |
|---|---|
| [`ACTION_FLOW.md`](ACTION_FLOW.md) | The action engine in depth: `doCommand`, the full command reference, worked traces, verified dead code, invariants for changing it |
| [`ARCHITECTURE_REVIEW.md`](ARCHITECTURE_REVIEW.md) | Assessment: strengths, problems, an explicit log of what could not be verified, and prioritised recommendations |

---

## 1. Runtime architecture

BGA games are split across a PHP server and a JS client that never trust each other:

```
   Browser (dojo/AMD)                        Server (PHP)
   ─────────────────────                     ──────────────────────────
   feastforodin.js          ── ajaxcall ──▶  feastforodin.action.php     (HTTP entry / arg parsing)
     modules/ffo.action.js                     │
                                               ▼
                                             FeastForOdin (feastforodin.game.php)
                                               │  traits: PlayerActions, StateFunctions,
                                               │          StateArguments, Undo
                                               ▼
                                             $this->gamestate  (states.inc.php)
                                               │
   modules/ffo.states.js    ◀── state args ───┤
   modules/ffo.notifications.js ◀── notifs ───┘
```

Three distinct channels flow back to the client:

| Channel | Produced by | Consumed by |
|---|---|---|
| Full game snapshot | `getAllDatas()` (`feastforodin.game.php:879`) | `ffo.setup.js` on load/F5 |
| State entry + `args` | `states.inc.php` `args` → `helper.state-arguments.php` | `ffo.states.js` `onEnteringState` |
| Incremental deltas | `notifyAllPlayers()` / `notifyPlayer()` | `ffo.notifications.js` subscriptions |

---

## 2. File map

### Server

| File | Role |
|---|---|
| `feastforodin.game.php` | Main `Table` subclass. Globals registry, setup, `getAllDatas`, scoring, `spend`/`gain`, zombie handling |
| `states.inc.php` | The state machine (`$machinestates`) — ~130 states |
| `feastforodin.action.php` | AJAX entry points; validates raw args, delegates to `actXxx()` |
| `feastforodin.view.php` / `*.tpl` | Static page shell (nearly empty — the DOM is built in JS) |
| `dbmodel.sql` | Schema: `player` extensions plus `occupation`, `weapon`, `meeple`, `command_queue`, `forge`, `mountain_strips`, `available_islands`, `string_globals`, `asset_diffs`, and a `*_saved` mirror of each for undo |
| `modules/helper.state-functions.php` | `st*` functions — everything the machine does autonomously |
| `modules/state-functions/doCommand.php` | **The command-queue interpreter** (see §4) |
| `modules/helper.state-arguments.php` | `arg*` functions — data handed to the client per state |
| `modules/helper.player-actions.php` | `PlayerActions` trait; `include`s + `use`s one trait per player action from `modules/player-actions/` |
| `modules/helper.undo.php` | Snapshot / restore |
| `modules/class.*.php` | Domain objects: `Action`, `FfoPlayer`, `Occupation`, `Weapon`, `Forge`, `Island`, `MountainStrip`, `Requirements` |
| `modules/data/*.php` | Static data tables returned as plain arrays: `boards.php` (87 KB), `goods.php`, `actions.php`, `artisan_sheds.php`, `personal_resource_markers.php` |

### Client

| File | Role |
|---|---|
| `feastforodin.js` | `declare("bgagame.feastforodin")`; binds methods pulled in from the `ffo.*` modules |
| `modules/ffo.setup.js` | Builds the whole board DOM from `gamedatas`; `onResize` scaling logic |
| `modules/ffo.states.js` | `onEnteringState` / `onLeavingState` / `onUpdateActionButtons`, keyed by state name |
| `modules/ffo.action.js` | One handler per user gesture → `ajaxcall` |
| `modules/ffo.notifications.js` | ~30 `dojo.subscribe` handlers + animations |
| `modules/ffo.dragging.js`, `GoodsSource.js`, `GoodsTarget.js`, `GoodMatrix.js` | Drag-and-drop of polyomino goods onto boards, with matrix-based fit/legality checks |
| `modules/Player.js` | Per-player view model and DOM ownership |
| `modules/ffo.i18n.js` | Occupation/board text (70 KB) |

---

## 3. Game flow: the round loop

State IDs from `states.inc.php`. Square boxes are `game` (automatic) states; **bold** are player-facing.

```
 1 gameSetup
   ↓
 2 preGame ──────────┬─ 3  **bestOfTwoStartingOcc** (multiactive) ─┐
   (stPreGame)       │      └──────────────────────────────────────┘ back to 2
                     ├─ 8..16 harvest/Norwegians setup:
                     │    8 **drawPersonalResource** → 9 → (8 | 11 | 10)
                     │   11 **drawPersonalResource2** → 12 → (11 | 13 | 15)
                     │   13 **buildArtisanShed** → 14 → (13 | 10)
                     │   15 drawTwoBrown → 16 **discardOneBrown** → 10
                     └─ 10 newViking
   ┌──────────────────────────────────────────────────────────────────┐
   │ 10 newViking          each player moves a Feast meeple → Thing   │
   │   ↓                                                             │
   │ 20 harvestGoods       round-dependent crop handout               │
   │   ↓ (via 51)                                                    │
   │ 30 turnExplorationBoards   flip island board A/B/C/D by round    │
   │   ↓                                                             │
   │ 40 drawWeapon         every player draws 1 weapon card           │
   │   ↓ (via 51)                                                    │
   │ 49 setFirstPlayer                                                │
   │   ↓                                                             │
   │ 50 **pickAction** ⇄ 51 doCommand ⇄ 1001-1099 command states      │  ← main
   │   │   ↓ finished                                                 │    action
   │   │  59 **confirmTurn**                                          │    phase
   │   ↓ pass / affirm                                                │
   │ 60 nextPlayer ──── nextPlayer ──▶ back to 50                     │
   │   ↓ nextPhase (all passed)                                       │
   │ 70 determineStartPlayer   last placer becomes next first player   │
   │   ↓                                                             │
   │ 80/81 **income-placegoods** (multiactive) → 83 income            │
   │   ↓                                                             │
   │ 90 breeding                                                      │
   │   ↓ (via 51)                                                    │
   │ 100/101 **feast-placegoods** (multiactive) → 102 feast           │
   │   ↓ (via 51)                                                    │
   │ 110/111 **bonus-placegoods** (multiactive) → 112 bonus           │
   │   ↓ (via 51)                                                    │
   │ 120 updateMountainStrips → 130 removePlacedVikings → 140 nextRound
   └────────────────────────────────── loops to 10 ───────────────────┘

 last round (6 short / 7 long, decided in stFeast):
   150/151 **gameEnd-placegoods** → 152 gameEnd-scoring → 99 gameEnd
```

The `*-prepare` states (80, 100, 110, 150) all share `stSetAllMultiactive`, which flips every player
active simultaneously and grants extra clock time.

---

## 4. The command queue — the central mechanism

This is the single most important thing to understand about the codebase. **Rules are not encoded as
states; they are encoded as strings in a database queue**, and state `51 doCommand` is a trampoline
that executes them one at a time.

> A full deep-dive on this — complete command reference, the hunt/attack sub-machine, worked traces,
> verified dead code, and rules for modifying it safely — is in **[`ACTION_FLOW.md`](ACTION_FLOW.md)**.

### The queue

`command_queue` (`dbmodel.sql:108`) is a FIFO of `VARCHAR(255)` commands. `Action`
(`modules/class.action.php:624`) provides:

- `Action::push([...])` — append to the back
- `Action::unshift([...])` — prepend to the front (used when an effect must resolve *before* what's pending)
- `Action::pop()` — take the lowest `id`
- `Action::contains($prefix)`, `Action::dump()`

### Command grammar

```
primary-arg1-arg2...[:source]
```

`source` defaults to `action-{action_id}` and is used purely for log grouping and animation origin.
Examples: `take-coin-3:occupation-23`, `mountain-2`, `upgrade-3-1-true`, `exchange-wood-stockfish`,
`hunt-pillage`, `build-stonehouse-longship`, `state-flaxbaker`, `setplayer-12345`.

### Dispatch (`modules/state-functions/doCommand.php`)

Each entry into state 51 pops **one** command and branches:

1. **Queue empty** → flush pending asset diffs, update score, then jump to the state named by the
   `finishedTransition` string global. That global is the queue's *return address*: `stHarvestGoods`
   sets `finished_harvest`, `stDrawWeapon` sets `finished_drawweapon`, `stNextPlayer` sets
   `finished_postactions`, `stBreeding` sets `finished_breeding`, `stFeast` sets `finished_feast` or
   `finished_feastGameEnd`, `stBonus` sets `finished_bonus`, and `stPickAction` resets it to
   `finished`. This is why every phase routes *through* 51 — it guarantees occupation-triggered
   commands resolve before the phase advances.
2. **Mechanical command** → mutate state inline, then `nextState('next')` — straight back to 51.
   These include `take`, `takeif` (conditional), `takeper` (per-unit), `tpp` (take-per-player),
   `collect` (milk/wool), `build`, `draw`, `setaction`, `mountainreset`, `linkedactions`, `flush`,
   `setplayer`, and `occupation-<id>` (a large `if/else` ladder of per-card effects).
3. **Interactive command** → `nextState('command-' . $primary)`, routing to a `10xx` state where the
   player chooses something. Every one of those states transitions back to 51 when done.
   `state-<x>` is a generic escape hatch that jumps to `command-<x>` directly.

Because commands can push more commands while executing, effects compose without any state-machine
changes. A single action space can cascade into a dozen queued sub-decisions.

### How an action becomes commands

`Action::getAction($actionId)` (`class.action.php:198`) is a `switch` returning an **array of modes** —
mutually exclusive alternatives for one action space. Each is built by `makeBaseAction`, which derives
`preCommands` from the meeple count:

| Meeples | preCommands |
|---|---|
| 3 | `linkedactions`, `beforeorafter-draw-occupation` |
| 4 (or Norwegians 2-viking last column) | `beforeorafter-play-occupation`, `linkedactions` |
| otherwise | `linkedactions` |

`Action::getModifiedAction($actionId, $playerId)` then layers occupation effects on top: discounts
(Peddler, Patron, Master Bricklayer), swapping `draw-occupation` for `state-preceptor` (occ 99), and
adding whole extra modes (Knarr Builder occ 105, Dorestad Traveller occ 185).

Action IDs encode geometry: `Action::getCol()` / `getMeepleCount()` return `((id - 1) % 4) + 1`, so
the column *is* the meeple cost. Norwegians fifth-column actions use IDs `555x` and are special-cased.
`modules/data/actions.php` is the metadata table (`column`, `row`, `game`, `players`, `desc`) driving
`Action::filterActions($filter, $param)`, used for row/column queries like "all crafting actions".

---

## 5. One player action, end to end

Taking an action space:

```
1. ffo.action.js onAction()
     └─ ajaxcall("/feastforodin/feastforodin/action.html", { actionId })

2. feastforodin.action.php::action()
     └─ getArg("actionId", AT_posint) → $this->game->actAction($actionId)

3. helper.player-actions.php::actAction → player-actions/action.php::action()
     ├─ checkAction('action')                  // BGA verifies state + active player
     ├─ 5th-column? → nextState('vikingsNumber')   → state 52
     └─ else → buildAction($state)

4. player-actions/buildAction.php::buildAction()
     ├─ bump statistics; record placed_player_id (drives next round's first player)
     ├─ FfoPlayer::isActionAvailable()          // space unoccupied?
     ├─ enough meeples in 'thing'?
     ├─ FfoPlayer::moveMeepleToAction() + notifyAllPlayers("meepleMove")
     ├─ Action::push(preCommands + ['pickmodal'])
     └─ nextState('action')                     → state 51

5. state 51 pops 'linkedactions' → inline occupation hooks → back to 51
   state 51 pops 'pickmodal'    → state 1002 (or auto-resolved by stPickModal
                                  if there is only one mode)

6. stPickModal → Requirements::check($action_id, $player)
     └─ single mode? actSelectMode(0) immediately

7. selectMode.php pushes the chosen mode's commands, returns to 51,
   which drains them, then falls through finishedTransition = 'finished'
     → state 59 confirmTurn → affirm → state 60 nextPlayer
```

`Requirements::check` (`class.requirements.php`) validates costs, prerequisites, supply limits,
emigration room and the awkward double-actions (124, 128, 5557) *before* anything irreversible happens
— specifically so undo stays available.

---

## 6. Undo

Undo is a coarse snapshot-and-restore, scoped to one player's turn (`modules/helper.undo.php`).

- `stPickAction` calls `saveState($player_id)` on every turn start: the player row, their meeples and
  the building-supply counters are copied into `player_saved` / `meeple_saved` / globals.
- Lazier mirrors are taken only on first touch, each guarded by an `undo_*` flag global:
  `saveOccupations`, `saveWeapons`, `saveForge`, `saveStringGlobals`, `saveIslands`,
  `saveMountainStrips`.
- `setCanUndo(0)` is called the moment randomness or hidden information is consumed — `stHuntRoll`
  after a die roll, `draw-occupation`, random weapon draws. The client's undo button follows the
  `updateUndo` notification.
- `restoreState()` copies the mirrors back, clears `command_queue`, emits `undoTurn` with a fresh
  player payload, and sets `just_restored` so `stPickAction` doesn't immediately re-snapshot.
- Occupations 70/71 (Courier/Innkeeper) move a tile off *another* player's board, so they get bespoke
  reversal via `undoComplexOcc()` using a `string_globals` breadcrumb.

Almost every interactive `10xx` state carries `"undo" => 50`, so undo always lands back on
`pickAction`.

---

## 7. Occupations

~190 occupation cards, and they are the main source of complexity. There is no card-effect class
hierarchy; effects are scattered by *when they fire*:

| Timing | Where |
|---|---|
| Cost / mode modification | `Action::getModifiedAction()` |
| On play, immediate | `Occupation::getImmediateCommands()`, `getNextState()` |
| During command execution | the `occupation-<id>` branch of `doCommand()` |
| Phase hooks | `stHarvestGoods`, `stPreIncome`, `stIncome`, `stBreeding`, `stPreFeast`, `stFeast`, `stBonus`, `stNextPlayer`, `stUpdateMountainStrips` |
| Auto-skip when unusable | `stCheckSkip()` — one `else if` per `command-*` state |
| End-game scoring | `pass.php` (`gameEnd-placegoods`), `updatePlayerScore()` |

The universal predicate is `Occupation::has($player_id, $ability_id)`, and the universal action is
`Action::push([...])` / `Action::unshift([...])`. `stCheckSkip` is worth knowing about: it is a large
dispatcher that auto-passes an optional occupation state when the player can't afford or can't use it,
keeping the UI from prompting for impossible choices.

---

## 8. Client-side flow

### Composition

`feastforodin.js` is thin. Its constructor imports functions from the `ffo.*` modules and binds them
onto the game object:

```js
this.setup               = setup.setup.bind(this);
this.onEnteringState     = states.onEnteringState.bind(this);
this.onUpdateActionButtons = states.onUpdateActionButtons.bind(this);
this.setupNotifications  = notifications.setupNotifications.bind(this);
this.onAction            = action.onAction.bind(this);   // ...~40 more
```

### Lifecycle

1. **`setup(gamedatas)`** — builds the action board (base or one of the six Norwegians variants),
   a `Player` object per player, and every `GoodsSource` / `GoodsTarget` pair.
2. **`onEnteringState(name, args)`** — a `switch` over state names (`ffo.states.js:44+`) that reveals
   modals, highlights legal targets, and configures the goods selectors.
3. **`onUpdateActionButtons`** — a second, much longer `switch` (~line 733+) adding the per-state
   buttons and their costs.
4. **`setupNotifications`** — ~30 `dojo.subscribe` handlers: `takeGoods`, `spendGoods`,
   `diffResources`, `meepleMove`, `updateBoards`, `updateScore`, `updateUndo`, `undoTurn`,
   `drawOccupation`, `customHarvest`, …

### Goods placement (the tetris part)

Goods are polyominoes placed on grid boards. This is the client's most intricate subsystem:

- `GoodMatrix` converts a good's `cells`/`rotation`/`flipped` into a 0/1 matrix and walks its outline.
- `GoodsSource` starts a drag on pointer-down and clones a full-size ghost element.
- `GoodsTarget` owns a board's drop area, snaps to the grid, and validates against board voids and
  already-placed goods.
- Placements are **provisional** until committed. `hasUnsavedChanges()` scans targets for provisional
  goods; `updatePlacementButtons()` swaps "Pass" for "Save and done"; `onSaveBoards` posts the whole
  layout to `saveBoards.html`, where `FfoPlayer::checkProvisionals()` re-validates server-side and
  recomputes each board's cached `_income`, `_negatives`, `_penalties` and `_bonus`.

Never trust the client here — `saveBoards.php` re-derives everything.

---

## 9. Persistence model

**Hybrid relational + JSON.** The `player` row carries scalar resources (`coin`, `wood`, `stone`,
`ore`, `penalty`, `vp`) plus four JSON blobs: `goods`, `boards`, `ships`, `emigrations` (and a
free-form `extra`).

`FfoPlayer::getFullPlayer($id)` / `saveFullPlayer($player)` decode/encode that envelope, and are
the *only* sanctioned way to touch player state. The common shape is:

```php
$player = FfoPlayer::getFullPlayer($player_id);
$this->spend($player, "coin", 2, "occupation-42");
$this->gain($player, "silk", 1, "occupation-42");
FfoPlayer::saveFullPlayer($player);
```

`spend`/`gain` (`feastforodin.game.php:271`, `:328`) both emit a notification *and* append a row to
`asset_diffs`. `flushAssetDiffs()` later groups those rows by `(player_id, source)` and emits one
readable log line — `"X exchanges: ⬥⬥ for ◆"` — instead of a dozen separate messages.

### Two kinds of global

| Kind | Storage | Access |
|---|---|---|
| Integers | BGA globals, declared in `initGameStateLabels` (`feastforodin.game.php:35`) | `getGameStateValue` / `setGameStateValue` |
| Strings | `string_globals` table | `$this->setString(k, v)` / `getString(k)` |

Notable string globals: `currentCommand` (the command being executed — `arg*` functions parse it to
build their payloads), `finishedTransition` (queue return address), `takenStrips`, `custom_harvest`,
`allowed_upgrade_types`, `twoVikingsPlaced{playerId}`, `preGameOccupation{playerId}`, `occ:70`/`occ:71`.

---

## 10. Configuration

`gameoptions.json` maps table options onto globals `100`–`113`, read through predicates on the main
class: `isShortGame()`, `isSoloGame()`, `isNorwegiansExpansion()`, `isNorwegiansAllIslands()`,
`hasMiniExpansion1()`, `hasMiniExpansion2()` (Harvest), `isYuleExpansion()`,
`isBestOfTwoStartingOcc()`, `isConfirmTimer()`, `hasOccupationDeck('a'|'b'|'c')`.

Expansions change flow in several places at once — action board layout and IDs, setup states 8–16,
breeding animals (horses/pigs), harvest schedule, available islands, the minor forge. When tracing a
bug, establish which options are on first.

`gamepreferences.json` holds per-player UI preferences; `stats.json` declares the tracked statistics.

---

## 11. Conventions and gotchas

- **`setGameStateValue` in a `game` state is not transactional.** Because `doCommand` re-enters
  state 51 constantly, per-action flags must be reset explicitly — `stPickAction` clears ~20 of them
  at the start of each turn.
- **`$this->actPass(false)`** is the idiom for an auto-skip from an `st*` function; the `false`
  bypasses `checkAction`, since there's no real player request.
- **Two "current player" notions.** `getActivePlayerId()` is the machine's active player;
  `getCurrentPlayerId()` is whoever sent the HTTP request. Multiactive states must use the latter.
  `getAllDatas()` also filters hidden information by `getCurrentPlayerId()` (other players'
  occupation `ability` fields are stripped).
- **`setplayer-<id>` commands** temporarily retarget the active player so out-of-turn occupation
  effects can resolve during automatic phases.
- **SQL is built by string interpolation throughout.** Values reaching it are constrained by
  `feastforodin.action.php`'s `AT_posint` / `AT_alphanum` argument types plus explicit regex/whitelist
  checks (e.g. `selectGoods`, `equipBoard`); keep that discipline when adding entry points.
- **Solo mode** uses two meeple colours to track which round a viking was placed in; see
  `stRemovePlacedVikings` and the Inspector check in `buildAction.php`.
- **`_ide_helper.php` and `bga-framework.d.ts`** are stubs for editor autocomplete only — not shipped
  logic.
- Several `getAllDatas()` blocks are marked `//DEBUG:` and patch older saved games in-flight
  (e.g. backfilling `standalone` on board definitions). Leave them until legacy tables have drained.

---

## 12. Where to start for a given task

| Task | Entry point |
|---|---|
| Add/change an action space | `class.action.php::getAction()` + `data/actions.php` |
| Implement an occupation | `Occupation::getImmediateCommands`/`getNextState`, then the relevant `st*` hook or `doCommand` branch |
| Add a player decision point | new state in `states.inc.php` → `arg*` in `helper.state-arguments.php` → `possibleactions` → new `player-actions/*.php` trait → `feastforodin.action.php` endpoint → `ffo.states.js` + `ffo.action.js` |
| Change round structure | `states.inc.php` plus the `finishedTransition` writers in `helper.state-functions.php` |
| Fix a board/goods layout bug | `data/boards.php`, `GoodsTarget.js`, `GoodMatrix.js`, `FfoPlayer::checkProvisionals` |
| Fix scoring | `FeastForOdin::updatePlayerScore()` |
