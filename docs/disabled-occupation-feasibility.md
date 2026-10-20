# Disabled Occupations: Feasibility Findings

The occupation appendix documents every card, but seven abilities are explicitly filtered out during game setup in `modules/class.occupation.php:505-509`: Locksmith (28), Tutor (52), Farm Shop Owner (53), Arms Dealer (56), Nobleman (98), Meat Buyer (150), and Codliver Oil Presser (175). The filter disables them regardless of deck choice.

## Turn-control constraint

There are two distinct cases:

- **Player-initiated use or a trigger caused by that player's own action:** the player can generally make the choice during their turn. These do not inherently require interrupting another player's action.
- **A passive trigger that gives a player a choice during another player's action or a phase:** the game must route control to the card owner, resolve or decline the choice, and resume the interrupted flow.

The game has a `setplayer-<id>` command, handled by `changeActivePlayer()` in `modules/state-functions/doCommand.php:267-271`. It is used for some queued effects, including effects during the bonus phase (`modules/helper.state-functions.php:1489-1499`). This is not a general suspend/resume mechanism: each effect must explicitly arrange the right player and continuation. Existing flow documentation also notes that after some `setplayer` commands, the active player is left changed until the next phase resets it (`ACTION_FLOW.md`, “`setplayer` — an out-of-turn effect”).

`playOccupation` normally calls `checkAction`, while Tutor has a special path that permits playing outside that command state (`modules/player-actions/playOccupation.php:4-8, 27-31`). `useOccupation` checks that the caller is active (`modules/player-actions/useOccupation.php:5-10`). Restricting occupation plays to the active player can simplify card-play timing, but does not address later passive triggers. It would also house-rule the “at any time” text of Tutor, Farm Shop Owner, and Arms Dealer unless eligible multi-active phases are included.

## Per-occupation assessment

| ID | Occupation | Existing implementation and turn-control assessment | Feasibility |
|---:|---|---|---|
| 28 | Locksmith | The optional oil purchase is queued when chest goods are gained in `modules/state-functions/doCommand.php:429-434` and `modules/player-actions/selectGoods.php:382-387`. Bonus gains route the choice through `setplayer` in `modules/helper.state-functions.php:1489-1499`. Other chest-gain paths, including occupation effects, need consistent trigger coverage and correct player routing. | **Implementable; routing and trigger coverage need verification.** |
| 52 | Tutor | The play-from-hand UI and payment path already exist (`modules/Player.js:1120-1124`, `modules/player-actions/playOccupation.php:27-31`). This is initiated by the Tutor's owner, not automatically by another player's action. | **Implementable; mostly enable and test.** |
| 53 | Farm Shop Owner | The use UI and server-side validation/payment/upgrade are present (`modules/ffo.action.js:618-630`, `modules/player-actions/useOccupation.php:168-183`). It is an owner-initiated action, not a passive interruption. | **Implementable; mostly enable and test.** |
| 56 | Arms Dealer | The UI and server handling for choosing two weapons, discarding them, and gaining ore exist (`modules/ffo.action.js:618-631`, `modules/player-actions/useOccupation.php:223-268`). This is owner-initiated. | **Implementable; mostly enable and test.** |
| 98 | Nobleman | The appendix triggers this after grain is placed in a stone or long house. No implementation was found. The board-placement endpoint is `modules/player-actions/saveBoards.php`; the choice can likely be offered to the board owner when they place grain, subject to confirming which states permit placement. | **Implementable; new effect required.** |
| 150 | Meat Buyer | Existing optional exchanges cover some gains (`modules/state-functions/doCommand.php:437-443`, `modules/player-actions/declareSuccess.php:197-205`, `modules/player-actions/selectGoods.php:428-445`). The rule applies whenever two stockfish or salt meat are received, so every source must be checked; queues currently do not generally switch to the card owner. | **Implementable; complete trigger coverage and routing first.** |
| 175 | Codliver Oil Presser | Existing optional oil purchases cover some stockfish gains (`modules/player-actions/declareSuccess.php:146-150, 197-203`, `modules/player-actions/selectMode.php:514-518`, `modules/player-actions/selectGoods.php:404-410`). The appendix applies to any qualifying action gain, so other gain sources and cross-player effects need checking. | **Implementable; complete trigger coverage and routing first.** |

## Recommendation

Do not re-enable the seven by simply removing the setup filter. First:

1. Decide whether “at any time” occupations may be used during another player's turn. Restricting them to the owner's turn is simpler but changes their printed timing.
2. Keep owner-initiated uses separate from passive triggers. The existing turn checks make Tutor, Farm Shop Owner, and Arms Dealer much lower-risk than passive effects.
3. Audit all sources for Locksmith, Meat Buyer, and Codliver Oil Presser. For any trigger that can affect a non-active owner, queue the choice for that owner and explicitly resume the interrupted player/action afterward.
4. Implement Nobleman at the house-placement point and test it in every state where goods placement is allowed.
5. Add tests for out-of-turn rewards, multi-active phases, declining optional effects, and resuming the original action before removing the filter.

**Conclusion:** all seven are technically implementable. Three already have owner-initiated use flows, Nobleman needs a new trigger, and the three passive gain-trigger cards need complete source coverage plus safe out-of-turn choice routing before they can be enabled confidently.
