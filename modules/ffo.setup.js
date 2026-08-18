(function() {
	function onResize() {
		// Get view height:
		// = height of window - top of game area
		const windowHeight = window.innerHeight;
		const e_gamePlayArea = $('page-content');
		const gamePlayAreaTop = e_gamePlayArea.getBoundingClientRect().top + window.scrollY;
		
		// At least 500px tall
		const viewHeight = Math.max(windowHeight - gamePlayAreaTop, 500);
		//console.log("Resizing to viewHeight: " + viewHeight);
		
		let boardHeight = viewHeight;
		
		if (this.gamedatas.left_action_column > 0) {
			// Room for bonus actions too!
			boardHeight = (872/927) * viewHeight;
		}
		
		// If column, then don't resize board so aggressively!
		if (window.matchMedia('(max-width: 700px)').matches) {
			boardHeight = (window.innerWidth * 0.8)/0.4867;
		} else if (this.options.bigActionBoard) {
			boardHeight = Math.max(boardHeight, 1130);
		}
		
		let boardWidth = (boardHeight * 0.4867);
		
		// Set board height
		const e_actionBoard = $('ffo-action-board');
		
		if (!this.gamedatas.options.norwegians_expansion) {
			dojo.style(e_actionBoard, {
				width: boardWidth + 'px',
				height: boardHeight + 'px'
			});
		} else {
			boardWidth = (boardHeight * 0.5563);
			// relative heights:
			const norwegians1 = 432;
			const norwegians2 = 385;
			const norwegians3 = 319;
			const norwegiansHeightRelative = norwegians1 + norwegians2 + norwegians3;

			const norwegians1a = $('ffo-action-board-norwegians-first-many');
			const norwegians1b = $('ffo-action-board-norwegians-first-few');
			const norwegians2a = $('ffo-action-board-norwegians-second-many');
			const norwegians2b = $('ffo-action-board-norwegians-second-few');
			const norwegians3a = $('ffo-action-board-norwegians-third-many');
			const norwegians3b = $('ffo-action-board-norwegians-third-few');
			if (norwegians1a) {
				dojo.style(norwegians1a, {
					width: boardWidth + 'px',
					height: boardHeight / norwegiansHeightRelative * norwegians1 + 'px'
				});
			}
			if (norwegians1b) {
				dojo.style(norwegians1b, {
					width: boardWidth + 'px',
					height: boardHeight / norwegiansHeightRelative * norwegians1 + 'px'
				});
			}
			if (norwegians2a) {
				dojo.style(norwegians2a, {
					width: boardWidth + 'px',
					height: boardHeight / norwegiansHeightRelative * norwegians2 + 'px'
				});
			}
			if (norwegians2b) {
				dojo.style(norwegians2b, {
					width: boardWidth + 'px',
					height: boardHeight / norwegiansHeightRelative * norwegians2 + 'px'
				});
			}
			if (norwegians3a) {
				dojo.style(norwegians3a, {
					width: boardWidth + 'px',
					height: boardHeight / norwegiansHeightRelative * norwegians3 + 'px'
				});
			}
			if (norwegians3b) {
				dojo.style(norwegians3b, {
					width: boardWidth + 'px',
					height: boardHeight / norwegiansHeightRelative * norwegians3 + 'px'
				});
			}
		}

		
		// Set bonus action heights
		if (this.gamedatas.left_action_column > 0) {
			const bonusActionRatio = boardWidth / 1259;
			dojo.style($('ffo-bonus-action-left'), {
				width: (687 * bonusActionRatio) + 'px',
				height: (163 * bonusActionRatio) + 'px'
			});
			dojo.style($('ffo-bonus-action-right'), {
				width: (572 * bonusActionRatio) + 'px',
				height: (163 * bonusActionRatio) + 'px'
			});
		}
		
		// Find available width
		// = content width - right of action board
		let availableWidth = e_gamePlayArea.getBoundingClientRect().width;
		
		// If horizontal layout:
		if (window.matchMedia( "screen and (max-width: 700px)" ).matches) {
			availableWidth -= 5;
		} else {
			availableWidth -= boardWidth + 45;
		}
		
		const zoomLevel = availableWidth / 873;
		
		if (zoomLevel < 1) {
			this.zoomLevel = zoomLevel;
			dojo.style($('ffo-player-boards-holders'), {
				transform: 'scale(' + zoomLevel + ')',
				transformOrigin: '0 0',
			});
			
			// : Fix sizing of element (page flow)
			const boundingRect = $('ffo-player-boards-holders').getBoundingClientRect();
			const marginRight = boundingRect.width - boundingRect.width / zoomLevel;
			const marginBottom = boundingRect.height - boundingRect.height / zoomLevel;
			dojo.style($('ffo-player-boards-holders'), {
				marginRight: marginRight + 'px',
				marginBottom: marginBottom + 'px',
			});
			
			// TODO : Fix placing and sizing of dragged elements
			// Idea: Don't allow dragging if you have a zoom active?
			// Magnifying glass button, always the same size?
		} else {
			this.zoomLevel = 1;
			dojo.style($('ffo-player-boards-holders'), {
				transform: '',
				transformOrigin: '0 0',
				marginRight: 0,
				marginBottom: 0,
			});
			
		}
		
		{
			const boardsHolders = dojo.query('.ffo-player-boards-holder');
			const boardsMain = dojo.query('.ffo-player-board-main');
			if (boardsHolders.length > 0 && boardsMain.length > 0) {
				const spareSpace = boardsHolders[0].getBoundingClientRect().width - boardsMain[0].getBoundingClientRect().width;
				if (spareSpace < 125) {
					dojo.query('.ffo-player-board-occupations').addClass('ffo-player-board-occupations-below');
				} else {
					dojo.query('.ffo-player-board-occupations').removeClass('ffo-player-board-occupations-below');
				}
			}
		}
		
		this.fitTopTray();
		this.fitModal();
		dojo.style($('ffo-occupation-modes'), {'display': 'none'});
		$('ffo-occupation-modes').setAttribute('data-current-occupation', '');
	}
	
	function selectBoard(playerId) {
		// Make all panels invisible
		const boards = dojo.query('.ffo-player-boards-holder');
		for (let i = 0; i < boards.length; i++) {
			boards[i].style.display = 'none';
		}
		// Make my panel visible
		$('ffo-player-boards-holder-' + playerId).style.removeProperty('display');
		if (playerId != this.playerId()) {
			// Reposition all goods on this player's targets...
			this.playersById[playerId].repositionGoods();
		}
	}
	
	function setup ( gamedatas ) {
		let mobileControls = false;
		let responsiveOccupations = true;
		let singleView = true;
		
		if (this.prefs[100].value == 1) {
			// Mobile only
			mobileControls = this.mobileAndTabletCheck();
		} else if (this.prefs[100].value == 2) {
			// Always
			mobileControls = true;
		} else {
			// Never
			mobileControls = false;
		}
		if (this.prefs[101].value == 2) {
			responsiveOccupations = false;
		}
		if (this.prefs[102].value == 2) {
			singleView = false;
		}

		dojo.addClass(document.documentElement, mobileControls ? 'ffo-mobile-controls' : 'ffo-desktop-controls');
		
		this.options = {
			shipsInSideBar: false,
			playedOccupationsInSideBar: false,
			allowPrePlanningFeast: true,
			showActionDescriptions: true,
			confirmTimer: (this.gamedatas.options.confirm_timer) && (!this.g_archive_mode && this.bRealtime),
			bigActionBoard: false,
			singleView,
			mobileControls,
			responsiveOccupations,
		};
		
		dojo.place($('ffo-occupation-modes'), document.body);
		
		const numPlayers = Object.keys(gamedatas.players).length;
		
		onResize.call(this);
		

		// setup UI based on wether game is base or expansion

		if (gamedatas.options.norwegians_expansion) {

			// remove base game board
			dojo.query($('ffo-action-board')).remove();
			dojo.query($('ffo-bonus-action-holder')).remove();

			//remove unused norwegian action boards
			if (numPlayers <= 2) {
				dojo.query($('ffo-action-board-norwegians-first-many')).remove();
				dojo.query($('ffo-action-board-norwegians-second-many')).remove();
				dojo.query($('ffo-action-board-norwegians-third-many')).remove();
			} else if (numPlayers <= 3) {
				dojo.query($('ffo-action-board-norwegians-first-many')).remove();
				dojo.query($('ffo-action-board-norwegians-second-few')).remove();
				dojo.query($('ffo-action-board-norwegians-third-few')).remove();
			} else {
				dojo.query($('ffo-action-board-norwegians-first-few')).remove();
				dojo.query($('ffo-action-board-norwegians-second-few')).remove();
				dojo.query($('ffo-action-board-norwegians-third-few')).remove();
			}

		} else {
			dojo.query($('ffo-action-board-norwegians')).remove();
			// UI changes for 4 players
			if (gamedatas.left_action_column > 0) {
				dojo.style($('ffo-bonus-action-holder'), 'display', 'block');
				dojo.addClass($('ffo-bonus-action-left'), 'ffo-bonus-action-left-' + gamedatas.left_action_column);
				dojo.addClass($('ffo-bonus-action-right'), 'ffo-bonus-action-right-' + gamedatas.right_action_column);
				
				dojo.place(`<div class="ffo-action" data-action-id="${72 + +gamedatas.left_action_column}"></div>`, $('ffo-bonus-action-left'));
				dojo.place(`<div class="ffo-action" data-action-id="${72 + +gamedatas.right_action_column}"></div>`, $('ffo-bonus-action-right'));
			} else {
				dojo.style($('ffo-bonus-action-holder'), 'display', 'none');
			}
		}

		
		// Setting up player boards
		this.playersById = {};
		
		let index = 0;
		for (const i in gamedatas.player_order) {
			if (gamedatas.player_order[i] == this.playerId()) {
				index = +i;
				break;
			}
		}
		console.log("SETUP")
		console.log(this.gamedatas)
		
		// Players
		const playerBoardsHolders = $('ffo-player-boards-holders');
		//for( let playerId in gamedatas.players ) {
		for (let i = 0; i < numPlayers; i++) {
			const playerId = gamedatas.player_order[index];
			const po = gamedatas.players[playerId];
			const shortGameClass = gamedatas.options.short_game ? "ffo-player-board-main-short" : "";
			
			const playerBoardsHolder = dojo.place( `<div class="ffo-player-boards-holder" id="ffo-player-boards-holder-${playerId}"></div>`, playerBoardsHolders );
			const playerBoardMain = dojo.place( `<div class="ffo-player-board-main ${shortGameClass}"></div>`, playerBoardsHolder );
			const playerBoardOccs = dojo.place( `<div class="ffo-player-board-occupations"></div>`, playerBoardsHolder );
			const playerBoardExtra = dojo.place( `<div class="ffo-player-board-extra"></div>`, playerBoardsHolder );
			dojo.place( `<div style="clear: both;"></div>`, playerBoardsHolder );
			
			// Username
			const nameHolder = dojo.place( `<div class="ffo-player-name" style="${this.options.singleView || playerId == this.playerId() ? 'left: 45px;' : ''}${['ce2512', 'dfb000'].includes(po.color) ? ' background-color: rgba(0, 0, 0, 0.5);' : ''}">
				<span style="color: #${po.color};">${po.name}</span>
			</div>`, playerBoardMain );
			
			if (this.options.singleView) {
				const arrowColour = ['ce2512', 'dfb000'].includes(po.color) ? '#fff' : '#000';
				const prev = dojo.place(`<span style="cursor: pointer; color: ${arrowColour};">&larr;</span>`, nameHolder, 'first');
				const next = dojo.place(`<span style="cursor: pointer; color: ${arrowColour};">&rarr;</span>`, nameHolder, 'last');
				
				const nextIndex = (+index + 1) % gamedatas.player_order.length;
				const prevIndex = (+index + gamedatas.player_order.length - 1) % gamedatas.player_order.length;
				
				dojo.connect(next, 'onpointerdown', this, selectBoard.bind(this, gamedatas.player_order[nextIndex]));
				dojo.connect(prev, 'onpointerdown', this, selectBoard.bind(this, gamedatas.player_order[prevIndex]));
			}
			
			// Tokens on main board
			dojo.place( `<div class="ffo-feast-meeple-holder"></div>`, playerBoardMain );
			dojo.place( `<div class="ffo-feast-ship-holder"></div>`, playerBoardMain );
			dojo.place( `<div class="ffo-thing-meeple-holder"></div>`, playerBoardMain );
			dojo.place( `<div class="ffo-bench-meeple-holder"></div>`, playerBoardMain );
			dojo.place( `<div class="ffo-whalingboat-holder"></div>`, playerBoardMain );
			dojo.place( `<div class="ffo-bigboat-holder"></div>`, playerBoardMain );
			// dojo.place( `<div class="ffo-sheep-holder"></div>`, playerBoardMain );
			// dojo.place( `<div class="ffo-cow-holder"></div>`, playerBoardMain );
			
			if (gamedatas.options.harvest_expansion && gamedatas.custom_harvest) {
				//setup custom harvest markers
				const isShortGame = gamedatas.options.short_game;
				const short = isShortGame ? "short" : "";
				const customHarvestHolder = dojo.place( `<div class="ffo-thing-custom-harvest-holder ${short}"></div>`, playerBoardMain );
				
				const customHarvest = gamedatas.custom_harvest.split('-');
				if (customHarvest.length > 7) {
					customHarvest.splice(7);
				}
				if (isShortGame && customHarvest.length > 6) {
					customHarvest.splice(6);
				}
				customHarvest.forEach(harvest => {
					dojo.place(`<div class="tile-${harvest}"></div>`, customHarvestHolder);
				}); 

				const unrevealedTilesCount = isShortGame 
					? 6 - customHarvest.length 
					: 7 - customHarvest.length;

				for (let i = 0; i < unrevealedTilesCount; i++) {
					dojo.place(`<div class="tile-back"></div>`, customHarvestHolder);
				}

				//setup personal resource markers
				const personalResourceHolder = dojo.place( `<div class="ffo-thing-personal-resource-holder"></div>`, playerBoardMain );

				const markersArray = [];
				if (po.personal_resource_marker) {
					markersArray.push(po.personal_resource_marker); 
				}
				if (po.personal_resource_marker2) {
					markersArray.push(po.personal_resource_marker2); 
				}

				const nextHarvest = customHarvest.slice(-1); 
				let markerCount = 1;
				markersArray.forEach(marker => {
					const split = marker.split('-');
					const frontSideClass = nextHarvest == '0' ? `personal-resource-${split[1]}-dark` : `personal-resource-${split[0]}-light`;
					const backSideClass =  nextHarvest == '0' ? `personal-resource-${split[0]}-light` : `personal-resource-${split[1]}-dark`;

					const container = dojo.place(`<div class="ffo-thing-personal-resource-marker ffo-thing-personal-resource-marker-${markerCount}"></div>`, personalResourceHolder );
				    dojo.place(`<div class="personal-resource ${frontSideClass} front-side" data-class="personal-resource-${split[0]}-light"></div>`, container);
					dojo.place(`<div class="personal-resource ${backSideClass} back-side" data-class="personal-resource-${split[1]}-dark"></div>`, container);
					markerCount++;
				})
			}

			const playerBoardDiv = $('player_board_'+playerId);
			const playerBoardCustomDiv = dojo.place( this.format_block('jstpl_player_board', po ), playerBoardDiv );
			
			const player = new Player(this, po, playerBoardsHolder, playerBoardCustomDiv);
			this.playersById[playerId] = player;
			
			if (+player.playerObj.passed) {
				this.disablePlayerPanel( playerId );
			}
			
			player.updateResourceDisplay();
			player.updateWeaponsDisplay();
			player.updateOccupationsDisplay();
			player.updateMeepleDisplay();
			player.updateGoodsDisplay();
			player.updateShipsDisplay();
			player.updateBoardsDisplay();
			player.updateEmigrationsDisplay();
			player.updateArtisanShedsDisplay();
			
			if (this.options.mobileControls) {
				player.goodsRow.style.pointerEvents = 'none';
			}
			
			if (playerId == this.playerId()) {
				{
					const goods = player.goodsRow;
					this.addGoodsSource(goods);
				}
				
				// Add listeners for Ore/Wood/Silver
				{
					const goods = dojo.query('.ffo-main-resources', playerBoardCustomDiv);
					if (goods.length > 0) {
						this.addGoodsSource(goods[0]);
					}
				}
			}
			
			if (this.options.singleView) {
				// console.log(this.gamedatas.players[playerId].name)
				// Only your board (or the first board) is visible
				if (playerId == this.playerId() || (this.isSpectator && i == 0)) {
					// My board, keep it!
					// alert(this.gamedatas.players[playerId].name + ' keeps the board')
				} else {
					// alert(this.gamedatas.players[playerId].name + ' board is hidden')
					playerBoardsHolder.style.display = 'none';
				}
			}
			
			// Add switch to view button
			if (this.options.singleView && numPlayers > 1) {
				// TODO: decide what to do here, since this interferes with mobile - too easy to switch player boards when scrolling
				
				// const eye = dojo.place(
				// 	'<div class="ffo-icon ffo-icon-eye"></div>',
				// 	$('player_name_' + playerId)
				// );
				// dojo.connect($('overall_player_board_' + playerId), 'onpointerdown', this, selectBoard.bind(this, playerId));
				// dojo.connect(eye, 'onpointerdown', this, selectBoard.bind(this, playerId));
			}
			
			index = (+index + 1) % gamedatas.player_order.length;
		}
				
		// Tooltips
		this.addTooltipToClass( 'ffo-num-coin-income-holder', _('Income'), '', 0 );
		
		this.updateActionUseds();
		this.updateFirstPlayerDisplay();
		
		// Setup GoodsTargets
		// Also needs setting up whenever a new shed/house/island is taken
		
		onResize.call(this);
		
		// Todo: investigate using onScreenWidthChange instead?
		dojo.connect(window, "onresize", this.debounce(onResize.bind(this, true), 200));
		
		// Setup dragging listeners
		if (! this.options.mobileControls) {
			document.addEventListener('pointermove', GoodsSource.onMouseMove.bind(this));
			document.addEventListener('pointerup', GoodsSource.onMouseUp.bind(this));
			document.addEventListener('pointercancel', GoodsSource.onMouseUp.bind(this));
		}
		
		// Clickers
		dojo.connect($('ffo-action-board-holder'), "onclick", this.onAction);
		dojo.connect($('ffo-top-tray'), "onclick", this.onTopTrayClick);
		dojo.connect($('save_boards_button'), "onclick", this.onSaveBoards);
		dojo.connect($('cancel_boards_button'), "onclick", this.onCancelBoards);
		
		// Tabs
		dojo.connect($('ffo-tab-island'), "onclick", this.onShowModalIslands.bind(this, null, null));
		dojo.connect($('ffo-tab-forge'), "onclick", this.onShowModalForge.bind(this, null, null));
		dojo.connect($('ffo-tab-boats'), "onclick", this.onShowModalBoats.bind(this, null));
		dojo.connect($('ffo-tab-strips'), "onclick", this.onShowModalMountainStrips.bind(this, null));
		dojo.connect($('ffo-tab-goods'), "onclick", this.onShowModalGoods.bind(this, null));
		dojo.connect($('ffo-tab-sheds'), "onclick", this.onShowModalSheds.bind(this, null));
		dojo.connect($('ffo-tab-phases'), "onclick", this.onShowModalPhases.bind(this, null));
		dojo.connect($('ffo-tab-score'), "onclick", this.onShowModalScores.bind(this, null));
		// dojo.connect($('ffo-tab-autopass'), "onclick", this.onShowModalAutopass.bind(this, null));
		dojo.connect($('ffo-modal'), "onclick", this.onClickModal.bind(this));
		
		if (gamedatas.options.norwegians_expansion) {
			dojo.style($('ffo-tab-vptokens'), 'display', 'block');
			dojo.connect($('ffo-tab-vptokens'), "onclick", this.onShowVpTokens.bind(this, null));

		}

		// Action hover-overs
		{
			const actions = dojo.query('.ffo-action');
			for (let i = 0; i < actions.length; i++) {
				const action = actions[i];
				const actionId = action.getAttribute('data-action-id');
				action.id = 'ffo-action-' + actionId;
				let extra = '';
				if (this.options.showActionDescriptions) {
					if (actionId.startsWith('r') || actionId.startsWith('nr')) {
						const id = actionId.startsWith('n') ? actionId.substring(1) : actionId;
						extra = `<div>
							<h3>${this.i18n.getRowHeading(id)}</h3>
						</div>`;
					} else if (actionId.startsWith('c') || actionId.startsWith('nc')) {
						const id = actionId.startsWith('n') ? actionId.substring(1) : actionId;
						extra = `<div>
							<p>${this.i18n.getColHeading(id)}</p>
						</div>`;
					} else {
						let actionTexts = this.i18n.getAction(actionId) || {desc: ''}; //fallback before i18n is added
						extra = `<div>
							${actionTexts.name ? '<h3>' + actionTexts.name + '</h3>' : ''}
							<p>${actionTexts.desc}</p>
						</div>`;
					}
				}
				
				// Add tooltip
				const previewClassNorwegians = gamedatas.options.norwegians_expansion ? "ffo-action-preview-norwegians" : "";

				this.addTooltipHtml( 
					action.id, 
					`<div class="ffo-action-tooltip">
						<div class="ffo-action-preview ${previewClassNorwegians} ffo-action-preview-${actionId}"></div>
						${extra}
						<div style="clear: both;"></div>
					</div>`, 
					0 );
				if (actionId.startsWith('r')) {
					this.tooltips[action.id].position = ['above', 'after', 'below', 'before'];
				} else {
					this.tooltips[action.id].position = ['after', 'above', 'below', 'before'];
				}
			}
			
			if (gamedatas.left_action_column > 0) {
				const bonusActions = dojo.query('.ffo-action', $('ffo-bonus-action-holder'));
				for (let i = 0; i < bonusActions.length; i++) {
					const action = bonusActions[i];
					const actionId = action.getAttribute('data-action-id');
					action.id = 'ffo-action-' + actionId;
					
					const actionTexts = this.i18n.getAction(actionId);
					let extra = '';
					if (this.options.showActionDescriptions) {
						extra = `<div>
						${actionTexts.name ? '<h3>' + actionTexts.name + '</h3>' : ''}
						<p>${actionTexts.desc}</p>
					</div>`;
					}
					
					this.addTooltipHtml( 
						action.id, 
						`<div class="ffo-action-tooltip">
							<div class="ffo-action-preview ffo-action-preview-imitate"></div>
							${extra}
							<div style="clear: both;"></div>
						</div>`, 
						0 );
					this.tooltips[action.id].position = ['after', 'above', 'below', 'before'];
				}
			}
		}
		
		// Localisation
		$('ffo-to-translate-unsaved-message').innerHTML = _("You have unsaved changes");
		$('cancel_boards_button').innerHTML = _("Discard");
		$('save_boards_button').innerHTML = _("Save");
		
		// do not preload unused assets for given expansion and player count
		if (this.gamedatas.options.norwegians_expansion) {
			this.dontPreloadImage( 'action_board.jpg' );
			if (numPlayers <= 2) {
				this.dontPreloadImage( 'action_board_norwegians_1_many.jpg' );
				this.dontPreloadImage( 'action_board_norwegians_2_many.jpg' );
				this.dontPreloadImage( 'action_board_norwegians_3_many.jpg' );
			} else if (numPlayers <= 3) {
				this.dontPreloadImage( 'action_board_norwegians_1_many.jpg' );
				this.dontPreloadImage( 'action_board_norwegians_2_few.jpg' );
				this.dontPreloadImage( 'action_board_norwegians_3_few.jpg' );
			} else {
				this.dontPreloadImage( 'action_board_norwegians_1_few.jpg' );
				this.dontPreloadImage( 'action_board_norwegians_2_few.jpg' );
				this.dontPreloadImage( 'action_board_norwegians_3_few.jpg' );
			}
		} else {
			this.dontPreloadImage( 'goods_norwegians.png' );
			this.dontPreloadImage( 'goods_norwegians_erroneous.png' );
			this.dontPreloadImage( 'goods_norwegians_provisional.png' );
			this.dontPreloadImage( 'islands_norwegians.jpg' );
			this.dontPreloadImage( 'icons_norwegians.png' );
			this.dontPreloadImage( 'forge_minor.png' );
			this.dontPreloadImage( 'artisan_sheds.jpg' );
		}

		this.setupNotifications();
	}
	
	define({ setup, onResize });
})();