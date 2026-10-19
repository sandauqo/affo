/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * feastforodin implementation : © <Your name here> <Your email address here>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * feastforodin.js
 *
 * feastforodin user interface script
 * 
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

 define([
	"dojo",
	"dojo/_base/declare",
	"dojo/debounce",
	"ebg/core/gamegui",
	g_gamethemeurl + "modules/ffo.setup.js",
	g_gamethemeurl + "modules/ffo.states.js",
	g_gamethemeurl + "modules/ffo.notifications.js",
	g_gamethemeurl + "modules/ffo.action.js",
	g_gamethemeurl + "modules/ffo.i18n.js",
	"ebg/counter",
	g_gamethemeurl + "modules/anime.min.js",

	// Other
	"dojo/NodeList-traverse",

	// Classes
	g_gamethemeurl + "modules/Player.js",
	g_gamethemeurl + "modules/GoodsSource.js",
	g_gamethemeurl + "modules/GoodsTarget.js",
	g_gamethemeurl + "modules/GoodMatrix.js",
],
function (
    dojo,
	declare,
	debounce,
	gamegui,
	setup,
	states,
	notifications,
	action,
	i18n,
	counter,
	anime
) {
    return declare("bgagame.feastforodin", ebg.core.gamegui, {
        constructor: function(){
			this.interval = 0;
			this.confirmTimerSeconds = 0;
			
			this.debounce = debounce;
			this.anime = anime;
			this.goodsSources = [];
			this.goodsTargets = [];
			this.modalTargets = [];

			this.setup = setup.setup.bind(this);
			this.onResize = setup.onResize.bind(this);
			this.onEnteringState = states.onEnteringState.bind(this);
			this.onLeavingState = states.onLeavingState.bind(this);
			this.onUpdateActionButtons = states.onUpdateActionButtons.bind(this);
			this.updateHuntButtons = states.updateHuntButtons.bind(this);
			this.setupNotifications = notifications.setupNotifications.bind(this);

			this.i18n = i18n;
			
			this.onAction = action.onAction.bind(this);
			this.onPass = action.onPass.bind(this);
			this.onPassCheck = action.onPassCheck.bind(this);
			this.onPassVikings = action.onPassVikings.bind(this);
			this.onPassFeastCheck = action.onPassFeastCheck.bind(this);
			this.onPassEndGameCheck = action.onPassEndGameCheck.bind(this);
			this.onSaveBoardsAndPassEndGame = action.onSaveBoardsAndPassEndGame.bind(this);
			this.onAffirm = action.onAffirm.bind(this);
			this.onDeclareSuccess = action.onDeclareSuccess.bind(this);
			this.onDeclareFailure = action.onDeclareFailure.bind(this);
			this.onUndo = action.onUndo.bind(this);
			this.onReroll = action.onReroll.bind(this);
			this.onTopTrayClick = action.onTopTrayClick.bind(this);
			this.onSelectMode = action.onSelectMode.bind(this);
			this.onSelectGoods = action.onSelectGoods.bind(this);
			this.onSaveBoards = action.onSaveBoards.bind(this);
			this.onSaveBoardsAndPass = action.onSaveBoardsAndPass.bind(this);
			this.onSaveFeastBoardsAndPass = action.onSaveFeastBoardsAndPass.bind(this);
			this.onCancelBoards = action.onCancelBoards.bind(this);
			this.onBuyShip = action.onBuyShip.bind(this);
			this.onSetAutoPassAction = action.onSetAutoPassAction.bind(this);
			this.onEquipShip = action.onEquipShip.bind(this);
			this.onEquipBoard = action.onEquipBoard.bind(this);
			this.onUseOccupation = action.onUseOccupation.bind(this);
			this.onChooseVikingsNumber = action.onChooseVikingsNumber.bind(this);
			this.onSelectForSlaughter = action.onSelectForSlaughter.bind(this);
			this.onClickResource = action.onClickResource.bind(this);
			this.onSelectResources = action.onSelectResources.bind(this);
			this.onSelectWeapons = action.onSelectWeapons.bind(this);
			this.onClickWeapon = action.onClickWeapon.bind(this);
			this.onFlipArtisanShed = action.onFlipArtisanShed.bind(this);
			this.onClickOnceTwiceUpgradeGood = action.onClickOnceTwiceUpgradeGood.bind(this);
			this.onClickGoodUnique = action.onClickGoodUnique.bind(this);
			this.sendUseOccupationFromModal = action.sendUseOccupationFromModal.bind(this);
			this.onPlayOccupation = action.onPlayOccupation.bind(this);
			this.onGoBack = action.onGoBack.bind(this);
			this.onSelectPersonalResource = action.onSelectPersonalResource.bind(this);
			this.onDrawPersonalResource = action.onDrawPersonalResource.bind(this);
			this.onChooseBestOfTwoStartingOcc = action.onChooseBestOfTwoStartingOcc.bind(this);
			this.onBuildArtisanShed = action.onBuildArtisanShed.bind(this);
			this.onSelectArtisanShed = action.onSelectArtisanShed.bind(this);
        },
        mobileAndTabletCheck: function() {
			console.log('mobileAndTabletCheck');
			return window.matchMedia('(max-width: 700px), (any-pointer: coarse)').matches;
		},
		
		setLoader: function(imageProgress, logProgress) {
			this.inherited(arguments);
			
			if (imageProgress >= 100 && logProgress >= 100) {
				this.onResize();
			}
		},
		
		updateFirstPlayerDisplay: function() {
			const cpBoards = dojo.query('.cp_board');
			for (let i = 0; i < cpBoards.length; i++) {
				dojo.removeClass(cpBoards[i], 'ffo-first-player');
			}
			
			dojo.addClass($('cp_board_p' + this.gamedatas.first_player_id), 'ffo-first-player');
		},

		addGoodsSource: function (sourceDiv, soleTarget = null) {
			const source = new GoodsSource(this, sourceDiv, soleTarget);
			this.goodsSources.push(source);
			return source;
		},

		addGoodsTarget: function (targetDiv, information, me, player, selfOnly = false, originalTarget = null) {
			const target = new GoodsTarget(this, targetDiv, information, me, player, selfOnly, originalTarget);
			this.goodsTargets.push(target);
			return target;
		},
		
		hasUnsavedChanges: function() {
			// Unsaved changes iff there are any provisional goods in targets
			// Show "You have unsaved changes" message : Cancel / Save
			let unsavedChanges = false;
			for (const i in this.goodsTargets) {
				const target = this.goodsTargets[i];
				
				if (target.information.type == "homefeast") {
					if (! ["feast-placegoods", "command-personalfeast"].includes(this.gamedatas.gamestate.name)) {
						// Ignore changes on feast board if not in a feast phase
						continue;
					}
				}
				
				if (target.hasProvisional()) {
					unsavedChanges = true;
					break;
				}
			}
			return unsavedChanges;
		},

		hasAutopassActive: function() {
			console.log('checking ...')
			console.log(this.gamedatas);
		},
		
		updatePlacementButtons: function() {
			// Show only one of "Pass" and "Save and done"
			const e_pass = $('placement_pass_button');
			if (! e_pass) return;
			
			const e_save = $('save_and_done_button');
			if (! e_save) return;
			
			if (this.hasUnsavedChanges()) {
				dojo.style(e_pass, 'display', 'none');
				dojo.style(e_save, 'display', 'inline-block');
			} else {
				dojo.style(e_save, 'display', 'none');
				dojo.style(e_pass, 'display', 'inline-block');
			}
		},

		numPlayers: function () {
			return this.players.length;
		},

		playerId: function () {
			return this.player_id;
		},

		onShowModalIslands: function ( holder, islands ) {
			if (! holder) {
				// Show modal
				this.openModal(true, false);
				// Populate with forge!
				holder = $("ffo-modal-content");
			}
			
			holder.innerHTML = "";
			for (const i in this.gamedatas.islands) {
				const island = this.gamedatas.islands[i];
				if (islands && ! islands.includes(island.name)) {
					continue;
				}

				const norwegiansIslands = this.gamedatas.options.norwegians_expansion ? 'ffo-player-board-island-norwegians' : '';

				const placed = dojo.place(`<div class="ffo-player-board-island ${norwegiansIslands} ffo-player-board-island-${island.name}" data-island-name="${island.name}"></div>`, holder);
				
				// When you click flip, swap it for the other-side island!
				const otherSides = {
					'shetland': 'bearisland',
					'faroeislands': 'baffinisland',
					'iceland': 'labrador',
					'greenland': 'newfoundland',
					'lofoten': 'orkney',
					
					'bearisland': 'shetland',
					'baffinisland': 'faroeislands',
					'labrador': 'iceland',
					'newfoundland': 'greenland',
					'orkney': 'lofoten',

					'isleofman': 'limerick',
					'isleofskye': 'wexford',
					'outerhebrides': 'cork',
					'islay': 'waterford',

					'isleofmull': 'caithness',
				};
				if (otherSides[island.name]) {
					const flipButton = dojo.place(`<div class="ffo-rotate-button-holder">
						<div class="ffo-icon ffo-icon-rotate"></div>
					</div>`, placed);
				
					dojo.connect(flipButton, "onclick", (event) => {
						dojo.stopEvent( event );
						if (dojo.hasClass(placed, 'ffo-island-reverse')) {
							dojo.removeClass(placed, 'ffo-island-reverse');
							dojo.removeClass(placed, `ffo-player-board-island-${otherSides[island.name]}`);
							dojo.addClass(placed, `ffo-player-board-island-${island.name}`);
						} else {
							dojo.addClass(placed, 'ffo-island-reverse');
							dojo.removeClass(placed, `ffo-player-board-island-${island.name}`);
							dojo.addClass(placed, `ffo-player-board-island-${otherSides[island.name]}`);
						}
					});
				}

				if (+island.coins > 0) {
					// Put coins on top!
					const coinHolder = dojo.place('<div class="ffo-island-coin-holder"></div>', placed);
					
					for (let j = 0; j < +island.coins; j++) {
						dojo.place('<div class="ffo-icon ffo-good ffo-icon-coin"></div>', coinHolder);
					}
				}
			}
		},

		onShowModalForge: function ( holder, minorOnly = false ) {
			if (! holder) {
				// Show modal
				this.openModal(true, false);
				// Populate with forge!
				holder = $("ffo-modal-content");
			}
			
			holder.innerHTML = "";
			const norwegians = this.gamedatas.options.norwegians_expansion;
			const yule = this.gamedatas.options.yule_expansion;

			let forgePlaced = false;
			if (!minorOnly) {
				forgePlaced = dojo.place('<div class="ffo-forge"></div>', holder);
			}

			let forgeMinorPlaced = false;
			if (norwegians) {
				forgeMinorPlaced = dojo.place('<div class="ffo-forge ffo-forgeminor"></div>', holder);
			}
			
			let forgeYulePlaced = false;
			if (yule) {
				forgeYulePlaced = dojo.place('<div class="ffo-forge ffo-forge-yule"></div>', holder);
			}

			for (const i in this.gamedatas.forge) {
				const good = this.gamedatas.forge[i].name;
				const strength = this.gamedatas.forge[i].strength;
				const tongs = this.gamedatas.forge[i].tongs;
				if (['tree', 'table', 'sleigh', 'skates', 'skiis'].includes(good)) {
					dojo.place(
						`<div class="ffo-good ffo-good-norwegians ffo-good-${good}" data-rotation="0" data-flipped="0" data-good="${good}"><div class="ffo-good-image"></div></div>`,
						forgeYulePlaced
					);
				} else if (norwegians) { 
					if (+strength < 9 && +tongs) {
						dojo.place(
							`<div class="ffo-good ffo-good-norwegians ffo-good-${good}" data-rotation="0" data-flipped="0" data-good="${good}"><div class="ffo-good-image"></div></div>`,
							forgeMinorPlaced
						);
					} else if (!minorOnly) {
						dojo.place(
							`<div class="ffo-good ffo-good-norwegians ffo-good-${good}" data-rotation="0" data-flipped="0" data-good="${good}"><div class="ffo-good-image"></div></div>`,
							forgePlaced
						);
					}
				} else {
					dojo.place(
						`<div class="ffo-good ffo-good-${good}" data-rotation="0" data-flipped="0" data-good="${good}"><div class="ffo-good-image"></div></div>`,
						forgePlaced
					);
				}


			}

		},
		
		fitTopTray: function() {
			const tray = $('ffo-top-tray');
			let factor = 1;
			if (dojo.query('.ffo-forge', tray)) {
				// Scale forge until it fits
				factor = $('game_play_area').getBoundingClientRect().width / 821;
				if (factor > 1) {
					factor = 1;
				} else if (factor < 0.5) {
					factor = 0.5;
				}
			}
			// TODO : Sort something out for firefox?
			dojo.style(tray, 'zoom', factor);
		},
		onShowModalBoats: function () {
			// Show modal
			this.openModal(true, false);
			const modal = $("ffo-modal-content");
			modal.innerHTML = "";
			
			const holder = dojo.place(
				`<div class="ffo-boat-board"></div>`,
				modal
			);
			
			const ships = [];
			ships.push(dojo.place(
				`<div id="ffo-ship-modal-whalingboat" class="ffo-ship ffo-ship-whalingboat" data-ship-type="whalingboat"></div>`,
				holder
			));
			ships.push(dojo.place(
				`<div id="ffo-ship-modal-knarr" class="ffo-ship ffo-ship-knarr" data-ship-type="knarr"></div>`,
				holder
			));
			ships.push(dojo.place(
				`<div id="ffo-ship-modal-longship" class="ffo-ship ffo-ship-longship" data-ship-type="longship"></div>`,
				holder
			));
			for (const i in ships) {
				const ship = ships[i];
				//dojo.connect(ship, "onclick", this.onBuyShip);
			}
			
			const shipButtons = [];
			shipButtons.push(dojo.place(
				`<a href="#" id="ffo-ship-modal-whalingboat-buy" class="action-button bgabutton bgabutton_blue" data-ship-type="whalingboat">${_('Buy whaling boat')}
				<br>
				3 <div class="ffo-icon ffo-icon-coin"></div></a>`,
				holder
			));
			shipButtons.push(dojo.place(
				`<a href="#" id="ffo-ship-modal-knarr-buy" class="action-button bgabutton bgabutton_blue" data-ship-type="knarr">${_('Buy knarr')}
				<br>
				5 <div class="ffo-icon ffo-icon-coin"></div></a>`,
				holder
			));
			shipButtons.push(dojo.place(
				`<a href="#" id="ffo-ship-modal-longship-buy" class="action-button bgabutton bgabutton_blue" data-ship-type="longship">${_('Buy longship')}
				<br>
				8 <div class="ffo-icon ffo-icon-coin"></div></a>`,
				holder
			));
			for (const i in shipButtons) {
				const ship = shipButtons[i];
				dojo.connect(ship, "onclick", this.onBuyShip);
			}
			
			this.addTooltipHtml( 
				"ffo-ship-modal-whalingboat", 
				`<div class="ffo-ship-tooltip">
					<div class="ffo-ship ffo-ship-whalingboat"></div>
				</div>`, 
				0 );
			this.addTooltipHtml( 
				"ffo-ship-modal-knarr", 
				`<div class="ffo-ship-tooltip">
					<div class="ffo-ship ffo-ship-knarr"></div>
					<div class="ffo-ship ffo-ship-knarr-emigrated"></div>
				</div>`, 
				0 );
			this.addTooltipHtml( 
				"ffo-ship-modal-longship", 
				`<div class="ffo-ship-tooltip">
					<div class="ffo-ship ffo-ship-longship"></div>
					<div class="ffo-ship ffo-ship-longship-emigrated"></div>
				</div>`, 
				0 );
		},
		onShowModalMountainStrips: function ( holder ) {
			if (! holder) {
				// Show modal
				this.openModal(true, false);
				// Populate with forge!
				holder = $("ffo-modal-content");
			}
			
			holder.innerHTML = "";

			for (const i in this.gamedatas.mountainstrips) {
				const strip = this.gamedatas.mountainstrips[i];
				const placed = dojo.place(
					`<div class="ffo-mountain-strip ffo-mountain-strip-${strip.code}" data-strip-id="${strip.id}"><div class="ffo-mountain-strip-washout"></div></div>`,
					holder
				);
				// Place relevant resources on the strips
				const resources = strip.resources.split(',');
				for (let j = resources.length - 1; j >= 0; j--) {
					const r = resources[j];
					const index = resources.length - j - 1;
					// If coins, put two coins...
					if (r == 'coins') {
						dojo.place(
							`<div class="ffo-icon ffo-icon-coinpair" data-slot="${index}"></div>`,
							placed
						);
					} else if (r == 'orepair') {
						dojo.place(
							`<div class="ffo-orepair" data-slot="${index}">
								<div class="ffo-icon ffo-icon-ore"></div>
								<div class="ffo-icon ffo-icon-ore"></div>
							<div>`,
							placed
						);
					} else {
						dojo.place(
							`<div class="ffo-icon ffo-icon-${r}" data-slot="${index}"></div>`,
							placed
						);
					}
				}
			}
		},
		onShowModalGoods: function ( holder ) {
			if (! holder) {
				// Show modal
				this.openModal(true, false);
				holder = $("ffo-modal-content");
			}
			
			holder.innerHTML = `
			<table class="ffo-goods-tray-table">
				<tbody>
					<tr>
						<td><div class="ffo-good ffo-good-rune" data-rotation="0" data-flipped="0"><div class="ffo-good-image"></div></div></td>
						<td><div class="ffo-good ffo-good-silverware" data-rotation="0" data-flipped="0"><div class="ffo-good-image"></div></div></td>
						<td><div class="ffo-good ffo-good-chest" data-rotation="0" data-flipped="0"><div class="ffo-good-image"></div></div></td>
						<td><div class="ffo-good ffo-good-silk" data-rotation="0" data-flipped="0"><div class="ffo-good-image"></div></div></td>
					</tr>
					<tr>
						<td><div class="ffo-good ffo-good-oil" data-rotation="0" data-flipped="0"><div class="ffo-good-image"></div></div></td>
						<td><div class="ffo-good ffo-good-hide" data-rotation="0" data-flipped="0"><div class="ffo-good-image"></div></div></td>
						<td><div class="ffo-good ffo-good-wool" data-rotation="0" data-flipped="0"><div class="ffo-good-image"></div></div></td>
						<td><div class="ffo-good ffo-good-linen" data-rotation="0" data-flipped="0"><div class="ffo-good-image"></div></div></td>
					</tr>
					<tr>
						<td><div class="ffo-good ffo-good-mead" data-rotation="0" data-flipped="0"><div class="ffo-good-image"></div></div></td>
						<td><div class="ffo-good ffo-good-stockfish" data-rotation="0" data-flipped="0"><div class="ffo-good-image"></div></div></td>
						<td><div class="ffo-good ffo-good-milk" data-rotation="0" data-flipped="0"><div class="ffo-good-image"></div></div></td>
						<td><div class="ffo-good ffo-good-saltmeat" data-rotation="0" data-flipped="0"><div class="ffo-good-image"></div></div></td>
					</tr>
					<tr>
						<td><div class="ffo-good ffo-good-peas" data-rotation="0" data-flipped="0"><div class="ffo-good-image"></div></div></td>
						<td><div class="ffo-good ffo-good-flax" data-rotation="0" data-flipped="0"><div class="ffo-good-image"></div></div></td>
						<td><div class="ffo-good ffo-good-beans" data-rotation="0" data-flipped="0"><div class="ffo-good-image"></div></div></td>
						<td><div class="ffo-good ffo-good-grain" data-rotation="0" data-flipped="0"><div class="ffo-good-image"></div></div></td>
					</tr>
				</tbody>
			</table>
			
			<table class="ffo-goods-tray-table">
				<tbody>
					<tr>
						<td><div class="ffo-good ffo-good-spices" data-rotation="0" data-flipped="0"><div class="ffo-good-image"></div></div></td>
						<td><div class="ffo-good ffo-good-jewelry" data-rotation="0" data-flipped="0"><div class="ffo-good-image"></div></div></td>
						<td><div class="ffo-good ffo-good-treasurechest" data-rotation="0" data-flipped="0"><div class="ffo-good-image"></div></div></td>
						<td><div class="ffo-good ffo-good-silverhoard" data-rotation="0" data-flipped="0"><div class="ffo-good-image"></div></div></td>
					</tr>
					<tr>
						<td><div class="ffo-good ffo-good-skinandbones" data-rotation="0" data-flipped="0"><div class="ffo-good-image"></div></div></td>
						<td><div class="ffo-good ffo-good-fur" data-rotation="0" data-flipped="0"><div class="ffo-good-image"></div></div></td>
						<td><div class="ffo-good ffo-good-robe" data-rotation="0" data-flipped="0"><div class="ffo-good-image"></div></div></td>
						<td><div class="ffo-good ffo-good-clothing" data-rotation="0" data-flipped="0"><div class="ffo-good-image"></div></div></td>
					</tr>
					<tr>
						<td><div class="ffo-good ffo-good-gamemeat" data-rotation="0" data-flipped="0"><div class="ffo-good-image"></div></div></td>
						<td><div class="ffo-good ffo-good-sheep" data-rotation="0" data-flipped="0"><div class="ffo-good-image"></div></div></td>
						<td><div class="ffo-good ffo-good-whalemeat" data-rotation="0" data-flipped="0"><div class="ffo-good-image"></div></div></td>
						<td><div class="ffo-good ffo-good-cattle" data-rotation="0" data-flipped="0"><div class="ffo-good-image"></div></div></td>
					</tr>
					<tr>
						<td><div class="ffo-good ffo-good-cabbage" data-rotation="0" data-flipped="0"><div class="ffo-good-image"></div></div></td>
						<td></td>
						<td><div class="ffo-good ffo-good-fruits" data-rotation="0" data-flipped="0"><div class="ffo-good-image"></div></div></td>
						<td></td>
					</tr>
				</tbody>
			</table>
			`;
			if (this.gamedatas.options.norwegians_expansion) {
				holder.innerHTML += `
				<table class="ffo-goods-tray-table">
					<tbody>
						<tr>
							<td><div class="ffo-good ffo-good-tools" data-rotation="0" data-flipped="0"><div class="ffo-good-image"></div></div></td>
							<td><div class="ffo-good ffo-good-wadmal" data-rotation="0" data-flipped="0"><div class="ffo-good-image"></div></div></td>
						</tr>
						<tr>
							<td><div class="ffo-good ffo-good-antlers" data-rotation="0" data-flipped="0"><div class="ffo-good-image"></div></div></td>
							<td><div class="ffo-good ffo-good-leather" data-rotation="0" data-flipped="0"><div class="ffo-good-image"></div></div></td>
						</tr>
						<tr>
							<td><div class="ffo-good ffo-good-pig" data-rotation="0" data-flipped="0"><div class="ffo-good-image"></div></div></td>
							<td><div class="ffo-good ffo-good-horse" data-rotation="0" data-flipped="0"><div class="ffo-good-image"></div></div></td>
						</tr>
						<tr>
							<td><div class="ffo-good ffo-good-herbs" data-rotation="0" data-flipped="0"><div class="ffo-good-image"></div></div></td>
							<td></td>
						</tr>
					</tbody>
				</table>
				`;
			}
			if (this.gamedatas.options.harvest_expansion) {
				holder.innerHTML += `
				<table class="ffo-goods-tray-table">
					<tbody>
						<tr>
							<td><div class="ffo-good ffo-good-gullgubber"><div class="ffo-good-image"></div></div></td>
						</tr>
						<tr>
							<td><div class="ffo-good ffo-good-amber"><div class="ffo-good-image"></div></div></td>
						</tr>
						<tr>
							<td><div class="ffo-good ffo-good-eggs"><div class="ffo-good-image"></div></div></td>
						</tr>
						<tr>
							<td><div class="ffo-good ffo-good-berries"><div class="ffo-good-image"></div></div></td>
						</tr>
					</tbody>
				</table>
				`;
			}
		},
		onShowModalSheds: function ( holder ) {
			if (! holder) {
				// Show modal
				this.openModal(true, true, "no-mobile-flex");
				holder = $("ffo-modal-content");
			}
			
			holder.innerHTML = "";
			
			dojo.place(`<div class="ffo-player-board-building ffo-player-board-building-longhouse"><span class="ffo-num-remaining-building">${this.gamedatas.num_longhouses} / 5</span></div>`, holder);
			dojo.place(`<div class="ffo-player-board-building ffo-player-board-building-shed"><span class="ffo-num-remaining-building">${this.gamedatas.num_sheds} / 3</span></div>`, holder);
			dojo.place(`<div class="ffo-player-board-building ffo-player-board-building-stonehouse"><span class="ffo-num-remaining-building">${this.gamedatas.num_stonehouses} / 3</span></div>`, holder);
		},
		onShowVpTokens: function ( holder ) {
			if (! holder) {
				// Show modal
				this.openModal();
				holder = $("ffo-modal-content");
			}
			
			holder.innerHTML = "";
			
			dojo.place(`<div class="ffo-norwegians-icon ffo-icon-vptoken-four scaled"><span class="ffo-num-vp-token">${this.gamedatas.num_vp_token_4} / 2</span></div>`, holder);
			dojo.place(`<div class="ffo-norwegians-icon ffo-icon-vptoken-three scaled"><span class="ffo-num-vp-token">${this.gamedatas.num_vp_token_3} / 6</span></div>`, holder);
			dojo.place(`<div class="ffo-norwegians-icon ffo-icon-vptoken-two scaled"><span class="ffo-num-vp-token">∞</span></div>`, holder);
		},
		onShowModalPhases: function ( holder ) {
			if (! holder) {
				// Show modal
				this.openModal();
				holder = $("ffo-modal-content");
			}
			
			holder.innerHTML = "";
			
			// TODO : Put white cube on current phase?
			// Maybe tab could always show the correct phase?
			const placed = dojo.place('<div class="ffo-phase-board"></div>', holder);
		},
		onShowModalAutopass: function () {
			// Show modal
			this.openModal(true, false);
			const holder = $("ffo-modal-content");
			holder.innerHTML = "";
			
			// const holder = dojo.place(
			// 	`<div class="ffo-boat-board"></div>`,
			// 	modal
			// );

			const autopass = this.gamedatas.players[this.playerId()]['autopass'];
			const buttons = [];

			const wrapper = dojo.place('<div class="ffo-phase-board-wrapper"></div>', holder);

			buttons.push(dojo.place('<div class="ffo-phase-board ffo-phase-board-income" data-action-type="income"></div>', wrapper));
			buttons.push(dojo.place('<div class="ffo-phase-board ffo-phase-board-feast" data-action-type="feast"></div>', wrapper));
			buttons.push(dojo.place('<div class="ffo-phase-board ffo-phase-board-bonus" data-action-type="bonus"></div>', wrapper));

			for (const i in buttons) {
				dojo.connect(buttons[i], "onclick", this.onSetAutoPassAction);
			}
		},
		onShowModalOccupation: function( occupations, i ) {
			const numOccs = occupations.length;
			const o = occupations[i];
			// Show modal
			this.openModal();
			const holder = $("ffo-modal-content");
			holder.innerHTML = "";
			
			const occText = this.i18n.getOcc(o.ability);
			
			// Put the occ inside, with some text!
			const placed = dojo.place('<div class="ffo-occupation-zoom"></div>', holder);
			const inner = dojo.place(`<div class="ffo-occupation-zoom-inner">
			</div>`, placed);
			
			dojo.place(`<div class="ffo-card ffo-card-occ-${o.ability}">
			</div>`, inner);
			
			dojo.place(`<div>
				<h3>${occText.name}</h3>
				<p>${occText.desc}</p>
			</div>`, placed);
			
			if (numOccs > 1) {
				const previousArrow = dojo.place(`<div class="ffo-occupation-zoom-arrow">
					&lt;
				</div>`, inner, 'first');
				const nextArrow = dojo.place(`<div class="ffo-occupation-zoom-arrow">
					&gt;
				</div>`, inner, 'last');
				dojo.connect(nextArrow, "onclick", (e) => {
					dojo.stopEvent( e );
					this.onShowModalOccupation( occupations, (i + 1) % numOccs );
				});
				dojo.connect(previousArrow, "onclick", (e) => {
					dojo.stopEvent( e );
					this.onShowModalOccupation( occupations, (i + numOccs - 1) % numOccs );
				});
			}
		},
		onShowModalArtisanShed: function( shed, fragment ) {
			// Show modal
			this.openModal(true, true, "no-mobile-flex");
			const holder = $("ffo-modal-content");
			holder.innerHTML = "";
			
			dojo.place(fragment, holder);
		},
		onShowModalScores: function( holder ) {
			if (! holder) {
				// Show modal
				this.openModal(true, false);
				holder = $("ffo-modal-content");
			}
			
			let playerCols = '';
			
			const scoreCats = [
				'',
				'ships', 
				'emigrations', 
				'exploration_boards',
				'sheds_and_houses',
				'sheep_and_cattle',
				'occupations',
				'silver',
				'english_crown',
				'',
				'neg_home_board',
				'neg_exploration_boards',
				'neg_sheds_and_houses',
				'neg_thing_penalty',
				'total'
			];
			const scoreCatsText = [
				'<b>' + _('Positives') + '</b>',
				_('Ships'), 
				_('Emigrations'), 
				_('Exploration boards'),
				_('Sheds and houses'),
				_('Animals'),
				_('Occupations'),
				_('Silver'),
				_('English Crown'),
				'<b>' + _('Negatives') + '</b>',
				_('Home board'),
				_('Exploration boards'),
				_('Sheds and houses'),
				_('Thing penalty'),
				'<b>' + _('Total') + '</b>'
			];
			const scoreCols = [];
			for (let j = 0; j < scoreCats.length; j++) {
				scoreCols.push('');
			}
			
			for (let playerId in this.playersById) {
				const player = this.playersById[playerId];
				playerCols += `<th>${player.name}</th>`;
				
				for (let j = 0; j < scoreCats.length; j++) {
					if (scoreCats[j]) {
						scoreCols[j] += `<td>${player.playerObj.score_breakdown[scoreCats[j]]}</td>`;
					} else {
						scoreCols[j] += `<td></td>`;
					}
				}
			}
			
			let scoreRows = '';
			for (let j = 0; j < scoreCats.length; j++) {
				let extra = '';
				if (scoreCats[j] == '') {
					extra = ' style="border-top: 1px solid black"';
				} else if (scoreCats[j] == 'total') {
					extra = ' style="border-top: 3px double black"';
				}
				scoreRows += `<tr${extra}>
					<td class="ffo-score-category">${scoreCatsText[j]}</td>
					${scoreCols[j]}
				</tr>`;
			}
			
			const table = `<table class="ffo-score-table">
				<thead>
					<tr>
						<th></th>
						${playerCols}
					</tr>
				</thead>
				<tbody>
					${scoreRows}
				</tbody>
			</table>`;
			
			// Add score table!
			holder.innerHTML = table;
		},
		updateMobileControls: function() {
			// Iff marked good is flippable, show flip button
			let showFlippable = false;
			if (GoodsSource.marked) {
				const asset = GoodsSource.marked.information.type;
				showFlippable = ['horn', 'helmet', 'cane', 'pan', 'sickle', 'anvil', 'sleigh', 'skiis'].includes(asset);
			}
			
			dojo.query('.ffo-flip-button').style('display', showFlippable ? 'inline-block' : 'none');
		},
		onShowModalGoodsTarget: function ( goodsTarget ) {
			if (GoodsSource.marked) {
				// Remove any crosshair from the old good
				dojo.removeClass(GoodsSource.marked, 'ffo-marked');
				dojo.query(".ffo-icon-crosshair", GoodsSource.marked).remove();
				if (GoodsSource.marked.placedNode) {
					dojo.query(".ffo-icon-crosshair", GoodsSource.marked.placedNode).remove();
				}
				GoodsSource.marked = null;
				this.updateMobileControls();
			}
			
			const player = this.playersById[this.playerId()];
			this.modalTargets = [];
			// Show modal
			this.openModal(false, true);
			const holder = $("ffo-modal-content");
			holder.innerHTML = "";
			
			// Buttons
			const buttonsRow = dojo.place('<div class="ffo-placement-modal-buttons"></div>', holder);
			// Save and discard buttons
			const closeBtn = dojo.place(`<a href="#" class="action-button bgabutton bgabutton_blue" onclick="return false;">${_('Close')}</a>`, buttonsRow);
			const saveBtn = dojo.place(`<a href="#" class="action-button bgabutton bgabutton_blue" onclick="return false;">${_('Save')}</a>`, buttonsRow);
			const discardBtn = dojo.place(`<a href="#" class="action-button bgabutton bgabutton_red" onclick="return false;">${_('Discard')}</a>`, buttonsRow);
			const noSaveMessage = dojo.place(`<div><i>${_('You can only save the Feast board during the Feast phase.')}</i></div>`, buttonsRow);
			
			if (goodsTarget.information.type == "homefeast" && ! ['feast-placegoods', 'command-personalfeast'].includes(this.gamedatas.gamestate.name)) {
				dojo.style(saveBtn, {display: 'none'});
			} else {
				dojo.style(noSaveMessage, {display: 'none'});
				dojo.style(closeBtn, {display: 'none'});
			}
			
			// Show board + valid goods
			const placed = dojo.place('<div class="ffo-placement-modal"></div>', holder);
			const targetHolder = dojo.place('<div class="ffo-placement-modal-target"></div>', placed);
			
			const sourceHolder = dojo.place('<div class="ffo-placement-modal-source"></div>', placed);
			const sourceResourceHolder = dojo.place('<div class="ffo-placement-modal-resource-source"><table class="ffo-main-resources"><tbody><tr></tr></tbody></table></div>', sourceHolder);
			const sourceGoodHolder = dojo.place('<div class="ffo-placement-modal-good-source ffo-goods-row"></div>', sourceHolder);
			
			const sourceTransformsHolder = dojo.place('<div class="ffo-placement-modal-good-transforms"></div>', sourceHolder);
			if (! this.options.mobileControls) {
				sourceTransformsHolder.style.display = 'none';
			}
			
			const rotateButton = dojo.place('<a href="#" class="action-button bgabutton bgabutton_blue" onclick="return false;"><div class="ffo-icon ffo-icon-rotate-white"></div></a>', sourceTransformsHolder);
			const flipButton = dojo.place('<a href="#" class="action-button bgabutton bgabutton_blue ffo-flip-button" onclick="return false;"><div class="ffo-icon ffo-icon-resize-white"></div></a>', sourceTransformsHolder);
			const cancelButton = dojo.place('<a href="#" class="action-button bgabutton bgabutton_blue" onclick="return false;"><div class="ffo-icon ffo-icon-cancel"></div></a>', sourceTransformsHolder);
			
			flipButton.style.display = 'none';
			
			const sourceResourceRow = dojo.query('tr', sourceResourceHolder)[0];

			let boardDiv = null;
			if (['!shed', 'stonehouse', 'longhouse'].includes(goodsTarget.information.category)) {
				// try to clone
				const index = goodsTarget.targetDiv.getAttribute('data-board-index');
				const board = player.getBoardByBoardIndex(index);
				boardDiv = dojo.place(dojo.clone(board), targetHolder);
				dojo.addClass(boardDiv, "ffo-board-zoomed");
			} else {
				boardDiv = dojo.place(`<div></div>`, targetHolder);
				dojo.addClass(boardDiv, goodsTarget.targetDiv.className);
				dojo.addClass(boardDiv, "ffo-board-zoomed");
				// Home or feast, cut it off!
				if (goodsTarget.information.type == "home") {
					dojo.addClass(boardDiv, 'ffo-home-zoomed');
				} else if (goodsTarget.information.type == "homefeast") {
					dojo.addClass(boardDiv, 'ffo-homefeast-zoomed');
					// Feast needs to have meeples (to show current round)
					const feastMeepleHolder = player.feastMeepleHolder.cloneNode(true);
					dojo.style(feastMeepleHolder, {
						left: "12px",
						top: "13px",
					});
					const newFeastMeepleHolder = dojo.place(feastMeepleHolder, boardDiv);
					// Remove all meeple ids from this one!
					const newMeeples = dojo.query('.ffo-icon-meeple', newFeastMeepleHolder);
					for (let i = 0; i < newMeeples.length; i++) {
						newMeeples[i].removeAttribute('data-meeple-id');
					}
					
					// Same for emigrations
					const oldShipsHolder = dojo.query('.ffo-feast-ship-holder', player.mainBoard)[0];
					const feastShipsHolder = oldShipsHolder.cloneNode(true);
					dojo.style(feastShipsHolder, {
						left: "12px",
						top: "13px",
					});
					dojo.place(feastShipsHolder, boardDiv);
				}
			}

			if (['spinnery', 'beekeeper', 'pigsty', 'icehouse', 'hunting'].includes(goodsTarget.information.type)) {
				const index = goodsTarget.targetDiv.getAttribute('data-board-index');
				const buildingGoods = dojo.query('.ffo-building-good', goodsTarget.targetDiv);
				for (let i = 0; i < buildingGoods.length; i++) {
					const equipped = buildingGoods[i].getAttribute('data-equipped');
					const resourceSlot = dojo.place(`<div class="ffo-building-good ffo-building-${goodsTarget.information.standalone[i]['item']}" data-board-index="${index}" data-slot="0" data-equipped="${equipped}"></div>`, boardDiv);
					dojo.connect(resourceSlot, "onclick", this.onEquipBoard);
				}	
			}

			if (['lumberjack', 'mason', 'armoury', 'shed', 'stonehouse'].includes(goodsTarget.information.type)) {
				const index = goodsTarget.targetDiv.getAttribute('data-board-index');
				const buildingGoods = dojo.query('.ffo-building-resource', goodsTarget.targetDiv);
				for (let i = 0; i < buildingGoods.length; i++) {
					const equipped = buildingGoods[i].getAttribute('data-equipped');
					const resourceSlot = dojo.place(`<div class="ffo-building-resource ffo-building-${goodsTarget.information.standalone[i]['item']}" data-board-index="${index}" data-slot="0" data-equipped="${equipped}"></div>`, boardDiv);
					dojo.connect(resourceSlot, "onclick", this.onEquipBoard);
				}	
			}

			const newTarget = this.addGoodsTarget(boardDiv, JSON.parse(JSON.stringify(goodsTarget.information)), true, player, true, goodsTarget);
			
			newTarget.zoomOverride = 1;
			newTarget.temporary = true;
			newTarget.linkedSource.zoomOverride = newTarget.zoomOverride;
			newTarget.linkedSource.temporary = true;

			this.modalTargets.push(newTarget);
			this.modalTargets.push(newTarget.linkedSource);

			// Source

			const draggingGoods = [];
			{
				// Goods (only relevant ones)
				const goodsRow = player.goodsRow;
				const goods = dojo.query(".ffo-good", goodsRow);
				for (let i = 0; i < goods.length; i++) {
					const good = goods[i].cloneNode(true);
					good.information = JSON.parse(JSON.stringify(goods[i].information));
					if (! goodsTarget.information.allowedColours.includes(good.information.colour)) {
						continue;
					}
					dojo.place(good, sourceGoodHolder);
					if (dojo.hasClass(good, "ffo-dragging")) {
						draggingGoods.push(good);
					}
				}

				if (goodsTarget.information.type == "whaling") {
					whalingBoats = dojo.query(".ffo-ship-whalingboat", player.mainBoard);
					for (let i = 0; i < whalingBoats.length; i++) {
						const boat = whalingBoats[i].cloneNode(true);
						dojo.addClass(boat, 'ffo-good');
						dojo.place(boat, sourceGoodHolder);
						draggingGoods.push(boat);
					}

				}

				const gs = this.addGoodsSource(sourceHolder, newTarget);
				gs.temporary = true;
				gs.zoomOverride = newTarget.zoomOverride;
				this.modalTargets.push(gs);
				
				// Resources
				const relevantResources = [];
				for (let i in goodsTarget.information.allowedColours) {
					const type = goodsTarget.information.allowedColours[i];
					if (GoodsSource.isGoodResource(type)) {
						relevantResources.push(type);
					}
				}
				if (player.hasOcc(40) && ["stonehouse", "longhouse"].includes(goodsTarget.information.type)) {
					// Allow wood
					relevantResources.push("wood");
				}

				for (let i in relevantResources) {
					const resource = relevantResources[i];
					dojo.place(`<td>
						<div class="ffo-icon ffo-good ffo-icon-${resource}"></div>
						<span class="ffo-num-${resource}-p${player.playerId}"></span>
					</td>`, sourceResourceRow);
				}

				player.updateResourceDisplay();
			}
			
			// Target
			{
				const goods = dojo.query(".ffo-good", goodsTarget.dropDiv);
				for (let i = 0; i < goods.length; i++) {
					const good = goods[i].cloneNode(true);
					good.information = JSON.parse(JSON.stringify(goods[i].information));
					const provisional = dojo.hasClass(goods[i], "ffo-provisional");
					const isResource = GoodsSource.isGoodResource(good.information.type);
					
					newTarget.addGood(
						good, 
						good.information.coords, 
						false, 
						provisional);
					if (provisional) {
						let sourceNode = null;
						if (isResource) {
							sourceNode = dojo.query('.ffo-icon-' + good.information.type, sourceResourceRow)[0];
						} else {
							for (let j = 0; j < draggingGoods.length; j++) {
								const dg = draggingGoods[j];

								//whalingboat
								if (dg.getAttribute('data-ship-id') &&
									dg.getAttribute('data-ship-id') == good.getAttribute('data-ship-id')) {
									sourceNode = dg;
									draggingGoods.splice(j, 1);
									break;
								}

								if (dg.information.type == good.information.type) {
									sourceNode = dg;
									draggingGoods.splice(j, 1);
									break;
								}
							}
						}
						if (sourceNode) {
							good.sourceNode = sourceNode;
							if (! isResource) {
								sourceNode.placedNode = good;
							}
						} else {
							console.error("No sourceNode found for provisional. Going to cause problems!");
						}
					}
				}
			}
			
			// TODO : Pick and apply scale to modal which is as big as possible
			dojo.connect(closeBtn, "onclick", (e) => {
				// Close the dialog only
				this.onCloseModalGoodsTarget( newTarget, goodsTarget );
			});
			dojo.connect(saveBtn, "onclick", (e) => {
				// Copy the changes
				this.onCloseModalGoodsTarget( newTarget, goodsTarget );
				// Save only the one board
				this.onSaveBoards( e, goodsTarget, () => {
					this.updatePlacementButtons();
				} );
			});
			dojo.connect(discardBtn, "onclick", (e) => {
				// Copy the changes
				this.onCloseModalGoodsTarget( newTarget, goodsTarget );
				// Cancel the one board
				this.onCancelBoards( e, goodsTarget );
				this.updatePlacementButtons();
			});
			dojo.connect(rotateButton, "onclick", this.rotateMarked.bind(this));
			dojo.connect(flipButton, "onclick", this.flipMarked.bind(this));
			dojo.connect(cancelButton, "onclick", this.cancelMarked.bind(this));

			// Used by onClickModal only!
			this.currentNewTarget = newTarget;
			this.currentGoodsTarget = goodsTarget;

			this.fitModal();
		},
		onShowModalFarmShopOwner: function (occupationId) {
			this.openModal(false, false, "", true);
			
			const holder = $("ffo-modal-content");
			holder.innerHTML = "";
			
			const wrapper = dojo.place('<div class="occupation-modal-wrapper"></div>', holder);

			const player = this.playersById[this.playerId()];

			const goodsRow = dojo.place(`<div class="ffo-occupation-modal occupation-modal"></div>`, wrapper);

			for (const good of player.playerObj.goods) {
				if (good.colour == "orange") {
					let placedGood = dojo.place(
						`<div class="ffo-good ffo-good-${good.type}" data-rotation="0" data-flipped="0" data-good="${good.type}">
							<div class="ffo-good-image"></div>
						</div>`,
						goodsRow
					);
					dojo.connect(placedGood, "onclick", (e) => {this.onClickGoodUnique(e)});
				}
			}

			const buttonsRow = dojo.place('<div class="ffo-placement-modal-buttons modal-small"></div>', wrapper);
			const upgradeBtn = dojo.place(`<a href="#" class="action-button bgabutton bgabutton_blue" onclick="return false;">${_('Upgrade')}</a>`, buttonsRow);
			const closeBtn = dojo.place(`<a href="#" class="action-button bgabutton bgabutton_blue" onclick="return false;">${_('Close')}</a>`, buttonsRow);
			

			dojo.connect(closeBtn, "onclick", (e) => {this.onHideModal()});
			dojo.connect(upgradeBtn, "onclick", (e) => {this.sendUseOccupationFromModal(occupationId), this.onHideModal()});
		},
		onShowModalArmsDealer: function (occupationId) {
			this.openModal(false, false, "", true);
			
			const holder = $("ffo-modal-content");
			holder.innerHTML = "";
			
			const wrapper = dojo.place('<div class="occupation-modal-wrapper"></div>', holder);

			const weaponsRow = dojo.place('<div class="ffo-occupation-modal"></div>', wrapper);

			const player = this.playersById[this.playerId()];

			for (const i in player.playerObj.weapons) {
				const w = player.playerObj.weapons[i];
				const placedWeapon = dojo.place(`<div class="ffo-card ffo-card-weapon ffo-card-weapon-${w.type}" data-resource-type="${w.id}"></div>`, weaponsRow);
				dojo.connect(placedWeapon, "onclick", (e) => {this.onClickWeapon(e)});
			}

			const buttonsRow = dojo.place('<div class="ffo-placement-modal-buttons modal-small"></div>', wrapper);
			const exchangeBtn = dojo.place(`<a href="#" class="action-button bgabutton bgabutton_blue" onclick="return false;">${_('Exchange')}</a>`, buttonsRow);
			const closeBtn = dojo.place(`<a href="#" class="action-button bgabutton bgabutton_blue" onclick="return false;">${_('Close')}</a>`, buttonsRow);
			

			dojo.connect(closeBtn, "onclick", (e) => {this.onHideModal()});
			dojo.connect(exchangeBtn, "onclick", (e) => {this.sendUseOccupationFromModal(occupationId), this.onHideModal()});
		},
		onShowModalTutor: function (occupationId) {
			this.openModal(false, false, "", true);
			
			const holder = $("ffo-modal-content");
			holder.innerHTML = "";
			
			const wrapper = dojo.place('<div class="occupation-modal-wrapper"></div>', holder);

			const occupationsRow = dojo.place('<div class="ffo-occupation-modal"></div>', wrapper);

			const player = this.playersById[this.playerId()];

			for (const i in player.playerObj.occupationsHand) {
				const o = player.playerObj.occupationsHand[i];
					const occupationWrapper = dojo.place('<div class="ffo-cards-container-item occupation-card">', occupationsRow);
					const cardPlaced = dojo.place(`<div class="ffo-card ffo-card-occ-${o.ability}" data-occupation-id="${o.id}" data-mode-id="0"></div>`, occupationWrapper);
					dojo.connect(cardPlaced, "onclick", (e) => {this.onPlayOccupation(e, true), this.onHideModal()});
				if (this.gamedatas.options.norwegians_expansion) {
					let token = null;
					const token4 = this.gamedatas.num_vp_token_4;
					const token3 = this.gamedatas.num_vp_token_3;
					if (token4 > 0) {
						token = 'four';
					} else if (token3 > 0) {
						token = 'three';
					} else {
						token = 'two';
					}
					const tokenPlaced = dojo.place(`<div class="ffo-card ffo-norwegians-icon ffo-icon-vptoken-${token} scaled" data-occupation-id="${o.id}" data-mode-id="1"></div>`, occupationWrapper);
					dojo.connect(tokenPlaced, "onclick", (e) => {this.onPlayOccupation(e, true), this.onHideModal()});
				}	
			}

			const buttonsRow = dojo.place('<div class="ffo-placement-modal-buttons modal-small"></div>', holder);
			const closeBtn = dojo.place(`<a href="#" class="action-button bgabutton bgabutton_blue" onclick="return false;">${_('Close')}</a>`, buttonsRow);

			dojo.connect(closeBtn, "onclick", (e) => {this.onHideModal()});
		},
		rotateMarked: function() {
			if (GoodsSource.marked) {
				const target = GoodsSource.marked;
				const eventTarget = GoodsSource.marked.placedNode;
				const asset = target.information.type;
				if (! GoodsSource.isGoodResource(asset)) {
					// Can only rotate assets
					// Mostly copied from GoodsSource
					target.information.rotation = (target.information.rotation + 1) % 4;
					dojo.setAttr(target, 'data-rotation', target.information.rotation);
					if (eventTarget) {
						eventTarget.information.rotation = target.information.rotation;
						dojo.setAttr(eventTarget, 'data-rotation', target.information.rotation);
						eventTarget.dropDiv.addGood(eventTarget, eventTarget.information.coords, false);
						eventTarget.dropDiv.checkProvisionals();
					}
					if (target.information.type !== 'sleigh') {
						const matrix = new GoodMatrix(target.information); 
						const clipPath = matrix.generateClipPath();
						dojo.style(target, 'clip-path', clipPath);
						dojo.style(eventTarget, 'clip-path', clipPath);
					}
				}
			}
		},
		flipMarked: function() {
			if (GoodsSource.marked) {
				const target = GoodsSource.marked;
				const eventTarget = GoodsSource.marked.placedNode;
				const asset = target.information.type;
				const flippable = ['horn', 'helmet', 'cane', 'pan', 'sickle', 'anvil', 'sleigh', 'skiis'].includes(asset);
				if (flippable) {
					// Can only flip certain special tiles
					// Mostly copied from GoodsSource
					target.information.flipped = !target.information.flipped;
					dojo.setAttr(target, 'data-flipped', target.information.flipped ? 1: 0);
					// If flipping on rotation 1 or 3, also rotate twice
					// This ensures that flipping is always on a vertical plane
					if (target.information.rotation == 1 || target.information.rotation == 3) {
						target.information.rotation = (target.information.rotation + 2) % 4;
						dojo.setAttr(target, 'data-rotation', target.information.rotation);
					}
					if (eventTarget) {
						eventTarget.information.flipped = target.information.flipped;
						dojo.setAttr(eventTarget, 'data-flipped', target.information.flipped ? 1: 0);
						eventTarget.information.rotation = target.information.rotation;
						dojo.setAttr(eventTarget, 'data-rotation', target.information.rotation);
						// Flipping can change overlaps!
						eventTarget.dropDiv.checkProvisionals();
					}

					if (target.information.type !== 'sleigh') {
						const matrix = new GoodMatrix(target.information); 
						const clipPath = matrix.generateClipPath();
						dojo.style(target, 'clip-path', clipPath);
						dojo.style(eventTarget, 'clip-path', clipPath);
					}
				}
			}
		},
		cancelMarked: function() {
			if (GoodsSource.marked) {
				const target = GoodsSource.marked;
				const eventTarget = GoodsSource.marked.placedNode;
				const asset = target.information.type;
				if (GoodsSource.isGoodResource(asset)) {
					if (target && ! target.placedNode) {
						GoodsSource.returnToSource(target);
						target.dropDiv.checkProvisionals();
					}
					// Now remove mark!
					// Remove any crosshair from the old good
					dojo.query(".ffo-icon-crosshair", GoodsSource.marked).remove();
					if (GoodsSource.marked.placedNode) {
						dojo.query(".ffo-icon-crosshair", GoodsSource.marked.placedNode).remove();
					}
					GoodsSource.marked = null;
					this.updateMobileControls();
				} else {
					if (eventTarget) {
						GoodsSource.returnToSource(eventTarget);
						eventTarget.dropDiv.checkProvisionals();
						if (target) {
							target.placedNode = null;
						}
					}
				}
			}
		},
		onCloseModalGoodsTarget: function( newTarget, originalTarget ) {
			if (GoodsSource.marked) {
				// Remove any crosshair from the old good
				dojo.query(".ffo-icon-crosshair", GoodsSource.marked).remove();
				dojo.removeClass(GoodsSource.marked, 'ffo-marked');
				if (GoodsSource.marked.placedNode) {
					dojo.query(".ffo-icon-crosshair", GoodsSource.marked.placedNode).remove();
				}
				GoodsSource.marked = null;
				this.updateMobileControls();
			}

			this.modalTargets = [];
			this.currentNewTarget = null;
			this.currentGoodsTarget = null;
			const player = this.playersById[this.playerId()];
			
			// Remove all provisionals from original
			{
				const provisionals = dojo.query(".ffo-good.ffo-provisional", originalTarget.dropDiv);
				for (let i = 0; i < provisionals.length; i++) {
					const provisional = provisionals[i];
					GoodsSource.returnToSource(provisional, false, false);
				}
			}
			
			// Add all provisionals from new
			{
				const provisionals = dojo.query(".ffo-good.ffo-provisional", newTarget.dropDiv);
				for (let i = 0; i < provisionals.length; i++) {
					const provisional = provisionals[i];
					
					const isResource = GoodsSource.isGoodResource(provisional.information.type);
					
					// Match up with a source good...
					if (isResource) {
						// ...?
						const good = provisional.cloneNode(true);
						good.information = provisional.information;
						
						originalTarget.addGood(
							good, 
							good.information.coords, 
							false);
							
						const sourceNode = dojo.query('.ffo-icon-' + good.information.type, $('player_board_' + player.playerId))[0];
						good.sourceNode = sourceNode;
					} else {
						// deal with whaling boat exeption
						if (provisional.getAttribute('data-ship-type') == 'whalingboat') {
							shipsRow = player.whalingBoatRow;
							ships = dojo.query(".ffo-ship", shipsRow);

							for (let j = 0; j < ships.length; j++) {
								const target = ships[j];
								if (ships[j].getAttribute('data-ship-id') == provisional.getAttribute('data-ship-id')) {
									provisional.information.id = +provisional.getAttribute('data-ship-id');
									target.information = provisional.information;
									
									const draggee = target.cloneNode(true);
									draggee.information = provisional.information;
									draggee.sourceNode = target;
									draggee.id = '';
									dojo.addClass(draggee, 'ffo-good');
									// TODO: what do we do with original? 
									dojo.addClass(target, 'ffo-dragging');
									originalTarget.addGood(draggee, [0, 0], false);
									break;
								}
							}
						} else {
							const goodsRow = player.goodsRow;
							const goods = dojo.query(".ffo-good", goodsRow);

							for (let j = 0; j < goods.length; j++) {
								const target = goods[j];
								if (dojo.hasClass(target, "ffo-dragging"))
									continue;
								if (goods[j].information.type == provisional.information.type) {
									// Copy information over
									dojo.setAttr(target, 'data-flipped', provisional.information.flipped ? 1: 0);
									dojo.setAttr(target, 'data-rotation', provisional.information.rotation);
									target.information = provisional.information;
									
									const draggee = target.cloneNode(true);
									
									draggee.information = provisional.information;
									draggee.sourceNode = target; //debug;
									target.placedNode = draggee;
									draggee.id = ''; // Remove id so as not to have duplicate ids
									dojo.removeClass(draggee, 'ffo-dragging');
									dojo.addClass(draggee, 'ffo-draggee');
									dojo.addClass(target, 'ffo-dragging');
									
									// Place good
									originalTarget.addGood(draggee, provisional.information.coords, false);
									break;
								}
							}

						}


					}
				}
			}
			
			// Remove all temporary sources/targets
			{
				for (let i = this.goodsSources.length - 1; i >= 0; i--) {
					if (this.goodsSources[i].temporary) {
						this.goodsSources.splice(i, 1);
					}
				}
				for (let i = this.goodsTargets.length - 1; i >= 0; i--) {
					if (this.goodsTargets[i].temporary) {
						this.goodsTargets.splice(i, 1);
					}
				}
			}
		

			this.onHideModal();
		},
		
		onClickModal: function ( event ) {
			if (dojo.hasClass($("ffo-modal"), "ffo-modal-autoclose")) {
				// Need to find a better way to do this!
				this.onHideModal();
			} else if (event.target == event.currentTarget && this.currentNewTarget) {
				// If this is the goods modal, we should close that...
				this.onCloseModalGoodsTarget( this.currentNewTarget, this.currentGoodsTarget );
			}
		},
		
		fitModal: function() {
			// Placement modals
			const pms = dojo.query('.ffo-placement-modal-target');
			for (let i = 0; i < pms.length; i++) {
				const pm = pms[i];
				
				let factor = 1;
				
				const bz = dojo.query('.ffo-board-zoomed', pm);
				if (bz.length > 0) {
					const w = bz[0].offsetWidth;
					const aw = window.innerWidth * 0.8 - 40;
					let f = aw / w;
					if (f > 1)
						f = 1;
					else if (f < 0.5)
						f = 0.5;
					factor = f;
				}
				
				dojo.style(pm, 'transform', 'scale(' + factor + ')');
				
				// Set the zoomLevel
				for(let j in this.modalTargets) {
					this.modalTargets[j].zoomOverride = factor;
				}
				break;
			}
		},
		
		openModal: function(autoClose = true, flex = true, customClass = "", small = false) {
			const scrollDistance = window.scrollY;
			
			dojo.style(document.body, "top", (scrollDistance * -1) + 'px');
			if (flex) {
				dojo.addClass($("ffo-modal"), "ffo-modal-flex");
			} else {
				dojo.removeClass($("ffo-modal"), "ffo-modal-flex");
			}
			if (small) {
				dojo.addClass($("ffo-modal-content"), "ffo-modal-small");
			} else {
				dojo.removeClass($("ffo-modal-content"), "ffo-modal-small");
			}
			dojo.addClass($("ffo-modal"), "ffo-modal-visible" + (autoClose ? ' ffo-modal-autoclose' : ''));
			if (! autoClose) {
				dojo.removeClass($("ffo-modal"), "ffo-modal-autoclose");
			}
			dojo.addClass($("ffo-modal"), customClass);
			dojo.addClass(document.body, "ffo-modal-open");
			
			dojo.style($('ffo-occupation-modes'), {'display': 'none'});
			$('ffo-occupation-modes').setAttribute('data-current-occupation', '');
		},

		onHideModal: function () {
			// Need to find a better way to do this!
			dojo.removeClass($("ffo-modal"), "ffo-modal-visible");
			dojo.removeClass($("ffo-modal"), "no-mobile-flex");
			dojo.removeClass(document.body, "ffo-modal-open");
			
			// Remove the negative top value on the body
			const prevTop = document.body.style.top;
			if (prevTop) {
				document.body.style.top = "";
				window.scroll(window.scrollX, -parseInt(prevTop))
			}
		},
		
		syncError: function( code = -1) {
			alert("Out of sync. Need to refresh. Code: " + code);
			throw 'Sync error!';
		},
		
		updateActionUseds: function() {
			const actions = dojo.query('.ffo-action', $('ffo-action-board-holder'));
			for (let i = 0; i < actions.length; i++) {
				const action = actions[i];
				const numMeeples = dojo.query('.ffo-icon-meeple', action).length;
				if (numMeeples > 0) {
					dojo.addClass(action, 'ffo-action-used');
				} else {
					dojo.removeClass(action, 'ffo-action-used');
				}
			}
		},
		
		updateConfirmTimer: function() {
			if (! this.options.confirmTimer) return;
			const e = $('ffo-confirm-timer');
			if (e) {
				const seconds = Math.round(this.confirmTimerSeconds);
				e.innerHTML = '(' + seconds + ')';
			}
		},
		
		startConfirmTimer: function() {
			if (! this.options.confirmTimer) return;
			this.confirmTimerSeconds = 10;
			this.updateConfirmTimer();
			this.stopConfirmTimer();
			this.interval = setInterval(()=>{
				this.confirmTimerSeconds--;
				this.updateConfirmTimer();
				const e = $('ffo-confirm-timer');
				if (!e) {
					this.stopConfirmTimer();
				} else if (this.confirmTimerSeconds <= 0) {
					this.stopConfirmTimer();
					// Send confirm!
					this.onAffirm();
				}
			}, 1000);
		},
		
		stopConfirmTimer: function() {
			if (this.interval) {
				clearInterval(this.interval);
				this.interval = null;
			}
		},
		
		setAllInnerHtml: function(query, content) {
			const results = dojo.query(query);
			for (let i = 0; i < results.length; i++) {
				results[i].innerHTML = content;
			}
		},
		
		format_string_recursive: function(log, args) {
			try {
				if (log && args && !args.processed) {
					args.processed = true;
					
					for (const key in args) {
						if (key == 'inject_for') {
							args[key] = `<div class="ffo-icon ffo-icon-arrow"></div>`;
						} else if (key.startsWith('inject_good_')) {
							const asset = key.substring('inject_good_'.length);
							if (GoodsSource.isGoodResource(asset)) {
								args[key] = `<div class="ffo-icon ffo-good ffo-icon-${asset}"></div>`;
							} else {
								args[key] = `<div class="ffo-good ffo-good-${asset}" data-rotation="0" data-flipped="0" data-good="${asset}"><div class="ffo-good-image"></div></div>`;
							}
						} else if (key.startsWith('inject_weapon_')) {
							const asset = key.substring('inject_weapon_'.length);
							args[key] = `<div class="ffo-card ffo-card-weapon-${asset}"></div>`;
						} else if (key.startsWith('inject_occupation_')) {
							const params = key.substring('inject_occupation_'.length);
							const parts = params.split('_');
							if (parts[0] == 'x') {
								// Back of card
								if (parts[1] == 'true') {
									args[key] = `<div class="ffo-card ffo-card-occ-starting-back"></div>`;
								} else {
									args[key] = `<div class="ffo-card ffo-card-occ-back"></div>`;
								}
							} else {
								// Front of card
								args[key] = `<div class="ffo-card ffo-card-occ-${parts[0]}"></div> (<b>${this.i18n.getOcc(parts[0]).name}</b>)`;
							}
						} else if (key.startsWith('inject_die_')) {
							const dieType = key.substring('inject_die_'.length);
							args[key] = `<div class="ffo-icon ffo-icon-${dieType}"></div>`;
						}
					}
				}
			} catch (e) {
				console.error(log,args,"Exception thrown", e.stack);
			}
			return this.inherited(arguments);
		}
	});
});
