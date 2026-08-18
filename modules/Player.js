class Player {
	constructor(game, playerObj, boardsHolder, sidePanel) {
		this.game = game;
		this.playerObj = playerObj;
		this.playerId = playerObj.id;
		this.name = playerObj.name;
		
		this.mainBoard = dojo.query('.ffo-player-board-main', boardsHolder)[0];
		this.extraBoards = dojo.query('.ffo-player-board-extra', boardsHolder)[0];
		this.playedOccs = dojo.query('.ffo-player-board-occupations', boardsHolder)[0];
		
		this.sidePanel = sidePanel;
		
		this.colour = {
			"dfb000" : "yellow", 
			"123888" : "blue", 
			"ce2512" : "red", 
			"2d2e29" : "black"
		}[this.playerObj.colour];
		
		// black + yellow - blue + red
		this.alternateColour = {
			"dfb000" : "black", 
			"123888" : "red", 
			"ce2512" : "blue", 
			"2d2e29" : "yellow"
		}[this.playerObj.colour];
	}
	
	getMeepleColour(meeple) {
		let meepleColour = this.colour;
		if (+meeple.colour > 0) {
			meepleColour = this.alternateColour;
		}
		return meepleColour;
	}
	
	hasOcc(ability_id) {
		for (const i in this.playerObj.occupationsPlayed) {
			const o = this.playerObj.occupationsPlayed[i];
			if (o.ability == ability_id) {
				return true;
			}
		}
		return false;
	}

	hasBuiltArtisanShed() {
		if (!this.playerObj.artisan_shed) {
			return true;
		}
		return !!this.playerObj.boards.filter(b => b.category == 'shed' && b.type !== 'shed').length;
	}
	
	updateResourceDisplay() {
		// Update the resource display in player panel
		this.game.setAllInnerHtml(".ffo-num-coin-p" + this.playerId, this.playerObj.coin);
		this.game.setAllInnerHtml(".ffo-num-wood-p" + this.playerId, this.playerObj.wood);
		this.game.setAllInnerHtml(".ffo-num-stone-p" + this.playerId, this.playerObj.stone);
		this.game.setAllInnerHtml(".ffo-num-ore-p" + this.playerId, this.playerObj.ore);
		this.game.setAllInnerHtml(".ffo-num-penalty-p" + this.playerId, this.playerObj.penalty);
		$("ffo-penalty-row-p" + this.playerId).style.display = this.playerObj.penalty > 0 ? '' : 'none';
		this.game.setAllInnerHtml(".ffo-num-vp-p" + this.playerId, this.playerObj.vp);
		$("ffo-vp-row-p" + this.playerId).style.display = this.playerObj.vp > 0 ? '' : 'none';

		// Incomes
		let income = 0;
		for (const i in this.playerObj.boards) {
			if (this.playerObj.boards[i]._income) {
				income += +this.playerObj.boards[i]._income;
			}
		}

		this.game.setAllInnerHtml(".ffo-num-coin-income1-p" + this.playerId, income);
		this.game.setAllInnerHtml(".ffo-num-coin-income2-p" + this.playerId, '+' + +income);
	}
	
	updateWeaponsDisplay() {
		const row = $('ffo-weapon-row-p' + this.playerId);
		row.innerHTML = '';
		for (const i in this.playerObj.weapons) {
			const w = this.playerObj.weapons[i];
			row.innerHTML += `<div class="ffo-card ffo-card-weapon-${w.type}">
			</div>`;
		}
	}
	
	updateOccupationsDisplay() {
		{
			const row = $('ffo-occupation-row-p' + this.playerId);
			row.innerHTML = '';
			
			for (const i in this.playerObj.occupationsHand) {
				const o = this.playerObj.occupationsHand[i];
				let fragment = "";
				if (o.ability) {
					const occText = this.game.i18n.getOcc(o.ability);
					fragment = `<div class="ffo-occupation-tooltip">
						<div class="ffo-card ffo-card-occ-${o.ability}" data-occupation-id="${o.id}">
						</div>
						<div>
							<h3>${occText.name}</h3>
							<p>${occText.desc}</p>
						</div>
						<div style="clear: both;"></div>
					</div>`;
					const placed = dojo.place(`<div id="ffo-occupation-${o.id}" class="ffo-card ffo-card-occ-${o.ability}" data-occupation-id="${o.id}">
					</div>`, row);
					dojo.connect(placed, "onclick", () => {
						this.game.onShowModalOccupation(this.playerObj.occupationsHand, i);
					});
				} else {
					const back = +o.starter ? 'ffo-card-occ-starting-back' : 'ffo-card-occ-back';
					fragment = `<div class="ffo-card ${back}" data-occupation-id="${o.id}">
					</div>`;
					dojo.place(`<div id="ffo-occupation-${o.id}" class="ffo-card ${back}" data-occupation-id="${o.id}">
					</div>`, row);
				}
				this.game.addTooltipHtml('ffo-occupation-' + o.id, fragment, 0);
			}
		}
		{
			const row = this.game.options.playedOccupationsInSideBar ? $('ffo-occupation-played-row-p' + this.playerId) : this.playedOccs;
			row.innerHTML = '';
			for (const i in this.playerObj.occupationsPlayed) {
				const o = this.playerObj.occupationsPlayed[i];
				const occText = this.game.i18n.getOcc(o.ability);
				const fragment = `
				<div class="ffo-occupation-tooltip">
					<div class="ffo-card ffo-card-occ-${o.ability}" data-occupation-id="${o.id}"></div>
					<div>
						<h3>${occText.name}</h3>
						<p>${occText.desc}</p>
					</div>
					<div style="clear: both;"></div>
				</div>`;
				const placed = dojo.place(`<div id="ffo-occupation-${o.id}" class="ffo-card ffo-card-occ-${o.ability}" data-occupation-id="${o.id}" data-ability-id="${o.ability}"></div>`, row);
				this.game.addTooltipHtml('ffo-occupation-' + o.id, fragment, 0);
				dojo.connect(placed, "onclick", this.game.onUseOccupation);
			}
		}
	}
	
	updateArtisanShedsDisplay() {
		const row = $('ffo-artisan-shed-row-p' + this.playerId);
		if ( this.game.gamedatas.options.norwegians_expansion && 
			!this.hasBuiltArtisanShed() && 
			!dojo.query('.ffo-player-board-building-artisan-shed-provisional', row).length) {

			const shed = this.playerObj.artisan_shed.split('-');
			const artisanShed = dojo.place( `<div class="ffo-player-board-building-artisan-shed ffo-player-board-building-${shed[0]} ffo-player-board-building-artisan-shed-provisional" data-artisan-shed=${shed[0]} data-artisan-shed-flipped=${shed[1]} id="ffo-player-shed-${this.playerId}"></div>`, row );				
			const flipButton = dojo.place(`<div class="ffo-rotate-button-holder">
			<div class="ffo-icon ffo-icon-rotate"></div>
		</div>`, artisanShed);

		dojo.connect(flipButton, "onclick", this.game.onFlipArtisanShed);
		
		const artisanShedDescription = _('Whenever you could build a shed, you may instead build your artisan shed. If you do so, you decide which of the two sides you wish to build. Each player may build an artisan shed only once per game.');
		const fragment = `
			<div class="ffo-player-board-building-artisan-shed ffo-player-board-building-${shed[0]}"></div>
			<div class="ffo-player-board-building-artisan-shed ffo-player-board-building-${shed[1]}"></div>
			<p>${artisanShedDescription}</p>
			`;
		this.game.addTooltipHtml('ffo-player-shed-' + this.playerId, fragment, 0);
		dojo.connect(artisanShed, "onclick", () => {
			this.game.onShowModalArtisanShed(shed, fragment);
		});
		}
	}

	get feastMeepleHolder() {
		return dojo.query('.ffo-feast-meeple-holder', this.mainBoard)[0];
	}
	
	get thingMeepleHolder() {
		return dojo.query('.ffo-thing-meeple-holder', this.mainBoard)[0];
	}
	
	get benchMeepleHolder() {
		return dojo.query('.ffo-bench-meeple-holder', this.mainBoard)[0];
	}
	
	get meepleRow() {
		return $('ffo-meeple-row-p' + this.playerId);
	}
	
	get goodsRow() {
		return dojo.query('.ffo-goods-row', this.sidePanel)[0];
	}
	
	get whalingBoatRow() {
		return dojo.query('.ffo-whalingboat-holder', this.mainBoard)[0];
	}

	get whalinghut() {
		return dojo.query('.ffo-player-board-building-whaling', this.extraBoards)[0];
	}

	getBoardByBoardIndex(boardIndex) {
		return dojo.query(`[data-board-index="${boardIndex}"]`, this.extraBoards)[0];
	}

	updateMeepleDisplay(options = {}) {
		if (options.clearOld) {
			dojo.query('.ffo-icon-meeple', this.feastMeepleHolder).remove();
			dojo.query('.ffo-icon-meeple.ffo-icon-meeple-' + this.colour, $('ffo-action-board-holder')).remove();
			if (+this.game.gamedatas.options.solo_game) {
				// If it's a solo game, remove the alternates too
				dojo.query('.ffo-icon-meeple.ffo-icon-meeple-' + this.alternateColour, $('ffo-action-board-holder')).remove();
			}
			dojo.query('.ffo-icon-meeple', this.meepleRow).remove();
			dojo.query('.ffo-icon-meeple', this.thingMeepleHolder).remove();
			dojo.query('.ffo-icon-meeple', this.benchMeepleHolder).remove();
		}
		const feastSlotsUsed = {};
		for (const i in this.playerObj.meeples) {
			const m = this.playerObj.meeples[i];
			
			const element = `<div data-meeple-id="${m.id}" class="ffo-icon ffo-icon-meeple ffo-icon-meeple-${this.getMeepleColour(m)}">
			</div>`;
			if (m.location == 'feast') {
				// Put it in the feast m.slot
				const placed = dojo.place(element, this.feastMeepleHolder);
				placed.setAttribute("data-slot", m.slot);
				if (feastSlotsUsed[m.slot]) {
					// Raise this up a bit!
					dojo.addClass(feastSlotsUsed[m.slot], 'ffo-feast-meeple-upper');
				} else {
					feastSlotsUsed[m.slot] = placed;
				}
			} else if (m.location == 'action') {
				// Put it in the m.action_id
				const actionBox = dojo.query('.ffo-action[data-action-id="'+m.action_id+'"]', $('ffo-action-board-holder'))[0];
				const placed = dojo.place(element, actionBox);
			} else if (m.location == 'thing') {
				// Put it in the thing space AND the player-panel
				const placed1 = dojo.place(element, this.meepleRow);
				const placed2 = dojo.place(element, this.thingMeepleHolder);
			} else if (m.location == 'bench') {
				// Put it in the bench space
				const placed = dojo.place(element, this.benchMeepleHolder);
			}
		}
	}
	
	updateShipsDisplay() {
		const sideShipsHolderDiv = $('ffo-ship-row-p' + this.playerId);
		const whalingBoatHolderDiv = dojo.query('.ffo-whalingboat-holder', this.mainBoard)[0];
		const bigBoatHolderDiv = dojo.query('.ffo-bigboat-holder', this.mainBoard)[0];
		
		sideShipsHolderDiv.innerHTML = '';
		whalingBoatHolderDiv.innerHTML = '';
		bigBoatHolderDiv.innerHTML = '';
		for (const i in this.playerObj.ships) {
			const ship = this.playerObj.ships[i];
			
			const fragment = `<div class="ffo-ship ffo-ship-${ship.type}" data-ship-type="${ship.type}" data-ship-id="${ship.id}"></div>`;
			
			if (this.game.options.shipsInSideBar) {
				dojo.place(fragment, sideShipsHolderDiv);
			}
			
			let placed = null;
			if (ship.type == 'whalingboat') {
				placed = dojo.place(fragment, whalingBoatHolderDiv);
			} else {
				placed = dojo.place(fragment, bigBoatHolderDiv);
			}
			
			let maxOre = 0;
			if (ship.type == 'whalingboat') maxOre = 1;
			if (ship.type == 'longship') maxOre = 3;
			
			for (let j = 0; j < maxOre; j++) {
				const oreSlot = dojo.place(`<div class="ffo-ship-ore" data-slot="${j}" data-equipped="${ship.ore > j ? 1 : 0}"></div>`, placed);
				dojo.connect(oreSlot, "onclick", this.game.onEquipShip);
			}
		}
	}
	
	updateEmigrationsDisplay() {
		const holder = dojo.query('.ffo-feast-ship-holder', this.mainBoard)[0];
		holder.innerHTML = '';
		let left = 0;
		for (const i in this.playerObj.emigrations) {
			const emigration = this.playerObj.emigrations[i];

			// if whalingboat, and offset is "in between" black division lines, adjust a bit, looks better
			let leftAdjusted = left;
			const inBetween = (os) => Math.floor(os % 65) !== (os % 65);
			if (emigration == 'whalingboat') {
				if (left != 0 && inBetween(left)) {
					leftAdjusted -= 1;
				}
			}

			const fragment = `<div class="ffo-ship ffo-ship-${emigration}-emigrated" style="left: ${leftAdjusted}px">
			</div>`;
			
			left += emigration == 'whalingboat' ? 32.5 : 65;

			dojo.place(fragment, holder);
		}
	}
	
	placeGood( good, holder, withToolbox ) {
		if (this.game.options.mobileControls) {
			// On mobile, never show toolbox
			withToolbox = false;
		}
		
		if (good['cols'] === 1 && good['rows'] === 1) {
			// rm rotate btn for harvest expansion 1x1 tiles (for now anyway, they are just too small)
			withToolbox = false;
		}

		let flippable = ['horn', 'helmet', 'cane', 'pan', 'sickle', 'anvil', 'skiis', 'sleigh'].includes(good.type);
		let rotateable = true;
		const toolbox = withToolbox ? `<div class="ffo-good-toolbox">
		${rotateable ? '<div class="ffo-toolbox-button ffo-action-rotate"><div class="ffo-icon ffo-icon-rotate"></div></div>' : ''}
		${flippable ? '<div class="ffo-toolbox-button ffo-action-flip"><div class="ffo-icon ffo-icon-resize"></div></div>' : ''}
		</div>` : '';
		// TODO - Unify into "create good" function
		const goodDiv = dojo.place(
			`<div class="ffo-good ffo-good-${good.type}" data-rotation="${good.rotation}" data-flipped="${good.flipped ? 1 : 0}" data-good="${good.type}"><div class="ffo-good-image"></div>${toolbox}</div>`,
			holder
		);
		
		goodDiv.information = good;
	}
	
	placeGoods( holder, withToolbox, filter = null ) {
		for (const i in this.playerObj.goods) {
			const good = this.playerObj.goods[i];
			if (filter) {
				if (filter(good.type)) {
					continue;
				}
			}
			this.placeGood(good, holder, withToolbox);
		}
	}
	
	updateGoodsDisplay(options = {}) {
		// TODO : Source
		const goodsHolderDiv = this.goodsRow;
		
		const alreadyGoods = dojo.query('.ffo-good', goodsHolderDiv);
		const unused = [];
		if (options.clearOld) {
			alreadyGoods.remove();
		} else {
			for (let i = 0; i < alreadyGoods.length; i++) {
				// Push dragging ones to the start (keep those first)
				if (dojo.hasClass(alreadyGoods[i], 'ffo-dragging')) {
					unused.unshift(alreadyGoods[i]);
				} else {
					unused.push(alreadyGoods[i]);
				}
			}
		}
		
		for (const i in this.playerObj.goods) {
			const newGood = this.playerObj.goods[i];

			//DEBUG
			if (!newGood) {
				console.error('null good found');
				continue;
			}

			// If it's already in the holder, fine
			let found = false;
			for (const j in unused) {
				const oldGood = unused[j];
				if (oldGood.information.type == newGood.type) {
					found = true;
					unused.splice(j, 1);
					break;
				}
			}
			if (! found) {
				// We need to add it!
				this.placeGood(newGood, goodsHolderDiv, this.game.playerId() == this.playerId);
			}
		}
		
		// Afterwards, anything still unused needs to get removed
		for (const j in unused) {
			const oldGood = unused[j];
			// If this has a placedNode, remove that too
			if (oldGood.placedNode) {
				oldGood.placedNode.remove();
			}
			oldGood.remove();
		}
		
		// TODO : Put sheep/cattle in the player board slots too
	}
	
	repositionGoods() {
		for (let i = this.game.goodsTargets.length - 1; i >= 0; i--) {
			const gt = this.game.goodsTargets[i];
			if (gt.player.playerId == this.playerId) {
				gt.repositionGoods();
			}
		}
	}
	
	updateBoardsDisplay(options = {}) {
		if (options.clearOld) {
			// Delete all boards/dropDivs
			for (let i = this.game.goodsSources.length - 1; i >= 0; i--) {
				const gs = this.game.goodsSources[i];
				const gt = gs.linkedTarget;
				if (gt && gt.player.playerId == this.playerId) {
					// Remove linked source
					this.game.goodsSources.splice(i, 1);
				}
			}
			for (let i = this.game.goodsTargets.length - 1; i >= 0; i--) {
				const gt = this.game.goodsTargets[i];
				if (gt.player.playerId == this.playerId) {
					// Remove targets
					this.game.goodsTargets.splice(i, 1);
				}
			}
			
			// The main board is a special case - just remove the dropdivs directly...
			dojo.query('.ffo-drop-div', this.mainBoard).remove();
			
			// Remove additional boards
			dojo.query('.ffo-player-board-building', this.extraBoards).remove();
			dojo.query('.ffo-player-board-island', this.extraBoards).remove();
			
			for (const i in this.playerObj.boards) {
				this.playerObj.boards[i].placed = false;
			}
		}

		for (const i in this.playerObj.boards) {
			const board = this.playerObj.boards[i];
			
			// Only place new boards
			if (board.placed) continue;
			
			board.placed = true;
			let boardDiv = null;
			
			if (["home", "homefeast"].includes(board.type)) {
				// If home/homefeast add to existing
				boardDiv = this.mainBoard;
			} else if (["shed", "stonehouse", "longhouse"].includes(board.type)) {

				// If a building, add the building
				boardDiv = dojo.place( `<div class="ffo-player-board-building ffo-player-board-building-${board.type}" data-board-index="${board.index}"></div>`, this.extraBoards );
				
				if (board.type == "shed") {
					// Add wood and stone slots
					for (let j = 0; j < 3; j++) {
						if (! board.wood) board.wood = 0;
						if (! board.stone) board.stone = 0;
						const woodSlot = dojo.place(`<div class="ffo-building-resource ffo-building-wood" data-board-index="${board.index}" data-slot="${j}" data-equipped="${board.wood > j ? '1' : '0'}"></div>`, boardDiv);
						const stoneSlot = dojo.place(`<div class="ffo-building-resource ffo-building-stone" data-board-index="${board.index}" data-slot="${j}" data-equipped="${board.stone > j ? '1' : '0'}"></div>`, boardDiv);
						dojo.connect(woodSlot, "onclick", this.game.onEquipBoard);
						dojo.connect(stoneSlot, "onclick", this.game.onEquipBoard);
					}
				} else if (board.type == "stonehouse") {
					if (! board.wood) board.wood = 0;
					if (! board.stone) board.stone = 0;
					// Add wood and stone slot
					const woodSlot = dojo.place(`<div class="ffo-building-resource ffo-building-wood"  data-board-index="${board.index}" data-slot="0" data-equipped="${board.wood > 0 ? '1' : '0'}"></div>`, boardDiv);
					const stoneSlot = dojo.place(`<div class="ffo-building-resource ffo-building-stone" data-board-index="${board.index}" data-slot="0" data-equipped="${board.stone > 0 ? '1' : '0'}"></div>`, boardDiv);
					dojo.connect(woodSlot, "onclick", this.game.onEquipBoard);
					dojo.connect(stoneSlot, "onclick", this.game.onEquipBoard);
				}
			// Norwegian artisan sheds
			} else if (["whaling", "spinnery", "mason", "beekeeper", "hermit", "icehouse", "pigsty", "hunting", "armoury", "cowshed", "lumberjack", "barn"].includes(board.type)) {

				boardDiv = dojo.place( `<div class="ffo-player-board-building ffo-player-board-building-artisan-shed ffo-player-board-building-${board.type}" data-board-index="${board.index}"></div>`, this.extraBoards );
				// destroy provisional if exists
				const provisionalDiv = $('ffo-artisan-shed-row-p' + this.playerId);
				const provisional = dojo.query('.ffo-player-board-building-artisan-shed-provisional', provisionalDiv);
				dojo.destroy(provisional[0]);

				if (board.type == "mason") {
					const stoneSlot0 = dojo.place(`<div class="ffo-building-resource ffo-building-stone" data-board-index="${board.index}" data-slot="0" data-equipped="${board.stone > 0 ? '1' : '0'}"></div>`, boardDiv);
					const stoneSlot1 = dojo.place(`<div class="ffo-building-resource ffo-building-stone" data-board-index="${board.index}" data-slot="1" data-equipped="${board.stone > 1 ? '1' : '0'}"></div>`, boardDiv);
					dojo.connect(stoneSlot0, "onclick", this.game.onEquipBoard);
					dojo.connect(stoneSlot1, "onclick", this.game.onEquipBoard);
				} else if (board.type == "lumberjack") {
					const woodSlot0 = dojo.place(`<div class="ffo-building-resource ffo-building-wood" data-board-index="${board.index}" data-slot="0" data-equipped="${board.wood > 0 ? '1' : '0'}"></div>`, boardDiv);
					const woodSlot1 = dojo.place(`<div class="ffo-building-resource ffo-building-wood" data-board-index="${board.index}" data-slot="1" data-equipped="${board.wood > 1 ? '1' : '0'}"></div>`, boardDiv);
					dojo.connect(woodSlot0, "onclick", this.game.onEquipBoard);
					dojo.connect(woodSlot1, "onclick", this.game.onEquipBoard);
				} else if (board.type == "armoury") {
					const oreSlot = dojo.place(`<div class="ffo-building-resource ffo-building-ore" data-board-index="${board.index}" data-slot="0" data-equipped="${board.ore > 0 ? '1' : '0'}"></div>`, boardDiv);
					dojo.connect(oreSlot, "onclick", this.game.onEquipBoard);
				} else if (board.type == "spinnery") {
					const linenSlot = dojo.place(`<div class="ffo-building-good ffo-building-linen" data-board-index="${board.index}" data-slot="0" data-equipped="${board.linen > 0 ? '1' : '0'}"></div>`, boardDiv);
					dojo.connect(linenSlot, "onclick", this.game.onEquipBoard);
				} else if (board.type == "beekeeper") {
					const meadSlot = dojo.place(`<div class="ffo-building-good ffo-building-mead" data-board-index="${board.index}" data-slot="0" data-equipped="${board.mead > 0 ? '1' : '0'}"></div>`, boardDiv);
					dojo.connect(meadSlot, "onclick", this.game.onEquipBoard);
				} else if (board.type == "pigsty") {
					const beansSlot = dojo.place(`<div class="ffo-building-good ffo-building-beans" data-board-index="${board.index}" data-slot="0" data-equipped="${board.beans > 0 ? '1' : '0'}"></div>`, boardDiv);
					dojo.connect(beansSlot, "onclick", this.game.onEquipBoard);
				} else if (board.type == "icehouse") {
					const stockfishSlot = dojo.place(`<div class="ffo-building-good ffo-building-stockfish" data-board-index="${board.index}" data-slot="0" data-equipped="${board.stockfish > 0 ? '1' : '0'}"></div>`, boardDiv);
					dojo.connect(stockfishSlot, "onclick", this.game.onEquipBoard);
				} else if (board.type == "hunting") {
					const hideSlot = dojo.place(`<div class="ffo-building-good ffo-building-hide" data-board-index="${board.index}" data-slot="0" data-equipped="${board.hide > 0 ? '1' : '0'}"></div>`, boardDiv);
					dojo.connect(hideSlot, "onclick", this.game.onEquipBoard);
				} 
			} else {
				// If expansion, actually add the board

				const norwegiansIslands = this.game.gamedatas.options.norwegians_expansion ? 'ffo-player-board-island-norwegians' : '';

				boardDiv = dojo.place( `<div class="ffo-player-board-island ${norwegiansIslands} ffo-player-board-island-${board.type}" data-board-index="${board.index}"></div>`, this.extraBoards );
			}
			board.element = boardDiv;
			
			const goodsTarget = this.game.addGoodsTarget(boardDiv, board, this.playerId == this.game.playerId(), this);
			
			// Add all the goods to the board
			for (let j = 0; j < board.goods.length; j++) {
				const good = board.goods[j];
				let div = null;
				if (GoodsSource.isGoodResource(good.type)) {
					div = dojo.place(
						`<div class="ffo-good ffo-icon ffo-icon-${good.type}" data-rotation="${good.rotation}" data-flipped="${good.flipped ? 1 : 0}" data-good="${good.type}"><div class="ffo-good-image"></div></div>`,
						goodsTarget.dropDiv
					);
				} else {
					div = dojo.place(
						`<div class="ffo-good ffo-good-${good.type}" data-rotation="${good.rotation}" data-flipped="${good.flipped ? 1 : 0}" data-good="${good.type}"><div class="ffo-good-image"></div></div>`,
						goodsTarget.dropDiv
					);
				}
				div.information = good;
				goodsTarget.addGood(div, good.coords, false, false);
			}
		}
	}
	
	getGoodsTarget( boardIndex ) {
		for (const i in this.game.goodsTargets) {
			const t = this.game.goodsTargets[i];
			if (t.player.playerId == this.playerId && +t.information.index == +boardIndex) {
				return t;
			}
		}
		return null;
	}
	
	countKnarrs() {
		let knarrs = 0;
		for (const i in this.playerObj.ships) {
			const ship = this.playerObj.ships[i];
			
			if (ship.type == 'knarr') {
				knarrs++;
			}
		}
		return knarrs;
	}
}