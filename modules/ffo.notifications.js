(function() {
	const moveTime = 500;
	const delayTime = 200;
	
	function getLocationFromSource(source) {
		// Source can be one of:
		// - action-{actionId}
		// - board-{boardIndex}
		// - mountainstrip-{id}
		// - ship-{shipId}
		// - boatboard-{shipType}
		const pieces = source.split('-');
		
		if (pieces[0] == 'action') {
			return dojo.query(`.ffo-action[data-action-id="${pieces[1]}"]`, $('ffo-action-board'))[0];
		} else if (pieces[0] == 'board') {
			// ---
		} else if (pieces[0] == 'mountainstrip') {
			const strips = dojo.query(`.ffo-mountain-strip[data-strip-id="${pieces[1]}"]`, $('ffo-top-tray'));
			if (strips.length > 0) {
				// Grab the left-most element here...
				const elements = dojo.query('.ffo-icon:not(.ffo-icon-taken)', strips[0]);
				if (elements.length > 0) {
					dojo.addClass(elements[elements.length - 1], 'ffo-icon-taken');
					return elements[elements.length - 1];
				}
			}
			return $('ffo-tab-strips');
		} else if (pieces[0] == 'ship') {
			const ships = dojo.query(`.ffo-ship[data-ship-id="${pieces[1]}"]`, $('feast-for-odin'));
			if (ships.length > 0)
				return ships[0];
		} else if (pieces[0] == 'boatboard') {
			// A bit much if animations can occur at any time
			//return $('ffo-tab-boats');
		} else if (pieces[0] == 'harvest') {
			return $('page-title');
		} else if (pieces[0] == 'occupation') {
			const occs = dojo.query(`.ffo-player-board-occupations .ffo-card-occ-${pieces[1]}`);
			if (occs.length > 0)
				return occs[0];
		}
		
		return null;
	}
	
	function handleMeepleMove( notif ) {
		// TODO : Update playerObj.meeples?
		// TODO : User Player.updateMeepleDisplay()?
		const meeples = notif.args.meeples;
		let afterTime = 0;
		
		for (const i in meeples) {
			const meeple = meeples[i];
			const player = this.playersById[meeple.player_id];
			
			for (const j in player.playerObj.meeples) {
				const m = player.playerObj.meeples[j];
				if (+m.id == +meeple.id) {
					m.location = meeple.location;
					m.slot = meeple.slot;
					m.action_id = meeple.action_id;
				}
			}
			
			const elements = dojo.query('.ffo-icon-meeple[data-meeple-id="'+meeple.id+'"]');
			for (let j = 0; j < elements.length; j++) {
				const element = elements[j];
				const parent = element.parentElement;
				
				// Only move if the target location is different to the current location?
				let move = false;
				if (dojo.hasClass(parent, 'ffo-meeple-row')) {
					// Side bar (Thing meeples)
					if (meeple.location != 'thing') {
						// Fade this one out
						this.fadeOutAndDestroy( element );
					}
				} else if (dojo.hasClass(parent, 'ffo-action')) {
					// Action slot
					if (meeple.location != 'action') {
						// Move to the new loc!
						// CAUTION: Can you ever move meeples from one action to another?
						move = true;
					}
				} else if (dojo.hasClass(parent, 'ffo-thing-meeple-holder')) {
					// Thing
					if (meeple.location != 'thing') {
						// Move to the new loc!
						move = true;
					}
				} else if (dojo.hasClass(parent, 'ffo-feast-meeple-holder')) {
					// Feast
					if (meeple.location != 'feast') {
						// Move to the new loc!
						move = true;
					}
				} else if (dojo.hasClass(parent, 'ffo-bench-meeple-holder')) {
					// Thing
					if (meeple.location != 'bench') {
						// Move to the new loc!
						move = true;
					}
				}
				
				// Move element to new location!
				// - if currently in sidebar, just fade out
				// - if moving to Thing, also fade in to sidebar
				
				if (move) {
					dojo.removeClass(element, 'ffo-feast-meeple-upper');
					let target = null;
					if (meeple.location == 'thing') {
						target = player.thingMeepleHolder;
					} else if (meeple.location == 'bench') {
						target = player.benchMeepleHolder;
					} else if (meeple.location == 'feast') {
						target = player.feastMeepleHolder;
					} else if (meeple.location == 'action') {
						target = dojo.query('.ffo-action[data-action-id="'+meeple.action_id+'"]', $('ffo-action-board-holder'))[0];
					}
					
					// After finished, add to the new place
					if (target) {
						const time = 500;
						afterTime = time;
						this.slideToObjectAndDestroy( element, target, time );
						
						setTimeout(() => {
							const fragment = `<div data-meeple-id="${meeple.id}" class="ffo-icon ffo-icon-meeple ffo-icon-meeple-${player.getMeepleColour(meeple)}">
							</div>`;
							const placed = dojo.place(fragment, target);
							if (meeple.location == 'feast') {
								placed.setAttribute("data-slot", meeple.slot);
							} else if (meeple.location == 'thing') {
								// Also add to the row
								dojo.place(fragment, player.meepleRow);
							}
							this.updateActionUseds();
						}, time);
					}
				}
			}
		}
		this.notifqueue.setSynchronousDuration( afterTime );
	}
	
	function handleCustomHarvest( notif ) {
		const currentHarvest = +notif.args.current_harvest;
		const nextHarvest = +notif.args.next_harvest;
		
		//reveal next harvest
		const customHarvestHolders = dojo.query('.ffo-thing-custom-harvest-holder', $('ffo-player-board-main'));
		customHarvestHolders.forEach(customHarvestHolder => {
			const nextHarvestTiles = dojo.query('.tile-back', customHarvestHolder);
	
			if (nextHarvestTiles.length) {
				dojo.removeClass(nextHarvestTiles[0], 'tile-back');
				dojo.addClass(nextHarvestTiles[0], `tile-${nextHarvest}`)
			}
		});

		// "flip" personal resource marker(s) if necessary
		const personalResourceHolders = dojo.query('.ffo-thing-personal-resource-holder', $('ffo-player-board-main'));
		personalResourceHolders.forEach(personalResourceHolder => {
			
			const frontSides = dojo.query(".front-side", personalResourceHolder)
			const backSides = dojo.query(".back-side", personalResourceHolder)

			if (currentHarvest !== 0 && nextHarvest === 0) {				
				for(let i = 0; i < frontSides.length; i++) {
					const frontSide = frontSides[i];
					const backSide = backSides[i];
					const frontSideClass = frontSide.getAttribute('data-class');
					const backSideClass = backSide.getAttribute('data-class');
					dojo.removeClass(frontSide, frontSideClass);
					dojo.addClass(frontSide, backSideClass);
					dojo.removeClass(backSide, backSideClass);
					dojo.addClass(backSide, frontSideClass);
				}
			}
			if (currentHarvest === 0 && nextHarvest !== 0) {				
				for(let i = 0; i < frontSides.length; i++) {
					const frontSide = frontSides[i];
					const backSide = backSides[i];
					const frontSideClass = frontSide.getAttribute('data-class');
					const backSideClass = backSide.getAttribute('data-class');
					dojo.removeClass(frontSide, backSideClass);
					dojo.addClass(frontSide, frontSideClass);
					dojo.removeClass(backSide, frontSideClass);
					dojo.addClass(backSide, backSideClass);
				}
			}
		});
	}

	function handleDrawPersonalResourceMarker( notif ) {
		const marker = notif.args.marker;
		const playerId = notif.args.player_id;

		const playerBoardsHolder = dojo.query( `#ffo-player-boards-holder-${playerId}`, $('feast-for-odin') )[0];

		const personalResourceHolder = dojo.query( '.ffo-thing-personal-resource-holder', playerBoardsHolder )[0];

		const split = marker.split('-');
		const frontSideClass = `personal-resource-${split[0]}-light`;
		const backSideClass = `personal-resource-${split[1]}-dark`;

		const existingMarkers = dojo.query('.ffo-thing-personal-resource-marker', personalResourceHolder);
		const markerClass = existingMarkers.length === 1 ? 'ffo-thing-personal-resource-marker-2' : 'ffo-thing-personal-resource-marker-1';

		const container = dojo.place(`<div class="ffo-thing-personal-resource-marker ${markerClass}"></div>`, personalResourceHolder );
		dojo.place(`<div class="personal-resource ${frontSideClass} front-side" data-class="${frontSideClass}"></div>`, container);
		dojo.place(`<div class="personal-resource ${backSideClass} back-side" data-class="${backSideClass}"></div>`, container);
	}

	function handleDrawOccupation( notif ) {
		const playerId = notif.args.player_id;
		const ignorePlayerId = notif.args.ignore_player_id;
		const occupation = notif.args.occupation;
		
		if (ignorePlayerId == this.playerId()) {
			return;
		}
		
		const player = this.playersById[playerId];
		player.playerObj.occupationsHand.push( occupation );
		player.updateOccupationsDisplay();
	}
	
	function handlePlayOccupation( notif ) {
		const playerId = notif.args.player_id;
		const player = this.playersById[playerId];
		const occupation = notif.args.occupation;
		const vp =  notif.args.vp;
		const num_vp_token_4 = notif.args.num_vp_token_4;
		const num_vp_token_3 = notif.args.num_vp_token_3;

		if (occupation) {
			for (let i = 0; i < player.playerObj.occupationsHand.length; i++) {
				if (player.playerObj.occupationsHand[i].id == occupation.id) {
					player.playerObj.occupationsHand.splice(i, 1);
					break;
				}
			}
			if (!vp) {
				player.playerObj.occupationsPlayed.push( occupation );
			}
		}

		if (vp) {
			player.playerObj.vp = +player.playerObj.vp + vp;
			player.updateResourceDisplay();
		}

		if ((num_vp_token_4 || num_vp_token_4 == 0) && (num_vp_token_3 || num_vp_token_3 == 0)) {
			this.gamedatas.num_vp_token_4 = num_vp_token_4;
			this.gamedatas.num_vp_token_3 = num_vp_token_3;
		}

		player.updateOccupationsDisplay();
	}
	
	function handleDiscardOccupation( notif ) {
		const playerId = notif.args.player_id;
		const occupation = notif.args.occupation;
		
		const player = this.playersById[playerId];
		for (let i = 0; i < player.playerObj.occupationsHand.length; i++) {
			if (player.playerObj.occupationsHand[i].id == occupation.id) {
				player.playerObj.occupationsHand.splice(i, 1);
				break;
			}
		}

		player.updateOccupationsDisplay();
	}

	function handleDrawWeapon( notif ) {
		const playerId = notif.args.player_id;
		const weapons = notif.args.weapons;
		
		const player = this.playersById[playerId];
		for (const i in weapons) {
			player.playerObj.weapons.push( weapons[i] );
		}
		player.updateWeaponsDisplay();
	}
	
	function handleDiscardWeapons( notif ) {
		const playerId = notif.args.player_id;
		const weapons = notif.args.weapons;
		
		const player = this.playersById[playerId];
		for (let j = 0; j < weapons.length; j++) {
			const weapon = weapons[j];
			for (let i = 0; i < player.playerObj.weapons.length; i++) {
				if (player.playerObj.weapons[i].id == weapon.id) {
					player.playerObj.weapons.splice(i, 1);
					break;
				}
			}
		}
		player.updateWeaponsDisplay();
	}
	
	function handleDiffResources( notif ) {
		const playerId = notif.args.player_id;
		const resource = notif.args.resource;
		const diff = notif.args.diff;
		// action-n, harvest, ..., can also be a "sink"
		const source = notif.args.source;
		
		const player = this.playersById[playerId];
		
		// TODO : Source!!
		player.playerObj[resource] = +player.playerObj[resource] + +diff;
		
		for (const i in this.goodsTargets) {
			const t = this.goodsTargets[i];
			t.checkProvisionals();
		}
		
		if (source) {
			// Animate to/from space with a temp token
			const location1 = dojo.query('.ffo-main-resources .ffo-icon-' + resource, player.sidePanel)[0];
			const location2 = getLocationFromSource(source);
			const a = diff > 0 ? location2 : location1;
			const b = diff > 0 ? location1 : location2;
			let lastTime = 0;
			if (a && b) {
				for (let i = 0; i < Math.abs(diff); i++) {
					this.slideTemporaryObject( 
						`<div class="ffo-icon ffo-icon-${resource}"></div>`, 
						document.body, 
						a, 
						b, 
						moveTime, 
						delayTime * i );
					lastTime = delayTime * i + moveTime;
				}
			}
			setTimeout(() => {
				player.updateResourceDisplay();
			}, lastTime);
			this.notifqueue.setSynchronousDuration( lastTime );
		} else {
			// Immediate
			player.updateResourceDisplay();
			this.notifqueue.setSynchronousDuration( 0 );
		}
	}
	
	function handleTakeGoods( notif ) {
		const playerId = notif.args.player_id;
		const goods = notif.args.goods;
		const source = notif.args.source; // action-n, harvest, ...
		
		const player = this.playersById[playerId];
		
		if (source && getLocationFromSource(source)) {
			// Animate to/from space with a temp token
			const b = dojo.query('.ffo-goods-row', player.sidePanel)[0];
			const a = getLocationFromSource(source);
			let lastTime = 0;
			if (a && b) {
				for (let i = 0; i < goods.length; i++) {
					const g = goods[i];
					player.playerObj.goods.push(g);
					
					this.slideTemporaryObject( 
						`<div class="ffo-good ffo-good-${g.type}"><div class="ffo-good-image"></div></div>`, 
						document.body, 
						a, 
						b, 
						moveTime, 
						delayTime * i );
					lastTime = delayTime * i + moveTime;
					setTimeout(() => {
						player.placeGood(g, player.goodsRow, true);
					}, lastTime);
				}
			} else {
				alert("Notification error. Please refresh.");
			}
			this.notifqueue.setSynchronousDuration( lastTime );
		} else {
			// Immediate
			for (const i in goods) {
				const good = goods[i];
				if (good) {
					player.playerObj.goods.push(good);
					player.placeGood(good, player.goodsRow, true);
				}
			}
			this.notifqueue.setSynchronousDuration( 0 );
		}
	}
	
	function handleSpendGoods( notif ) {
		const playerId = notif.args.player_id;
		const goods = notif.args.goods;
		// action-n, harvest, ..., is actually a "sink"
		const source = notif.args.source;
		
		const player = this.playersById[playerId];
		
		if (source && getLocationFromSource(source)) {
			// Animate to/from space with a temp token
			const a = dojo.query('.ffo-goods-row', player.sidePanel)[0];
			const b = getLocationFromSource(source);
			let lastTime = 0;
			if (a && b) {
				let k = 0;
				let lastTime = 0;
				for (let i = player.playerObj.goods.length - 1; i >= 0; i--) {
					const g1 = player.playerObj.goods[i];
					for (const j in goods) {
						const g2 = goods[j];
						if (g1.type == g2.type) {
							goods.splice(j, 1);
							player.playerObj.goods.splice(i, 1);
							
							this.slideTemporaryObject( 
								`<div class="ffo-good ffo-good-${g2.type}"><div class="ffo-good-image"></div></div>`, 
								document.body, 
								a, 
								b, 
								moveTime, 
								delayTime * k );
							lastTime = delayTime * k + moveTime;
							k++;
							break;
						}
					}
				}
				
				player.updateGoodsDisplay();
				
				for (const i in this.goodsTargets) {
					const t = this.goodsTargets[i];
					t.checkProvisionals();
				}
				
				this.notifqueue.setSynchronousDuration( lastTime );
			} else {
				alert("Notification error. Please refresh.");
			}
		} else {
			// Immediate
			for (let i = player.playerObj.goods.length - 1; i >= 0; i--) {
				const g1 = player.playerObj.goods[i];
				for (const j in goods) {
					const g2 = goods[j];
					if (g1.type == g2.type) {
						goods.splice(j, 1);
						player.playerObj.goods.splice(i, 1);
						break;
					}
				}
			}
			
			player.updateGoodsDisplay();
			
			for (const i in this.goodsTargets) {
				const t = this.goodsTargets[i];
				t.checkProvisionals();
			}
			this.notifqueue.setSynchronousDuration( 0 );
		}
	}
	
	function handleSpendShips( notif ) {
		const playerId = notif.args.player_id;
		const ships = notif.args.ships;
		// action-n, harvest, ..., is actually a "sink"
		const source = notif.args.source;
		
		const player = this.playersById[playerId];
		
		for (let i = player.playerObj.ships.length - 1; i >= 0; i--) {
			const g1 = player.playerObj.ships[i];
			for (const j in ships) {
				const g2 = ships[j];
				if (g1.id == g2.id) {
					ships.splice(j, 1);
					player.playerObj.ships.splice(i, 1);
					break;
				}
			}
		}
		player.updateShipsDisplay();
	}
	
	function handleEquipShip( notif ) {
		const playerId = notif.args.player_id;
		const ship = notif.args.ship;
		
		const player = this.playersById[playerId];
		
		for (let i = player.playerObj.ships.length - 1; i >= 0; i--) {
			const s = player.playerObj.ships[i];
			if (s.id == ship.id) {
				s.ore = ship.ore;
			}
		}
		player.updateShipsDisplay();
	}
	
	function handleEquipBoard( notif ) {
		const playerId = notif.args.player_id;
		const boardIndex = notif.args.board_index;
		const resource = notif.args.resource;
		let amount = notif.args.amount;
		
		const player = this.playersById[playerId];
		
		for (let i = player.playerObj.boards.length - 1; i >= 0; i--) {
			const board = player.playerObj.boards[i];
			if (board.index == boardIndex) {

				const slots = dojo.query('.ffo-building-'+resource, board.element);

				board[resource] = amount;

				for (const slot of slots) {
					if (amount > 0) {
						slot.setAttribute('data-equipped', '1');
					} else {
						slot.setAttribute('data-equipped', '0');
					}
					amount--;
				}
			}
		}
		// if modal is open, adjust too
		const modalOpen = dojo.hasClass(document.body, 'ffo-modal-open');
		if (modalOpen) {
			let amount = notif.args.amount;
			const slots = dojo.query('.ffo-building-'+resource, $('ffo-modal-content'));

			for (const slot of slots) {
				if (amount > 0) {
					slot.setAttribute('data-equipped', '1');
				} else {
					slot.setAttribute('data-equipped', '0');
				}
				amount--;
			}

			if (! ['wood', 'stone', 'ore'].includes(resource)) {
				return;
			}
			
			const goodsRow = dojo.query('.ffo-placement-modal-good-source',  $('ffo-modal-content'))[0];

			if (notif.args.equip) {
				// equiped, so remove from goods row
				dojo.query('.ffo-good-'+resource)[0].remove();
			} else {
				// unequiped, so add to goods row
				for (let i = player.playerObj.boards.length - 1; i >= 0; i--) {
					const board = player.playerObj.boards[i];
					if (board.index == boardIndex) {
						const originalGoodsRow = player.goodsRow;
						const goods = dojo.query(".ffo-good", originalGoodsRow);
						for (let i = 0; i < goods.length; i++) {
							const good = goods[i].cloneNode(true);
							good.information = JSON.parse(JSON.stringify(goods[i].information));
							if (! board.allowedColours.includes(good.information.colour)) {
								continue;
							}
							if (good.information.type == resource) {
								dojo.place(good, goodsRow);
								//add only one
								break;
							}

						}
					}
				}
			}

		}
	}
	
	function handleNewBoard( notif ) {
		const playerId = notif.args.player_id;
		const board = notif.args.board;
		
		const player = this.playersById[playerId];
		player.playerObj.boards.push( board );
		player.updateBoardsDisplay();
		player.updateResourceDisplay();
		player.updateArtisanShedsDisplay();
	}
	
	function handleNewShip( notif ) {
		const playerId = notif.args.player_id;
		const ship = notif.args.ship;
		
		const player = this.playersById[playerId];
		player.playerObj.ships.push( ship );
		player.updateShipsDisplay();
	}
	
	function handleUpdateIslands( notif ) {
		const islands = notif.args.islands;
		
		this.gamedatas.islands = islands;
		
		// TODO : If the islands modal is open, refresh it?
	}
	
	function handleUpdateForge( notif ) {
		const forge = notif.args.forge;
		
		this.gamedatas.forge = forge;
		
		// TODO : If the forge modal is open, refresh it?
	}
	
	function handleUpdateBuildingSupply( notif ) {
		const numSheds = notif.args.num_sheds;
		const numStonehouses = notif.args.num_stonehouses;
		const numLonghouses = notif.args.num_longhouses;
		
		this.gamedatas.num_sheds = numSheds;
		this.gamedatas.num_stonehouses = numStonehouses;
		this.gamedatas.num_longhouses = numLonghouses;
		
		// TODO : If the sheds modal is open, refresh it?
	}
	
	function handleUpdateMountainStrips( notif ) {
		const mountainstrips = notif.args.mountainstrips;
		
		this.gamedatas.mountainstrips = mountainstrips;
		
		// TODO : If the mountain strips modal is open, refresh it?
	}
	
	function handleEmigrate( notif ) {
		const playerId = notif.args.player_id;
		const emigration = notif.args.emigration;
		
		const player = this.playersById[playerId];
		player.playerObj.emigrations.push( emigration );
		player.updateEmigrationsDisplay();
	}
	
	function handlePlayerPass( notif ) {
		const playerId = notif.args.player_id;
		
		const player = this.playersById[playerId];
		player.passed = true;
		this.disablePlayerPanel( playerId )
	}
	
	function handleUnpassAll( notif ) {
		this.enableAllPlayerPanels( );
	}
	
	function handleFirstPlayer( notif ) {
		const playerId = notif.args.player_id;
		
		this.gamedatas.first_player_id = playerId;
		this.updateFirstPlayerDisplay();
	}
	
	function handleUpdateBoards( notif ) {
		const playerId = notif.args.player_id;
		const update = notif.args.update;
		const incomes = notif.args.incomes;
		const skipAnimate = notif.args.skip_animate || this.instantaneousMode;
		
		const player = this.playersById[playerId];
		
		if (playerId == this.playerId() && GoodsSource.marked) {
			// Remove any crosshair from the old good
			dojo.query(".ffo-icon-crosshair", GoodsSource.marked).remove();
			if (GoodsSource.marked.placedNode) {
				dojo.query(".ffo-icon-crosshair", GoodsSource.marked.placedNode).remove();
			}
			GoodsSource.marked = null;
			this.updateMobileControls();
		}

		for (const k in update) {
			const parts = k.split('-');
			const boardIndex = +parts[1];
			const target = player.getGoodsTarget(boardIndex);
			// Update cached income
			if (incomes && incomes.hasOwnProperty(boardIndex) && player.playerObj.boards[boardIndex]) {
				player.playerObj.boards[boardIndex]._income = +incomes[boardIndex];	
			}

			const newGoods = update[k];
			for (const j in newGoods) {
				const good = newGoods[j];
				if (GoodsSource.isGoodResource(good.type)) {
					if (+playerId == this.playerId()) {
						// Turn it from provisional into real
						const matches = dojo.query(`.ffo-good.ffo-provisional.ffo-icon-${good.type}`, target.dropDiv);
						let found = null;
						for (let l = 0; l < matches.length; l++) {
							const m = matches[l];
							if (m.information.coords[0] == good.coords[0] && m.information.coords[1] == good.coords[1]) {
								found = m;
								break;
							}
						}
						if (found) {
							dojo.removeClass(found, 'ffo-provisional ffo-erroneous ffo-draggee ffo-marked');
							dojo.addClass(found, 'ffo-fixed');
						} else {
							// If there is no provisional, it's probably a game replay
							// Reduce that resource by one
							player.playerObj[good.type]--;
							
							// Place a new one
							// TODO : Animate?
							const newGood = dojo.place(
								`<div class="ffo-good ffo-icon ffo-icon-${good.type}" data-rotation="${good.rotation}" data-flipped="${good.flipped ? 1 : 0}" data-good="${good.type}"><div class="ffo-good-image"></div></div>`,
								target.dropDiv
							);
							newGood.information = good;
							target.addGood(newGood, good.coords, false, false);
						}
					} else {
						// Reduce that resource by one
						player.playerObj[good.type]--;
						
						// Place a new one
						// TODO : Animate?
						const newGood = dojo.place(
							`<div class="ffo-good ffo-icon ffo-icon-${good.type}" data-rotation="${good.rotation}" data-flipped="${good.flipped ? 1 : 0}" data-good="${good.type}"><div class="ffo-good-image"></div></div>`,
							target.dropDiv
						);
						newGood.information = good;
						target.addGood(newGood, good.coords, false, false);
					}
				} else {
					if (skipAnimate) {
						// Make a new one!
						const newGood = dojo.place(
							`<div class="ffo-good ffo-good-${good.type}" data-rotation="${good.rotation}" data-flipped="${good.flipped ? 1 : 0}" data-good="${good.type}"><div class="ffo-good-image"></div></div>`,
							target.dropDiv
						);
						newGood.information = good;
						target.addGood(newGood, good.coords, false, false);
					} else {
						// Get the good from the tray (.dragging first)
						const trayGood = getPlayerGood(player, good, target);
						removePlayerObjGood(player, good);
						
						if (trayGood) {
							trayGood.information.rotation = good.rotation;
							trayGood.information.flipped = good.flipped;
							trayGood.setAttribute("data-rotation", good.rotation);
							trayGood.setAttribute("data-flipped", good.flipped ? 1 : 0);
							
							if (dojo.hasClass(trayGood, "ffo-dragging")) {
								// If it's dragging, simply remove the tray good and unprovisionalise the placed good
								// We hope we've grabbed the right one...
								const placedNode = trayGood.placedNode;
								if (placedNode) {
									dojo.removeClass(placedNode, 'ffo-provisional ffo-erroneous ffo-draggee');
									dojo.addClass(placedNode, 'ffo-fixed');
									dojo.query(".ffo-good-toolbox", placedNode).remove();
								} else {
									this.syncError(2);
								}
								
								trayGood.remove();
							} else {
								// Otherwise, move it to the target...
								target.addGood(trayGood, good.coords, true, false);
								dojo.query(".ffo-good-toolbox", trayGood).remove();
							}
						}
					}
				}
				// another whalingboat in Whaling Hut exeption 
				if (good.type == 'whalingboat') {
					const whalingboat = dojo.query('.ffo-ship-whalingboat', player.whalinghut)[0];
					dojo.query(".ffo-ship-ore", whalingboat)[0].remove();
					dojo.removeClass(whalingboat, 'ffo-provisional');
					dojo.addClass(whalingboat, 'ffo-fixed');
				}
			}
		}
		
		// Update resource display
		player.updateResourceDisplay();
		
		let unsavedChanges = this.hasUnsavedChanges();
		$('ffo-unsaved-message').style.display = unsavedChanges ? 'block' : 'none';
	}
	
	function handleClearFeastBoards( notif ) {
		// Remove all goods from all feast boards
		// TODO : Fade out and remove
		for (const i in this.goodsTargets) {
			const target = this.goodsTargets[i];
			if (target.information.type == "homefeast") {
				dojo.query('.ffo-good.ffo-fixed', target.dropDiv).remove();
			}
		}
		// (Any provisionals can remain)
	}
	
	function handleUndoTurn( notif ) {
		const playerId = notif.args.player_id;
		const player_obj = notif.args.player;
		
		const player = this.playersById[playerId];
		player.playerObj = player_obj;
		
		if (playerId != this.playerId()) {
			for (let i in player.playerObj.occupationsHand) {
				delete player.playerObj.occupationsHand[i].ability;
			}
		} else {
			
		}
		
		handleUpdateMountainStrips.call( this, notif );
		handleUpdateForge.call( this, notif );
		handleUpdateIslands.call( this, notif );
		handleUpdateBuildingSupply.call( this, notif );
		handlePlayOccupation.call( this, notif );
		
		// Update everything about the player, as if in setup...
		player.updateResourceDisplay();
		player.updateWeaponsDisplay();
		player.updateOccupationsDisplay();
		player.updateMeepleDisplay({clearOld: true});
		player.updateGoodsDisplay({clearOld: true});
		player.updateShipsDisplay();
		player.updateBoardsDisplay({clearOld: true});
		player.updateEmigrationsDisplay();
		player.updateArtisanShedsDisplay();
		
		this.updateActionUseds();
	}
	
	function handleSetRound( notif ) {
		const round = notif.args.round;
		
		this.gamedatas.round = +round;
	}
	
	function handleUpdateScore( notif ) {
		const playerId = notif.args.player_id;
		const scoreBreakdown = notif.args.score_breakdown;
		
		const player = this.playersById[playerId];
		player.playerObj.score_breakdown = scoreBreakdown;
		this.scoreCtrl[ playerId ].toValue( scoreBreakdown.total );
	}
	
	function handleUpdateUndo( notif ) {
		const canUndo = notif.args.can_undo;
		
		this.gamedatas.can_undo = +canUndo;
	}
	
	function setupNotifications( ) {
		// Register notifications, and add functions here
		dojo.subscribe("clearFeastBoards", handleClearFeastBoards.bind(this));
		dojo.subscribe("diffResources", handleDiffResources.bind(this));
		dojo.subscribe("discardWeapons", handleDiscardWeapons.bind(this));
		dojo.subscribe("drawOccupation", handleDrawOccupation.bind(this));
		dojo.subscribe("drawWeapon", handleDrawWeapon.bind(this));
		dojo.subscribe("emigrate", handleEmigrate.bind(this));
		dojo.subscribe("equipShip", handleEquipShip.bind(this));
		dojo.subscribe("equipBoard", handleEquipBoard.bind(this));
		dojo.subscribe("firstPlayer", handleFirstPlayer.bind(this));
		dojo.subscribe("meepleMove", handleMeepleMove.bind(this));
		dojo.subscribe("newBoard", handleNewBoard.bind(this));
		dojo.subscribe("newShip", handleNewShip.bind(this));
		dojo.subscribe("playerPass", handlePlayerPass.bind(this));
		dojo.subscribe("playOccupation", handlePlayOccupation.bind(this));
		dojo.subscribe("setRound", handleSetRound.bind(this));
		dojo.subscribe("spendGoods", handleSpendGoods.bind(this));
		dojo.subscribe("spendShips", handleSpendShips.bind(this));
		dojo.subscribe("takeGoods", handleTakeGoods.bind(this));
		dojo.subscribe("undoTurn", handleUndoTurn.bind(this));
		dojo.subscribe("unpassAll", handleUnpassAll.bind(this));
		dojo.subscribe("updateBoards", handleUpdateBoards.bind(this));
		dojo.subscribe("updateBuildingSupply", handleUpdateBuildingSupply.bind(this));
		dojo.subscribe("updateForge", handleUpdateForge.bind(this));
		dojo.subscribe("updateIslands", handleUpdateIslands.bind(this));
		dojo.subscribe("updateMountainStrips", handleUpdateMountainStrips.bind(this));
		dojo.subscribe("updateScore", handleUpdateScore.bind(this));
		dojo.subscribe("updateUndo", handleUpdateUndo.bind(this));
		dojo.subscribe("discardOccupation", handleDiscardOccupation.bind(this));
		dojo.subscribe("customHarvest", handleCustomHarvest.bind(this));
		dojo.subscribe("drawPersonalResourceMarker", handleDrawPersonalResourceMarker.bind(this));

		
		// Synchronous notifications
		this.notifqueue.setSynchronous( 'diffResources' );
		this.notifqueue.setSynchronous( 'spendGoods' );
		this.notifqueue.setSynchronous( 'takeGoods' );
		this.notifqueue.setSynchronous( 'meepleMove' );
		
		// Game end stuff
		// dojo.subscribe("discardTileSlow", handleDiscardTile.bind(this));
		// dojo.subscribe("debenSlow", handleDeben.bind(this));
		// this.notifqueue.setSynchronous( 'discardTileSlow', 1000 );
		// this.notifqueue.setSynchronous( 'debenSlow', 1000 );
	}
	
	// Helpers
	function getPlayerGood(player, good, goodTarget) {
		const goodsRow = player.goodsRow;
		const r1 = dojo.query(`.ffo-good-${good.type}.ffo-dragging`, goodsRow);
		if (r1.length > 0) {
			// If possible, get the good with the right rotation/flipped status, and that's on the right board
			for (var i = 0; i < r1.length; i++) {
				if (r1[i].information.rotation == good.rotation &&
					r1[i].information.flipped == good.flipped)
				{
					if (! goodTarget || (r1[i].placedNode && r1[i].placedNode.dropDiv == goodTarget)) {
						return r1[i];
					}
				}
			}
			return r1[0];
		}
		const r2 = dojo.query(`.ffo-good-${good.type}`, goodsRow);
		if (r2.length > 0) {
			return r2[0];
		}
		return null;
	}
	
	function removePlayerObjGood(player, good) {
		for (let i in player.playerObj.goods) {
			if (player.playerObj.goods[i].type == good.type) {
				player.playerObj.goods.splice(i, 1);
				break;
			}
		}
	}
	
	define({ setupNotifications });
})();