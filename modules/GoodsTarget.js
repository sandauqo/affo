class GoodsTarget {
	constructor(game, targetDiv, information, me, player, selfOnly, originalTarget = null) {
		this.game = game;
		this.me = me;
		this.player = player;
		this.targetDiv = targetDiv;
		// Add new div inside targetDiv which is the actual drop div
		this.dropDiv = dojo.place("<div class=\"ffo-drop-div\"></div>", this.targetDiv);
		if (me && information.type != "shed") {
			this.expandButton = dojo.place(`<div class="ffo-icon ffo-icon-expand"></div>`, this.dropDiv);
			
			if (dojo.hasClass(this.targetDiv, "ffo-board-zoomed")) {
				dojo.connect(this.expandButton, "onclick", this.game.onCloseModalGoodsTarget.bind(this.game, this, originalTarget));
			} else {
				dojo.connect(this.expandButton, "onclick", this.game.onShowModalGoodsTarget.bind(this.game, this));
			}
		}
		
		this.information = information;
		this.cellSize = 32;
		
		this.linkedSource = this.game.addGoodsSource(this.dropDiv, selfOnly ? this : null);
		// add to linked source? makes it move once snapped though
		// this.game.addGoodsSource(this.dropDiv, selfOnly ? this : null); // TODO: why was this here
		this.linkedSource.linkedTarget = this;
		
		// TODO : offset depends on the type of board...
		// switch (this.information.type) {}
		// home, exploration (+ name), shed, stonehouse, longhouse
		// ...
		let expandLeft = 0;
		let expandTop = -7;

		let left = 0;
		let top = 0;
		
		if (this.information.type == "home") {
			left = 14;
			top = 10;
			expandTop = -5;
		} else if (this.information.type == "homefeast") {
			left = 462;
			top = 20;
			// offsetLeft/Bottom are where cells are 32px
			// If this is the homefeast-zoomed, we need to add an adjustment
			if (dojo.hasClass(this.targetDiv, "ffo-homefeast-zoomed")) {
				left -= 448;
				top -= 4;
			}
			expandTop = -19;
			expandLeft = -19;
		} else if (this.information.type == "shetland") {
			left = 43;
			top = 50;
		} else if (this.information.type == "bearisland") {
			left = 75;
			top = 50;
		} else if (this.information.type == "faroeislands") {
			left = 31;
			top = 25;
			expandTop = 32;
		} else if (this.information.type == "baffinisland") {
			left = 42;
			top = 49;
			expandLeft = -34;
		} else if (this.information.type == "iceland") {
			left = 72;
			top = 54;
		} else if (this.information.type == "labrador") {
			left = 42;
			top = 49;
		} else if (this.information.type == "greenland") {
			left = 42;
			top = 49;
		} else if (this.information.type == "newfoundland") {
			left = 42;
			top = 49;
		} else if (this.information.type == "orkney") {
			left = 28;
			top = 58;
			expandLeft = -4;
		} else if (this.information.type == "lofoten") {
			left = 20;
			top = 45;
		} else if (this.information.type == "tierradelfuego") {
			left = 15;
			top = 75;
			expandTop = -35;
		} else if (this.information.type == "shed") {
			left = 0;
			top = 0;
		} else if (this.information.type == "stonehouse") {
			left = 239;
			top = 24;
			expandLeft = -4;
		} else if (this.information.type == "longhouse") {
			left = 27;
			top = 55;
			expandTop = -34;
		} else if (this.information.type == "whaling") {
			left = 26;
			top = 47;
			expandTop = -34;
			expandLeft = 344;
		} else if (this.information.type == "spinnery") {
			left = 235;
			top = 27;
			expandTop = -16;
			expandLeft = 130;
		} else if (this.information.type == "mason") {
			left = 235;
			top = 47;
			expandTop = -39;
			expandLeft = 130;
		} else if (this.information.type == "beekeeper") {
			left = 235;
			top = 15;
			expandTop = -9;
			expandLeft = 0;
		} else if (this.information.type == "icehouse") {
			left = 235;
			top = 47;
			expandTop = -39;
			expandLeft = 130;
		} else if (this.information.type == "hermit") {
			left = 235;
			top = 15;
			expandTop = -7;
			expandLeft = 133;
		} else if (this.information.type == "pigsty") {
			left = 235;
			top = 47;
			expandTop = -39;
			expandLeft = 130;
		} else if (this.information.type == "hunting") {
			left = 235;
			top = 15;
			expandTop = 40;
			expandLeft = 133;
		} else if (this.information.type == "armoury") {
			left = 267;
			top = 15;
			expandTop = -10;
			expandLeft = -37;
		} else if (this.information.type == "cowshed") {
			left = 235;
			top = 15;
			expandTop = -8;
			expandLeft = 132;
		} else if (this.information.type == "lumberjack") {
			left = 234;
			top = 47;
			expandTop = -42;
			expandLeft = 0;
		} else if (this.information.type == "barn") {
			left = 202;
			top = 47;
			expandTop = -42;
			expandLeft = 34;
		} else if (this.information.type == "isleofman") {
			left = 25;
			top = 58;
		} else if (this.information.type == "isleofskye") {
			left = 18;
			top = 65;
			expandTop = -27;
		} else if (this.information.type == "limerick") {
			left = 77;
			top = 65;
			expandLeft = -55;
		} else if (this.information.type == "wexford") {
			left = 23;
			top = 66;
			expandTop = -32;
			expandLeft = -5;
		} else if (this.information.type == "waterford") {
			left = 16;
			top = 56;
			expandTop = -27;
		} else if (this.information.type == "outerhebrides") {
			left = 25;
			top = 60;
		} else if (this.information.type == "islay") {
			left = 21;
			top = 61;
		} else if (this.information.type == "cork") {
			left = 9;
			top = 85;
			expandTop = -37;
		} else if (this.information.type == "isleofmull") {
			left = 26;
			top = 58;
		} else if (this.information.type == "caithness") {
			left = 9;
			top = 100;
		}
		
		dojo.style(this.dropDiv, {
			left: left + 'px',
			top: top + 'px',
			width: (this.information.cols * this.cellSize) + 'px',
			height: (this.information.rows * this.cellSize) + 'px',
		});

		if (this.expandButton) {
			dojo.style(this.expandButton, {
				left: expandLeft + 'px',
				top: expandTop + 'px',
			});
		}
	}
	
	isValidCoords(coords, checkVoids = true, good = false) {
		if (coords[0] < 0) return false;
		if (coords[1] < 0) return false;
		if (coords[0] >= this.information.cols) return false;
		if (coords[1] >= this.information.rows) return false;
		if (checkVoids) {
			for (const c in this.information.cells) {
				const cell = this.information.cells[c];

				if (cell.coords[0] == coords[0] && cell.coords[1] == coords[1]) {
					if (cell.type == "void") {
						// This is on a void cell
						return false;
					}

					if (good && cell.type == "unique") {
						// const goodType = good.information.type.replace("pregnant", ''); // should allow pregnanthorse ?
						if (cell.uniqueType !== good.information.type) {
							return false;
						}
					}
				}
			}
		}
		if (this.information.feast) {
			// Current round and rightwards is not valid
			let spaces = 5 + +gameui.gamedatas.round;
			if (gameui.gamedatas.options.short_game) {
				spaces++;
			}
			if (coords[0] >= spaces) {
				return false;
			}
		}
		return true;
	}
	
	// Print coords
	static pc(coords) {
		return coords[0] + ', ' + coords[1];
	}
	
	static neighbours(coords) {
		return [
			[coords[0] - 1, coords[1]],
			[coords[0] + 1, coords[1]],
			[coords[0], coords[1] - 1],
			[coords[0], coords[1] + 1],
		];
	}
	
	static goodCells(good) {
		const coords = [];
		for (let c = 0; c < good.information.cols; c++) {
			for (let r = 0; r < good.information.rows; r++) {
				// If this cell is void, skip it!
				let skip = false;
				for (const cc in good.information.cells) {
					const cell = good.information.cells[cc];
					if (cell.coords[0] == c && cell.coords[1] == r) {
						if (cell.type == "void") {
							skip = true;
							break;
						} else {
							alert("Invalid good cell type: " + cell.type);
						}
					}
				}
				
				if (skip) continue;
				
				// Find this cell on the Target
				// Tranform according to flipped & rotated
				let effectiveRotation = good.information.rotation;
				if (good.information.flipped) {
					// If flipped, and rotation is odd, rotate twice
					if (effectiveRotation == 1 || effectiveRotation == 3) {
						effectiveRotation = (effectiveRotation + 2) % 4;
					}
				}
				const theta = effectiveRotation * Math.PI/2;
				const rotationMatrix = [
					[Math.cos(theta), -Math.sin(theta)],
					[Math.sin(theta), Math.cos(theta)],
				];
				const transformedCoords = [
					rotationMatrix[0][0] * c + rotationMatrix[1][0] * r,
					rotationMatrix[0][1] * c + rotationMatrix[1][1] * r,
				];
				
				// Translate to be from bottom-left corner, not from transformed origin
				if (effectiveRotation == 1) {
					transformedCoords[1] += good.information.cols - 1;
				} else if (effectiveRotation == 2) {
					transformedCoords[0] += good.information.cols - 1;
					transformedCoords[1] += good.information.rows - 1;
				} else if (effectiveRotation == 3) {
					transformedCoords[0] += good.information.rows - 1;
				}
				
				transformedCoords[0] = Math.round(transformedCoords[0]);
				transformedCoords[1] = Math.round(transformedCoords[1]);
				
				if (good.information.flipped) {
					// Swap to the other side x-direction
					if (effectiveRotation == 0 || effectiveRotation == 2) {
						transformedCoords[0] = good.information.cols - transformedCoords[0] - 1;
					} else {
						transformedCoords[0] = good.information.rows - transformedCoords[0] - 1;
					}
				}
				
				coords.push(
					transformedCoords
				);
			}
		}
		return coords;
	}
	
	accept(good) {
		if (good.information.colour === 'whalingboat') {
			good.information.cols = 3;
			good.information.rows = 3;
		}
		
		const zoom = this.zoomOverride ? this.zoomOverride : this.game.zoomLevel;
		
		// You can only place on your own targets
		if (! this.me) return false;
		
		// You cannot place on feast board outside designated phase
		if (! gameui.options.allowPrePlanningFeast) {
			if (this.information.type == "homefeast") {
				if (gameui.gamedatas.gamestate.name != "feast-placegoods" && gameui.gamedatas.gamestate.name != "command-personalfeast") {
					return false;
				}
			}
		}

		// Only allow goods of the valid colours
		if (! this.information.allowedColours.includes(good.information.colour)) {
			if (good.information.colour == "wood" && 
				this.player.hasOcc(40) && 
				["stonehouse", "longhouse"].includes(this.information.type))
			{
				// Allow it after all!
			} else {
				return false;
			}
		}

		const goodRect = good.getBoundingClientRect();
		const dropDivRect = this.dropDiv.getBoundingClientRect();
		
		// 1. Convert top/left of the good to top/left relative to dropDiv
		const offsetLeftPre = goodRect.left - dropDivRect.left;
		const offsetBottomPre = dropDivRect.height - (goodRect.top - dropDivRect.top + goodRect.height);
		
		const offsetLeft = offsetLeftPre / zoom;
		const offsetBottom = offsetBottomPre / zoom;
		
		// 2. Find coords of top-left cell
		// Assuming 32px cells
		const bottomLeftCoords = [
			Math.floor((offsetLeft + this.cellSize/2)/this.cellSize),
			Math.floor((offsetBottom + this.cellSize/2)/this.cellSize)
		];
		
		if (this.information.feast) {
			// Must touch the bottom row
			if (bottomLeftCoords[1] > 0) {
				return false;
			}
		}
		
		// 3. For each cell in the good, find the corresponding cell in the target
		// If rotated/flipped, these cells are different!
		// Mobile: only one cell needs be valid
		// Desktop: all must be valid (might change this?)
		const cells = GoodsTarget.goodCells(good);
		let numValids = 0;
		for (const i in cells) {
			const coords = cells[i];
			
			const targetCoords = [
				bottomLeftCoords[0] + coords[0],
				bottomLeftCoords[1] + coords[1]
			];
			
			const valid = this.isValidCoords(targetCoords, true, good);
			if (valid) {
				numValids++;
			}
			if (! this.game.options.mobileControls && ! valid) {
				return false;
			}
		}
		
		if (this.game.options.mobileControls && numValids == 0) {
			return false;
		}

		if (good.getAttribute('data-ship-type') == 'whalingboat') { // another whalingboat exeption: just force whalingboat into place
			return {
				target: this,
				bottomLeft: [0, 0],
			}
		}

		return {
			target: this,
			bottomLeft: bottomLeftCoords,
		};
	}
	
	repositionGoods() {
		const zoom = this.zoomOverride ? this.zoomOverride : this.game.zoomLevel;
		
		// For each good, place it well!
		const goods = dojo.query(".ffo-good", this.dropDiv);
		for (let i = 0; i < goods.length; i++) {
			const good = goods[i];
			const bottomLeft = good.information.coords;
			const goodRect = good.getBoundingClientRect();
			const dropDivRectPre = this.dropDiv.getBoundingClientRect();
			const dropDivRect = {
				height: dropDivRectPre.height / zoom,
				left: dropDivRectPre.left,
				top: dropDivRectPre.top,
			};
			const targetGoodRectHeight = ((good.information.rotation == 1 || good.information.rotation == 3) ? good.information.cols : good.information.rows) * this.cellSize;
			const targetTop = dropDivRect.height - targetGoodRectHeight - bottomLeft[1] * this.cellSize;
			
			dojo.style(good, {
				left: (bottomLeft[0] * this.cellSize) + 'px',
				top: (targetTop) + 'px',
			});
		}
	}
	
	addGood(good, bottomLeft, animate = true, provisional = true) {
		const zoom = this.zoomOverride ? this.zoomOverride : this.game.zoomLevel;

		const goodRect = good.getBoundingClientRect();
		const dropDivRectPre = this.dropDiv.getBoundingClientRect();
		const dropDivRect = {
			height: dropDivRectPre.height / zoom,
			left: dropDivRectPre.left,
			top: dropDivRectPre.top,
		};

		good.dropDiv = this;
		good.information.coords = bottomLeft;
		
		// 1. Convert top/left of the good to top/left relative to dropDiv
		let targetGoodRectHeight = ((good.information.rotation == 1 || good.information.rotation == 3) ? good.information.cols : good.information.rows) * this.cellSize;
		let targetTop = dropDivRect.height - targetGoodRectHeight - bottomLeft[1] * this.cellSize;
		
		// Add the good to the dropDiv
		this.dropDiv.appendChild(good);
		dojo.style(good, {
			left: ((goodRect.left - dropDivRect.left)/zoom) + 'px',
			top: ((goodRect.top - dropDivRect.top)/zoom) + 'px',
			transform: '',
		});
		
		let targetLeft = bottomLeft[0] * this.cellSize;

		if (good.getAttribute('data-ship-type') == 'whalingboat' 
			|| good.getAttribute('good-type') == 'whalingboat' 
			|| dojo.hasClass(good, 'ffo-good-whalingboat')) { 
			// another whalingboat exeption: non standard size so just force into place
			targetLeft = 15;
			targetTop = -5;
		}

		if (animate) {
			this.game.anime({
				targets: good,
				duration: 200,
				left: (targetLeft) + 'px',
				top: (targetTop) + 'px',
				easing: 'easeOutQuad'
			});
		} else {
			dojo.style(good, {
				left: (targetLeft) + 'px',
				top: (targetTop) + 'px',
			});
		}
		
		if (provisional) {
			dojo.removeClass(good, 'ffo-draggee');
			dojo.addClass(good, 'ffo-provisional');		
		} else {
			dojo.addClass(good, 'ffo-fixed');
			// Remove the toolbox
			dojo.query(".ffo-good-toolbox", good).remove();
		}
	
		this.checkProvisionals();
	}
	
	static makeErroneous(good, why) {
		if (dojo.hasClass(good, 'ffo-provisional'))
			dojo.addClass(good, 'ffo-erroneous');
			good.setAttribute('data-error-why', why);
	}
	
	checkProvisionals() {
		const goods = dojo.query(".ffo-good", this.dropDiv);

		for (let g = 0; g < goods.length; g++) {
			const good = goods[g];
			dojo.removeClass(good, 'ffo-erroneous');
			good.removeAttribute('data-error-why');
		}

		// Build array of all cells. Each is a list of the goods which overlap it
		const data = [];
		for (let i = 0; i < this.information.cols; i++) {
			const column = [];
			for (let j = 0; j < this.information.rows; j++) {
				let type = null;
				for (const c in this.information.cells) {
					const cell = this.information.cells[c];
					if (cell.coords[0] == i && cell.coords[1] == j) {
						// If a cell is multiple types, we only care about void
						if (type == null || type != 'void') {
							type = cell.type;
						}
					}
				}
				column.push({
					coords: [i, j],
					goods: [],
					type: type
				});
			}
			data.push(column);
		}

		const availableResources = {
			coin: this.player.playerObj.coin,
			wood: this.player.playerObj.wood,
			stone: this.player.playerObj.stone,
			ore: this.player.playerObj.ore,
		};

		// Loop through all goods, and add them to data
		for (let g = 0; g < goods.length; g++) {
			const good = goods[g];
			
			// Check we have the resources
			if (GoodsSource.isGoodResource(good.information.type)) {
				if (availableResources[good.information.type] < 0) {
					// Over the limit!
					GoodsTarget.makeErroneous(good, 'resources');
				}
				availableResources[good.information.type]++;
			}
			
			// Loop through each cell of this good
			let uniqueTypeCellCount = 0;
			const cells = GoodsTarget.goodCells(good);
			for (const i in cells) {
				const coords = cells[i];
				
				const bottomLeftCoords = good.information.coords;
				const targetCoords = [
					bottomLeftCoords[0] + coords[0],
					bottomLeftCoords[1] + coords[1]
				];
				
				// If they overlap other goods: red
				if (this.isValidCoords(targetCoords, false)) {
					const datum = data[targetCoords[0]][targetCoords[1]];
					if (datum.goods.length > 0) {
						// Collision!
						// Mark red
						GoodsTarget.makeErroneous(good, 'collision');
						
						for (let gg = 0; gg < datum.goods.length; gg++) {
							const collider = datum.goods[gg];
							GoodsTarget.makeErroneous(collider, 'collision');
						}
					}
					data[targetCoords[0]][targetCoords[1]].goods.push(good);

					// if one cell type is unique, all of them have to be
					if (datum.type == "unique") {
						uniqueTypeCellCount++;
						if (uniqueTypeCellCount !== +i + 1) {
							GoodsTarget.makeErroneous(good, 'unique-tile-error');
						}
					}

				}
				
				// If they overhang the edge or a void: red
				if (! this.isValidCoords(targetCoords)) {
					// Overlaps void!
					GoodsTarget.makeErroneous(good, 'overlaps-void');
				}
				
				// If they touch a forbidden colour: red
				// Check 4 adjacent cells for different, same-coloured goods
				const neighbours = GoodsTarget.neighbours(targetCoords);
				if (this.information.nonTouchingColours.includes(good.information.colour)) {
					for (const n in neighbours) {
						const nn = neighbours[n];
						if (this.isValidCoords(nn, false)) {
							const datum = data[nn[0]][nn[1]];
							if (datum.goods.length > 0) {
								for (let gg = 0; gg < datum.goods.length; gg++) {
									const neighbour = datum.goods[gg];
									if (neighbour != good) {
										// Mark both red
										if (neighbour.information.colour == good.information.colour) {
											GoodsTarget.makeErroneous(neighbour, 'touching');
											GoodsTarget.makeErroneous(good, 'touching');
										}
									}
								}
							}
						}
					}
				}
			}
		}

		// Can't cover an income space which has non-void blanks down and left
		for (const c in this.information.cells) {

			const cell = this.information.cells[c];
			if (cell.type == "income") {

				// Only bother checking if covered by a provisional
				const datum = data[cell.coords[0]][cell.coords[1]];

				if (datum.goods.length > 0) {
					for (let gg = 0; gg < datum.goods.length; gg++) {
						const good = datum.goods[gg];
						if (dojo.hasClass(good, "ffo-provisional")) {
							// Check all cells down and left of this!
							let gap = false;
							outer: for (let i = 0; i <= cell.coords[0]; i++) {
								for (let j = 0; j <= cell.coords[1]; j++) {
									// Don't check the income space itself
									if (i == cell.coords[0] && j == cell.coords[1])
										continue;
									// If the cell is covered, fine!
									if (data[i][j].goods.length > 0)
										continue;
									// If the cell is a void or bonus, fine!
									if (data[i][j].type == "void" || data[i][j].type == "bonus")
										continue;
									gap = true;
									break outer;
								}
							}
							if (gap) {
								GoodsTarget.makeErroneous(good, 'income');
							}
						}
					}
				}
			}
		}
		
		if (this.information.feast) {
			// Must touch the bottom row
			// (server-side only)
			// Can't overlap the current round, or further right
			// (server-side only)
			// Only one good of each type can be horizontal
			const horizontalGoods = {};
			for (let g = 0; g < goods.length; g++) {
				const good = goods[g];
				if (good.information.cols == good.information.rows) {
					// Square goods are exempt
					continue;
				}
				if (good.information.rotation == 0 || good.information.rotation == 2) {
					if (! horizontalGoods[good.information.type]) {
						horizontalGoods[good.information.type] = 0;
					}
					horizontalGoods[good.information.type] += 1;
				}
			}
			for (let g = 0; g < goods.length; g++) {
				const good = goods[g];
				if (dojo.hasClass(good, 'ffo-provisional')) {
					if (good.information.rotation == 0 || good.information.rotation == 2) {
						let horizontalLimit = 1;
						if (good.information.type == "peas" && this.player.hasOcc(186)) {
							horizontalLimit = 2;
						}
						if (horizontalGoods[good.information.type] > horizontalLimit) {
							// Error
							GoodsTarget.makeErroneous(good, 'horizontal');
						}
					}
				}
			}
			
			// Are you feeding?
			let spaces = 5 + +this.game.gamedatas.round;
			if (this.game.options.short_game) {
				spaces++;
			}
			this.feeding = true;
			for (let i = (this.player.playerObj.emigrations.length * 2); i < spaces; i++) {
				const datum = data[i][0];
				if (datum.goods.length == 0) {
					// Not fed!
					this.feeding = false;
					break;
				}
			}
		}
	}
	
	hasProvisional() {
		const goods = dojo.query(".ffo-good.ffo-provisional", this.dropDiv);
		return goods.length > 0;
	}

	overlaps(brE, brC ) {
		if (
			/* Does container left or right edge pass through element? */
			((brE.left < brC.left && brE.right > brC.left) ||
			(brE.left < brC.right && brE.right > brC.right)) &&
			/* Does container top or bottom edge pass through element? */
			((brE.top < brC.top && brE.bottom > brC.top) ||
			(brE.top < brC.bottom && brE.bottom > brC.bottom))) {
		
			return true;
		  }
		  return false;
	}

}