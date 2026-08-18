// On mouse down on a goods div (player panel, or big version):
// - start dragging the resource you're on.
// - mark it as being dragged in all goods divs.
// - make and drag a big version of it
// - if released over a valid board area, leave it there and snap it
// - otherwise, vroom back to the player panel and make as not dragging

class GoodsSource {
	constructor(game, sourceDiv) {
		this.game = game;
		this.sourceDiv = sourceDiv;
		// Add connect listener
		dojo.connect(sourceDiv, "onpointerdown", this.onMouseDown.bind(this));
	}
	
	onMouseDown(event) {
		event.preventDefault();
		
		const targets = dojo.query(event.target).closest('.ffo-good');

		if (targets.length >= 1) {
			// Zoom back any other 'dragging' goods
			// From ALL sources
			// ...
			
			// Fade this good out
			// Todo: should be from ALL sources
			const target = targets[0];
			
			// Create a new good div, and start dragging it
			const draggee = target.cloneNode(true);
			draggee.id = ''; // Remove id so as not to have duplicate ids
			dojo.addClass(draggee, 'ffo-draggee');
			dojo.addClass(target, 'ffo-dragging');
			draggee.sourceNode = target;
			document.body.appendChild(draggee);
			GoodsSource.dragging = draggee;
			// TODO: call onMouseMove now, with an appropriate event?
			// animate it up to the right size?
		}
	}
	
	static onMouseUp(event) {
		if (! GoodsSource.dragging ) return;
		//this.game.slideToObjectAndDestroy(GoodsSource.dragging, GoodsSource.dragging.sourceNode, 200, 0);
		const dragging = GoodsSource.dragging;
		
		// - over target, drop and snap it
		{
			// TODO
		}
		
		// - otherwise, zoom back to original source
		{
			const sourceNode = dragging.sourceNode;
			const clientRect = sourceNode.getBoundingClientRect();
			const targetLeft = clientRect.left + window.scrollX;
			const targetTop = clientRect.top + window.scrollY;
			this.anime({
				targets: dragging,
				duration: 200,
				left: targetLeft + 'px',
				top: targetTop + 'px',
				width: sourceNode.offsetWidth + 'px',
				height: sourceNode.offsetHeight + 'px',
				easing: 'easeOutQuad',
				complete: function(anim) {
					dojo.removeClass(dragging.sourceNode, 'ffo-dragging');
					dragging.remove();
				}
			});
		}
		
		// Remove listeners
		
		GoodsSource.dragging = null;
	}
	
	static onMouseMove(event) {
		if (! GoodsSource.dragging ) return;
		
		GoodsSource.dragging.style.left = event.pageX - GoodsSource.dragging.offsetWidth / 2 + 'px';
		GoodsSource.dragging.style.top = event.pageY - GoodsSource.dragging.offsetHeight / 2 + 'px';
	}
}

// Currently dragging good
GoodsSource.dragging = null;