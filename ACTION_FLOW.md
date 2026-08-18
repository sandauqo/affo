# The Main Action Flow — `doCommand` in Depth

Companion to [`FLOW.md`](FLOW.md), which covers the project as a whole. This file documents **only**
what happens between a player clicking an action space and their turn ending — the part of the
codebase that carries almost all of the game's rules.

Everything below was verified against the source. File:line references are given so you can jump.

---

## 1. The mental model

The single thing to internalise:

> **An action is not code. An action is a list of strings pushed onto a database queue. State 51
> (`doCommand`) is a trampoline that pops one string per visit and either executes it or bounces to a
> state where the player answers a question — then comes back for the next string.**

```
                    ┌──────────────────────────────────────────┐
                    │           command_queue (DB)             │
                    │  ["linkedactions", "pickmodal", ... ]    │
                    └──────────────────────────────────────────┘
                                     ▲   │ pop()
                        push/unshift │   ▼
   state 50            ┌─────────────────────────────┐
   pickAction ────────▶│   state 51  doCommand       │◀─────────────┐
   (place meeples,     │   the trampoline            │              │
    push commands)     └─────────────────────────────┘              │
                          │            │           │               │
             mechanical   │            │ inter-    │ queue empty   │
             (executes    │            │ active    │               │
              inline)     │            ▼           ▼               │
                          │      states 1001-1099   finishedTransition
                          │      "ask the player"   → next phase / state 59
                          └────────────▶ 'next' ───┘               │
                                         │                          │
                                         └──── 'done' ──────────────┘
```

Every interactive state's `done` / `pass` transition points back to 51. That is the whole contract.
Because a command may push *more* commands while executing, arbitrarily deep effect chains compose
without touching the state machine.

---

## 2. Vocabulary

| Term | Meaning |
|---|---|
| **Action space** | A numbered slot on the board. ID encodes its column: `((id - 1) % 4) + 1` = column = meeple cost (`class.action.php:184`). Norwegians fifth column uses IDs `555x`. |
| **Mode** | One of several mutually exclusive readings of the same space ("longhouse + knarr" *or* "stone house + longship"). `Action::getAction()` returns an **array**; the array index *is* the `mode_id`. |
| **Command** | A `VARCHAR(255)` string like `take-coin-3:occupation-23`. The unit of execution. |
| **Queue** | Table `command_queue`; FIFO by auto-increment `id`. |
| **Trampoline** | State 51. Pops and dispatches one command, then re-enters itself. |
| **Return address** | The `finishedTransition` string global — where to go when the queue drains. |
| **preCommands** | Commands that run *before* the mode is chosen (occupation draw/play timing, linked actions). |

---

## 3. Anatomy of an action definition

`class.action.php` holds three layers:

### 3.1 The raw definition — `Action::getAction($actionId)` (`:198`)

A ~420-line `switch`. Each case returns an array of `Action` objects built by `makeBaseAction`:

```php
case 4:
    return [
        self::makeBaseAction($n, ["build-stonehouse-longship"], ["wood" => 2, "stone" => 2]),
        self::makeBaseAction($n, ["build-longhouse-knarr"],     ["wood" => 2, "stone" => 2]),
    ];
```

An `Action` object is a plain record: `board`, `numberMeeples`, `preCommands`, `commands`, `cost`,
`prerequisite`.

`cost` is **spent**; `prerequisite` must merely be **owned** (e.g. `["longship" => 1]` to raid).

### 3.2 preCommands are derived from meeple count — `makeBaseAction` (`:35`)

| Meeples | preCommands | Why |
|---|---|---|
| 3 | `linkedactions`, `beforeorafter-draw-occupation` | 3-meeple spaces draw an occupation |
| 4 (or Norwegians 2-viking last column) | `beforeorafter-play-occupation`, `linkedactions` | 4-meeple spaces play one |
| anything else | `linkedactions` | |

Note the deliberate ordering comment at `:44`: linked actions run *before* the optional draw so the
player can still undo while requirements are being checked.

### 3.3 Occupation rewriting — `Action::getModifiedAction($actionId, $playerId)` (`:63`)

Called everywhere instead of `getAction`. It validates the action belongs to the active game variant,
then mutates the definitions:

- strips preCommands for clone actions (73–76)
- swaps `draw-occupation` → `state-preceptor` (occ 99)
- applies coin/stone discounts (occ 1 Peddler, 2 Patron, 116 Master Bricklayer)
- **appends whole extra modes** (occ 105 Knarr Builder on action 5, occ 185 Dorestad Traveller on 57)
- collapses modes when an artisan shed is already built (actions 77, 5550)

---

## 4. Stage 1 — click to queue

```
ffo.action.js onAction()
  └─ ajaxcall "action.html" { actionId }
       └─ feastforodin.action.php::action()          arg typed AT_posint
            └─ actAction() → player-actions/action.php::action()
```

`action.php` (`modules/player-actions/action.php:17`) is a small router:

1. `checkAction('action')` — BGA verifies the state allows it and the caller is the active player.
2. Norwegians fifth column with >1 viking available → `nextState('vikingsNumber')` (state 52) to ask
   how many vikings to commit; with exactly 1 it auto-picks and continues.
3. Otherwise → `buildAction($state)`.

`buildAction` (`modules/player-actions/buildAction.php:4`) is where the turn is actually committed. In
the `pickAction` / `chooseVikingsNumber` case it:

1. bumps statistics and sets `placed_player_id` — **this is what decides next round's first player**
   (`stDetermineStartPlayer` reads it);
2. `FfoPlayer::isActionAvailable($action_id)` — space must be empty;
3. computes `$number_meeples`, adjusted by occ 106 on action 60;
4. verifies the player has that many meeples in `thing`;
5. fires occ 168 (Earl of Lade) if exactly 3 vikings remain, occ 166 on a second 2-viking placement;
6. `FfoPlayer::moveMeepleToAction()` → `notifyAllPlayers("meepleMove")`;
7. **`Action::push($actions[0]->preCommands + ['pickmodal'])`**;
8. `nextState('action')` → state 51.

The same function also serves `command-clone`, `command-follower`, `command-latecomer` and
`command-inspector`, each with different validation and — importantly — different queue semantics:
`follower` and `latecomer` use `unshift` instead of `push` (`$push_or_unshift`, `:118`/`:147`), because
their copied action must resolve *inside* the current effect chain rather than after it.

Note `preCommands` is read from `$actions[0]` only. All modes of a space share the same meeple count,
so their preCommands are identical by construction — but nothing enforces that.

---

## 5. Stage 2 — the trampoline (`doCommand`)

`modules/state-functions/doCommand.php` — one function, ~1075 lines.

### 5.1 Skeleton

```php
function doCommand() {
    $player_id = getActivePlayerId();
    $action_id = getGameStateValue('action_id');

    if (isPlayerZombie($player_id))  DELETE FROM command_queue;   // :9  bail out entirely

    $command = Action::pop();                                      // :14
    $nextState = 'next';

    if (! isset($command)) {
        /* ---- EXIT PATH ---- */                                  // :20-52
    } else {
        $parts   = explode(':', $command);                         // :55
        $command = $parts[0];
        $source  = $parts[1] ?? "action-$action_id";

        setString('currentCommand', $command);                     // :62  <-- global, read by arg*
        setGameStateValue('num_rolls', 0);
        $components = explode('-', $command);
        $primary    = $components[0];

        if (in_array($primary, [ ...33 interactive names... ])) {   // :67  family A
            $nextState = 'command-' . $primary;
            ...special cases that *rewrite* $nextState...
        }
        else if ($primary == 'beforeorafter') { ... }               // :171 family B
        else if ($primary == 'setaction')     { ... }
        ...
        else if ($primary == 'occupation')    { ...~500 lines... }  // :552
        else throw new BgaVisibleSystemException("Command not implemented: $command");

        gamestate->nextState($nextState);                          // :1076
    }
}
```

### 5.2 The exit path (queue empty)

```php
flushAssetDiffs();                       // collapse asset_diffs into readable log lines
updatePlayerScore($player);              // recompute + persist + notify

// pre-game occupation bookkeeping (Harvest mini-exp, short game, non-Norwegians)
if (getString("preGameOccupation$player_id") !== '1' && usingAnyTimeOccupationOutOfAction) { ... }

if (usingAnyTimeOccupationOutOfAction) { // out-of-band occupation play
    setGameStateValue('usingAnyTimeOccupationOutOfAction', 0);
    nextState('pickAction');             // straight back to 50, skip confirmTurn
    return;
}

nextState( getString("finishedTransition") );
```

`finishedTransition` is the crux of the whole design. Writers:

| Written by | Value | Lands on |
|---|---|---|
| `stPickAction` (`helper.state-functions.php:394`) | `finished` | 59 confirmTurn |
| `stHarvestGoods` (`:500`) | `finished_harvest` | 30 turnExplorationBoards |
| `stDrawWeapon` (`:447`) | `finished_drawweapon` | 49 setFirstPlayer |
| `stNextPlayer` (`:259`) | `finished_postactions` | 70 determineStartPlayer |
| `stBreeding` (`:1059`) | `finished_breeding` | 100 feast-prepare |
| `stFeast` (`:1250`/`:1252`) | `finished_feast` / `finished_feastGameEnd` | 110 bonus-prepare / 150 gameEnd |
| `stBonus` (`:1549`) | `finished_bonus` | 120 updateMountainStrips |

This is why the automatic phases (harvest, weapon draw, breeding, feast, bonus) all route *through*
51 instead of transitioning directly: occupations fired during those phases push commands, and the
trampoline must drain them before the round moves on.

### 5.3 Command string format

```
primary [-arg]* [:source]
```

- `primary` selects the dispatch branch — **everything before the first hyphen**.
- `source` is cosmetic: it groups log lines in `flushAssetDiffs()` and tells the client where to
  animate from. Defaults to `action-{action_id}`.

### 5.4 Complete command reference

**Family A — interactive** (whitelist at `:67`). Sets `$nextState = 'command-' . $primary` and lets a
`10xx` state ask the player something.

| Command | State | Question asked |
|---|---|---|
| `pickmodal` | 1002 | which mode of this action space? |
| `exchange-<from>-<to>` | 1003 | trade one asset for another? |
| `forge` / `forgebuy` / `forgeminor` | 1004 / 1005 / 1083 | which special tile? |
| `mountain-<n>` | 1006 | which mountain strip, taking `n` resources |
| `trade` | 1007 | which green goods to upgrade |
| `upgrade-<tiles>-<steps>[-true]`, `sameupgrade`, `nwupgrade`, `payupgrade`, `meatupgrade`, `flip`, `oncetwiceupgrade` | 1008, 1039, 1071, 1052, 1018, 1038, 1090 | which goods to upgrade/flip |
| `hunt-<type>[-0]` | 1063 → 1009 → 1010 | the hunt sub-machine (§5.6) |
| `attack-<type>` | 1098 → 1099 → 1095 | the attack sub-machine |
| `emigrate`, `emigratepassable`, `emigrateminor`, `emigrateminorpassable` | 1012, 1049, 1084, 1092 | which boat emigrates |
| `explore-<vikings>`, `explorepassable-<vikings>` | 1078, 1079 | which island |
| `swapwhalingboatknarr`, `buylongshipfifthcol`, `buywhalingboatfifthcol` | 1013, 1080, 1091 | ship swaps/purchases |
| `clone-<column>` | 1014 | which action to copy |
| `slaughter-<animal>` | 1081 | which animal |
| `resources-2-unique` | 1082 | which two building resources |
| `anytwoanimals` | 1085 | which two animals |
| `discardocc` / `coinsoroccs` | 1086 / 1087 | which occupation to bin / coins vs cards |
| `armsdealer` | 1089 | which two weapons to discard |
| `doubleaction-<id>-<phase>` | 1100 | *not really interactive* — see §5.5 |

**Family B — mechanical.** Mutates state inline and returns `'next'`.

| Command | Effect | Line |
|---|---|---|
| `take-<asset>[-n]` | gain `n` of an asset; chains occ 154/155/28/150 | `:406` |
| `takeif-<asset>-<req>[-amt]` | gain 1 only if the player owns `amt` of `req` | `:475` |
| `takeper-<asset>-<per>` | gain 1 per owned `per` | `:494` |
| `tpp-<asset>` | take *per player at the table* | `:450` |
| `collect-milk` / `collect-wool` | up to 3, capped by herd size; Norwegians alters the maths | `:514` |
| `build-<a>[-<b>]` | build houses / artisan sheds / boats, decrement supply, room checks | `:272` |
| `draw-occupation` | draw a card, **kills undo** | `:338` |
| `draw-weapon[-<type>[-<n>]]` | typed draw is undoable; `x`/absent type is random and kills undo | `:373` |
| `play-occupation` | → `command-playOccupation` (1001) | `:397` |
| `linkedactions` | fires occ 113 / 121 / 164 based on the action's row | `:186` |
| `mountainreset` | clears `takenStrips`, settles occ 122/156/154/155/124 payouts | `:208` |
| `setaction-<id>` | rewrites the `action_id` global | `:184` |
| `setplayer-<id>[-nopass]` | retargets the active player mid-chain (out-of-turn effects) | `:266` |
| `flush` | force `flushAssetDiffs()` now, so the log reads in the right order | `:261` |
| `state-<x>` | escape hatch: jump straight to `command-<x>` | `:263` |
| `occupation-<id>` | per-card effect ladder, ~500 lines | `:552` |

Anything else throws `Command not implemented`.

### 5.5 Family A's hidden complexity: `$nextState` gets rewritten

`$nextState = 'command-' . $primary` is only the *default*. Three nested special cases override it:

**`pickmodal`** (`:69`) — if undo is already gone (an irreversible occupation was played), it
re-checks requirements and, on failure, sets `can_pass_override = 1` rather than throwing. That flag is
what lets the player escape a dead-end action they can no longer perform (see `pass.php:30`).

**`hunt` / `attack`** (`:79`–`:102`) — occupations divert the flow *before* the roll:

```php
if ($components[1] == "raid"    && Occupation::has($player_id, 90))  $nextState = 'command-swordfighter';
if ($components[1] == "pillage" && Occupation::has($player_id, 132)) $nextState = 'command-berserker';
if ($components[1] == "snare"   && Occupation::has($player_id, 131)) Action::push(["exchange-stockfish-gamemeat"]);
```

States 1068 (swordfighter) and 1074 (berserker) both transition `done`/`pass` → **1063**
(`command-prehunt`). So an `attack-raid` command belonging to a player holding occ 90 is routed into
the *hunt* sub-machine rather than the attack one. Both read the same `currentCommand` through
`argHunt()`, so the displayed hunt type stays correct — the practical difference is which reward UI
the player gets.

**`doubleaction`** (`:103`–`:170`) — actions 124, 128 and 5557 are two-part. The command carries its
own phase counter and re-queues its successor:

```php
if ($components[1] == 124) {
    if ($phase == 1) {
        Action::unshift(["doubleaction-124-2"]);          // schedule part 2
        if (hasLongship || (occ147 && hasKnarr)) {
            Action::unshift(["explorepassable-0"]);
            $nextState = "command-explorepassable-0";     // (a) never takes effect
        } else {
            $nextState = "next";
        }
        $nextState = "next";                              // (b) unconditional overwrite
    } else { ... }
}
```

Line (b) unconditionally overwrites line (a). That is *load-bearing*: `command-explorepassable-0` is
not a valid transition name (`states.inc.php` defines `command-explorepassable`), so if it ever
survived, BGA would throw. Because the command was also `unshift`ed onto the queue, falling through to
`'next'` produces the correct behaviour on the following trampoline pass. Do not "clean up" the dead
assignment by deleting line (b).

### 5.6 The hunt / attack sub-machine

The only place several states chain without returning to 51 between each:

```
hunt-<type>   →  1063 command-prehunt      "you are about to hunt"   [affirm | undo]
                   ↓ affirm
                 1009 command-hunt-roll    stHuntRoll: rolls d8/d12, setCanUndo(0)
                   ↓
                 1010 command-hunt         [reroll | declareSuccess | declareFailure
                   │                        | selectSpecialTile]
                   ├─ reroll ──────────────▶ 1009
                   └─ done / declareFailure ▶ 51

attack-<type> →  1098 command-preattack → 1099 command-attack-roll → 1095 command-attack
                   └─ reroll ▶ 1099    └─ done / declareFailure ▶ 51
```

`stHuntRoll` (`helper.state-functions.php:809`) increments `num_rolls`, rolls the die into the
`die_value` global, calls `setCanUndo(0)`, and logs the roll. It also honours the `goBack` global as a
no-op re-entry so returning from a reward screen doesn't re-roll.

The maths lives in `argHunt()` (`helper.state-arguments.php:72`) and is duplicated by the client for
live preview: `value = die + (ore + cards + resources) * multiplier + bonus`, where `multiplier` is
`+1` for raid/pillage/theft (roll high) and `-1` for everything else (reduce to zero). Ten hunt types,
each with its own die, weapon set, resource and description, all in parallel lookup arrays.

Resolution is `declareSuccess` (`player-actions/declareSuccess.php`), which spends resources and
weapon cards, validates the total against the target, hands out the type-specific rewards, fires a
dozen occupations, and ends with `nextState('done')` → 51. For raid/pillage/theft it instead records
`raid_value` and the reward is picked in-place from the tray via `selectSpecialTile` /
`selectAttackReward`.

---

## 6. Stage 3 — `pickmodal` and `selectMode`

State 1002's `action` is `stPickModal` (`helper.state-functions.php:413`):

```php
if (can_pass_override)      { /* player is stuck; let them pass */ }
else if (count($action) === 1) { actSelectMode(0); }        // skip the UI entirely
else                        { Requirements::check($action_id, $player); }
```

So single-mode actions never show a prompt — they call straight into `selectMode`.

`selectMode`'s `command-pickmodal` branch (`player-actions/selectMode.php:342`) is the real heart of an
action:

1. bounds-check `mode_id` against `getCurrentAction()`;
2. `Requirements::check($action_id, $player, $mode_id)` — now for that specific mode;
3. **pay** `$mode->cost` via `spend()`, then save the player;
4. build two arrays, `$before_action` and `$after_action`, from ~25 occupation checks keyed on
   `action_id` and sometimes `mode_id` (Clerk, Ship's Cook, Foreign Trader, Master Count, Priest,
   Furrier, Cottager, Bosporus Traveller, Armed Fighter, …);
5. honour a deferred `pending_play` by appending `play-occupation` to `$after_action` — unless this is
   a clone action (`:548`);
6. `Action::unshift(array_merge($before_action, $mode->commands, $after_action))`;
7. `nextState('done')` → 51.

**`unshift`, not `push`.** The mode's commands must run before anything already queued behind the
action (e.g. a pending `play-occupation` from a 4-meeple space, or a follower's copied action).

The rest of `selectMode` is a ~280-line `else if` chain, one branch per `command-*` state that offers a
menu (metalsmith, preacher, clerk, sower, furmerchant, coinsoroccs, …). Every branch ends at the same
`nextState('done')` on line 637.

### Deferred draw / play

`beforeorafter-draw-occupation` and `-play-occupation` let the player choose *when* the card
happens — this matters because drawing kills undo, and playing may change whether the action is even
legal.

| Choice | Mechanism |
|---|---|
| now | `Action::unshift(["draw-occupation"])` / `["play-occupation"]` (`selectMode.php:14`/`:21`) |
| later | set global `pending_draw` / `pending_play` |

`pending_play` is consumed in `selectMode` step 5 above. `pending_draw` is consumed much later, in
`affirm()` under `state == 'confirmTurn'` (`affirm.php:450`), which duplicates the drawing code from
`doCommand` rather than queueing a command. Both flags are cleared unconditionally by `stPickAction`.

---

## 7. Stage 4 — ending the turn

```
queue empty → finishedTransition = 'finished' → 59 confirmTurn
```

`stConfirmTurn` (`helper.state-functions.php:2044`) auto-affirms when undo is unavailable, so the
confirmation step only appears when it could actually be useful. `argConfirmTurn` passes
`pending_draw` so the client can warn that a card is still owed.

`affirm` → 60 `nextPlayer` → `stNextPlayer` (`:212`):

- not everyone has passed → advance to the next unpassed player, back to 50;
- everyone has passed → queue the end-of-phase occupations (occ 91 Latecomer, occ 9 Ore Boatman,
  each preceded by `setplayer-<id>`), set `finishedTransition = 'finished_postactions'`, and
  `nextState('nextPhase')` → **51 again**, so those queued commands resolve before the income phase.

---

## 8. Worked traces

Queue contents shown after each step. `▸` marks a state visit.

### A. Simplest case — action 1 (build a shed, 2 wood, 1 meeple)

```
▸ 50 pickAction      click space 1
                     buildAction: meeple → action, push preCommands + pickmodal
                     queue: [linkedactions, pickmodal]
▸ 51                 pop linkedactions → action 1 is in row 'houses';
                     occ 121 Carpenter? no → 'next'
                     queue: [pickmodal]
▸ 51                 pop pickmodal → 'command-pickmodal'
                     queue: []
▸ 1002               stPickModal: only 1 mode → actSelectMode(0)
                       Requirements::check(1) — sheds left in supply?
                       spend 2 wood
                       unshift(["build-shed"])
                     queue: [build-shed]        → 'done'
▸ 51                 pop build-shed → num_sheds--, addBoard, updateBuildingSupply → 'next'
                     queue: []
▸ 51                 empty → flushAssetDiffs, updatePlayerScore
                     finishedTransition = 'finished'
▸ 59 confirmTurn     affirm
▸ 60 nextPlayer
```

### B. Three meeples with a deferred draw — action 43 (`mountain-3`, `mountain-2`)

```
▸ 50                 queue: [linkedactions, beforeorafter-draw-occupation, pickmodal]
▸ 51                 linkedactions → 'next'
▸ 51                 pop beforeorafter-draw-occupation
                       Requirements::check FIRST (don't hand out a card for an illegal action)
                       → 'command-beforeorafter-draw'
▸ 1060               player picks "later" → pending_draw = 1 → 'done'
▸ 51                 pop pickmodal → 1002 → single mode → selectMode
                       unshift([mountain-3, mountain-2, mountainreset])
▸ 51                 pop mountain-3 → 'command-mountain'
▸ 1006               argMountain{num:3}; selectStrip; records strip in takenStrips → 'done'
▸ 51                 pop mountain-2 → 1006 again (a different strip — takenStrips blocks reuse)
▸ 51                 pop mountainreset → clears takenStrips, settles occ 122/156/154/155 → 'next'
▸ 51                 empty → 'finished'
▸ 59 confirmTurn     affirm → affirm() sees pending_draw → draws the occupation now
▸ 60 nextPlayer
```

### C. An occupation firing mid-chain

Playing occ 22 (Miner) during `command-playOccupation`:

```
▸ 1001               playOccupation(): validate in hand, Occupation::play(), notify,
                     resolved_effect = 1
                     Occupation::getImmediateCommands(22)
                       = [takeper-ore-longship, takeper-stone-longship, takeper-coin-longship]
                     each suffixed ":occupation-22", then "flush" appended
                     Action::unshift([...those 4...])          ← FRONT of queue
                     → 'done'
▸ 51 ×3              three takeper commands: 1 ore/stone/coin per longship owned
▸ 51                 flush → asset_diffs collapse into one "X takes: …" log line
▸ 51                 …continues with whatever the action had queued behind it
```

`getImmediateCommands` (`class.occupation.php:400`) maps ~14 cards to command lists; everything else
falls through to `["occupation-$ability_id"]`, i.e. the big ladder inside `doCommand`. A third group is
handled by `getNextState` (`:453`), which maps ~24 cards straight to a `command-*` state.

**So a single occupation's logic can live in any of five places** — `getImmediateCommands`,
`getNextState`, the `occupation-<id>` ladder in `doCommand`, an `st*` phase hook, and `stCheckSkip`.
When hunting for a card's behaviour, grep its numeric ID across `modules/`.

### D. `setplayer` — an out-of-turn effect

During `stBonus`, another player's Waterford board grants a longhouse:

```php
Action::push(["setplayer-$player_id", "build-longhouse"]);
```

```
▸ 112 bonus          pushes the pair, sets finishedTransition = 'finished_bonus'
▸ 51                 pop setplayer-<id> → gamestate->changeActivePlayer() → 'next'
▸ 51                 pop build-longhouse → builds for THAT player → 'next'
▸ 51                 empty → 'finished_bonus' → 120 updateMountainStrips
```

The active player is left pointing at whoever ran last; the next phase's `st*` function resets it.

---

## 9. Why it reads as messy — the specific reasons

Not vague criticism; these are the concrete structural properties that make the file hard.

1. **State 51 is a god node.** It carries ~120 transitions (`states.inc.php:157-263`), one string per
   interactive command. Adding a command means editing at least four places that must agree by
   convention alone: the whitelist at `doCommand.php:67`, a transition entry in `states.inc.php`, the
   new `10xx` state block, and a branch in a `player-actions/*` trait.

2. **The queue is untyped strings in a DB column.** No schema, no constructor, no validation. A typo
   is invisible until runtime, when it surfaces as `Command not implemented: <string>` — and only for
   the player unlucky enough to trigger it. `Action::push` interpolates directly into SQL
   (`class.action.php:639`).

3. **Two parallel lists that must stay in sync.** The `in_array($primary, [...])` whitelist and the
   `command-*` transition table encode the same knowledge in two formats. Nothing checks them against
   each other.

4. **Two ways to reach the same state.** `mountain-2` (whitelisted primary) and `state-mountain`
   (generic escape hatch, `:263`) both land on 1006 — but only the first sets up `currentCommand` with
   arguments the `arg*` function can parse. Which one is correct depends on the state.

5. **`$nextState` is assigned, then conditionally re-assigned, then sometimes unconditionally
   overwritten.** §5.5 documents a case where the overwrite is what makes the code correct.

6. **`push` vs `unshift` is load-bearing and unmarked.** Getting it wrong doesn't crash; it silently
   reorders rule resolution. There is no comment at most call sites explaining which is intended.

7. **`currentCommand` is a global the `arg*` functions re-parse.** `argHunt`, `argUpgrade`,
   `argMountain`, `argExchange`, `argExplore`, `argSlaughter` all `explode('-', getString('currentCommand'))`
   and index positionally. The command string is effectively an undocumented positional parameter list
   shared between two files.

8. **One function, one flat `if/else` chain, ~1075 lines.** The `occupation-<id>` branch alone runs
   ~500 lines, of which ~200 are the coordinate arithmetic for occupations 70/71 (Courier/Innkeeper)
   moving a 2-cell tile off another player's board — inline, with four rotation × two flip cases
   written out longhand twice.

9. **Cross-cutting card logic has no home.** See §8C: five possible locations per occupation.

10. **Stale TODOs that describe the opposite of the current code.** `doCommand.php:54` says
    `// TODO : Allow override source to be used!` immediately above the line that uses the override
    source. `selectMode.php:372` says the occupation hooks below it "should go to linkedactions" —
    they are still there.

---

## 10. Verified traps

Things I confirmed by reading, that will mislead you otherwise.

**Unreachable states.** `1011 command-hunt-reward`, `1066 command-hunt-reward2`,
`1096 command-attack-reward` and `1097 command-attack-reward2` cannot be entered:

- No code anywhere emits the `'raid'` or `'raider'` transitions that lead to 1011 / 1066 — grepping
  `nextState` across `modules/player-actions/` shows every hunt handler ends with `'done'`.
- 1096 / 1097 are reachable only via 51's `command-attack-reward` / `command-attack-reward2`
  transition names, and `doCommand` can never produce them: `$primary` is the text before the *first*
  hyphen, so `attack-reward-…` always yields `command-attack`.

Reward selection actually happens **in place** in 1010 / 1095. `ffo.states.js:473` renders the
candidate tiles into the top tray during `command-hunt`, and the player's click calls
`selectSpecialTile` or `selectAttackReward` directly.

**Two endpoints have their state guard disabled.** `declareSuccess.php:6` and
`selectAttackReward.php:6` both have `checkAction` commented out:

```php
// self::checkAction( 'declareSuccess' );
```

This is what makes the in-place reward flow work (those actions are not in every calling state's
`possibleactions`). The consequence is that neither verifies the caller is in a legal state; they rely
on `getActivePlayerId()` plus their own affordability checks. Both remain guarded against *other*
players acting, but not against being called at the wrong time. Worth knowing before you refactor
either one — and worth tightening if the reward states are ever formalised.

**`stCheckSkip` is a second dispatcher.** `helper.state-functions.php:1671-2032` is a ~360-line
`if/else if` on `$state['name']`, auto-passing optional occupation states the player can't use. If an
occupation prompt "doesn't appear", check here before assuming the command never queued.

**Solo mode uses two meeple colours** to track placement round; `command-inspector` in
`buildAction.php:167` rejects returning a meeple of the inactive colour.

---

## 11. Invariants — rules for changing this code safely

1. **Every `10xx` state must have a transition back to 51** for each of its `possibleactions`
   outcomes. Miss one and the game deadlocks with no error.
2. **Every interactive state that can be entered before randomness should also have
   `"undo" => 50`.** Most do; copy an existing block rather than writing one from scratch.
3. **Call `setCanUndo(0)` the instant hidden or random information is consumed.** Random draws and die
   rolls already do; anything new that reveals information must too.
4. **`unshift` for effects that must resolve inside the current chain; `push` for effects that follow
   it.** When in doubt, write down the queue contents at that moment.
5. **Never assume the queue is empty at the end of an `st*` phase function.** Set
   `finishedTransition` and route through 51.
6. **Adding a command primary** = whitelist entry (if interactive) + `states.inc.php` transition +
   state block + handler branch. All four.
7. **Keep `Requirements::check` before anything irreversible.** The 3-meeple `beforeorafter-draw`
   ordering exists precisely for this.
8. **`getActivePlayerId()` vs `getCurrentPlayerId()`** — the latter in multiactive states and in
   handlers reachable out of turn (`pass.php` mixes both deliberately).
9. **Commands are interpolated into SQL.** Never build one from unvalidated client input.

---

## 12. Debugging playbook

| Symptom | Where to look |
|---|---|
| `Command not implemented: X` | Typo in a pushed command, or a new primary missing its `doCommand` branch |
| `Unexpected state: <name>` / `Unrecognised state` | A `player-actions` trait is missing a branch for a state that lists its action in `possibleactions` |
| Game hangs on a `command-*` state | That state lacks a transition for the outcome the handler emitted |
| An occupation silently does nothing | `stCheckSkip` auto-passed it; or its ID is missing from `getImmediateCommands` / `getNextState` / the `occupation` ladder |
| Effects resolve in the wrong order | `push` where `unshift` was needed (or vice versa) |
| Log lines are interleaved oddly | Missing `flush` command between two effect groups |
| Undo available when it shouldn't be | A new randomness/reveal path missing `setCanUndo(0)` |
| Wrong first player next round | `placed_player_id` — set in `buildAction.php:21`, read by `stDetermineStartPlayer` |

**Inspecting live state.** `Action::dump()` (`class.action.php:665`) returns the queue.
`FeastForOdin` also exposes debug helpers callable from BGA Studio's console: `lotsOfResources()`,
`givewood()`, `good($type)`, `occ($ability)`, `pocc($ability)`, `docc($ability)`,
`giveisland($name)`, `debugSetColumns($l, $r)` (`feastforodin.game.php:526-647`, `:1054`).

The commented-out line at `doCommand.php:17` is the fastest way to watch the queue drain in the game
log:

```php
self::notifyAllPlayers( "message", 'Running command: ' . $command, array());
```
