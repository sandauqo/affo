(function() {
	function onPass( event ) {
		dojo.stopEvent( event );
		
		if ( ! this.checkAction( 'pass' ) ) {
			return;
		}
		
		this.ajaxcall( "/feastforodin/feastforodin/pass.html", { 
			lock: true,
		}, this,
			function( result ) {},
			function( is_error) {}
		);
	}
	
	function onPassVikings( event ) {
		dojo.stopEvent( event );
		
		if ( ! this.checkAction( 'pass' ) ) {
			return;
		}
		
		this.confirmationDialog( _('You still have vikings which you will not be able to play if you pass. Are you sure you are done?'), dojo.hitch( this, function() {
			onPass.call( this, event );
		} ) );
	}
	
	function onGoBack( event ) {
		dojo.stopEvent( event );
		
		if ( ! this.checkAction( 'goBack' ) ) {
			return;
		}
		
		this.ajaxcall( "/feastforodin/feastforodin/goBack.html", { 
			lock: true,
		}, this,
			function( result ) {},
			function( is_error) {}
		);
	}

	function onUndo( event ) {
		dojo.stopEvent( event );
		
		if ( ! this.checkAction( 'undo' ) ) {
			return;
		}
		
		this.ajaxcall( "/feastforodin/feastforodin/undo.html", { 
			lock: true,
		}, this,
			function( result ) {},
			function( is_error) {}
		);
	}
	
	function onPassCheck( event ) {
		dojo.stopEvent( event );
		
		let unsavedChanges = this.hasUnsavedChanges();
		if (unsavedChanges) {
			// Ask user to please cancel or save them
			this.confirmationDialog( _('You have placed goods on your boards which have not been saved. Are you sure you are done?'), dojo.hitch( this, function() {
				onPass.call( this, event );
			} ) );
		} else {
			onPass.call( this, event );
		}
	}
	
	function onPassEndGameCheck( event, checkUnsaved = true ) {
		dojo.stopEvent( event );
		
		let unsavedChanges = checkUnsaved ? this.hasUnsavedChanges() : false;
		let unplacedResources = false;
		
		// Do you have unfulfilled resource slots?
		const player = this.playersById[this.playerId()];
		let availableWood = player.playerObj.wood;
		let availableStone = player.playerObj.stone;
		for (let i = player.playerObj.boards.length - 1; i >= 0; i--) {
			const b = player.playerObj.boards[i];
			if (b.type == 'shed') {
				if (! b.wood) b.wood = 0;
				if (! b.stone) b.stone = 0;
				if (b.wood < 3 && availableWood > 0) {
					unplacedResources = true;
					break;
				}
				if (b.stone < 3 && availableStone > 0) {
					unplacedResources = true;
					break;
				}
			} else if (b.type == 'stonehouse') {
				if (! b.wood) b.wood = 0;
				if (! b.stone) b.stone = 0;
				if (b.wood < 1 && availableWood > 0) {
					unplacedResources = true;
					break;
				}
				if (b.stone < 1 && availableStone > 0) {
					unplacedResources = true;
					break;
				}
			}
		}
		
		const pendingDialogs = [];
		if (unsavedChanges) {
			pendingDialogs.push(_('You have placed goods on your boards which have not been saved. Are you sure you are done?'));
		}
		if (unplacedResources) {
			pendingDialogs.push(_('You have sheds and/or houses with empty resource spaces you could fill. Are you sure you are done?'));
		}
		
		if (pendingDialogs.length > 0) {
			// Ask user to please cancel or save them
			this.confirmationDialog( pendingDialogs[0], () => {
				if (pendingDialogs.length > 1) {
					this.confirmationDialog( pendingDialogs[1], () => {
						onPass.call( this, event );
					} );
				} else {
					onPass.call( this, event );
				}
			} );
		} else {
			onPass.call( this, event );
		}
	}
	
	function onPassFeastCheck( event ) {
		dojo.stopEvent( event );
		
		let unsavedChanges = this.hasUnsavedChanges();
		if (unsavedChanges) {
			// Ask user to please cancel or save them
			this.confirmationDialog( _('You have placed goods on your boards which have not been saved. Are you sure you are done?'), dojo.hitch( this, function() {
				onPassFeast.call( this, event );
			} ) );
		} else {
			onPassFeast.call( this, event );
		}
	}
	
	function onPassFeast( event ) {
		dojo.stopEvent( event );
		const player = this.playersById[this.playerId()];
		
		// Are you actually feeding your vikings?
		for (const i in this.goodsTargets) {
			const gt = this.goodsTargets[i];
			if (gt.information.type == "homefeast" && gt.player.playerId == player.playerId) {
				gt.checkProvisionals();
				if (! gt.feeding) {
					this.confirmationDialog( _('You are not fully feeding your vikings and will receive a penalty. Are you sure you are done?'), dojo.hitch( this, function() {
						onPass.call( this, event );
					} ) );
				} else {
					onPass.call( this, event );
				}
				break;
			}
		}
	}
	
	function onSelectGoods( event ) {
		dojo.stopEvent( event );
		
		if ( ! this.checkAction( 'selectGoods' ) ) {
			return;
		}
		
		// Get all selected goods in top-tray
		const tray = $('ffo-top-tray');
		const goodElements = dojo.query('.ffo-good.ffo-provisional', tray);
		const allGoodElements = dojo.query('.ffo-good', tray);
				
		if (this.gamedatas.gamestate.args && this.gamedatas.gamestate.args.action_id == 115) {
			const goodsOnce = [];
			const goodsTwice = [];
			for (let i = 0; i < goodElements.length; i++) {
				if (goodElements[i].parentElement.id == 'once-row') {
					goodsOnce.push(goodElements[i].getAttribute('data-good'));
				} else {
					goodsTwice.push(goodElements[i].getAttribute('data-good'));
				}
			}

			if (!goodsOnce.length && !goodsTwice.length) {
				this.showMessage( 'No goods selected ', 'error' );
				return;
			}

			if (goodsOnce.length > 2 || goodsTwice.length > 1) {
				this.showMessage( 'Too many goods selected ', 'error' );
				return;
			}

			if (goodsOnce.length < 2 || goodsTwice.length < 1) {
				const message = 'You have not selected the maximum number of goods that you can. Are you sure you are done?'
				this.confirmationDialog( message, dojo.hitch( this, function() {
					this.ajaxcall( "/feastforodin/feastforodin/selectGoods.html", { 
						lock: true,
						goods: goodsOnce.join('-'),
						goods2: goodsTwice.join('-'),
					}, this,
						function( result ) {},
						function( is_error) {}
					);
				} ) );
			} else {
				this.ajaxcall( "/feastforodin/feastforodin/selectGoods.html", { 
					lock: true,
					goods: goodsOnce.join('-'),
					goods2: goodsTwice.join('-'),
				}, this,
					function( result ) {},
					function( is_error) {}
				);
			}


			return;
		}

		const goods = [];

		for (let i = 0; i < goodElements.length; i++) {
			goods.push(goodElements[i].getAttribute('data-good'));
		}

		// If you are upgrading fewer goods than you can, warn the user!
		let max = 0;
		if (goods.length < allGoodElements.length) {
			const state = this.gamedatas.gamestate.name;
			const numTiles = this.gamedatas.gamestate.args ? this.gamedatas.gamestate.args.num_tiles : 0;
			const numSteps = this.gamedatas.gamestate.args ? this.gamedatas.gamestate.args.num_steps : 0;
			if (state == 'command-trade') {
				// 1 of each green...
				const differentGreens = {};
				for (let i = 0; i < allGoodElements.length; i++) {
					const goodName = allGoodElements[i].getAttribute('data-good');
					if (['oil', 'hide', 'linen', 'wool', 'skinandbones', 'fur', 'robe', 'clothing', 'antlers', 'leather', 'amber', 'amber_ee'].includes(goodName)) {
						differentGreens[goodName] = 1;
					}
				}
				max = Object.keys(differentGreens).length;
			} else if (state == 'command-upgrade' || state == 'command-nwupgrade' || state == 'command-meatupgrade' || state == 'command-payupgrade') {
				max = numTiles;
			} else if (state == 'command-sameupgrade') {
				// For now, don't warn on this one...
			}
		}
		
		if (max > 0 && goods.length < max) {
			const message = dojo.string.substitute(
				_('You have only selected ${num1} good(s) out of a possible ${num2}. Are you sure you are done?'),
				{
					num1: goods.length,
					num2: max
				}
			);
			this.confirmationDialog( message, dojo.hitch( this, function() {
				this.ajaxcall( "/feastforodin/feastforodin/selectGoods.html", { 
					lock: true,
					goods: goods.join('-'),
					goods2: []
				}, this,
					function( result ) {},
					function( is_error) {}
				);
			} ) );
		} else {
			this.ajaxcall( "/feastforodin/feastforodin/selectGoods.html", { 
				lock: true,
				goods: goods.join('-'),
				goods2: []
			}, this,
				function( result ) {},
				function( is_error) {}
			);
		}
	}
	
	function onDeclareSuccess( event ) {
		dojo.stopEvent( event );
		
		if ( ! this.checkAction( 'declareSuccess' ) ) {
			return;
		}
		
		// Get all selected goods in top-tray
		const tray = $('ffo-top-tray');
		const numResources = dojo.query('.ffo-icon.ffo-selected', tray).length;
		const numSwords = dojo.query('.ffo-card.ffo-card-weapon-sword.ffo-selected', tray).length;
		const numSnares = dojo.query('.ffo-card.ffo-card-weapon-snare.ffo-selected', tray).length;
		const numSpears = dojo.query('.ffo-card.ffo-card-weapon-spear.ffo-selected', tray).length;
		const numBows = dojo.query('.ffo-card.ffo-card-weapon-bow.ffo-selected', tray).length;
		const numCards = numSwords + numSnares + numSpears + numBows;
		// TODO : Should use the value in the span really... because of occs which affect the value
		
		const args = this.gamedatas.gamestate.args;
		let confirmation = false;
		if (["game", "snare", "whales", "whalesminor", "elk", "fish", "game2"].includes(args.hunt_type)) {
			// Warning if rolled too low!
			if (+args.die_value - numResources - numCards < 0) {
				confirmation = true;
				this.confirmationDialog( _('A successful hunt only requires getting the result to 0. Are you sure you want to spend more than required?'), () => {
					this.ajaxcall( "/feastforodin/feastforodin/hunt.html", { 
						lock: true,
						numResources,
						numSwords,
						numSnares,
						numSpears,
						numBows,
					}, this,
						function( result ) {},
						function( is_error) {}
					);
				} );
			}
			if (! confirmation) {
				this.ajaxcall( "/feastforodin/feastforodin/hunt.html", { 
					lock: true,
					numResources,
					numSwords,
					numSnares,
					numSpears,
					numBows,
				}, this,
					function( result ) {},
					function( is_error) {}
				);
			}
		} else {
			// if attack
			let message;
			const goodElements = dojo.query('.ffo-good.ffo-provisional:not(.ffo-erroneous)', tray);

			const raider = dojo.query('#raider-toggle', tray)[0];

			if (goodElements.length != 1 && !raider) {
				message = _('You have to select one good.');
			}

			if (raider) {
				if (![1,2].includes(goodElements.length)) {
					message = _('You have to select one or two goods.');
				}
			}

			if (message) {
				this.showMessage( message, 'error' );
				return;
			}

			let good = goodElements[0].getAttribute('data-good');
			let good2;
			if (raider) {
				good2 = goodElements[1] ? goodElements[1].getAttribute('data-good') : null;
			}

			this.ajaxcall( "/feastforodin/feastforodin/selectAttackReward.html", { 
				lock: true,
				specialTile: good,
				specialTile2: good2,
				numResources,
				numSwords,
				numSnares,
				numSpears,
				numBows,
			}, this,
				function( result ) {},
				function( is_error) {}
			);
		}
		
	}
	
	function onSelectAttackReward( event ) {
		dojo.stopEvent( event );

		const target = event.target.closest('.ffo-good');
		if (target == null) {
			// Not clicking on a good, nothing
			return;
		}

		if (dojo.hasClass(target, 'ffo-erroneous')) {
			return;
		}

		if (dojo.hasClass(target, 'ffo-provisional')) {
			dojo.removeClass(target, 'ffo-provisional');
		} else {
			dojo.addClass(target, 'ffo-provisional');
		}
	}

	function onAffirm( event ) {
		if (event)
			dojo.stopEvent( event );
		
		if ( ! this.checkAction( 'affirm' ) ) {
			return;
		}
		
		this.ajaxcall( "/feastforodin/feastforodin/affirm.html", { 
			lock: true,
		}, this,
			function( result ) {},
			function( is_error) {}
		);
	}

	function onDeclareFailure( event ) {
		dojo.stopEvent( event );
		
		if ( ! this.checkAction( 'declareFailure' ) ) {
			return;
		}
		
		this.ajaxcall( "/feastforodin/feastforodin/declareFailure.html", { 
			lock: true,
		}, this,
			function( result ) {},
			function( is_error) {}
		);
	}
	
	function onReroll( event ) {
		dojo.stopEvent( event );
		
		if ( ! this.checkAction( 'reroll' ) ) {
			return;
		}
		
		this.ajaxcall( "/feastforodin/feastforodin/reroll.html", { 
			lock: true,
		}, this,
			function( result ) {},
			function( is_error) {}
		);
	}
	
	function onAction( event ) {
		dojo.stopEvent( event );

		if (event.target == event.currentTarget) {
			// Clicking on the board background, nothing
			return;
		}
		
		// If clicking on the expander, toggle expansion!
		if (event.target == $('ffo-action-board-expander')) {
			this.options.bigActionBoard = !this.options.bigActionBoard;
			this.onResize();
			return;
		}
		
		// Get the parent .ffo-action div
		const target = event.target.closest('.ffo-action');
		if (target == null) {
			// Not clicking on an action panel, nothing
			return;
		}
		
		if ( ! this.checkAction( 'action' ) ) {
			return;
		}
		
		let actionId = target.getAttribute('data-action-id');
		
		if (actionId.startsWith('r') 
			|| actionId.startsWith('c')
			|| actionId.startsWith('nr')
			|| actionId.startsWith('nc')) {
			// Clicking on a heading!
			return;
		}

		const ajaxCall = () => this.ajaxcall( "/feastforodin/feastforodin/action.html", { 
			lock: true,
			actionId,
		}, this,
			function( result ) {},
			function( is_error) {}
		);

		const state = this.gamedatas.gamestate.name;

		if (state == 'command-inspector' || state == 'command-inspector2') {
			ajaxCall();
			return;
		}

		if (actionId.startsWith('65') || actionId.startsWith('126')) {
			this.confirmationDialog( _('You cannot undo this action because you will be drawing a new occupation(s). Proceed?'), () => ajaxCall());
		} else if (actionId.startsWith('555')) {
			const player = this.playersById[this.playerId()];
			const meeples = player.playerObj.meeples.filter(m => m.location == 'thing');
			if (meeples.length > 2) {
				this.confirmationDialog( _('You have more than 2 vikings left in your Thing. Are you sure you want to play the 5th column action? This will be your last action this round so the rest of your vikings will be wasted.'), () => ajaxCall());
			} else {
				ajaxCall();
			}
		} else {
			ajaxCall();
		}
		
	}
	
	function onPlayOccupation( event, tutor = false ) {
		dojo.stopEvent( event );
		
		if (!tutor) {
			if (event.target == event.currentTarget) {
				// Clicking on the top tray, nothing
				return;
			}
						
			if ( ! this.checkAction( 'playOccupation' ) ) {
				return;
			}
		}
		
		const target = event.target.closest('.ffo-card');
		if (target == null) {
			// Not clicking on a card, nothing
			return;
		}

		const occupationId = +target.getAttribute('data-occupation-id');

		const modeId = +target.getAttribute('data-mode-id');
		
		this.ajaxcall( "/feastforodin/feastforodin/playOccupation.html", { 
			lock: true,
			occupationId,
			playOrToken: modeId,
			tutor: tutor ? 1 : 0,
		}, this,
			function( result ) {},
			function( is_error) {}
		);
	}
	
	function sendUseOccupation(target, occupationId, modeId, data = null) {
		dojo.style($('ffo-occupation-modes'), {'display': 'none'});
		$('ffo-occupation-modes').setAttribute('data-current-occupation', '');
		dojo.addClass(target, 'ffo-card-used');
		this.ajaxcall( "/feastforodin/feastforodin/useOccupation.html", { 
			lock: true,
			occupationId,
			modeId,
			data,
		}, this,
			( result ) => {
				dojo.removeClass(target, 'ffo-card-used');
			},
			( is_error)  => {
				dojo.removeClass(target, 'ffo-card-used');
			}
		);
	}
	
	function sendUseOccupationFromModal(occupationId) {
		const modal = $('ffo-modal');
		const resourceElements = dojo.query('.ffo-provisional', modal);

		if (!resourceElements.length) {
			return;
		}

		let data = resourceElements[0].getAttribute('data-good');

		if (!data) {
			if (resourceElements.length !== 2) {
				// warn?
			} else {
				data1 = resourceElements[0].getAttribute('data-resource-type');
				data2 = resourceElements[1].getAttribute('data-resource-type');
				data = data1 + "_" +  data2;
			}
		}

		this.ajaxcall( "/feastforodin/feastforodin/useOccupation.html", { 
			lock: true,
			occupationId,
			modeId: 0,
			data,
		}, this,
			function( result ) {},
			function( is_error) {}
		);
	}

	function onUseOccupation( event ) {
		dojo.stopEvent( event );
		
		const target = event.target.closest('.ffo-card');
		if (target == null) {
			// Not clicking on a card, nothing
			return;
		}
		
		if (dojo.hasClass(target, 'ffo-card-used')) {
			// Can't use a card whilst it's being used
			return;
		}
		
		const occupationId = +target.getAttribute('data-occupation-id');
		const abilityId = +target.getAttribute('data-ability-id');
		
		if ([52, 53, 56].includes(abilityId)) {
			if (abilityId == 52) {
				// Tutor
				this.onShowModalTutor(occupationId);
			}
			if (abilityId == 53) {
				// Farm Shop Owner
				this.onShowModalFarmShopOwner(occupationId);
			}
			if (abilityId == 56) {
				// Arms Dealer
				this.onShowModalArmsDealer(occupationId);
			}
		} else if ([46, 50, 51, 54, 56, 182, 187].includes(abilityId)) {
			const box = $('ffo-occupation-modes');
			if (box.getAttribute('data-current-occupation') == occupationId) {
				dojo.style($('ffo-occupation-modes'), {'display': 'none'});
				box.setAttribute('data-current-occupation', '');
			} else {
				box.setAttribute('data-current-occupation', occupationId);
				const modes = [];
				if (abilityId == 46) {
					modes.push(_('Pay') + ' 3 ' + `<div class="ffo-icon ffo-icon-wood"></div><div class="ffo-good ffo-good-wool"><div class="ffo-good-image"></div></div>`);
					modes.push(_('Pay') + ' 3 ' + `<div class="ffo-icon ffo-icon-wood"></div><div class="ffo-good ffo-good-linen"><div class="ffo-good-image"></div></div>`);
				}
				else if (abilityId == 50) {
					modes.push(_('Pay') + ` <div class="ffo-good ffo-good-rune"><div class="ffo-good-image"></div></div>`);
				}
				else if (abilityId == 51) {
					modes.push(_('Take') + ` <div class="ffo-icon ffo-icon-coin"></div> <div class="ffo-good ffo-good-milk"><div class="ffo-good-image"></div></div>`);
					modes.push(_('Take') + ` <div class="ffo-icon ffo-icon-coin"></div> <div class="ffo-good ffo-good-cabbage"><div class="ffo-good-image"></div></div>`);
				}
				else if (abilityId == 52) {
					modes.push(_('Pay') + ` <div class="ffo-icon ffo-icon-coin"></div>`);
				}
				else if (abilityId == 54) {
					modes.push(_('Take') + ` <div class="ffo-good ffo-good-silk"><div class="ffo-good-image"></div></div>`);
					modes.push(_('Take') + ` <div class="ffo-good ffo-good-chest"><div class="ffo-good-image"></div></div>`);
				}
				else if (abilityId == 56) {
					modes.push(`<div class="ffo-card ffo-card-weapon-back"></div>
					<div class="ffo-card ffo-card-weapon-back"></div> 	
					<div class="ffo-icon ffo-icon-arrow"></div>
					<div class="ffo-icon ffo-icon-ore"></div>`);
				}
				else if (abilityId == 182) {
					modes.push(_('Pay') + ' 2 ' + `<div class="ffo-icon ffo-icon-wood"></div>`);
					modes.push(_('Pay') + ' 5 ' + `<div class="ffo-icon ffo-icon-wood"></div>`);
				}
				else if (abilityId == 187) {
					// If the belt is not available, auto choose the other option?
					modes.push(_('Take') + ` <div class="ffo-good ffo-good-belt"><div class="ffo-good-image"></div></div>`);
					modes.push(_('Take') + ` <div class="ffo-good ffo-good-treasurechest"><div class="ffo-good-image"></div></div>`);
				}
				
				box.innerHTML = '';
				for (let i = 0; i < modes.length; i++) {
					const placed = dojo.place(`<a href="#" class="action-button bgabutton bgabutton_blue" onclick="return false;" id="pass_button">${modes[i]}</a>`, box);
					dojo.connect(placed, "onclick", () => {
						sendUseOccupation.call(this, target, occupationId, i);
					});
				}
				
				// Place box by the clicking occy!
				dojo.style($('ffo-occupation-modes'), {'display': 'flex'});
				const rect = target.getBoundingClientRect();
				const boxRect = box.getBoundingClientRect();
				
				// If this would be above the screen, place below the occ instead
				if (rect.top + window.scrollY - boxRect.height - 10 < 0) {
					dojo.style(box, {
						top: (rect.bottom + window.scrollY + 10) + 'px',
						left: (rect.left + (rect.width - boxRect.width) / 2) + 'px',
					});
				} else {
					dojo.style(box, {
						top: (rect.top + window.scrollY - boxRect.height - 10) + 'px',
						left: (rect.left + (rect.width - boxRect.width) / 2) + 'px',
					});
				}
			}
		} else {
			sendUseOccupation.call(this, target, occupationId, 0);
		}
	}
	
	function onSelectSpecialTile( event ) {
		dojo.stopEvent( event );
		
		if (event.target == event.currentTarget) {
			// Clicking on the top tray, nothing
			return;
		}
		
		const target = event.target.closest('.ffo-good');
		if (target == null) {
			// Not clicking on a good, nothing
			return;
		}
		
		if ( ! this.checkAction( 'selectSpecialTile' ) ) {
			return;
		}
		
		const good = target.getAttribute('data-good');
		
		this.ajaxcall( "/feastforodin/feastforodin/selectSpecialTile.html", { 
			lock: true,
			specialTile: good,
		}, this,
			function( result ) {},
			function( is_error) {}
		);
	}
	
	function onSelectStrip( event ) {
		dojo.stopEvent( event );
		
		if (event.target == event.currentTarget) {
			// Clicking on the top tray, nothing
			return;
		}
		
		const target = event.target.closest('.ffo-mountain-strip');
		if (target == null) {
			// Not clicking on a good, nothing
			return;
		}
		
		if ( ! this.checkAction( 'selectStrip' ) ) {
			return;
		}
		
		const stripId = target.getAttribute('data-strip-id');
		
		this.ajaxcall( "/feastforodin/feastforodin/selectStrip.html", { 
			lock: true,
			stripId,
		}, this,
			function( result ) {},
			function( is_error) {}
		);
	}
	
	function onSelectMode( event ) {
		dojo.stopEvent( event );
		
		if ( ! this.checkAction( 'selectMode' ) ) {
			return;
		}
		
		const target = event.target.closest('.bgabutton');
		
		const modeId = target.getAttribute('data-mode-id');
		const actionId = target.getAttribute('data-action-id');
		
		let confirmPrompt = false;

		if (this.gamedatas.num_sheds == 0) {
			if (actionId == 1 || (actionId == 77 && modeId == 0) || (actionId == 5550 && modeId == 0)) {
				this.confirmationDialog( _('There are no more sheds left. Do you want to do this action anyway?'), dojo.hitch( this, function() {
					onSelectModeAjax.call(this, modeId);
				} ) );
				confirmPrompt = true;
			}
		}
		if (this.gamedatas.num_stonehouses == 0) {
			if (actionId == 2 || (actionId == 90 && modeId == 0) || (actionId == 4 && modeId == 0)) {
				this.confirmationDialog( _('There are no more stonehouses left. Do you want to do this action anyway?'), dojo.hitch( this, function() {
					onSelectModeAjax.call(this, modeId);
				} ) );
				confirmPrompt = true;
			}
		}
		if (this.gamedatas.num_longhouses == 0) {
			if (actionId == 3 || (actionId == 91 && modeId == 0) || (actionId == 4 && modeId == 1)) {
				this.confirmationDialog( _('There are no more longhouses left. Do you want to do this action anyway?'), dojo.hitch( this, function() {
					onSelectModeAjax.call(this, modeId);
				} ) );
				confirmPrompt = true;
			}
		}
		if (!confirmPrompt) {
			onSelectModeAjax.call(this, modeId);
		}
	}
	
	function onSelectModeAjax( modeId ) {
		this.ajaxcall( "/feastforodin/feastforodin/selectMode.html", { 
			lock: true,
			modeId,
		}, this,
			function( result ) {},
			function( is_error) {}
		);
	}

	function onClickGood( event ) {
		dojo.stopEvent( event );
		
		if (event.target == event.currentTarget) {
			// Clicking on the top tray, nothing
			return;
		}
		
		const target = event.target.closest('.ffo-good');
		if (target == null) {
			// Not clicking on a good, nothing
			return;
		}
		
		// Toggle selected status of this good
		if (dojo.hasClass(target, 'ffo-provisional')) {
			dojo.removeClass(target, 'ffo-provisional');
		} else {
			dojo.addClass(target, 'ffo-provisional');
		}
	}
	
	
	function onClickGoodUnique( event ) {
		dojo.stopEvent( event );
		
		if (event.target == event.currentTarget) {
			// Clicking on the top tray, nothing
			return;
		}
		
		const target = event.target.closest('.ffo-good');
		if (target == null) {
			// Not clicking on a good, nothing
			return;
		}

		dojo.query(target).siblings(".ffo-provisional").forEach(node => {
			dojo.removeClass(node, 'ffo-provisional');
		});
		dojo.addClass(target, 'ffo-provisional');
	}

	function onClickOnceTwiceUpgradeGood( event ) {
		dojo.stopEvent( event );
		
		if (event.target == event.currentTarget) {
			// Clicking on the top tray, nothing
			return;
		}
		
		const target = event.target.closest('.ffo-good');
		if (target == null) {
			// Not clicking on a good, nothing
			return;
		}
		
		const parentId = target.parentElement.id;
		const targetClassName = target.className.split(" ")[1];

		let twin = null;

		if (parentId == 'once-row') {
			twin = dojo.query('.'+targetClassName, 'twice-row')[0];
		}

		if (parentId == 'twice-row') {
			twin = dojo.query('.'+targetClassName, 'once-row')[0];
		}

		// Toggle selected status of this good and do the opposite to the twin good
		if (dojo.hasClass(target, 'ffo-provisional')) {
			dojo.removeClass(target, 'ffo-provisional');
		} else {
			dojo.addClass(target, 'ffo-provisional');
			if (twin) {
				dojo.removeClass(twin, 'ffo-provisional');
			}
		}
	}

	function onClickResource( event ) {
		dojo.stopEvent( event );
		
		if (event.target == event.currentTarget) {
			// Clicking on the top tray, nothing
			return;
		}
		
		const target = event.target.closest('.ffo-resource');
		if (target == null) {
			// Not clicking on a good, nothing
			return;
		}
		
		// Toggle selected status of this good
		if (dojo.hasClass(target, 'ffo-provisional')) {
			dojo.removeClass(target, 'ffo-provisional');
		} else {
			dojo.addClass(target, 'ffo-provisional');
		}
	}

	function onSelectPersonalResource( event ) {
		dojo.stopEvent( event );
		
		if (event.target == event.currentTarget) {
			// Clicking on the top tray, nothing
			return;
		}
		
		const target = event.target.closest('.personal-resource-holder');
		if (target == null) {
			// Not clicking on a personal resource, nothing
			return;
		}

		if (dojo.hasClass(target, 'disabled')) {
			// cannot select disabled (already taken)
			return;
		}

		// Keep only selected item active
		if (dojo.hasClass(target, 'selected')) {
			dojo.removeClass(target, 'selected');
		} else {
			const otherResourceHolders = dojo.query('.personal-resource-holder', $('ffo-top-tray'));
			otherResourceHolders.forEach(item => dojo.removeClass(item, 'selected'));
			dojo.addClass(target, 'selected');
		}
	}

	function onSelectArtisanShed( event ) {
		dojo.stopEvent( event );
		
		if (event.target == event.currentTarget) {
			// Clicking on the top tray, nothing
			return;
		}
		
		const target = event.target.closest('.artisan-shed-container');
		if (target == null) {
			return;
		}

		if (dojo.hasClass(target, 'disabled')) {
			// cannot select disabled (already taken)
			return;
		}

		// Keep only selected item active
		if (dojo.hasClass(target, 'selected')) {
			dojo.removeClass(target, 'selected');
		} else {
			const otherResourceHolders = dojo.query('.artisan-shed-container', $('ffo-top-tray'));
			otherResourceHolders.forEach(item => dojo.removeClass(item, 'selected'));
			dojo.addClass(target, 'selected');
		}
	}

	function onBuildArtisanShed(event) {
		const resourceElements = dojo.query('.selected', $('ffo-top-tray'));

		if (!resourceElements.length) {
			return;
		}

		const shed = resourceElements[0].children[0].getAttribute('data-artisan-shed');

		this.ajaxcall( "/feastforodin/feastforodin/buildArtisanShed.html", { 
			lock: true,
			shed: shed,
		}, this,
			function( result ) {},
			function( is_error) {}
		);
	}

	function onDrawPersonalResource( event ) {

		const resourceElements = dojo.query('.selected', $('ffo-top-tray'));

		if (!resourceElements.length) {
			return;
		}

		const resource = resourceElements[0].getAttribute('data-resource');
		const isSecond = resourceElements[0].getAttribute('data-isSecond');

		this.ajaxcall( "/feastforodin/feastforodin/drawPersonalResource.html", { 
			lock: true,
			resource: resource,
			isSecond: isSecond,
		}, this,
			function( result ) {},
			function( is_error) {}
		);
	}

	function onSelectBestOfTwoStartingOcc( event ) {
		dojo.stopEvent( event );
		
		if (event.target == event.currentTarget) {
			// Clicking on the top tray, nothing
			return;
		}
		
		const target = event.target.closest('.ffo-cards-container-item');
		if (target == null) {
			// Not clicking on a personal resource, nothing
			return;
		}

		// Keep only selected item active
		if (dojo.hasClass(target, 'selected')) {
			dojo.removeClass(target, 'selected');
		} else {
			const otherResourceHolders = dojo.query('.ffo-cards-container-item', $('ffo-top-tray'));
			otherResourceHolders.forEach(item => dojo.removeClass(item, 'selected'));
			dojo.addClass(target, 'selected');
		}
	}

	function onChooseBestOfTwoStartingOcc( event ) {

		const resourceElements = dojo.query('.selected', $('ffo-top-tray'));

		if (!resourceElements.length) {
			return;
		}

		let occId = resourceElements[0].children[0].getAttribute('data-occupation-id');

		this.ajaxcall( "/feastforodin/feastforodin/chooseBestOfTwoStartingOcc.html", { 
			lock: true,
			occId: occId,
		}, this,
			function( result ) {},
			function( is_error) {}
		);
	}

	function onClickWeapon( event ) {
		dojo.stopEvent( event );
		
		const target = event.target.closest('.ffo-card-weapon');
		if (target == null) {
			// Not clicking on a good, nothing
			return;
		}
		
		// Toggle selected status of this good
		if (dojo.hasClass(target, 'ffo-provisional')) {
			dojo.removeClass(target, 'ffo-provisional');
		} else {
			dojo.addClass(target, 'ffo-provisional');
		}
	}

	function onClickDuringHuntOrAttack (event) {
		dojo.stopEvent( event );
		if (event.target == event.currentTarget) {
			// Clicking on the top tray, nothing
			return;
		}

		const target = event.target.closest('.ffo-hunt-item');
		if (target == null) {
			// Not clicking on a hunt item (stone,wood,weapons), try goods tile 
			onSelectAttackReward.call(this,event);
		}
		onClickHuntItem.call(this, event);
	}

	function onClickHuntItem( event ) {
		dojo.stopEvent( event );

		if (event.target == event.currentTarget) {
			// Clicking on the top tray, nothing
			return;
		}

		const target = event.target.closest('.ffo-hunt-item');
		if (target == null) {
			// Not clicking on a good, nothing
			return;
		}
		
		// Toggle selected status of this good
		if (dojo.hasClass(target, 'ffo-selected')) {
			dojo.removeClass(target, 'ffo-selected');
		} else {
			dojo.addClass(target, 'ffo-selected');
		}
		
		// Update Success/failure buttons as appropriate
		this.updateHuntButtons();
	}
	
	function onClickShip( event ) {
		dojo.stopEvent( event );
		
		if (event.target == event.currentTarget) {
			// Clicking on the top tray, nothing
			return;
		}
		
		const target = event.target.closest('.ffo-ship');
		if (target == null) {
			// Not clicking on a good, nothing
			return;
		}
		
		if ( ! this.checkAction( 'selectShip' ) ) {
			return;
		}
		
		const shipId = target.getAttribute('data-ship-id');
		
		this.ajaxcall( "/feastforodin/feastforodin/selectShip.html", { 
			lock: true,
			shipId,
		}, this,
			function( result ) {},
			function( is_error) {}
		);
	}
	
	function onEquipShip( event ) {
		dojo.stopEvent( event );
		
		// Put ore on selected boat. Use id with ships instead of type, I think.
		const target = event.target.closest('.ffo-ship');
		if (target == null) {
			// Not clicking on a good, nothing
			return;
		}
		
		const equipped = event.target.getAttribute('data-equipped') == '1';
		
		const shipId = target.getAttribute('data-ship-id');
		
		this.ajaxcall( 
			equipped ? "/feastforodin/feastforodin/unequipShip.html" : "/feastforodin/feastforodin/equipShip.html"
			, { 
			lock: true,
			shipId,
		}, this,
			function( result ) {},
			function( is_error) {}
		);
	}
	
	function onEquipBoard( event ) {
		dojo.stopEvent( event );
		
		const equipped = event.target.getAttribute('data-equipped') == '1';
		
		const boardIndex = event.target.getAttribute('data-board-index');
		let asset = null;
		if (dojo.hasClass(event.target, 'ffo-building-stone')) {
			asset = 'stone';
		}
		if (dojo.hasClass(event.target, 'ffo-building-wood')) {
			asset = 'wood';
		}
		if (dojo.hasClass(event.target, 'ffo-building-ore')) {
			asset = 'ore';
		}
		if (dojo.hasClass(event.target, 'ffo-building-linen')) {
			asset = 'linen';
		}
		if (dojo.hasClass(event.target, 'ffo-building-mead')) {
			asset = 'mead';
		}
		if (dojo.hasClass(event.target, 'ffo-building-beans')) {
			asset = 'beans';
		}
		if (dojo.hasClass(event.target, 'ffo-building-stockfish')) {
			asset = 'stockfish';
		}
		if (dojo.hasClass(event.target, 'ffo-building-hide')) {
			asset = 'hide';
		}
		this.ajaxcall( 
			equipped ? "/feastforodin/feastforodin/unequipBoard.html" : "/feastforodin/feastforodin/equipBoard.html"
			, { 
			lock: true,
			boardIndex,
			asset,
		}, this,
			function( result ) {},
			function( is_error) {}
		);
	}
	
	function onBuyShip( event ) {
		dojo.stopEvent( event );
		
		const target = event.target.closest('.ffo-ship') || event.target.closest('.action-button');
		if (target == null) {
			// Not clicking on a good, nothing
			return;
		}
		
		// if ( ! this.checkAction( 'buyShip' ) ) {
		// 	return;
		// }
		
		const shipType = target.getAttribute('data-ship-type');
		const self = this;
		
		this.ajaxcall( "/feastforodin/feastforodin/buyShip.html", { 
			lock: true,
			shipType,
		}, this,
			function( result ) {
				// Hide modal
				self.onHideModal();
			},
			function( is_error ) {}
		);
	}
	
	function onSetAutoPassAction( event ) {
		dojo.stopEvent( event );
		
		const target = event.target.closest('.ffo-phase-board');
		if (target == null) {
			// Not clicking on a good, nothing
			return;
		}
		
		// if ( ! this.checkAction( 'buyShip' ) ) {
		// 	return;
		// }
		
		const actionType = target.getAttribute('data-action-type');
		const self = this;
		
		this.ajaxcall( "/feastforodin/feastforodin/setAutoPassAction.html", { 
			lock: true,
			actionType,
		}, this,
			function( result ) {
				// Hide modal
				// self.onHideModal();
			},
			function( is_error ) {}
		);
	}

	function onSaveBoardsAndPass(event) {
		dojo.stopEvent( event );
		
		this.onSaveBoards(event, null, null, { pass: true });
	}
	
	function onSaveBoardsAndPassEndGame(event) {
		dojo.stopEvent( event );
		
		this.onSaveBoards(event, null, (result) => {
			onPassEndGameCheck.call( this, event, false );
		});
	}
	
	function onSaveFeastBoardsAndPass(event) {
		dojo.stopEvent( event );
		const player = this.playersById[this.playerId()];
		
		// Are you actually feeding your vikings?
		for (const i in this.goodsTargets) {
			const gt = this.goodsTargets[i];
			if (gt.information.type == "homefeast" && gt.player.playerId == player.playerId) {
				gt.checkProvisionals();
				if (! gt.feeding) {
					this.confirmationDialog( _('You are not fully feeding your vikings and will receive a penalty. Are you sure you are done?'), dojo.hitch( this, function() {
						onSaveBoardsAndPass.call( this, event );
					} ) );
				} else {
					onSaveBoardsAndPass.call( this, event );
				}
				break;
			}
		}
	}
	
	function onSaveBoards( event, board = null, onDone = null, options = {} ) {
		dojo.stopEvent( event );
		
		const request = {};
		for (const i in this.goodsTargets) {
			const target = this.goodsTargets[i];
			if (! target.me) continue;
			if (board != null && target != board) continue;
			if (target.information.type == "homefeast") {
				if (! ["feast-placegoods", "command-personalfeast"].includes(this.gamedatas.gamestate.name)) {
					// Ignore changes on feast board if not in a feast phase
					continue;
				}
			}
			const newTiles = [];
			const provisionals = dojo.query(".ffo-good.ffo-provisional", target.dropDiv);
			for (let j = 0; j < provisionals.length; j++) {
				const provisional = provisionals[j];
				if (dojo.hasClass(provisional, 'ffo-erroneous')) {
					// Don't allow uploading!
					// collision, overlaps-void, touching, income, horizontal
					const why = provisional.getAttribute('data-error-why');
					let message = _('You cannot save as you have an error on your boards: ');
					if (why == 'collision') {
						message += _('tiles may not overlap.');
					} else if (why == 'overlaps-void') {
						message += _('tiles must not overhang the board and must not cover beam or mountain spaces.');
					} else if (why == 'touching') {
						message += _('two goods of the same colour are touching where they shouldn\'t.');
					} else if (why == 'income') {
						message += _('income spaces can only be covered if the spaces below and to the left are also covered.');
					} else if (why == 'horizontal') {
						message += _('only one of each type of good may be placed horizontally on the feast board.');
					} else if (why == 'resources') {
						message += _('you have placed more resources than you have available.');
					} else if (why == 'unique-tile-error') {
						message += _('unique tile in your artisan shed is misplaced.');
					}
					this.showMessage( message, 'error' );
					return;
				}
				newTiles.push(provisional.information);
			}
			request['board-' + target.information.index] = newTiles;
		}
		
		Object.assign(request, options);
		
		// Send provisionals to server...
		this.ajaxcall( "/feastforodin/feastforodin/saveBoards.html", { 
			lock: true,
			request: JSON.stringify(request),
		}, this,
			onDone ? onDone : function( result ) {},
			function( is_error ) {},
			'post'
		);
	}
	
	function onCancelBoards( event, board = null ) {
		dojo.stopEvent( event );
		
		// Move all provisionals back to tray
		for (const i in this.goodsTargets) {
			const target = this.goodsTargets[i];
			if (board != null && target != board) continue;
			const provisionals = dojo.query(".ffo-good.ffo-provisional", target.dropDiv);
			for (let j = 0; j < provisionals.length; j++) {
				const provisional = provisionals[j];
				GoodsSource.returnToSource(provisional);
			}
		}
		
		// Reset message
		let unsavedChanges = this.hasUnsavedChanges();
		$('ffo-unsaved-message').style.display = unsavedChanges ? 'block' : 'none';
	}
	
	function onSelectIsland( event ) {
		dojo.stopEvent( event );
		
		if (event.target == event.currentTarget) {
			// Clicking on the top tray, nothing
			return;
		}
		
		const target = event.target.closest('.ffo-player-board-island');
		if (target == null) {
			// Not clicking on an island, nothing
			return;
		}
		
		const island = target.getAttribute('data-island-name');
		
		this.ajaxcall( "/feastforodin/feastforodin/selectIsland.html", { 
			lock: true,
			island,
		}, this,
			function( result ) {},
			function( is_error) {}
		);
	}
	
	function onTopTrayClick( event ) {
		// Forward to the appropriate handler
		switch (this.gamedatas.gamestate.name) {
			case 'command-explore':
			case 'command-explorepassable':
				return onSelectIsland.call(this, event);
			case 'command-playOccupation':
			case 'activatePreGameOccupation':
				return onPlayOccupation.call(this, event);
			case 'command-forge': 
			case 'command-forgebuy': 
			case 'command-forgeminor':
			case 'command-hunt-reward': 
			case 'command-fineblacksmith':
			case 'command-hunt-reward2':
				return onSelectSpecialTile.call(this, event);
			case 'command-mountain':
			case 'command-armedfighter':
				return onSelectStrip.call(this, event);
			case 'command-trade': 
			case 'command-upgrade': 
			case 'command-nwupgrade': 
			case 'command-payupgrade': 
			case 'command-flip': 
			case 'command-meatupgrade': 
			case 'command-sameupgrade': 
			case 'command-storeman': 
			case 'command-fieldfarmer':
			case 'command-anytwoanimals':
				return onClickGood.call(this, event);
			case 'command-hunt':
			case 'command-attack': 
				return onClickDuringHuntOrAttack.call(this, event);
			case 'command-emigrate': 
			case 'command-swapwhalingboatknarr':
			case 'command-buylongshipfifthcol':
			case 'command-emigratepassable':
			case 'command-wharfowner':  
			case 'command-shipbuilder':
			case 'command-harborguard': 
			case 'command-swordfighter':
			case 'command-berserker':
			case 'command-emigrateminor':
			case 'command-emigrateminorpassable':
				return onClickShip.call(this, event);
			case 'command-slaughter':
				return onSelectForSlaughter.call(this, event);
			case 'command-resources':
				return onClickResource.call(this, event);
			case 'command-discardocc':
				return onDiscardOccupation.call(this, event);
			case 'command-armsdealer':
				return onClickWeapon.call(this, event);
			case 'command-oncetwiceupgrade':
				return onClickOnceTwiceUpgradeGood.call(this, event);
			case 'drawPersonalResource':
			case 'drawPersonalResource2':
				return onSelectPersonalResource.call(this, event);
			case 'bestOfTwoStartingOcc':
			case 'discardOneBrown':
				return onSelectBestOfTwoStartingOcc.call(this, event);
			case 'buildArtisanShed':
				return onSelectArtisanShed.call(this, event);
		}
	}
	
	function onChooseVikingsNumber( event ) {
		dojo.stopEvent( event );
		
		const target = event.target.closest('.action-button');

		const vikingNumber = target.getAttribute('data-viking-number');

		const player = this.playersById[this.playerId()];
		const meeples = player.playerObj.meeples.filter(m => m.location == 'thing');
		if (vikingNumber == 1 && meeples.length > 1) {
			this.confirmationDialog( _('Are you sure you want to play only one viking? This is your last action this round so the rest of your vikings will be wasted.'), () => {
				this.ajaxcall( "/feastforodin/feastforodin/chooseVikingsNumber.html", { 
					lock: true,
					vikingNumber,
				}, this,
					function( result ) {},
					function( is_error) {}
				);
			} );
		} else {
			this.ajaxcall( "/feastforodin/feastforodin/chooseVikingsNumber.html", { 
				lock: true,
				vikingNumber,
			}, this,
				function( result ) {},
				function( is_error) {}
			);
		}
	}

	function onSelectForSlaughter (event) {
		dojo.stopEvent( event );
		const target = event.target.closest('.ffo-good');
		if (target == null) {
			// Not clicking on a good, nothing
			return;
		}
		const selectedAnimal = target.getAttribute('data-animal-type');

		this.ajaxcall( "/feastforodin/feastforodin/selectForSlaughter.html", { 
			lock: true,
			selectedAnimal,
		}, this,
			function( result ) {},
			function( is_error) {}
		);
	}

	function onSelectResources (event) {
		dojo.stopEvent( event );
		
		// if ( ! this.checkAction( 'selectResources' ) ) {
		// 	return;
		// }
		
		// Get all selected resources in top-tray
		const tray = $('ffo-top-tray');
		const resourceElements = dojo.query('.ffo-resource.ffo-provisional', tray);
		
		const resources = [];
		
		for (let i = 0; i < resourceElements.length; i++) {
			resources.push(resourceElements[i].getAttribute('data-resource-type'));
		}

		this.ajaxcall( "/feastforodin/feastforodin/selectResources.html", { 
			lock: true,
			resources: resources.join('-')
		}, this,
			function( result ) {},
			function( is_error) {}
		);
	}

	function onSelectWeapons (event) {
		dojo.stopEvent( event );
		
		// if ( ! this.checkAction( 'selectWeapons' ) ) {
		// 	return;
		// }
		
		// Get all selected resources in top-tray
		const tray = $('ffo-top-tray');
		const resourceElements = dojo.query('.ffo-card.ffo-provisional', tray);
		
		const resources = [];
		
		for (let i = 0; i < resourceElements.length; i++) {
			resources.push(resourceElements[i].getAttribute('data-resource-type'));
		}

		this.ajaxcall( "/feastforodin/feastforodin/selectWeapons.html", { 
			lock: true,
			weapons: resources.join('-')
		}, this,
			function( result ) {},
			function( is_error) {}
		);
	}

	function onFlipArtisanShed (event) {
		dojo.stopEvent( event );
		const target = event.target.closest('.ffo-player-board-building-artisan-shed');

		if (!target) {
			return;
		}

		const shed = target.getAttribute('data-artisan-shed');
		const shedFlipped = target.getAttribute('data-artisan-shed-flipped');

		target.classList.toggle(`ffo-player-board-building-${shed}`);
		target.classList.toggle(`ffo-player-board-building-${shedFlipped}`);
		target.setAttribute('data-artisan-shed', shedFlipped);
		target.setAttribute('data-artisan-shed-flipped', shed);

	}

	function onDiscardOccupation( event ) {
		dojo.stopEvent( event );
		
		if (event.target == event.currentTarget) {
			// Clicking on the top tray, nothing
			return;
		}
		
		const target = event.target.closest('.ffo-card');
		if (target == null) {
			// Not clicking on a card, nothing
			return;
		}
		
		if ( ! this.checkAction( 'discardOccupation' ) ) {
			return;
		}
		
		const occupationId = +target.getAttribute('data-occupation-id');
		
		this.ajaxcall( "/feastforodin/feastforodin/discardOccupation.html", { 
			lock: true,
			occupationId,
		}, this,
			function( result ) {},
			function( is_error) {}
		);
	}

	define({ onAction, onPass, onPassCheck, onPassFeastCheck, onPassEndGameCheck, onTopTrayClick, onSelectMode, onAffirm, onDeclareFailure, onReroll, onSelectGoods, onDeclareSuccess, onSaveBoards, onCancelBoards, onBuyShip, onEquipShip, onUseOccupation, onPassVikings, onSaveBoardsAndPass, onSaveFeastBoardsAndPass, onSaveBoardsAndPassEndGame, onUndo, onEquipBoard, onChooseVikingsNumber, onSelectForSlaughter, onClickResource, onSelectResources, onFlipArtisanShed, onDiscardOccupation, onSelectWeapons, onClickWeapon, onClickOnceTwiceUpgradeGood, onClickGoodUnique, sendUseOccupationFromModal, onSelectModeAjax, onPlayOccupation, onGoBack, onSetAutoPassAction, onSelectPersonalResource, onDrawPersonalResource, onChooseBestOfTwoStartingOcc, onSelectBestOfTwoStartingOcc, onBuildArtisanShed, onSelectArtisanShed });
})();