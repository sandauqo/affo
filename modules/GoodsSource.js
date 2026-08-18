// On mouse down on a goods div (player panel, or big version):
// - start dragging the resource you're on.
// - mark it as being dragged in all goods divs.
// - make and drag a big version of it
// - if released over a valid board area, leave it there and snap it
// - otherwise, vroom back to the player panel and make as not dragging

class GoodsSource {
	constructor(game, sourceDiv, soleTarget) {
		this.game = game;
		this.sourceDiv = sourceDiv;
		this.soleTarget = soleTarget;
		
		// Add connect listener
		dojo.connect(sourceDiv, "onpointerdown", this.onMouseDown.bind(this));
	}
	
	static isGoodResource(type) {
		return ['coin', 'wood', 'stone', 'ore'].includes(type);
	}
	
	onMouseDown(event) {
		const zoom = this.zoomOverride ? this.zoomOverride : this.game.zoomLevel;
		
		if (dojo.hasClass(event.target, 'ffo-icon-expand')) {
			return;
		}
		
		event.preventDefault();

		let target = null;
		let eventTarget = null;
		let clickingBackground = false;
		let targets = dojo.query(event.target).closest('.ffo-good');

		const player = this.game.playersById[this.game.playerId()];

		if (player.playerObj.boards.find(board => board.type === 'whaling')) {
			targets = targets.concat(dojo.query(event.target).closest('.ffo-ship-whalingboat'))
		}

		if (targets.length >= 1) {
			target = targets[0];
			eventTarget = target;
		}
		
		if (! target) {
			clickingBackground = true;
		}
		// TODO : If you're clicking over a "void",
		// then the target might be a different provisional div...
		// Change both target and eventTarget
		
		if (! clickingBackground && dojo.hasClass(target, 'ffo-fixed')) {
			// This is not a provisional tile, you can't drag it!
			clickingBackground = true;
		}

		let fromTarget = false;
		if (! clickingBackground && target.sourceNode) {
			if (! dojo.hasClass(target, 'ffo-provisional')) {
				// This is not a provisional tile, you can't drag it!
				clickingBackground = true;
			} else {
				target = target.sourceNode;
				fromTarget = true;
			}
		}
		
		if (! clickingBackground && dojo.hasClass(eventTarget, 'ffo-dragging')) {
			// This is already placed on the board, or being placed
			if (this.game.options.mobileControls) {
				// ...
			} else {
				clickingBackground = true;
			}
		}

		// AIM: If you are clicking yourself, move instead of doing nothing
		// if (this.game.options.mobileControls && ! clickingBackground && (GoodsSource.marked == target || GoodsSource.marked == eventTarget)) {
		// 	clickingBackground = true;
		// }
		
		// add initial clip path if new good is placed onto board
		if (target && !target.style.clipPath) {
			this.setClipPath(target, eventTarget);
		}

		if (clickingBackground) {
			if (this.linkedTarget && GoodsSource.marked && this.game.options.mobileControls) {
				// Move marked good to the clicked position! As if you'd mouse up'd there
				// set dragging
				//onMouseUp(event);
				const asset = GoodsSource.marked.information.type;
				let keepCrosshair = true;
				let setMarkedToNew = false;
				if (GoodsSource.isGoodResource(asset)) {
					if (GoodsSource.marked.sourceNode) {
						// Just move me!
						GoodsSource.marked.remove();
						setMarkedToNew = true;
					} else {
						// Place a new one
						keepCrosshair = false;
						const player = this.game.playersById[this.game.playerId()];
						if (+player.playerObj[asset] <= 0) {
							// Can't drag something you have none of
							return;
						}
						player.playerObj[asset]--;
						player.updateResourceDisplay();
					}
				} else {
					if (GoodsSource.marked.placedNode) {
						// Delete me!
						GoodsSource.marked.placedNode.remove();
						GoodsSource.marked.placedNode = null;
					}
				}
				
				const zoom = this.linkedTarget.zoomOverride ? this.linkedTarget.zoomOverride : this.game.zoomLevel;
				
				// fromTarget = coming from a Target (as opposed to a Source)
				// eventTarget = the thing we clicked on, remove it fromTarget
				// marked = thing we clicked on (or, in Source if we clicked on a target)
				this.createNewDiv(null, GoodsSource.marked, false, null, zoom);
				
				if (! keepCrosshair) {
					// Remove the crosshair
					dojo.query(".ffo-icon-crosshair", GoodsSource.dragging).remove();
				}
				if (setMarkedToNew) {
					GoodsSource.marked = GoodsSource.dragging;
				}
				
				// Find coordinates being clicked
				const dropDivRect = this.sourceDiv.getBoundingClientRect();
				const offsetLeftPre = event.clientX - dropDivRect.x;
				const offsetBottomPre = dropDivRect.height - (event.clientY - dropDivRect.y);
				const offsetLeft = offsetLeftPre / zoom;
				const offsetBottom = offsetBottomPre / zoom;
				
				const bottomLeftCoords = [
					Math.floor(offsetLeft/this.linkedTarget.cellSize),
					Math.floor(offsetBottom/this.linkedTarget.cellSize)
				];

				// Link to position of the crosshair
				const info = GoodsSource.marked.information;

				console.log(GoodsSource.getBottomLeftCoord(info))
				bottomLeftCoords[0] -= GoodsSource.getBottomLeftCoord(info);
				
				const bodyRect = document.body.getBoundingClientRect();
				const goodRect = GoodsSource.dragging.getBoundingClientRect();
				
				let overhangLeft = (goodRect.width / zoom - goodRect.width) / 2;
				let overhangTop = (goodRect.height / zoom - goodRect.height) / 2;
				
				dojo.style(GoodsSource.dragging, {
					top: (dropDivRect.y + dropDivRect.height - goodRect.height - overhangTop - bottomLeftCoords[1] * this.linkedTarget.cellSize * zoom - bodyRect.top) + 'px',
					left: (dropDivRect.x - overhangLeft + bottomLeftCoords[0] * this.linkedTarget.cellSize * zoom - bodyRect.left) + 'px',
				});
				


				// Do the onMouseUp
				// 1. Allow overlaps off the edge of the grid
				GoodsSource.onMouseUp.call( this.game, null );
				
				return;
			} else {
				return;
			}
		}

		// If clicking on a rotate/flip button, do that instead!
		if (dojo.hasClass(event.target, 'ffo-action-rotate')) {
			// Rotate me!
			// Mutate me, the source (if there is one) and change the information of both
			target.information.rotation = (target.information.rotation + 1) % 4;
			dojo.setAttr(target, 'data-rotation', target.information.rotation);
			if (target != eventTarget) {
				eventTarget.information.rotation = target.information.rotation;
				dojo.setAttr(eventTarget, 'data-rotation', target.information.rotation);
			}
			if (fromTarget) {
				// Re-add the good to its Target, to position correctly
				eventTarget.dropDiv.addGood(eventTarget, eventTarget.information.coords, false);
				eventTarget.dropDiv.checkProvisionals();
			}
			this.setClipPath(target, eventTarget);
			return;
		}
		if (dojo.hasClass(event.target, 'ffo-action-flip')) {
			// Rotate me!
			// Mutate me, the source (if there is one) and change the information of both
			target.information.flipped = !target.information.flipped;
			dojo.setAttr(target, 'data-flipped', target.information.flipped ? 1: 0);
			// If flipping on rotation 1 or 3, also rotate twice
			// This ensures that flipping is always on a vertical plane
			if (target.information.rotation == 1 || target.information.rotation == 3) {
				target.information.rotation = (target.information.rotation + 2) % 4;
				dojo.setAttr(target, 'data-rotation', target.information.rotation);
			}
			if (target != eventTarget) {
				eventTarget.information.flipped = target.information.flipped;
				dojo.setAttr(eventTarget, 'data-flipped', target.information.flipped ? 1: 0);
				eventTarget.information.rotation = target.information.rotation;
				dojo.setAttr(eventTarget, 'data-rotation', target.information.rotation);
			}
			if (fromTarget) {
				// Flipping can change overlaps!
				eventTarget.dropDiv.checkProvisionals();
			}
			this.setClipPath(target, eventTarget);
			return;
		}

		if (! target.information || GoodsSource.isGoodResource(target.information.type)) {
			// This must be a resource
			let resource = '';
			if (dojo.hasClass(target, 'ffo-icon-coin')) {
				resource = 'coin';
			} else if (dojo.hasClass(target, 'ffo-icon-wood')) {
				resource = 'wood';
			} else if (dojo.hasClass(target, 'ffo-icon-stone')) {
				resource = 'stone';
			} else if (dojo.hasClass(target, 'ffo-icon-ore')) {
				resource = 'ore';
			} else if (dojo.hasClass(target, 'ffo-ship-whalingboat')) {
				resource = 'whalingboat';
			}

			target.information = {
				type: resource,
				colour: resource,
				cols: 1,
				rows: 1,
				flipped: false,
				rotation: 0,
				cells: [],
			};
			if (! fromTarget) {
				if (! this.game.options.mobileControls) {
					// Lower the player's resource by one
					const player = this.game.playersById[this.game.playerId()];
					if (+player.playerObj[resource] <= 0) {
						// Can't drag something you have none of
						return;
					}
					player.playerObj[resource]--;
					player.updateResourceDisplay();
				}
			}
		}
		
		if (this.game.options.mobileControls) {
			// Add a crosshair to the good/resource, and mark this good as the "marked" one
			if (GoodsSource.marked) {
				// Remove any crosshair from the old good
				dojo.query(".ffo-icon-crosshair", GoodsSource.marked).remove();
				dojo.removeClass(GoodsSource.marked, 'ffo-marked');
				// Also from the placed good, if any!
				if (GoodsSource.marked.placedNode) {
					dojo.query(".ffo-icon-crosshair", GoodsSource.marked.placedNode).remove();
					dojo.removeClass(GoodsSource.marked.placedNode, 'ffo-marked');
				}
			}
			
			if (GoodsSource.isGoodResource(target.information.type)) {
				// Resource = mark the actual one
				GoodsSource.marked = eventTarget;
				dojo.addClass(GoodsSource.marked, 'ffo-marked');
				dojo.place(`<div class="ffo-icon ffo-icon-crosshair" data-x="0" data-y="0"></div>`, eventTarget);
			} else {
				// Good = mark the original
				GoodsSource.marked = target;
				dojo.addClass(GoodsSource.marked, 'ffo-marked');
				dojo.place(`<div class="ffo-icon ffo-icon-crosshair" data-x="0" data-y="0"></div>`, target);
				if (target.placedNode) {
					dojo.place(`<div class="ffo-icon ffo-icon-crosshair" data-x="0" data-y="0"></div>`, target.placedNode);
				}
			}
			// place it at (0, 0) [rotation dependent] + good dependant (e.g. anchor should be [1, 0])
			
			// If flippable, show the button
			this.game.updateMobileControls();
		} else {
			// Create a new good div, and start dragging it
			this.createNewDiv(event, target, fromTarget, eventTarget, zoom);
		}
	}
	
	createNewDiv(event, target, fromTarget, eventTarget, zoom) {
		const draggee = target.cloneNode(true);
		draggee.information = target.information;
		if (GoodsSource.isGoodResource(target.information.type)) {
			draggee.information = JSON.parse(JSON.stringify(draggee.information));
		}
		draggee.sourceNode = target;
		
		if (fromTarget) {
			eventTarget.remove();
			eventTarget.dropDiv.checkProvisionals();
		}
		
		draggee.id = ''; // Remove id so as not to have duplicate ids
		dojo.removeClass(draggee, 'ffo-dragging');
		dojo.removeClass(draggee, 'ffo-marked');
		dojo.addClass(draggee, 'ffo-draggee');
		const fadeOutSource = ! GoodsSource.isGoodResource(target.information.type);
		if (fadeOutSource) {
			dojo.addClass(target, 'ffo-dragging');
		}
		document.body.appendChild(draggee);
		GoodsSource.dragging = draggee;
		draggee.soleTarget = this.soleTarget;
		dojo.style(draggee, 'transform', 'scale(' + zoom +')');
		if (event) {
			GoodsSource.onMouseMove(event);
		}
	}
	
	static returnToSource(good, animate = true, updateResource = true) {
		const sourceNode = good.sourceNode;
		const clientRect = sourceNode.getBoundingClientRect();
		const targetLeft = clientRect.left + window.scrollX;
		const targetTop = clientRect.top + window.scrollY;
		
		// Reparent to the body and reposition
		if (good.parentNode != document.body) {
			const previousParentRect = good.parentNode.getBoundingClientRect();
			document.body.appendChild(good);
			good.style.position = "absolute";
			good.style.zIndex = 2010;
			good.style.top = (good.offsetTop + previousParentRect.top) + "px";
			good.style.left = (good.offsetLeft + previousParentRect.left) + "px";
		}
		
		if (animate) {
			gameui.anime({
				targets: good,
				duration: 200,
				left: targetLeft + 'px',
				top: targetTop + 'px',
				width: clientRect.width + 'px',
				height: clientRect.height + 'px',
				easing: 'easeOutQuad',
				complete: function(anim) {
					dojo.removeClass(sourceNode, 'ffo-dragging');
					good.remove();
				}
			});
		} else {
			dojo.style(good, {
				left: targetLeft + 'px',
				top: targetTop + 'px',
				width: clientRect.width + 'px',
				height: clientRect.height + 'px',
			});
			dojo.removeClass(sourceNode, 'ffo-dragging');
			good.remove();
		}
		if (sourceNode.children.length > 0) {
			const innerClientRect = sourceNode.children[0].getBoundingClientRect();
			const sideways = good.information.rotation == 1 || good.information.rotation == 3;
			if (animate) {
				gameui.anime({
					targets: good.children[0],
					duration: 200,
					width: (sideways ? innerClientRect.height : innerClientRect.width) + 'px',
					height: (sideways ? innerClientRect.width : innerClientRect.height) + 'px',
					easing: 'easeOutQuad',
				});
			} else {
				dojo.style(good, {
					width: (sideways ? innerClientRect.height : innerClientRect.width) + 'px',
					height: (sideways ? innerClientRect.width : innerClientRect.height) + 'px',
				});
			}
		}
		
		if (updateResource && GoodsSource.isGoodResource(good.information.type)) {
			// Add the resource back
			const player = gameui.playersById[gameui.playerId()];
			player.playerObj[good.information.type]++;
			player.updateResourceDisplay();
		}
	}
	
	static onMouseUp(event) {
		if (! GoodsSource.dragging ) return;
		//this.game.slideToObjectAndDestroy(GoodsSource.dragging, GoodsSource.dragging.sourceNode, 200, 0);
		const dragging = GoodsSource.dragging;
		
		let accepted = false;
		for (const i in this.goodsTargets) {
			const target = this.goodsTargets[i];
			if (dragging.soleTarget != null && target != dragging.soleTarget)
				continue;
			if (accepted = target.accept(dragging)) {
				break;
			}
		}
		
		
		if (accepted) {
			// - over target, drop and snap it
			accepted.target.addGood(dragging, accepted.bottomLeft, true, true);
			if (dragging.sourceNode) {
				dragging.sourceNode.placedNode = dragging;
			}
			
			// 1. Add this div as a child of the target
			// 2. anime() to the correct location
			// 3. set to faded out (.provisional)
			// 4. allow it to be dragged itself?
			// 5. set the source tile to be .placed?
		} else {
			// - otherwise, zoom back to original source
			GoodsSource.returnToSource(dragging);
			if (dragging.sourceNode) {
				dragging.sourceNode.placedNode = null;
			}
		}
		
		// Show "You have unsaved changes" message : Cancel / Save
		let unsavedChanges = this.hasUnsavedChanges();
		$('ffo-unsaved-message').style.display = unsavedChanges ? 'block' : 'none';
		this.updatePlacementButtons();
		GoodsSource.dragging = null;
	}
	
	static onMouseMove(event) {
		if (! GoodsSource.dragging ) return;
		
		let dtop = 0;
		if (dojo.hasClass(document.body, "ffo-modal-open")) {
			dtop -= parseInt(document.body.style.top);
		}
		
		GoodsSource.dragging.style.left = (event.pageX - GoodsSource.dragging.offsetWidth / 2) + 'px';
		GoodsSource.dragging.style.top = (dtop + event.pageY - GoodsSource.dragging.offsetHeight / 2) + 'px';
		
		// Temporary
		if (false) {
			let accepted = false;
			for (const i in this.goodsTargets) {
				const target = this.goodsTargets[i];
				if (GoodsSource.dragging.soleTarget != null && target != GoodsSource.dragging.soleTarget)
					continue;
				if (target.accept(GoodsSource.dragging)) {
					accepted = true;
					break;
				}
			}
		}
	}

	static getBottomLeftCoord(info) {
		const matrix = new GoodMatrix(info);
		const bottomRow = matrix.matrix.reverse()[0];
		return bottomRow.findIndex(i => i == 1);
	}

	getClipPath(target) {

		if (target.information && target.information.type === 'sleigh') return null;

		if (target.information) {
			// this is wrong target, have to do one on the board
			// also, initial clipPath not calculated
			const matrix = new GoodMatrix(target.information); 
			return matrix.generateClipPath();
		}
	}

	setClipPath(target, eventTarget) {
		const clipPath = this.getClipPath(target);
		if (clipPath) {
			dojo.style(target, 'clip-path', clipPath);
			dojo.style(eventTarget, 'clip-path', clipPath);
		}
	}
}

GoodsSource.dragging = null;
GoodsSource.marked = null;