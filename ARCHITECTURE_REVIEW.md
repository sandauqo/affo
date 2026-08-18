# Architecture Review

Companion to [`FLOW.md`](FLOW.md) (system overview) and [`ACTION_FLOW.md`](ACTION_FLOW.md) (the action
engine in depth). This file is an assessment: what the design gets right, where it hurts, what I could
not determine, and what I would change.

**Method.** I read the full server-side control flow (game class, state machine, all state functions,
`doCommand`, the `Action`/`Requirements`/`Occupation`/`FfoPlayer` classes, and most of
`modules/player-actions/`), skimmed the client modules, and measured the repository. I did **not** run
the game — there is no local PHP interpreter and BGA Studio is not reachable from here — so every claim
about runtime behaviour is derived from reading, and the ones I'm unsure about are logged in §6 rather
than asserted.

---

## 1. Metrics snapshot

| Measure | Value |
|---|---|
| PHP source | 58 files, ~21,700 lines (excl. `_ide_helper.php`) |
| JS source | 11 files, ~8,450 lines (excl. `anime.min.js`) |
| Largest data file | `modules/data/boards.php` — 5,444 lines |
| Largest logic files | `helper.state-functions.php` 2,066 · `states.inc.php` 1,382 · `feastforodin.game.php` 1,312 · `doCommand.php` 1,058 |
| Largest client files | `ffo.states.js` 1,579 · `ffo.action.js` 1,534 · `feastforodin.js` 1,476 |
| State machine size | ~130 states; state 51 alone holds ~120 transitions |
| `Occupation::has(...)` call sites | **183**, spread across **27 files** |
| Distinct occupation IDs referenced | **115** |
| Raw SQL call sites | **121** |
| Registered integer globals | ~60 (`feastforodin.game.php:35-121`) |
| Version control | **none** (no `.git`) |
| Automated tests | **none** |
| Build / dependency / static-analysis config | **none** (no `package.json`, `composer.json`, phpstan/psalm, CI) |
| PHP language level (IDE setting) | 8.4 |

Roughly 30,000 lines implementing a famously intricate board game plus four expansions, maintained
without version control or tests. That framing matters for everything below: the architecture is
better than the process around it.

---

## 2. Verdict

The core architectural bet — **encode rules as a queue of command strings interpreted by a trampoline
state, rather than as states in the state machine** — is the right call for this game, and I would keep
it. A Feast for Odin's action spaces are compositions of small effects with player choices interleaved
at arbitrary depth, and BGA's state machine is a poor fit for that (states are static, declared in a
PHP array, and can't be parameterised). The queue turns an intractable state-explosion problem into a
tractable interpreter problem.

The costs are all *implementation* costs, not conceptual ones: the command language is untyped, the
interpreter is one long function, the same knowledge is duplicated across files that must agree by
convention, and there's no test harness to catch it when they drift. Those are fixable incrementally
without disturbing the design.

**Highest-leverage change is not code:** put the project under version control and build a replay test
harness. Every structural refactor below is significantly riskier without those two.

---

## 3. Strengths

**3.1 The command queue is a genuinely good fit.** Effects compose by pushing more effects; depth is
unbounded; player choices interleave naturally. Compare the alternative — a distinct state per action
space per mode per interleaving — which would be thousands of states. The `unshift` vs `push`
distinction gives you precise control over rule ordering, which is exactly the vocabulary the rulebook
uses ("before you do X…", "then…").

**3.2 `finishedTransition` as a return address** is a clean solution to a real problem. It lets every
automatic phase (harvest, income, breeding, feast, bonus) reuse the same interpreter without knowing
who called it, so occupation triggers fired during those phases resolve before the phase advances. One
string global replaces what would otherwise be six near-duplicate interpreter states.

**3.3 Data-driven content.** Boards, goods, action metadata, artisan sheds and resource markers are
plain PHP arrays in `modules/data/`. Adding an island is data, not code. `Action::filterActions()` then
queries that data by row/column/variant, so rules like "all crafting actions" are expressed once.

**3.4 Undo actually exists, and its cost model is thought through.** Snapshot on turn start, lazy
per-table mirrors guarded by `undo_*` flags so you don't copy the forge table unless the turn touched
it, and `setCanUndo(0)` precisely where hidden information is consumed. For a game this complex, having
undo at all is a real achievement; most BGA implementations of heavy Euros don't.

**3.5 One file per player action.** `modules/player-actions/*.php` — 34 traits, mostly 1–7 KB. This is
the best-organised part of the codebase. The trait-per-action pattern gives each entry point a home
without a class hierarchy, and `helper.player-actions.php` is a readable index of the whole player API.

**3.6 Pragmatic persistence.** Putting `goods`/`boards`/`ships`/`emigrations` in JSON columns rather
than modelling polyomino placements relationally is the right trade. These are read and written as a
whole, never queried by field. `getFullPlayer`/`saveFullPlayer` is a single, consistently-used envelope.

**3.7 Auto-skip as a first-class concern.** `stCheckSkip`, `stUpgrade`, `stForge`, `stStoreman`,
`stStoreman` et al. suppress prompts the player can't act on, and `stPickModal` skips the mode dialog
when there's only one mode. In a game with this many optional triggers, that's the difference between
playable and exhausting. It's also why `isAutoPassablePhases`/autopass exist.

**3.8 Log quality.** `asset_diffs` + `flushAssetDiffs()` is a nice piece of design: instead of emitting
a notification per resource, spends and gains accumulate and collapse into one grouped line per
`(player, source)` — *"X exchanges: ⬥⬥ for ◆"*. The `flush` command lets effect chains control where the
grouping boundaries fall.

**3.9 Client/server split respects authority.** Placement is provisional client-side and re-validated
in `saveBoards.php` via `FfoPlayer::checkProvisionals()`. The client computes hunt previews for
responsiveness but `declareSuccess` recomputes and rejects bad totals.

---

## 4. Problems

Ordered by what I'd fix first, which is not the same as by severity of the code smell.

### 4.1 Process (blocking everything else)

- **No version control.** 30k lines, a live beta game, no history, no branches, no bisect, no blame, no
  rollback. `.idea/.gitignore` exists, suggesting Git was intended and never initialised. Any refactor
  in this document is dangerous until this changes.
- **No tests.** The game is deterministic given (options, RNG seed, action sequence), which makes it
  unusually well-suited to golden-master testing — yet nothing exists.
- **No static analysis.** With 115 occupation IDs referenced across 27 files by bare integer, a
  typo'd ID is invisible. PHPStan would catch a subset (undefined methods, array-shape drift) cheaply.
- **No dependency manifest or build step.** `anime.min.js` is vendored with no recorded version.
  `feastforodin.css` (210 KB) is compiled from SCSS, but only `_sprites_icons.scss` is present — the
  main SCSS source is either absent or outside the project, and there's no documented build command.

### 4.2 Correctness risk

- **SQL by string interpolation, 121 sites.** Values are constrained upstream by
  `feastforodin.action.php`'s `AT_posint`/`AT_alphanum` types plus ad-hoc regex checks (e.g.
  `selectGoods`), and BGA's arg typing is a real boundary — but it's the *only* boundary, applied by
  convention at each new endpoint. `Action::push()` interpolates command strings straight into an
  `INSERT` (`class.action.php:639`). One endpoint added without an `AT_*` type is an injection.
- **Two endpoints have their state guard disabled.** `declareSuccess.php:6` and
  `selectAttackReward.php:6` have `checkAction` commented out. This is load-bearing (it's what makes
  in-place reward selection work) but it means neither verifies the caller is in a legal state; they
  rely on `getActivePlayerId()` plus their own affordability checks. Detail in `ACTION_FLOW.md` §10.
- **Undo regenerates occupation primary keys.** `helper.undo.php:174` restores occupations with an
  explicit column list that omits `id`, so rows get new auto-increment IDs — unlike every other mirror
  table, which uses `INSERT ... SELECT *` and preserves IDs. Whether this can strand a card depends on
  the draw-then-undo path being genuinely unreachable (`setCanUndo(0)` fires on draw). See §6.
- **`player_saved` holds exactly one row, globally.** `saveState` does `DELETE FROM player_saved WHERE 1`
  before inserting (`helper.undo.php:17`). `restoreState` then reads `WHERE player_id = $player_id`, so
  a snapshot belonging to a *different* player yields NULL and a PHP error on the next line. Currently
  safe as far as I can tell — occ 91 Latecomer's mid-phase `saveState` (`helper.state-functions.php:235`)
  leads to state 1072, which offers no undo — but the invariant is implicit and one new `"undo" => 50`
  in the wrong state would break it.
- **The interactive whitelist and the transition table are duplicate knowledge.** The 33-element
  `in_array($primary, [...])` at `doCommand.php:67` must agree with ~120 `command-*` entries in
  `states.inc.php`. Nothing checks this. Drift manifests as a BGA "undefined transition" crash.
- **Four unreachable states** (`1011`, `1066`, `1096`, `1097`) plus the `'raid'`/`'raider'` transitions
  that would reach them. Verified in `ACTION_FLOW.md` §10. Dead code that reads as live code is worse
  than absent code: it invites you to modify the wrong thing.
- **Stale TODOs asserting the opposite of the code.** `doCommand.php:54` (`// TODO : Allow override
  source to be used!` directly above the line that uses it) and `selectMode.php:372`.

### 4.3 Structure

- **State 51 is a god node** with ~120 transitions. Adding one command primary requires four
  coordinated edits (whitelist, transition, state block, handler branch), none of which the compiler or
  a test can verify.
- **`doCommand` is 1,058 lines in a single flat `if/else` chain.** The `occupation-<id>` branch alone is
  ~500 lines, ~200 of which are longhand coordinate arithmetic for occupations 70/71 — four rotations ×
  two flips, written out twice (cane and horn) with only the tile names and offsets differing.
- **Occupation logic is scattered across five possible homes** per card: `getImmediateCommands`,
  `getNextState`, the `doCommand` ladder, `st*` phase hooks, and `stCheckSkip`. Measured spread: 183
  call sites of `Occupation::has` across 27 files for 115 distinct IDs. To answer "what does card 145
  do?" you must grep the whole tree. This is the single largest maintainability cost in the codebase.
- **~60 integer globals as the coordination mechanism.** `stPickAction` explicitly resets about twenty
  of them at the start of every turn (`helper.state-functions.php:363-395`), which is the tell: they're
  per-turn locals that outlive their scope because there's nowhere else to put them. Names like
  `occ_swordfighter`, `occ_berserker`, `occ_fishcook`, `occ_steward` are per-card state promoted to
  global.
- **`currentCommand` is an undocumented positional parameter list.** Six `arg*` functions
  `explode('-', getString('currentCommand'))` and index by position. The producer (a `push` call
  anywhere in the tree) and the consumer (an `arg*` function) share a format defined nowhere.
- **`push` vs `unshift` is semantically critical and mostly uncommented.** Getting it wrong silently
  reorders rule resolution rather than failing.
- **Duplicated rules across the language boundary.** Hunt maths lives in `argHunt()`
  (`helper.state-arguments.php:72`) and again in `ffo.states.js` for live preview; placement legality
  lives in `GoodMatrix`/`GoodsTarget` (JS) and again in `FfoPlayer::isValidCoords`/`checkProvisionals`
  (PHP); tile strengths appear in `ffo.states.js:1566+` and in the forge table. Some duplication is
  inherent to BGA (client responsiveness + server authority), but it's undefended by any shared
  fixture.
- **Duplication within a single file.** The `dice_types` map is written twice in
  `helper.state-functions.php` — `stHuntRoll` (`:820`) and `argHunt` (`:79`) — along with three sibling
  lookup tables.
- **Filename/identifier mismatches.** `class.player.php` defines `FfoPlayer`; the `helper.*.php` files
  define traits, not helpers. Minor, but it slows navigation.

### 4.4 Performance

I could not profile, so these are structural observations with an estimated shape, not measurements.

- **`Occupation::has()` is an uncached `SELECT *` per call** (`class.occupation.php:328`), and it's
  called 183 times statically — many inside loops over players. `stHarvestGoods` alone checks 8 cards ×
  N players; `getModifiedAction` checks up to 9 per invocation and is called from `getCurrentAction`,
  `Requirements::check`, `stPickModal` and `buildAction`. A single action plausibly issues hundreds of
  these. Two cheap fixes: `SELECT 1 ... LIMIT 1`, and a per-request memo keyed by player.
- **`getFullPlayer()` re-reads and re-decodes the player row on every call** (`class.player.php:211`),
  including all four JSON blobs. `doCommand` calls it in a dozen branches; some `st*` functions call it
  per player per hook.
- **`saveFullPlayer()` writes all four blobs unless `$fields` is passed** — and only two call sites pass
  it. `boards` is the largest blob and changes least often.
- **Each command costs 2 queries minimum** (`SELECT` all + `DELETE` one in `Action::pop()`), and
  `Action::unshift()` reads the whole queue, deletes it, and re-inserts everything.
- `Requirements::check` calls `Action::getModifiedAction`, which calls `getDataActions()` →
  `include "modules/data/actions.php"` — a 712-line file re-`include`d (returning a fresh array) on
  every call, as are `getDataBoards()` (5,444 lines) and `getDataGoods()`. No memoisation.

### 4.5 Client

- **Two switch statements carry the client's entire behaviour**: `onEnteringState` and
  `onUpdateActionButtons` in `ffo.states.js` (1,579 lines total). Adding a state means finding the right
  spot in both.
- **Layout constants are hardcoded pixel offsets** in long `if/else` chains keyed on board type
  (`GoodsTarget.js:34+`). These belong in `modules/data/boards.php` next to the board they describe.
- **No module bundling or minification**; `feastforodin.js` alone is 1,476 lines and the client ships
  ~8.5k lines plus a 70 KB i18n file and a 210 KB stylesheet.
- **`mobileAndTabletCheck()`** is a vendored 2012-era user-agent regex (`feastforodin.js:118`);
  feature/viewport detection is already used elsewhere in the same file (`matchMedia`) and is more
  reliable.
- Leftover `console.log` in state handlers (`ffo.states.js:405`, `feastforodin.js:174`).

---

## 5. What I'd deliberately *not* change

- **Keep the command queue.** It's the correct abstraction. Improve its typing, not its existence.
- **Keep JSON blobs for boards/goods/ships.** Normalising polyomino placement would be a large loss.
- **Don't delete the unconditional `$nextState = "next"`** in the `doubleaction-124` branch — it is
  load-bearing, see `ACTION_FLOW.md` §5.5.
- **Don't remove the `//DEBUG:` normalisation blocks** in `getAllDatas()` and `getFullPlayer()` until
  you can prove no live table still holds the old shapes. Move them to `upgradeTableDb` instead.
- **Don't add `declare(strict_types=1)` wholesale** without checking BGA framework compatibility first
  — the framework passes a lot of numeric strings.

---

## 6. Uncertainty log

Things I could not determine from reading alone, why each matters, and how to settle it.

| # | Unknown | Why it matters | How to resolve | My guess |
|---|---|---|---|---|
| 1 | Whether BGA processes consecutive `game`-state transitions **within one HTTP request** or one request per transition | Decides whether the trampoline's per-command query cost is amortised or multiplied by network round trips. If it's per-request, §4.4 is urgent; if in-request, it's merely wasteful | Instrument `doCommand` with a request-scoped counter and log it on Studio | In-request, server-side loop — moderately confident, unverified |
| 2 | Actual query count and wall time for a representative turn | Sizes the whole of §4.4 | Enable BGA Studio's SQL profiler, play one 4-player round | Hundreds of queries per action; the `Occupation::has` fix is probably the single biggest win |
| 3 | Whether draw-then-undo is truly unreachable, making the occupation ID regeneration harmless | If reachable, cards silently leave the game — a severe, hard-to-diagnose bug | Write a replay test: draw an occupation, attempt undo, assert deck+hand counts | Unreachable today via `setCanUndo(0)`, but only by coincidence of ordering |
| 4 | Why `helper.undo.php:174` omits `id` when every sibling mirror uses `SELECT *` | Suggests a past collision this works around; changing it blind could resurrect that bug | Search Studio logs for duplicate-key errors on `occupation`; ask the original author | A workaround for an AUTO_INCREMENT collision, since `occupation_saved` keeps its own sequence |
| 5 | Whether any path offers undo after a *foreign* player's `saveState` (the single-row `player_saved`) | A NULL row would produce a PHP fatal mid-turn | Add a guard in `restoreState` that logs and refuses when the saved row's `player_id` mismatches — it'll tell you empirically | Currently safe; the invariant is accidental |
| 6 | Completeness of Mini expansion 2 (Harvest) and Yule | `gameoptions.json:75` self-labels 112 as "experimental, expect some bugs"; several `stDrawPersonalResource*` functions are empty stubs | Diff implemented commands against the printed expansion rules | Partially complete |
| 7 | Whether `_income` / `_negatives` / `_bonus` / `_penalties` cached board fields are always recomputed after undo | Stale caches would silently corrupt income and final scoring | Replay test: place goods, undo, assert cached fields equal a fresh recompute | Probably fine because whole board JSON is restored, but the caches are written in several places |
| 8 | Whether `ffo.i18n.js` card text and `material.inc.php`'s `m_occupations` are generated from one source | If hand-maintained twice, they will drift | Compare a sample of ~10 card names/IDs across both | Hand-maintained separately |
| 9 | Whether `upgradeTableDb` covers every schema version in the wild | A missed version breaks running games on deploy | Check the oldest live table's `game_version` against the single `$from_version <= 2409041709` branch | One branch exists; likely sufficient but untested |
| 10 | Client performance on mobile with 4 players and many boards | `onResize` does whole-subtree `transform: scale()` and repositions every good | Chrome DevTools profile on a throttled device | Probably the worst UX cost in the project, unmeasured |
| 11 | Where the main SCSS source lives and how CSS is built | Can't safely change styling without it | Ask the maintainer; check for an out-of-tree build dir | Outside this project directory |
| 12 | Whether `player.extra` / `getExtraDefault()` is used for anything beyond autopass and personal resource markers | Affects whether it needs a schema | Grep `extra` usage across `class.player.php` | Those two uses only |
| 13 | Intent behind the `1100 doubleaction` state (declared, `action` commented out, one transition) | May be a half-finished refactor of the double-action mechanism | Ask the author; check whether `command-doubleaction` is ever emitted | Abandoned scaffolding — `doCommand` handles double actions inline instead |
| 14 | Whether solo mode's two-colour meeple scheme is fully honoured everywhere meeples move | A missed spot would let players reuse vikings across rounds | Replay a full solo game asserting meeple colours per round | `stRemovePlacedVikings` and Inspector handle it; other paths unaudited |

---

## 7. Large-scale recommendations

Each is written to be adoptable incrementally, because a big-bang refactor of a live game without
tests would be reckless.

### R1 — Initialise Git today (hours: 0.5)

```
git init && git add -A && git commit -m "Import existing implementation"
```

Add `.gitignore` for `.idea/`, and commit `feastforodin.css.map` decisions deliberately. Nothing else in
this document should be attempted first. Consider a second commit that records the current deployed
version so you can diff against production.

### R2 — Build a replay test harness (days: 3–5, highest ROI after R1)

The engine is deterministic given options + seed + action sequence, which makes golden-master testing
natural.

Path of least resistance:

1. Start with what's already pure: `GoodMatrix` (JS), `Action::getAction` (no DB), scoring maths in
   `updatePlayerScore` (takes a `$player` array), `Requirements` helpers `hasSpaceToEmigrate` /
   `canAffordToEmigrate`. Test these first with no framework changes.
2. Introduce a **seam** for the rest: the code already funnels DB access through five static shims on
   the game class (`getCollection`, `getObjectList`, `getObject`, `getValue`, `DbQuery` —
   `feastforodin.game.php:1378-1393`). That is a ready-made injection point. Put them behind an
   interface with an in-memory or SQLite implementation and a large slice of the engine becomes
   testable outside BGA.
3. Then record real games: log every `(endpoint, args)` plus the RNG seed, and assert final scores and
   board state. A handful of full-game fixtures across option combinations would catch the majority of
   regressions the current process cannot.

This is also the prerequisite that makes R3–R5 safe.

### R3 — Turn commands into typed values with a handler registry (days: 5–8)

Keep strings on the wire (the DB column, undo, existing games all keep working). Add:

```php
final class Command {
    public function __construct(
        public readonly string $primary,
        public readonly array  $args,
        public readonly ?string $source,
    ) {}
    public static function parse(string $raw): self { ... }
    public function __toString(): string { ... }
}

interface CommandHandler {
    public function primary(): string;
    public function isInteractive(): bool;      // ← replaces the in_array whitelist
    public function stateName(): ?string;       // ← lets you generate states.inc.php transitions
    public function execute(Command $c, GameContext $ctx): string;  // returns next transition
}
```

What this buys, concretely:

- The 33-element whitelist and the ~120 `command-*` transitions become **derived from one registry**,
  killing the drift class of bug in §4.2.
- `arg*` functions stop re-parsing `currentCommand` positionally; they read `$command->args` with names.
- Each handler is independently testable.
- Unknown primaries fail at registration time, not at runtime in someone's game.

Migrate one family at a time: register handlers for the mechanical commands first (`take`, `takeif`,
`takeper`, `tpp`, `collect`, `build`), leave `doCommand`'s `else` chain as the fallback, and delete
branches as they're ported.

### R4 — Give every occupation one home (days: 8–12, largest maintainability win)

Replace the five-location scatter with one descriptor per card:

```php
return [
  145 => new OccupationSpec(
      name: 'Mountain Guard',
      immediateCommands: [],
      nextState: null,
      onHunt: fn($ctx) => $ctx->unshift(['mountain-1', 'mountainreset']),
      skipWhen: null,
      points: 2,
  ),
  ...
];
```

Then rewrite the hooks as *queries against the registry* instead of 183 hand-written `Occupation::has`
branches:

```php
foreach ($this->occupationsWithHook($player_id, 'onHarvest') as $spec) {
    $spec->onHarvest($ctx);
}
```

Do it card by card, keeping `Occupation::has` working throughout: move a card into the registry, delete
its scattered `if` statements, run the replay tests. 115 cards is a lot of small, safe, verifiable
steps — good work to parallelise or to do in idle moments.

This also gives you the natural place to record which cards are implemented, which is currently
knowable only by grepping.

### R5 — Split `doCommand` and extract the geometry (days: 2–3, do alongside R3)

- Each handler class from R3 takes one branch.
- The occupation 70/71 coordinate arithmetic (~200 lines) becomes a small `TileSplitter` with a data
  table of `(rotation, flipped) → [offset1, offset2]`. Eight cases × two tiles as data, not as nested
  conditionals.
- The four hunt lookup tables get extracted once and shared by `stHuntRoll`, `argHunt` and
  `declareSuccess`.

### R6 — Add a caching repository layer (days: 1–2)

A per-request memo in front of `Occupation::has`, `getHand`, `getPlayed` and `getFullPlayer`,
invalidated by the write methods (`play`, `discard`, `draw`, `use`, `saveFullPlayer`, `restoreState`).
Combined with `SELECT 1 ... LIMIT 1`, this is likely the largest single performance win available and it
touches no game logic. Also memoise the `getData*()` includes into static properties.

### R7 — Modularise the client (days: 3–5)

Turn the two big switches into a state → handler map:

```js
const stateHandlers = {
  'command-hunt':    { onEnter: hunt.onEnter, buttons: hunt.buttons },
  'command-upgrade': { onEnter: upgrade.onEnter, buttons: upgrade.buttons },
  ...
};
```

one file per family under `modules/states/`. Move the pixel offsets from `GoodsTarget.js` into
`modules/data/boards.php` so board geometry is defined once and shared with the server.

### R8 — Add PHPStan at a low level and raise it (days: 1 to start)

Level 1–2 will already find undefined methods and typos. The trait-heavy design will fight you at
higher levels; treat level 3+ as aspirational and use a baseline file so the existing debt doesn't block
new checks.

---

## 8. Small improvements

Low risk, high value, each independently shippable. Roughly in order of payoff-to-effort.

**Correctness / safety**

1. `Occupation::has` → `SELECT 1 FROM occupation WHERE ... LIMIT 1` (`class.occupation.php:328`).
2. Add a guard at the top of `restoreState`: if the `player_saved` row is missing or belongs to another
   player, log loudly and refuse rather than dereferencing NULL (`helper.undo.php:221`).
3. Scope the snapshot delete: `DELETE FROM player_saved WHERE player_id = $player_id`, so a foreign
   snapshot can't be silently clobbered (`helper.undo.php:17`).
4. Restore `checkAction` in `declareSuccess.php:6` and `selectAttackReward.php:6` by adding those
   actions to the `possibleactions` of the states that legitimately call them (1010, 1095).
5. Preserve `id` in the occupation undo restore, or add a comment explaining why it must not be
   (`helper.undo.php:174`) — see uncertainty #4 before changing.
6. Delete states 1011, 1066, 1096, 1097 and the `'raid'`/`'raider'` transitions — or, if the intent was
   a richer reward flow, add a comment saying so. Either is better than the current ambiguity.
7. Add an explicit failure when `finishedTransition` is empty in `doCommand`'s exit path rather than
   silently defaulting (`doCommand.php:49` already suspects this branch is dead — assert it).

**Clarity**

8. Fix the two inverted TODOs (`doCommand.php:54`, `selectMode.php:372`).
9. Add a header comment to `doCommand.php` pointing at `ACTION_FLOW.md`, and one above
   `Action::push`/`unshift` stating the ordering contract in one sentence each.
10. Comment the `$nextState = "next"` overwrite in the `doubleaction-124` branch as intentional.
11. Rename for navigability: `class.player.php` → `class.ffoplayer.php` (or rename the class),
    `helper.*.php` → `trait.*.php`.
12. Promote the commented-out queue tracer (`doCommand.php:17`) to a real debug flag —
    `if (self::DEBUG_COMMANDS) { ... }` — so it can be switched on in Studio without editing code.
13. Extract the duplicated `dice_types` map (`helper.state-functions.php:820` vs `:79`) into one
    constant.

**Performance**

14. Memoise `getDataBoards()` / `getDataGoods()` / `getDataActions()` / `getDataArtisanSheds()` into
    static properties — they re-`include` files of up to 5,444 lines on every call.
15. Pass `$player` into helpers instead of re-fetching with `getFullPlayer` (several `st*` functions
    fetch the same player two or three times in one pass).
16. Use the existing `$fields` parameter of `saveFullPlayer` at call sites that only touch resources —
    it exists and is used twice.
17. Make `Action::unshift` a single `UPDATE command_queue SET id = id + n` plus an insert, instead of
    read-all / delete-all / re-insert-all (`class.action.php:648`).

**Client**

18. Remove leftover `console.log` calls (`ffo.states.js:405-406`, `feastforodin.js:174-175`).
19. Replace `mobileAndTabletCheck()` with the `matchMedia` checks already used elsewhere in the file.
20. Record the vendored `anime.min.js` version in a comment or a manifest.

---

## 9. Suggested sequence

| When | Do |
|---|---|
| **This week** | R1 (Git). Small items 1–3, 8–12, 18–20. They're safe without tests. |
| **Next** | R2 (test harness) starting with the pure functions, then the DB seam. R6 (caching) — measurable, no logic change. |
| **Then** | R3 (command registry), migrating mechanical commands first. R5 alongside it. Resolve uncertainties 1–5 as you go; each has a cheap experiment attached. |
| **Ongoing** | R4 (occupation registry), a few cards at a time, gated by the replay tests. R8 baseline, raised gradually. |
| **When there's appetite** | R7 (client modularisation), which is the least risky of the large items because the server contract doesn't move. |

---

## 10. Closing assessment

This is a competent implementation of a very hard game, and the central design decision is sound. Its
problems are overwhelmingly the accumulated cost of growing a rules interpreter organically without a
safety net: knowledge duplicated in places that must agree, one function that absorbed everything that
didn't have a home, and per-card logic distributed by *timing* rather than by *card*.

None of that requires an architectural rewrite. It requires a seam for tests, a typed command
abstraction, and a registry that gives each of the 115 occupations one place to live. Do those three and
the codebase becomes ordinary maintenance work rather than archaeology.

Start with `git init`.
