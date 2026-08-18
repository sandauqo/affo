(function() {
	function onEnteringState( stateName, args ) {
		console.log( 'Entering state: '+stateName );
		// console.log( args );
		this.stateName = stateName;

		if ( this.isCurrentPlayerActive() ) {
			dojo.addClass($('overall-content'), 'gamestate_playeractive');
		} else {
			dojo.removeClass($('overall-content'), 'gamestate_playeractive');
		}

		let expandButton = null;
		for (const i in this.goodsTargets) {
			const gt = this.goodsTargets[i];
			if (gt.information.type == "homefeast" && gt.player.playerId == this.playerId()) {
				expandButton = gt.expandButton;
				break;
			}
		}
		if (! this.options.allowPrePlanningFeast) {
			if (expandButton) {
				if (stateName == 'feast-placegoods') {
					dojo.style(expandButton, 'display', 'block');
				} else if (stateName == 'command-personalfeast' && this.isCurrentPlayerActive()) {
					dojo.style(expandButton, 'display', 'block');
				} else {
					dojo.style(expandButton, 'display', 'none');
				}
			}
		}

		let unsavedChanges = this.hasUnsavedChanges();
		$('ffo-unsaved-message').style.display = unsavedChanges ? 'block' : 'none';
		
		// Remove helper if there is one...
		const helperText = $('ffo-placement-helper-text');
		if (helperText) {
			helperText.remove();
		}
		
		switch( stateName )
		{
			case 'bestOfTwoStartingOcc':
				if( this.isCurrentPlayerActive() ) {
					// Put occupations in ffo-top-tray
					const tray = $('ffo-top-tray');
					tray.innerHTML = '';
					let innerHTML = '<div class="ffo-cards-container">';

					const player = this.playersById[this.playerId()];
					for (const i in player.playerObj.occupationsHand) {
						const o = player.playerObj.occupationsHand[i];
						innerHTML += `<div class="ffo-cards-container-item">
							<div class="ffo-card ffo-card-occ-${o.ability}" data-occupation-id="${o.id}" data-mode-id="0"></div>`;
						innerHTML += '</div>';
					}
					innerHTML += '</div>';
					tray.innerHTML = innerHTML;
				}
				break;
			case 'drawPersonalResource':
			case 'drawPersonalResource2':
				if( this.isCurrentPlayerActive() ) {
					const tray = $('ffo-top-tray');
					tray.innerHTML = '';
					const markers = args.args.markers;
					const container =  dojo.place('<div class="flex"></div>', tray);
					const isSecond = stateName === "drawPersonalResource2" ? 1 : 0;
					markers.forEach(marker => {
						const split = marker.split('-');
						const disabled = args.args.disabled.includes(marker) ? "disabled" : "";
						const holder = dojo.place(`<div class="personal-resource-holder ${disabled}" data-resource=${marker} data-isSecond=${isSecond}></div>`, container);
						const markerDiv = dojo.place('<div class="ffo-thing-personal-resource-marker-1"></div>', holder);
						dojo.place(`<div class="personal-resource personal-resource-${split[0]}-light front-side"></div>`, holder);
						dojo.place(`<div class="personal-resource personal-resource-${split[1]}-dark back-side"></div>`, holder);
					});

					this.fitTopTray();
				}
				break;
			case 'buildArtisanShed':
				if( this.isCurrentPlayerActive() ) {
					const tray = $('ffo-top-tray');
					tray.innerHTML = '';
					const sheds = args.args.artisan_sheds;
					sheds.forEach(shed => {
						const split = shed.split('-');
						const holder = dojo.place('<div class="artisan-shed-holder"></div>', tray);
						const disabled = args.args.disabled.includes(split[0]) ? "disabled" : "";
						const container1 = dojo.place(`<div class="${disabled} artisan-shed-container"></div>`, holder);
						dojo.place(`<div class="ffo-player-board-building ffo-player-board-building-artisan-shed ffo-player-board-building-${split[0]}" data-artisan-shed=${split[0]}></div>`, container1);
						const container2 = dojo.place(`<div class="${disabled} artisan-shed-container"></div>`, holder);
						dojo.place(`<div class="ffo-player-board-building ffo-player-board-building-artisan-shed ffo-player-board-building-${split[1]}" data-artisan-shed=${split[1]}></div>`, container2);
					})

					this.fitTopTray();
				}
				break;
			case 'discardOneBrown':
				if( this.isCurrentPlayerActive() ) {
					// Put occupations in ffo-top-tray
					const tray = $('ffo-top-tray');
					tray.innerHTML = '';
					let innerHTML = '<div class="ffo-cards-container">';

					const player = this.playersById[this.playerId()];
					console.log(args.args)
					console.log(player.playerObj.occupationsHand);
console.log(JSON.stringify(player.playerObj.occupationsHand));
					for (const i in player.playerObj.occupationsHand) {
						const o = player.playerObj.occupationsHand[i];
						if (o.starter === '1') continue;
						innerHTML += `<div class="ffo-cards-container-item">
							<div class="ffo-card ffo-card-occ-${o.ability}" data-occupation-id="${o.id}" data-mode-id="0"></div>`;
						innerHTML += '</div>';
					}
					innerHTML += '</div>';
					tray.innerHTML = innerHTML;
				}
// 				if( this.isCurrentPlayerActive() ) {
// 					// Put occupations in ffo-top-tray
// 					const tray = $('ffo-top-tray');
// 					tray.innerHTML = '';
// 					let innerHTML = '<div class="ffo-cards-container">';
// console.log(args.args)


// 				const player = this.playersById[this.playerId()];
// 				const hand = player.playerObj.occupationsHand

// console.log(hand);

// 					args.args.cards.forEach(card => {
// 						innerHTML += `<div class="ffo-cards-container-item">
// 							<div class="ffo-card ffo-card-occ-${card.ability}" data-occupation-id="${card.id}" data-mode-id="0"></div>`;
// 						innerHTML += '</div>';
// 					})
// 					innerHTML += '</div>';
// 					tray.innerHTML = innerHTML;
// 				}
				break;
			case 'bonus-placegoods':
			case 'income-placegoods':
			case 'feast-placegoods':
			case 'command-personalfeast':
			case 'gameEnd-placegoods':
				if( this.isCurrentPlayerActive() ) {
					if (stateName == 'income-placegoods') {
						const player = this.playersById[this.playerId()];
						if (player.hasOcc(42)) {
							this.gamedatas.gamestate.descriptionmyturn = _('${you} may place goods on your boards before receiving income, and may place 1 wood or stone in one of your sheds to receive 1 silver');
							this.updatePageTitle();
						}
					}
					
					const message = dojo.string.substitute(
						_('Use the ${icon} buttons to place goods on your boards.'),
						{
							'icon': '<div class="ffo-icon ffo-icon-expand"></div>'
						}
					);
					dojo.place(`<div id="ffo-placement-helper-text">
						${message}
					</div>`, $('maintitlebar_content'));
				}
				break;
			case 'command-pickmodal':
				const mustPass = +args.args.can_pass_override;
				if (mustPass) {
					this.gamedatas.gamestate.descriptionmyturn = _('${you} must pass as you do not meet the requirements for the action');
					this.updatePageTitle();
				}
				break;
			case 'command-forge': 
			case 'command-forgebuy':
			case 'command-forgeminor':
				if( this.isCurrentPlayerActive() ) {
					const tray = $('ffo-top-tray');
					this.onShowModalForge( tray, stateName == 'command-forgeminor' );
					if (stateName == 'command-forge') {
						const normalHolder = dojo.place(`<div></div>`, tray);
						// Add blue tiles
						const goods = ['jewelry'];
						for (const i in goods) {
							if (goods[i] == 'oil') {
								dojo.place(`<br>`, normalHolder);
							}
							dojo.place(`<div class="ffo-good ffo-good-${goods[i]}" data-rotation="0" data-flipped="0" data-good="${goods[i]}"><div class="ffo-good-image"></div></div>`, normalHolder);
						}
					}
					this.fitTopTray();
				}
				break;
			case 'command-explore':
			case 'command-explorepassable':
				// Put clickable islands at the top here!
				const islands = args.args.islands;
				const tray = $('ffo-top-tray');
				this.onShowModalIslands( tray, islands );
				break;
			case 'command-fineblacksmith': 
				if( this.isCurrentPlayerActive() ) {
					const tray = $('ffo-top-tray');
					tray.innerHTML = '';
					const normalHolder = dojo.place(`<div></div>`, tray, 'first');
					// Add possible tiles
					const goods = ['crucifix', 'cane'];
					for (const i in goods) {
						// If available, place it!
						for (const j in this.gamedatas.forge) {
							if (this.gamedatas.forge[j].name == goods[i]) {
								dojo.place(`<div class="ffo-good ffo-good-${goods[i]}" data-rotation="0" data-flipped="0" data-good="${goods[i]}"><div class="ffo-good-image"></div></div>`, normalHolder);
								break;
							}
						}
					}
				}
				break;
			case 'command-playOccupation': 
			case 'command-discardocc':
			case 'activatePreGameOccupation':
				if( this.isCurrentPlayerActive() ) {
					// Put occupations in ffo-top-tray
					const tray = $('ffo-top-tray');
					let innerHTML = '<div class="ffo-cards-container">';

					const player = this.playersById[this.playerId()];
					for (const i in player.playerObj.occupationsHand) {
						const o = player.playerObj.occupationsHand[i];
						if (o.starter === '1' && stateName == 'activatePreGameOccupation') continue;
						innerHTML += `<div class="ffo-cards-container-item">
							<div class="ffo-card ffo-card-occ-${o.ability}" data-occupation-id="${o.id}" data-mode-id="0"></div>`;
						if (this.gamedatas.options.norwegians_expansion && stateName == 'command-playOccupation') {
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
							innerHTML += `<div class="ffo-card ffo-norwegians-icon ffo-icon-vptoken-${token} scaled" data-occupation-id="${o.id}" data-mode-id="1"></div>`;
						}	
						innerHTML += '</div>';
					}
					innerHTML += '</div>';
					tray.innerHTML = innerHTML;
				}
				break;
			case 'command-mountain': 
			case 'command-armedfighter':
				if( this.isCurrentPlayerActive() ) {
					// Put strips in ffo-top-tray
					const tray = $('ffo-top-tray');
					this.onShowModalMountainStrips( tray );
				}
				break;
			case 'command-flip':
				if( this.isCurrentPlayerActive() ) {
					const tray = $('ffo-top-tray');
					tray.innerHTML = '';
					const player = this.playersById[this.playerId()];
					const forbiddenTypes = ['cattle', 'sheep', 'horse', 'pregnantcattle', 'pregnantsheep', 'pregnanthorse'];
					player.placeGoods(tray, false, (type) => {
						return forbiddenTypes.includes(type);
					});
				}
				break;
			case 'command-trade':
			case 'command-upgrade':
			case 'command-nwupgrade':
			case 'command-payupgrade':
			case 'command-sameupgrade':
			case 'command-meatupgrade': 
			case 'command-fieldfarmer':
				if( this.isCurrentPlayerActive() ) {
					const tray = $('ffo-top-tray');
					tray.innerHTML = '';
					const player = this.playersById[this.playerId()];
					let allowedTypes = (args && args.args) ? args.args.allowed_types : null;
					if (allowedTypes == null && stateName == 'command-trade') {
						allowedTypes = ['oil', 'hide', 'linen', 'wool', 'skinandbones', 'fur', 'robe', 'clothing', 'antlers', 'leather', 'amber', 'amber_ee'];
					}
					player.placeGoods(tray, false, (type) => {
						if (allowedTypes) {
							return ! allowedTypes.includes(type);
						} else {
							return false;
						}
					});
					// TODO : Only show relevant goods for the action (ones which can upgrade?)
					// Or...fade out the goods which can't upgrade.
					// Auto-select max if trading
					if (stateName == 'command-trade') {
						const allGoodElements = dojo.query('.ffo-good', tray);
						const differentGreens = {};
						for (let i = 0; i < allGoodElements.length; i++) {
							const goodName = allGoodElements[i].getAttribute('data-good');
							if (['oil', 'hide', 'linen', 'wool', 'skinandbones', 'fur', 'robe', 'clothing', 'antlers', 'leather', 'amber', 'amber_ee'].includes(goodName)) {
								differentGreens[goodName] = i;
							} else {
								
							}
						}
						for (let i in differentGreens) {
							dojo.addClass(allGoodElements[differentGreens[i]], 'ffo-provisional');
						}
					}
				}
				break;
			case 'command-oncetwiceupgrade':
				if( this.isCurrentPlayerActive() ) {
					const tray = $('ffo-top-tray');
					tray.innerHTML = '';
					const player = this.playersById[this.playerId()];
					let allowedTypes = (args && args.args) ? args.args.allowed_types : null;
					const onceRow = dojo.place(`<div id="once-row" class="once-twice-upgrade-row"></div>`, tray);
					dojo.place('<div class="once-twice-upgrade-once"></div>', onceRow);
					const twiceRow = dojo.place(`<div id="twice-row" class="once-twice-upgrade-row"></div>`, tray);
					dojo.place('<div class="once-twice-upgrade-twice"></div>', twiceRow);
					player.placeGoods(onceRow, false, (type) => {
						if (allowedTypes) {
							return ! allowedTypes.includes(type);
						} else {
							return false;
						}
					});
					player.placeGoods(twiceRow, false, (type) => {
						if (allowedTypes) {
							return ! allowedTypes.includes(type);
						} else {
							return false;
						}
					});
				}
				break;

			case 'command-storeman':
				if( this.isCurrentPlayerActive() ) {
					const tray = $('ffo-top-tray');
					tray.innerHTML = '';
					const player = this.playersById[this.playerId()];
					const amounts = {};
					for (const i in player.playerObj.goods) {
						const good = player.playerObj.goods[i];
						if (! amounts[good.type])
							amounts[good.type] = 0;
						if (!['sheep', 'cattle', 'horse', 'pig', 'pregnantsheep', 'pregnantcattle', 'pregnanthorse'].includes(good.type))
							amounts[good.type]++;
					}
					for (const i in player.playerObj.goods) {
						const good = player.playerObj.goods[i];
						if (amounts[good.type] == 3) {
							amounts[good.type] = 0;
							player.placeGood(good, tray, false);
						}
					}
					
				}
				break;
			case 'command-emigrate':
			case 'command-emigratepassable':
			case 'command-emigrateminor':
			case 'command-emigrateminorpassable':
			case 'command-harborguard':
			case 'command-swapwhalingboatknarr':
			case 'command-buylongshipfifthcol':
			case 'command-wharfowner':
			case 'command-shipbuilder':
			case 'command-swordfighter':
			case 'command-berserker':
				if( this.isCurrentPlayerActive() ) {
					const tray = $('ffo-top-tray');
					tray.innerHTML = '';
					
					const player = this.playersById[this.playerId()];
					for (const i in player.playerObj.ships) {
						const ship = player.playerObj.ships[i];
						
						const fragment = `<div class="ffo-ship ffo-ship-${ship.type}" data-ship-type="${ship.type}" data-ship-id="${ship.id}">
						</div>`;

						if (
							(['command-emigrate', 'command-emigratepassable', 'command-harborguard'].includes(stateName) && ship.type != 'whalingboat') ||
							(['command-swapwhalingboatknarr', 'command-buylongshipfifthcol', 'command-shipbuilder', 'command-emigrateminor', 'command-emigrateminorpassable'].includes(stateName) && ship.type == 'whalingboat') ||
							(['command-wharfowner'].includes(stateName) && ship.type == 'longship') ||
							(['command-swordfighter', 'command-berserker'].includes(stateName) && ship.type == 'longship' && ship.ore > 0)
						) {
							const placed = dojo.place(fragment, tray);
							
							for (let j = 0; j < ship.ore; j++) {
								const oreSlot = dojo.place(`<div class="ffo-ship-ore" data-slot="${j}" data-equipped="1"></div>`, placed);
							}
						}
					}
				}
				break;
			case 'command-hunt':
				if( this.isCurrentPlayerActive() ) {
					const tray = $('ffo-top-tray');
					tray.innerHTML = '';
					console.log('args')
					console.log(args.args)
					const player = this.playersById[this.playerId()];
					const dieType = args.args.die_type;
					const dieValue = args.args.die_value;
					const oreValue = args.args.ore_value;
					const huntType = args.args.hunt_type;
					const numRolls = args.args.num_rolls;
					const resourceType = args.args.resource_type;
					const weaponTypes = args.args.weapon_types;
					const bonus = args.args.bonus;
					// Put die
					dojo.place(`<div class="ffo-icon ffo-icon-${dieType}">
					</div>`, tray);
					dojo.place(`<span style="margin-left: 10px;" class="ffo-die-text">${dieValue}</span>`, tray);
					dojo.place(`<span id="ffo-die-modifier" class="ffo-die-text"></span>`, tray);
					dojo.place(`<span id="ffo-die-total" class="ffo-die-text"></span>`, tray);
					dojo.place(`<br><br>`, tray);
					
					// Put all resources
					for (let i = 0; i < +player.playerObj[resourceType]; i++) {
						dojo.place(`<div class="ffo-icon ffo-hunt-item ffo-icon-${resourceType}"></div>`, tray);
					}
					
					// Put all cards
					for (const i in player.playerObj.weapons) {
						const weapon = player.playerObj.weapons[i];
						if (weaponTypes.includes(weapon.type)) {
							dojo.place(`<div class="ffo-card ffo-hunt-item ffo-card-weapon-${weapon.type}">
							</div>`, tray);
						} else if (['raid', 'pillage', 'theft'].includes(huntType) && player.hasOcc(137) && weapon.type == 'spear') {
							dojo.place(`<div class="ffo-card ffo-hunt-item ffo-card-weapon-${weapon.type}">
							</div>`, tray);
						}
					}
					
					let card_icons = '';

					for (const w of weaponTypes) {
						card_icons += `<div class="ffo-card ffo-card-weapon-${w}"></div>`;
					}

					if (['raid', 'pillage', 'theft'].includes(huntType) && player.hasOcc(137)) {
						card_icons += `<div class="ffo-card ffo-card-weapon-spear"></div>`;
					}

					let rollLowMessage = '';
					let rollHighMessage = '';

					if (resourceType) {
						rollLowMessage += _('For success, reduce the result to 0 using ${card_icons} and ${resource_icon}.');
						rollHighMessage += _('For better goods, increase the result using ${card_icons} and ${resource_icon}.');
					} else {
						rollLowMessage += _('For success, reduce the result to 0 using ${card_icons}.');
						rollHighMessage += _('For better goods, increase the result using ${card_icons}.');
					}

					const message = dojo.string.substitute(
						['snare', 'game', 'whales', 'whalesminor', 'elk', 'fish', 'game2'].includes(huntType) ? rollLowMessage : rollHighMessage,
						{
							card_icons,
							'resource_icon': `<div class="ffo-icon ffo-icon-${resourceType}"></div>`
						}
					);
					dojo.place(`<div id="ffo-placement-helper-text">
						${message}
					</div>`, $('maintitlebar_content'));
					
					if (['raid', 'pillage', 'theft'].includes(huntType)) {
						const rewardsHolder = dojo.place(`<div class="ffo-potential-rewards"></div>`, tray);
						
						// Add rewards below too
						this.onShowModalForge( rewardsHolder );
						const normalHolder = dojo.place(`<div></div>`, rewardsHolder, 'first');
						// Add blue tiles
						const goods = ['rune', 'silverware', 'silk', 'chest', 'spices', 'jewelry', 'treasurechest', 'silverhoard'];

						if (this.gamedatas.options.norwegians_expansion) {
							goods.splice(5, 0, 'tools');
							goods.splice(8, 0, 'wadmal');
						}

						if (player.hasOcc(138)) {
							goods.push('oil', 'hide', 'linen', 'wool', 'skinandbones', 'fur', 'robe', 'clothing');
							if (this.gamedatas.options.norwegians_expansion) {
								goods.splice(15, 0, 'antlers');
								goods.splice(18, 0, 'leather');
							}
						}
						for (const i in goods) {
							if (goods[i] == 'oil') {
								dojo.place(`<br>`, normalHolder);
							}
							dojo.place(`<div class="ffo-good ffo-good-${goods[i]}" data-rotation="0" data-flipped="0" data-good="${goods[i]}"><div class="ffo-good-image"></div></div>`, normalHolder);
						}
						this.fitTopTray();
					}
					
					this.updateHuntButtons( true );
				}
				break;
			case 'command-hunt-reward': 
			case 'command-hunt-reward2': 
				if( this.isCurrentPlayerActive() ) {
					const player = this.playersById[this.playerId()];
					const tray = $('ffo-top-tray');
					tray.innerHTML = '';
					const rewardsHolder = dojo.place(`<div class="ffo-potential-rewards"></div>`, tray);
					this.onShowModalForge( rewardsHolder );
					const normalHolder = dojo.place(`<div></div>`, rewardsHolder, 'first');
					// Add blue tiles
					const goods = ['rune', 'silverware', 'silk', 'chest', 'spices', 'jewelry', 'treasurechest', 'silverhoard'];
					if (this.gamedatas.options.norwegians_expansion) {
						goods.splice(5, 0, 'tools');
						goods.splice(8, 0, 'wadmal');
					}
					if (player.hasOcc(138)) {
						goods.push('oil', 'hide', 'linen', 'wool', 'skinandbones', 'fur', 'robe', 'clothing');
						if (this.gamedatas.options.norwegians_expansion) {
							goods.splice(15, 0, 'antlers');
							goods.splice(18, 0, 'leather');
						}
					}
					for (const i in goods) {
						if (goods[i] == 'oil') {
							dojo.place(`<br>`, normalHolder);
						}
						dojo.place(`<div class="ffo-good ffo-good-${goods[i]}" data-rotation="0" data-flipped="0" data-good="${goods[i]}"><div class="ffo-good-image"></div></div>`, normalHolder);
					}
					this.updateHuntButtons( false );
					this.fitTopTray();
				}
				break;
			case 'command-attack':
				if( this.isCurrentPlayerActive() ) {
					const tray = $('ffo-top-tray');
					tray.innerHTML = '';
					const player = this.playersById[this.playerId()];
					const dieType = args.args.die_type;
					const dieValue = args.args.die_value;
					const oreValue = args.args.ore_value;
					const huntType = args.args.hunt_type;
					const numRolls = args.args.num_rolls;
					const resourceType = args.args.resource_type;
					const weaponTypes = args.args.weapon_types;
					const bonus = args.args.bonus;

					if (player.hasOcc(136) && huntType !== 'theft') {
						dojo.place(`<div id="raider-toggle">You can split battle result and select 2 goods</div>`, tray);
					}

					// Put die
					dojo.place(`<div class="ffo-icon ffo-icon-${dieType}">
					</div>`, tray);
					dojo.place(`<span style="margin-left: 10px;" class="ffo-die-text">${dieValue}</span>`, tray);
					dojo.place(`<span id="ffo-die-modifier" class="ffo-die-text"></span>`, tray);
					dojo.place(`<span id="ffo-die-total" class="ffo-die-text"></span>`, tray);
					dojo.place(`<br><br>`, tray);
					
					// Put all resources
					for (let i = 0; i < +player.playerObj[resourceType]; i++) {
						dojo.place(`<div class="ffo-icon ffo-hunt-item ffo-icon-${resourceType}"></div>`, tray);
					}
					
					// Put all cards
					for (const i in player.playerObj.weapons) {
						const weapon = player.playerObj.weapons[i];
						if (weaponTypes.includes(weapon.type)) {
							dojo.place(`<div class="ffo-card ffo-hunt-item ffo-card-weapon-${weapon.type}">
							</div>`, tray);
						} else if (['raid', 'pillage', 'theft'].includes(huntType) && player.hasOcc(137) && weapon.type == 'spear') {
							dojo.place(`<div class="ffo-card ffo-hunt-item ffo-card-weapon-${weapon.type}">
							</div>`, tray);
						}
					}
					
					let card_icons = '';

					for (const w of weaponTypes) {
						card_icons += `<div class="ffo-card ffo-card-weapon-${w}"></div>`;
					}

					if (['raid', 'pillage', 'theft'].includes(huntType) && player.hasOcc(137)) {
						card_icons += `<div class="ffo-card ffo-card-weapon-spear"></div>`;
					}

					let rollLowMessage = '';
					let rollHighMessage = '';

					if (resourceType) {
						rollLowMessage += _('For success, reduce the result to 0 using ${card_icons} and ${resource_icon}.');
						rollHighMessage += _('For better goods, increase the result using ${card_icons} and ${resource_icon}.');
					} else {
						rollLowMessage += _('For success, reduce the result to 0 using ${card_icons}.');
						rollHighMessage += _('For better goods, increase the result using ${card_icons}.');
					}

					const message = dojo.string.substitute(
						['snare', 'game', 'whales', 'whalesminor', 'elk', 'fish', 'game2'].includes(huntType) ? rollLowMessage : rollHighMessage,
						{
							card_icons,
							'resource_icon': `<div class="ffo-icon ffo-icon-${resourceType}"></div>`
						}
					);
					dojo.place(`<div id="ffo-placement-helper-text">
						${message}
					</div>`, $('maintitlebar_content'));
					
					if (['raid', 'pillage', 'theft'].includes(huntType)) {
						const rewardsHolder = dojo.place(`<div class="ffo-potential-rewards"></div>`, tray);
						
						// Add rewards below too
						this.onShowModalForge( rewardsHolder );
						const normalHolder = dojo.place(`<div></div>`, rewardsHolder, 'first');
						// Add blue tiles
						const goods = ['rune', 'silverware', 'silk', 'chest', 'spices', 'jewelry', 'treasurechest', 'silverhoard'];

						if (this.gamedatas.options.norwegians_expansion) {
							goods.splice(5, 0, 'tools');
							goods.splice(8, 0, 'wadmal');
						}

						if (player.hasOcc(138)) {
							goods.push('oil', 'hide', 'linen', 'wool', 'skinandbones', 'fur', 'robe', 'clothing');
							if (this.gamedatas.options.norwegians_expansion) {
								goods.splice(15, 0, 'antlers');
								goods.splice(18, 0, 'leather');
							}
						}
						for (const i in goods) {
							if (goods[i] == 'oil') {
								dojo.place(`<br>`, normalHolder);
							}
							dojo.place(`<div class="ffo-good ffo-good-${goods[i]}" data-rotation="0" data-flipped="0" data-good="${goods[i]}"><div class="ffo-good-image"></div></div>`, normalHolder);
						}
						this.fitTopTray();
					}
					
					this.updateHuntButtons( true );
				}
				break;
			case 'confirmTurn':
				this.startConfirmTimer();
				break;
			case 'command-slaughter':
				if( this.isCurrentPlayerActive() ) {
					const tray = $('ffo-top-tray');
					tray.innerHTML = '';
					const player = this.playersById[this.playerId()];
					const slaughterType = args.args.slaughterType;
					const allowedTypes = [slaughterType, `pregnant${slaughterType}`];
					for (const i in player.playerObj.goods) {
						const good = player.playerObj.goods[i];
						if (allowedTypes.includes(good.type)) {
							dojo.place(`<div class="ffo-good ffo-good-${good.type}" data-animal-type="${good.type}"><div class="ffo-good-image"></div></div>`, tray);
						}
					}
				}

				break;	
			case 'command-resources':
				if( this.isCurrentPlayerActive() ) {
					const tray = $('ffo-top-tray');
					tray.innerHTML = '';
					const player = this.playersById[this.playerId()];

					dojo.place('<div class="ffo-icon ffo-resource ffo-icon-wood" data-resource-type="wood"></div>', tray);
					dojo.place('<div class="ffo-icon ffo-resource ffo-icon-stone" data-resource-type="stone"></div>', tray);
					dojo.place('<div class="ffo-icon ffo-resource ffo-icon-ore" data-resource-type="ore"></div>', tray);
				}
				break;	
			case 'command-anytwoanimals':
				if( this.isCurrentPlayerActive() ) {
					const tray = $('ffo-top-tray');
					tray.innerHTML = '';
					dojo.place(`<div class="ffo-good ffo-good-sheep" data-good="sheep"><div class="ffo-good-image"></div></div>`, tray);
					dojo.place(`<div class="ffo-good ffo-good-sheep" data-good="sheep"><div class="ffo-good-image"></div></div>`, tray);
					dojo.place(`<div class="ffo-good ffo-good-horse" data-good="horse"><div class="ffo-good-image"></div></div>`, tray);
					dojo.place(`<div class="ffo-good ffo-good-horse" data-good="horse"><div class="ffo-good-image"></div></div>`, tray);
					dojo.place(`<div class="ffo-good ffo-good-pig" data-good="pig"><div class="ffo-good-image"></div></div>`, tray);
					dojo.place(`<div class="ffo-good ffo-good-pig" data-good="pig"><div class="ffo-good-image"></div></div>`, tray);
					dojo.place(`<div class="ffo-good ffo-good-cattle" data-good="cattle"><div class="ffo-good-image"></div></div>`, tray);
					dojo.place(`<div class="ffo-good ffo-good-cattle" data-good="cattle"><div class="ffo-good-image"></div></div>`, tray);
				}
				break;	
			case 'command-resolveModifierImmediatelly':
				if( this.isCurrentPlayerActive() ) {
					const tray = $('ffo-top-tray');
					tray.innerHTML = '';
					const player = this.playersById[this.playerId()];
					dojo.place(`<div class="ffo-card ffo-card-weapon">Do Something</div>`, tray);
				}
				break;
			case 'dummmy':
				break;
		}

		switch (stateName) {
			case 'income-placegoods':
			case 'feast-placegoods':
			case 'bonus-placegoods':
				// this.onSaveBoards(new Event(""), null, null, {"pass": true});
		}
	}
	
	function onLeavingState( stateName ) {
		switch( stateName )
		{
			case 'confirmTurn':
				this.stopConfirmTimer();
				break;
			case 'dummmy':
				break;
		}
	}
	
	function onUpdateActionButtons( stateName, args ) {

		// manage css class here to (instead on only onEnteringState) for multiactive situations, like bestOfTwoStartingOcc
		if ( this.isCurrentPlayerActive() ) {
			dojo.addClass($('overall-content'), 'gamestate_playeractive');
		} else {
			dojo.removeClass($('overall-content'), 'gamestate_playeractive');
		}

		if( this.isCurrentPlayerActive() ) {
			switch( stateName )
			{
				case 'bestOfTwoStartingOcc':
				case 'discardOneBrown':
					this.addActionButton( 'choose_button', _('Choose'), 'onChooseBestOfTwoStartingOcc' );
					break;
				case 'drawPersonalResource':
				case 'drawPersonalResource2':
					this.addActionButton( 'draw_button', _('Draw personal resource'), 'onDrawPersonalResource' );
					break;
				case 'buildArtisanShed':
					this.addActionButton( 'build_button', _('Build'), 'onBuildArtisanShed' );
					break;	
				case 'chooseVikingsNumber':
					const player = this.playersById[this.playerId()];
					this.addActionButton( 'choose1', `<span class="ffo-icon ffo-icon-meeple ffo-icon-meeple-${player.colour} choose-viking-btn"><span/>`, 'onChooseVikingsNumber' );
					this.addActionButton( 'choose2', `<span><span class="ffo-icon ffo-icon-meeple ffo-icon-meeple-${player.colour} choose-viking-btn"></span><span class="ffo-icon ffo-icon-meeple ffo-icon-meeple-${player.colour} choose-viking-btn"></span><span/>`, 'onChooseVikingsNumber' ); 
					$('choose1').setAttribute('data-viking-number', 1);
					$('choose2').setAttribute('data-viking-number', 2);
					break;
				case 'confirmTurn':
					if (+args.pending_draw) {
						this.addActionButton( 'affirm_button', _('Confirm and draw occupation') + ' <span id="ffo-confirm-timer"></span>', 'onAffirm' );
					} else {
						this.addActionButton( 'affirm_button', _('Confirm') + ' <span id="ffo-confirm-timer"></span>', 'onAffirm' );
					}
					this.updateConfirmTimer();
					break;
				case 'command-prehunt':
				case 'command-preattack':
					{
						const dieType = args.die_type;
						this.addActionButton( 'affirm_button', _('Roll') + ` <div class="ffo-icon ffo-icon-${dieType}">
						</div>`, 'onAffirm' );
					}
					break;
				case 'pickAction':
					this.addActionButton( 'pass_button', _('Pass'), 'onPassVikings' ); 
					break;
				case 'command-hunt-reward': 
					this.addActionButton( 'back_button', _('Back'), 'onGoBack', null, false, 'gray' ); 
					break;
				case 'command-playOccupation':
				case 'command-forgebuy':
				case 'command-swapwhalingboatknarr':
				case 'command-inspector':
				case 'command-wharfowner':
				case 'command-fineblacksmith':
				case 'command-harborguard':
				case 'command-emigratepassable':
				case 'command-emigrateminorpassable':
				case 'command-armedfighter':
				case 'command-hunt-reward2': 
				case 'command-swordfighter': 
				case 'command-berserker': 
				case 'command-follower':
				case 'command-latecomer':
				case 'command-explorepassable':
				case 'activatePreGameOccupation':
					this.addActionButton( 'pass_button', _('Pass'), 'onPass' ); 
					break;
				case 'command-inspector2': 
				case 'command-earl':
					this.addActionButton( 'affirm_button', _('Pay') + ' ' + this.gamedatas.round + ' <div class="ffo-icon ffo-icon-coin"></div>', 'onAffirm' );
					this.addActionButton( 'pass_button', _('Pass'), 'onPass' ); 
					break;
				case 'command-ironsmith':
					this.addActionButton( 'affirm_button', _('Pay') + ' 1 <div class="ffo-icon ffo-icon-coin"></div>', 'onAffirm' );
					this.addActionButton( 'pass_button', _('Pass'), 'onPass' ); 
					break;
				case 'command-hornblower':
					this.addActionButton( 'affirm_button', `1 <div class="ffo-icon ffo-icon-coin"></div>
					<div class="ffo-icon ffo-icon-arrow"></div> ` + _('Hunting Game'), 'onAffirm' );
					this.addActionButton( 'pass_button', _('Pass'), 'onPass' ); 
					break;
				case 'command-hornblower2':
					this.addActionButton( 'affirm_button', `2 <div class="ffo-icon ffo-icon-coin"></div>
					<div class="ffo-icon ffo-icon-arrow"></div> ` + _('Laying a Snare'), 'onAffirm' );
					this.addActionButton( 'pass_button', _('Pass'), 'onPass' ); 
					break;
				case 'command-cowherd':
					this.addActionButton( 'affirm_button', `
					<div class="ffo-good ffo-good-grain"><div class="ffo-good-image"></div></div>
					<div class="ffo-icon ffo-icon-coin"></div>
					<div class="ffo-icon ffo-icon-coin"></div>
					<div class="ffo-icon ffo-icon-arrow"></div> 
					<div class="ffo-good ffo-good-cattle"><div class="ffo-good-image"></div></div>`, 'onAffirm' );
					this.addActionButton( 'pass_button', _('Pass'), 'onPass' ); 
					break;
				case 'command-bonecollector':
					this.addActionButton( 'affirm_button', `
					<div class="ffo-icon ffo-icon-coin"></div>
					<div class="ffo-icon ffo-icon-arrow"></div> 
					<div class="ffo-good ffo-good-skinandbones"><div class="ffo-good-image"></div></div>`, 'onAffirm' );
					this.addActionButton( 'pass_button', _('Pass'), 'onPass' ); 
					break;
				case 'command-breeder':
					this.addActionButton( 'affirm_button', `
					<div class="ffo-good ffo-good-cattle"><div class="ffo-good-image"></div></div>
					<div class="ffo-icon ffo-icon-arrow"></div> 
					<div class="ffo-good ffo-good-pregnantcattle"><div class="ffo-good-image"></div></div>`, 'onAffirm' );
					this.addActionButton( 'pass_button', _('Pass'), 'onPass' ); 
					break;
				case 'command-shepherdboy':
					this.addActionButton( 'affirm_button', `
					2 <div class="ffo-icon ffo-icon-coin"></div>
					<div class="ffo-icon ffo-icon-arrow"></div> 
					<div class="ffo-good ffo-good-sheep"><div class="ffo-good-image"></div></div>`, 'onAffirm' );
					this.addActionButton( 'pass_button', _('Pass'), 'onPass' ); 
					break;
				case 'command-privatechef':
					this.addActionButton( 'affirm_button', _("Pay") + ` 4 <div class="ffo-icon ffo-icon-coin"></div>`, 'onAffirm' );
					this.addActionButton( 'pass_button', _('Pass'), 'onPass' ); 
					break;
				case 'command-quartermaster':
					{
						let cost = 8;
						// Discount for weapons...
						const player = this.playersById[this.playerId()];
						for (const i in player.playerObj.weapons) {
							const w = player.playerObj.weapons[i];
							if (w.type == 'sword') {
								cost -= 1;
							} else if (w.type == 'spear' || w.type == 'bow') {
								cost -= 0.5;
							}
						}
						if (cost < 0) {
							cost = 0;
						}
						this.addActionButton( 'affirm_button', _("Pay") + ` ${Math.floor(cost)} <div class="ffo-icon ffo-icon-coin"></div>`, 'onAffirm' );
						this.addActionButton( 'pass_button', _('Pass'), 'onPass' ); 
					}
					break;
				case 'command-longshipbuilder':
					{
						let cost = 8;
						const player = this.playersById[this.playerId()];
						// Discount for knarrs...
						let knarrs = 0;
						for (const i in player.playerObj.ships) {
							const ship = player.playerObj.ships[i];
							if (ship.type == "knarr") {
								knarrs++;
							}
						}
						if (knarrs >= 3) {
							cost -= 7;
						} else if (knarrs >= 2) {
							cost -= 5;
						} else if (knarrs >= 1) {
							cost -= 2;
						}
						this.addActionButton( 'affirm_button', _("Pay") + ` ${cost} <div class="ffo-icon ffo-icon-coin"></div>`, 'onAffirm' );
						this.addActionButton( 'pass_button', _('Pass'), 'onPass' ); 
					}
					break;
				case 'command-dragonslayer':
					this.addActionButton( 'affirm_button', _("Pay") + ` <div class="ffo-card ffo-card-weapon-snare"></div><div class="ffo-card ffo-card-weapon-snare"></div><div class="ffo-card ffo-card-weapon-spear"></div><div class="ffo-card ffo-card-weapon-spear"></div>`, 'onAffirm' );
					this.addActionButton( 'pass_button', _('Pass'), 'onPass' ); 
					break;
				case 'command-meatcurer':
					this.addActionButton( 'affirm_button', `<div class="ffo-card ffo-card-weapon-snare"></div>
					<div class="ffo-icon ffo-icon-arrow"></div>
					<div class="ffo-good ffo-good-saltmeat"><div class="ffo-good-image"></div></div>`, 'onAffirm' );
					this.addActionButton( 'pass_button', _('Pass'), 'onPass' ); 
					break;
				case 'command-snarespecialist':
					this.addActionButton( 'affirm_button', _("Pay") + ` <div class="ffo-card ffo-card-weapon-snare"></div>
					<div class="ffo-card ffo-card-weapon-snare"></div>`, 'onAffirm' );
					this.addActionButton( 'pass_button', _('Pass'), 'onPass' ); 
					break;
				case 'command-spicemerchant':
					this.addActionButton( 'affirm_button', `<div class="ffo-good ffo-good-oil"><div class="ffo-good-image"></div></div>
					<div class="ffo-good ffo-good-oil"><div class="ffo-good-image"></div></div> 
					
					<div class="ffo-icon ffo-icon-arrow"></div> 
					
					<div class="ffo-good ffo-good-spices"><div class="ffo-good-image"></div></div>`, 'onAffirm' );
					this.addActionButton( 'pass_button', _('Pass'), 'onPass' ); 
					break;
				case 'command-flaxbaker':
					this.addActionButton( 'affirm_button', `<div class="ffo-good ffo-good-flax"><div class="ffo-good-image"></div></div>
					
					<div class="ffo-icon ffo-icon-arrow"></div> 
					
					<div class="ffo-good ffo-good-grain"><div class="ffo-good-image"></div></div>
					<div class="ffo-icon ffo-icon-coin"></div>`, 'onAffirm' );
					this.addActionButton( 'pass_button', _('Pass'), 'onPass' ); 
					break;
				case 'command-silkstitcher':
					this.addActionButton( 'affirm_button', `3 <div class="ffo-icon ffo-icon-coin"></div>
					
					<div class="ffo-icon ffo-icon-arrow"></div> 
					
					<div class="ffo-good ffo-good-silk"><div class="ffo-good-image"></div></div>`, 'onAffirm' );
					this.addActionButton( 'pass_button', _('Pass'), 'onPass' ); 
					break;
				case 'command-spicetrader':
					this.addActionButton( 'affirm_button', `3 <div class="ffo-icon ffo-icon-coin"></div>
					
					<div class="ffo-icon ffo-icon-arrow"></div> 
					
					<div class="ffo-good ffo-good-spices"><div class="ffo-good-image"></div></div>`, 'onAffirm' );
					this.addActionButton( 'pass_button', _('Pass'), 'onPass' ); 
					break;
				case 'command-deerstalker':
					this.addActionButton( 'affirm_button', `2 <div class="ffo-icon ffo-icon-coin"></div>
					
					<div class="ffo-icon ffo-icon-arrow"></div> 
					
					<div class="ffo-good ffo-good-skinandbones"><div class="ffo-good-image"></div></div>`, 'onAffirm' );
					this.addActionButton( 'pass_button', _('Pass'), 'onPass' ); 
					break;
				case 'command-laborer':
					this.addActionButton( 'affirm_button', `2 <div class="ffo-icon ffo-icon-coin"></div>
					
					<div class="ffo-icon ffo-icon-arrow"></div> 
					
					<div class="ffo-good ffo-good-grain"><div class="ffo-good-image"></div></div> <div class="ffo-icon ffo-icon-wood"></div> <div class="ffo-icon ffo-icon-wood"></div>`, 'onAffirm' );
					this.addActionButton( 'pass_button', _('Pass'), 'onPass' ); 
					break;
				case 'command-hornturner':
					this.addActionButton( 'affirm_button', `<div class="ffo-good ffo-good-sheep"><div class="ffo-good-image"></div></div> 
					
					<div class="ffo-icon ffo-icon-arrow"></div> 
					
					<div class="ffo-good ffo-good-saltmeat"><div class="ffo-good-image"></div></div>
					<div class="ffo-good ffo-good-hide"><div class="ffo-good-image"></div></div>
					<div class="ffo-good ffo-good-wool"><div class="ffo-good-image"></div></div>`, 'onAffirm' );
					this.addActionButton( 'pass_button', _('Pass'), 'onPass' ); 
					break;
				case 'command-hornturner2':
					this.addActionButton( 'affirm_button', `3 <div class="ffo-icon ffo-icon-coin"></div> 
					
					<div class="ffo-icon ffo-icon-arrow"></div> 
					
					<div class="ffo-good ffo-good-horn"><div class="ffo-good-image"></div></div>`, 'onAffirm' );
					this.addActionButton( 'pass_button', _('Pass'), 'onPass' ); 
					break;
				case 'command-oreboatman':
				case 'command-fishcook':
				case 'income-placegoods':
				case 'bonus-placegoods':
					this.addActionButton( 'placement_pass_button', _('Done'), 'onPassCheck' );
					this.addActionButton( 'save_and_done_button', _('Save and done'), 'onSaveBoardsAndPass' );
					this.updatePlacementButtons();
					break;
				case 'gameEnd-placegoods':
					this.addActionButton( 'placement_pass_button', _('Done'), 'onPassEndGameCheck' );
					this.addActionButton( 'save_and_done_button', _('Save and done'), 'onSaveBoardsAndPassEndGame' );
					this.updatePlacementButtons();
					break;
				case 'feast-placegoods':
				case 'command-personalfeast':
					this.addActionButton( 'placement_pass_button', _('Done'), 'onPassFeastCheck' );
					this.addActionButton( 'save_and_done_button', _('Save and done'), 'onSaveFeastBoardsAndPass' );
					this.updatePlacementButtons();
					break;
				case 'command-buywhalingboatfifthcol':
					this.addActionButton( 'buy', `<div class="ffo-icon ffo-icon-coin"></div><div class="ffo-icon ffo-icon-wood"></div>
					<div class="ffo-icon ffo-icon-arrow"></div>
					<div class="ffo-ship ffo-ship-whalingboat"></div>`, 'onSelectMode' );
					this.addActionButton( 'pass_button', _('Pass'), 'onPass' ); 
					$('buy').setAttribute('data-mode-id', 0);
					break;
				case 'command-resolveModifierImmediatelly':
					this.addActionButton( 'resolve_immediatelly_button', _('Done'), 'onAffirm' );
					this.updatePlacementButtons();
					break;
				case 'command-pickmodal':
				case 'command-metalsmith':
				case 'command-hidebuyer':
				case 'command-preacher':
				case 'command-meatmerchant':
				case 'command-sower':
				case 'command-disheartenedwarrior':
				case 'command-lancebearer':
				case 'command-sponsor':
				case 'command-peacemaker':
				case 'command-beforeorafter-draw':
				case 'command-beforeorafter-play':
				case 'command-slowpoke':
				case 'command-bosporustraveller':
				case 'command-clerk':
				case 'command-preceptor':
				case 'command-corkshedbonus':

				

				if (stateName == 'command-pickmodal' && +args.can_pass_override) {
					this.addActionButton( 'pass_button', _('Pass'), 'onPass' );
					// TODO : Change the description text
					break;
				} else {
					let numModes = 0;
					let actionId = -1;
					let withPass = false;
					const player = this.playersById[this.playerId()];
					const buttonHtmls = [];
					if (stateName == 'command-beforeorafter-draw') {
						numModes = 2;
						buttonHtmls.push(_('Before action (can\'t undo)'));
						buttonHtmls.push(_('After action'));
					}
					else if (stateName == 'command-beforeorafter-play') {
						numModes = 2;
						buttonHtmls.push(_('Before action'));
						buttonHtmls.push(_('After action'));
					}
					else if (stateName == 'command-preceptor') {
						numModes = 2;
						buttonHtmls.push(_('Draw occupation'));
						buttonHtmls.push(_('Play occupation'));
					}
					else if (stateName == 'command-peacemaker') {
						numModes = 3;
						buttonHtmls.push(`<div class="ffo-icon ffo-icon-wood"></div>`);
						buttonHtmls.push(`<div class="ffo-icon ffo-icon-stone"></div>`);
						buttonHtmls.push(`<div class="ffo-icon ffo-icon-ore"></div>`);
						withPass = true;
					} 
					else if (stateName == 'command-bosporustraveller') {
						numModes = 2;
						buttonHtmls.push(`3 <div class="ffo-icon ffo-icon-coin"></div>
						<div class="ffo-icon ffo-icon-arrow"></div>
						<div class="ffo-good ffo-good-silk"><div class="ffo-good-image"></div></div>`);
						buttonHtmls.push(`4 <div class="ffo-icon ffo-icon-coin"></div>
						<div class="ffo-icon ffo-icon-arrow"></div>
						<div class="ffo-good ffo-good-spices"><div class="ffo-good-image"></div></div>`);
						withPass = true;
					} 
					else if (stateName == 'command-slowpoke') {
						numModes = 2;
						buttonHtmls.push(`<div class="ffo-icon ffo-icon-coin"></div>
						<div class="ffo-icon ffo-icon-arrow"></div>
						<div class="ffo-good ffo-good-oil"><div class="ffo-good-image"></div></div>`);
						buttonHtmls.push(`<div class="ffo-good ffo-good-mead"><div class="ffo-good-image"></div></div>
						<div class="ffo-icon ffo-icon-arrow"></div>
						<div class="ffo-good ffo-good-oil"><div class="ffo-good-image"></div></div>`);
						withPass = true;
					} 
					else if (stateName == 'command-corkshedbonus') {
						numModes = 3;
						withPass = true;
						const artisan_sheds = this.playersById[this.playerId()].playerObj.artisan_shed;
						buttonHtmls.push(`<div class="ffo-player-board-building ffo-player-board-building-shed"></div>`);
						buttonHtmls.push(`<div class="ffo-player-board-building ffo-player-board-building-artisan-shed ffo-player-board-building-${artisan_sheds.split('-')[0]}"></div>`);
						buttonHtmls.push(`<div class="ffo-player-board-building ffo-player-board-building-artisan-shed ffo-player-board-building-${artisan_sheds.split('-')[1]}"></div>`);
					} 
					else if (stateName == 'command-clerk') {
						numModes = player.countKnarrs();
						for (let i = 0; i < numModes; i++) {
							buttonHtmls.push(`${i + 1} <div class="ffo-icon ffo-icon-coin"></div>
							<div class="ffo-icon ffo-icon-arrow"></div>
							${i + 1} ↖`);
						}
						withPass = true;
					} 
					else if (stateName == 'command-metalsmith') {
						numModes = 3;
						buttonHtmls.push(`0 <div class="ffo-icon ffo-icon-coin"></div>
						<div class="ffo-icon ffo-icon-arrow"></div>
						<div class="ffo-good ffo-good-silverware"><div class="ffo-good-image"></div></div>`);
						buttonHtmls.push(`2 <div class="ffo-icon ffo-icon-coin"></div>
						<div class="ffo-icon ffo-icon-arrow"></div>
						<div class="ffo-good ffo-good-crucifix"><div class="ffo-good-image"></div></div>`);
						buttonHtmls.push(`5 <div class="ffo-icon ffo-icon-coin"></div>
						<div class="ffo-icon ffo-icon-arrow"></div>
						<div class="ffo-good ffo-good-grail"><div class="ffo-good-image"></div></div>`);
						withPass = true;
					} 
					else if (stateName == 'command-hidebuyer') {
						numModes = 3;
						buttonHtmls.push(`2 <div class="ffo-icon ffo-icon-coin"></div>
						<div class="ffo-icon ffo-icon-arrow"></div>
						 1 <div class="ffo-good ffo-good-hide"><div class="ffo-good-image"></div></div>`);
						buttonHtmls.push(`4 <div class="ffo-icon ffo-icon-coin"></div>
						<div class="ffo-icon ffo-icon-arrow"></div>
						2 <div class="ffo-good ffo-good-hide"><div class="ffo-good-image"></div></div>`);
						buttonHtmls.push(`6 <div class="ffo-icon ffo-icon-coin"></div>
						<div class="ffo-icon ffo-icon-arrow"></div>
						3 <div class="ffo-good ffo-good-hide"><div class="ffo-good-image"></div></div>`);
						withPass = true;
					} 
					else if (stateName == 'command-lancebearer') {
						numModes = 2;
						buttonHtmls.push(`<div class="ffo-good ffo-good-peas"><div class="ffo-good-image"></div></div>`);
						buttonHtmls.push(`<div class="ffo-icon ffo-icon-coin"></div>`);
					} 
					else if (stateName == 'command-disheartenedwarrior') {
						numModes = 2;
						buttonHtmls.push(`<div class="ffo-good ffo-good-stockfish"><div class="ffo-good-image"></div></div>`);
						buttonHtmls.push(`<div class="ffo-good ffo-good-oil"><div class="ffo-good-image"></div></div>`);
					} 
					else if (stateName == 'command-preacher') {
						numModes = 2;
						buttonHtmls.push(`<div class="ffo-good ffo-good-crucifix"><div class="ffo-good-image"></div></div>`);
						buttonHtmls.push(_("Mountain strips"));
						withPass = true;
					} 
					else if (stateName == 'command-sponsor') {
						numModes = 2;
						buttonHtmls.push(_('Pay') + ` 4 <div class="ffo-icon ffo-icon-wood"></div>`);
						buttonHtmls.push(_('Pay') + ` 3 <div class="ffo-icon ffo-icon-coin"></div>`);
						withPass = true;
					}
					else if (stateName == 'command-sower') {
						numModes = 2;
						buttonHtmls.push(`<div class="ffo-good ffo-good-flax"><div class="ffo-good-image"></div></div>`);
						buttonHtmls.push(`<div class="ffo-good ffo-good-grain"><div class="ffo-good-image"></div></div>`);
						withPass = true;
					}
					else if (stateName == 'command-meatmerchant') {
						numModes = 2;
						buttonHtmls.push(_('Pay') + ` <div class="ffo-good ffo-good-gamemeat"><div class="ffo-good-image"></div></div>`);
						buttonHtmls.push(_('Pay') + ` <div class="ffo-good ffo-good-whalemeat"><div class="ffo-good-image"></div></div>`);
						withPass = true;
					}
					else if (stateName == 'command-pickmodal') {
						numModes = args.num_modes;
						actionId = args.action_id;
						if (actionId == 57 && player.hasOcc(185)) {
							buttonHtmls.push(_("Raiding"));
							buttonHtmls.push(_("Upgrades"));
						} else if (actionId == 5 && player.hasOcc(105)) {
							buttonHtmls.push(`1 <div class="ffo-icon ffo-icon-wood"></div>
							<div class="ffo-icon ffo-icon-arrow"></div>
							<div class="ffo-ship ffo-ship-whalingboat"></div>`);
							buttonHtmls.push(`3 <div class="ffo-icon ffo-icon-wood"></div>
							<div class="ffo-icon ffo-icon-arrow"></div>
							<div class="ffo-ship ffo-ship-knarr"></div>`);
						} else if (actionId == 4) {
							buttonHtmls.push(_("Take") + ` <div class="ffo-player-board-building ffo-player-board-building-stonehouse"></div>
							<div class="ffo-ship ffo-ship-longship"></div>`);
							buttonHtmls.push(_("Take") + ` <div class="ffo-player-board-building ffo-player-board-building-longhouse"></div>
							<div class="ffo-ship ffo-ship-knarr"></div>`);
						} else if (actionId == 19) {
							buttonHtmls.push(`<div class="ffo-good ffo-good-sheep"><div class="ffo-good-image"></div></div>`);
							buttonHtmls.push(`<div class="ffo-icon ffo-icon-coin"></div>
							<div class="ffo-icon ffo-icon-arrow"></div>
							<div class="ffo-good ffo-good-cattle"><div class="ffo-good-image"></div></div>`);
						} else if (actionId == 38) {
							buttonHtmls.push(_("Pay") + ` <div class="ffo-icon ffo-icon-wood"></div>`);
							buttonHtmls.push(_("Pay") + ` <div class="ffo-icon ffo-icon-ore"></div>`);
						} else if (actionId == 52) {
							buttonHtmls.push(_("Mountain strips"));
							buttonHtmls.push(_("Upgrades"));
						} else if (actionId == 69) {
							buttonHtmls.push(_("Pay") + ` <div class="ffo-icon ffo-icon-stone"></div>`);
							buttonHtmls.push(_("Pay") + ` <div class="ffo-icon ffo-icon-ore"></div>`);
						} else if (actionId == 77 || actionId == 5550) {
							if (!player.hasBuiltArtisanShed()) {
								const artisan_sheds = this.playersById[this.playerId()].playerObj.artisan_shed;
								buttonHtmls.push(_("Take") + ` <div class="ffo-player-board-building ffo-player-board-building-shed"></div>`);
								buttonHtmls.push(_("Take") + ` <div class="ffo-player-board-building ffo-player-board-building-artisan-shed ffo-player-board-building-${artisan_sheds.split('-')[0]}"></div>`);
								buttonHtmls.push(_("Take") + ` <div class="ffo-player-board-building ffo-player-board-building-artisan-shed ffo-player-board-building-${artisan_sheds.split('-')[1]}"></div>`);
							}
						} else if (actionId == 90) {
							buttonHtmls.push(`<div class="ffo-icon ffo-icon-stone"></div>
							<div class="ffo-icon ffo-icon-arrow"></div>
							<div class="ffo-player-board-building ffo-player-board-building-stonehouse"></div>`);
							buttonHtmls.push(`<div class="ffo-icon ffo-icon-wood"></div>
							<div class="ffo-icon ffo-icon-arrow"></div>
							<div class="ffo-ship ffo-ship-knarr"></div>`);
						} else if (actionId == 91) {
							buttonHtmls.push(`<div class="ffo-icon ffo-icon-stone"></div><div class="ffo-icon ffo-icon-stone"></div>
							<div class="ffo-icon ffo-icon-arrow"></div>
							<div class="ffo-player-board-building ffo-player-board-building-longhouse"></div>`);
							buttonHtmls.push(`<div class="ffo-icon ffo-icon-wood"></div><div class="ffo-icon ffo-icon-wood"></div>
							<div class="ffo-icon ffo-icon-arrow"></div>
							<div class="ffo-ship ffo-ship-longship"></div>`);
						} else if (actionId == 93) {
							buttonHtmls.push(`<div class="ffo-good ffo-good-sheep"><div class="ffo-good-image"></div></div>
							<div class="ffo-icon ffo-icon-arrow"></div>
							<div class="ffo-good ffo-good-wool"><div class="ffo-good-image"></div></div>
							<div class="ffo-good ffo-good-hide"><div class="ffo-good-image"></div></div>
							<div class="ffo-good ffo-good-saltmeat"><div class="ffo-good-image"></div></div>`);
							buttonHtmls.push(`<div class="ffo-good ffo-good-horse"><div class="ffo-good-image"></div></div>
							<div class="ffo-icon ffo-icon-arrow"></div>
							<div class="ffo-good ffo-good-saltmeat"><div class="ffo-good-image"></div></div>
							<div class="ffo-good ffo-good-leather"><div class="ffo-good-image"></div></div>`);
							buttonHtmls.push(`<div class="ffo-good ffo-good-cattle"><div class="ffo-good-image"></div></div>
							<div class="ffo-icon ffo-icon-arrow"></div>
							<div class="ffo-good ffo-good-saltmeat"><div class="ffo-good-image"></div></div>
							<div class="ffo-good ffo-good-leather"><div class="ffo-good-image"></div></div>`);
						} else if (actionId == 86) {
							const coin = player.hasOcc(1) ? 2 : 3;
							buttonHtmls.push(`${coin} <div class="ffo-icon ffo-icon-coin"></div>
							<div class="ffo-icon ffo-icon-arrow"></div>
							<div class="ffo-good ffo-good-grain"><div class="ffo-good-image"></div></div>
							<div class="ffo-good ffo-good-sheep"><div class="ffo-good-image"></div></div>`);
							buttonHtmls.push(`${coin} <div class="ffo-icon ffo-icon-coin"></div>
							<div class="ffo-icon ffo-icon-arrow"></div>
							<div class="ffo-good ffo-good-grain"><div class="ffo-good-image"></div></div>
							<div class="ffo-good ffo-good-horse"><div class="ffo-good-image"></div></div>`);
							buttonHtmls.push(`${coin} <div class="ffo-icon ffo-icon-coin"></div>
							<div class="ffo-icon ffo-icon-arrow"></div>
							<div class="ffo-good ffo-good-grain"><div class="ffo-good-image"></div></div>
							<div class="ffo-good ffo-good-pig"><div class="ffo-good-image"></div></div>`);
							buttonHtmls.push(`${coin} <div class="ffo-icon ffo-icon-coin"></div>
							<div class="ffo-icon ffo-icon-arrow"></div>
							<div class="ffo-good ffo-good-grain"><div class="ffo-good-image"></div></div>
							<div class="ffo-good ffo-good-cattle"><div class="ffo-good-image"></div></div>`);
						} else if (actionId == 87) {
							const coin = player.hasOcc(1) ? 1 : 2;
							buttonHtmls.push(`${coin} <div class="ffo-icon ffo-icon-coin"></div>
							<div class="ffo-good ffo-good-beans"><div class="ffo-good-image"></div></div>
							<div class="ffo-icon ffo-icon-arrow"></div>
							<div class="ffo-good ffo-good-sheep"><div class="ffo-good-image"></div></div>
							<div class="ffo-good ffo-good-sheep"><div class="ffo-good-image"></div></div>`);
							buttonHtmls.push(`${coin} <div class="ffo-icon ffo-icon-coin"></div>
							<div class="ffo-good ffo-good-beans"><div class="ffo-good-image"></div></div>
							<div class="ffo-icon ffo-icon-arrow"></div>
							<div class="ffo-good ffo-good-pig"><div class="ffo-good-image"></div></div>
							<div class="ffo-good ffo-good-pig"><div class="ffo-good-image"></div></div>`);
							buttonHtmls.push(`${coin} <div class="ffo-icon ffo-icon-coin"></div>
							<div class="ffo-good ffo-good-beans"><div class="ffo-good-image"></div></div>
							<div class="ffo-icon ffo-icon-arrow"></div>
							<div class="ffo-good ffo-good-sheep"><div class="ffo-good-image"></div></div>
							<div class="ffo-good ffo-good-pig"><div class="ffo-good-image"></div></div>`);
						} else if (actionId == 89) {
							buttonHtmls.push(`<div class="ffo-good ffo-good-horse"><div class="ffo-good-image"></div></div>
							<div class="ffo-icon ffo-icon-arrow"></div>
							<div class="ffo-good ffo-good-saltmeat"><div class="ffo-good-image"></div></div>
							<div class="ffo-good ffo-good-leather"><div class="ffo-good-image"></div></div>`);
							buttonHtmls.push(`<div class="ffo-good ffo-good-cattle"><div class="ffo-good-image"></div></div>
							<div class="ffo-icon ffo-icon-arrow"></div>
							<div class="ffo-good ffo-good-saltmeat"><div class="ffo-good-image"></div></div>
							<div class="ffo-good ffo-good-leather"><div class="ffo-good-image"></div></div>`)
						} else if (actionId == 5555) {
							buttonHtmls.push(`<div class="ffo-icon ffo-icon-wood"></div>`);
							buttonHtmls.push(`<div class="ffo-icon ffo-icon-stone"></div>`);
							buttonHtmls.push(`<div class="ffo-icon ffo-icon-ore"></div>`);
						}
					}

					for (let i = 0; i < numModes; i++) {
						let buttonColour = 'blue';
						if (i < buttonHtmls.length) {
							this.addActionButton( 
								'mode_' + i + '_button', 
								buttonHtmls[i], 
								'onSelectMode',
								null,
								false,
								buttonColour );
							$('mode_' + i + '_button').setAttribute('data-mode-id', i);
							$('mode_' + i + '_button').setAttribute('data-action-id', actionId);
						}
					}
					if (withPass) {
						this.addActionButton( 'pass_button', _('Pass'), 'onPass' ); 
					}
					break;
				}
				case 'command-exchange':
				{
					const to = args ? args.to : "";
					const from = args ? args.from : "";
					let toDiv = '';
					if (["coin", "wood", "stone", "ore"].includes(to)) {
						toDiv = `<div class="ffo-icon ffo-icon-${to}"></div>`;
					} else {
						toDiv = `<div class="ffo-good ffo-good-${to}"><div class="ffo-good-image"></div></div>`;
					}
					let fromDiv = '';
					if (["coin", "wood", "stone", "ore"].includes(from)) {
						fromDiv = `<div class="ffo-icon ffo-icon-${from}"></div>`;
					} else {
						fromDiv = `<div class="ffo-good ffo-good-${from}"><div class="ffo-good-image"></div></div>`;
					}
					this.addActionButton( 'affirm_button', 
					`${fromDiv}
					<div class="ffo-icon ffo-icon-arrow"></div>
					${toDiv}`, 'onAffirm' );
					this.addActionButton( 'pass_button', _('Pass'), 'onPass' );
					break;
				}
				case 'command-trade':
				case 'command-upgrade': 
				case 'command-nwupgrade': 
				case 'command-sameupgrade': 
				case 'command-meatupgrade':
				case 'command-draw-weapon-x-2':
				case 'command-oncetwiceupgrade':
					{
						const player = this.playersById[this.playerId()];
						const actionId = args ? args.action_id : -1;
						if (player.hasOcc(102) && [45, 46].includes(+actionId)) {
							// Add extra options
							this.addActionButton( 
								'mode_0_button', 
								`<div class="ffo-good ffo-good-gamemeat"><div class="ffo-good-image"></div></div> 
								<div class="ffo-icon ffo-icon-arrow"></div> 
								<div class="ffo-good ffo-good-fur"><div class="ffo-good-image"></div></div>`, 
								'onSelectMode' );
							this.addActionButton( 
								'mode_1_button', 
								`<div class="ffo-good ffo-good-silk"><div class="ffo-good-image"></div></div> 
								<div class="ffo-icon ffo-icon-arrow"></div> 
								<div class="ffo-good ffo-good-fur"><div class="ffo-good-image"></div></div>`, 
								'onSelectMode' );
							$('mode_0_button').setAttribute('data-mode-id', 0);
							$('mode_1_button').setAttribute('data-mode-id', 1);
						}
					}
					this.addActionButton( 'action_button', _('Upgrade'), 'onSelectGoods' );
					break;
				case 'command-furmerchant':
					{
						const player = this.playersById[this.playerId()];
						const actionId = args ? args.action_id : -1;
						if (player.hasOcc(102) && [114].includes(+actionId)) {
							this.addActionButton( 
								'mode_0_button', 
								`<div class="ffo-good ffo-good-gamemeat"><div class="ffo-good-image"></div></div> 
								<div class="ffo-icon ffo-icon-arrow"></div> 
								<div class="ffo-good ffo-good-fur"><div class="ffo-good-image"></div></div>`, 
								'onSelectMode' );
							this.addActionButton( 
								'mode_1_button', 
								`<div class="ffo-good ffo-good-silk"><div class="ffo-good-image"></div></div> 
								<div class="ffo-icon ffo-icon-arrow"></div> 
								<div class="ffo-good ffo-good-fur"><div class="ffo-good-image"></div></div>`, 
								'onSelectMode' );
								this.addActionButton( 
									'mode_2_button', 
									_('Draw'), 
									'onSelectMode' );
							$('mode_0_button').setAttribute('data-mode-id', 0);
							$('mode_1_button').setAttribute('data-mode-id', 1);
							$('mode_2_button').setAttribute('data-mode-id', 2);
						}
						break;
					}
				case 'command-payupgrade':
					this.addActionButton( 'upgrade_button', '<div class="ffo-icon ffo-icon-coin"></div> <div class="ffo-icon ffo-icon-arrow"></div>' + _('Upgrade'), 'onSelectGoods' );
					this.addActionButton( 'pass_button', _('Pass'), 'onPass' );
					break;
				case 'command-soberman':
					this.addActionButton( 'affirm_button', _('Forgo placing mead') + ' <div class="ffo-icon ffo-icon-arrow"></div> <div class="ffo-icon ffo-icon-coin"></div>', 'onAffirm' );
					this.addActionButton( 'pass_button', _('Pass'), 'onPass' );
					break;
				case 'command-flip':
					this.addActionButton( 'upgrade_button', _('Flip'), 'onSelectGoods' );
					break;
				case 'command-fieldfarmer':
					this.addActionButton( 'upgrade_button', _('Upgrade'), 'onSelectGoods' );
					this.addActionButton( 'pass_button', _('Pass'), 'onPass' );
					break;
				case 'command-storeman':
					this.addActionButton( 'upgrade_button', _('Done'), 'onSelectGoods' );
					break;
				case 'command-hunt':
				{
					const player = this.playersById[this.playerId()];
					const numRolls = args.num_rolls;
					let maxRolls = 3;
					if (player.hasOcc(153)) {
						maxRolls++;
					}
					this.addActionButton( 'reroll_button', _('Reroll') + ` (${numRolls}/${maxRolls})`, 'onReroll', null, false, numRolls >= maxRolls ? 'gray' : 'blue' );
					this.addActionButton( 'hunt_button', _('Success'), 'onDeclareSuccess' );
					this.addActionButton( 'failure_button', _('Failure'), 'onDeclareFailure', null, false, 'red' );
					
					if (numRolls >= maxRolls) {
						dojo.addClass($('reroll_button'), 'ffo-invalid-button');
					}
					break;
				}
				case 'command-attack':
					{
						const player = this.playersById[this.playerId()];
						const numRolls = args.num_rolls;
						let maxRolls = 3;
						if (player.hasOcc(153)) {
							maxRolls++;
						}
						this.addActionButton( 'reroll_button', _('Reroll') + ` (${numRolls}/${maxRolls})`, 'onReroll', null, false, numRolls >= maxRolls ? 'gray' : 'blue' );
						this.addActionButton( 'hunt_button', _('Success'), 'onDeclareSuccess' );
						this.addActionButton( 'failure_button', _('Declare failure'), 'onDeclareFailure', null, false, 'red' );
						
						if (numRolls >= maxRolls) {
							dojo.addClass($('reroll_button'), 'ffo-invalid-button');
						}
						break;
					}
				case 'command-resources':
					this.addActionButton( 'select_button', _('Select'), 'onSelectResources' );
					break;	
				case 'command-anytwoanimals':
					this.addActionButton( 'select_button', _('Select'), 'onSelectGoods' );
					break;	
				case 'command-coinsoroccs':
					this.addActionButton( 'mode_0_button', _('Take') + ` 2 <div class="ffo-icon ffo-icon-coin"></div>`, 'onSelectMode' );
					this.addActionButton( 'mode_1_button', _('Draw') + ` 3 (can\'t undo)<div class="ffo-card ffo-card-occ-back"></div>`, 'onSelectMode' );
					$('mode_0_button').setAttribute('data-mode-id', 0)
					$('mode_1_button').setAttribute('data-mode-id', 1)
					break;
				case 'dummy':
					break;
			}
			if (this.gamedatas.gamestate.possibleactions && this.gamedatas.gamestate.possibleactions.includes("undo")) {
				if (+this.gamedatas.can_undo) {
					this.addActionButton( 'undo_turn_button', _('Undo turn'), 'onUndo', null, false, 'gray' ); 
				}
			}
		}
	}
	
	function updateHuntButtons( autoSelect = false ) {
		const dieValue = +this.gamedatas.gamestate.args.die_value || 0;
		const oreValue = +this.gamedatas.gamestate.args.ore_value || 0;
		const huntType = this.gamedatas.gamestate.args.hunt_type;
		let value = +this.gamedatas.gamestate.args.raid_value || 0;
		
		const bonus = +this.gamedatas.gamestate.args.bonus || 0;
		const tray = $('ffo-top-tray');
		const player = this.playersById[this.playerId()];
		
		if (autoSelect) {
			// select weapons and resources if it will make a difference
		}
		
		let canSucceed = false;
		{
			// Count selected stuff
			const numItems = dojo.query('.ffo-hunt-item.ffo-selected', tray).length;
			
			if (huntType == 'raid' || huntType == 'pillage' || huntType == 'theft') {
				// Raid/pillage you need to be higher than 6 (runestone)
				value += numItems;
				if (huntType == 'pillage') {
					// add 'ore' from one longship
					value += +oreValue;
				}
				if (player.hasOcc(89)) {
					// Stone counts double
					value += dojo.query('.ffo-hunt-item.ffo-icon-stone.ffo-selected', tray).length;
				}
				let minValue = 6;
				if (player.hasOcc(138)) {
					minValue = 5;
				}
				const actualResult = +dieValue + value + bonus;
				if (actualResult >= minValue) {
					canSucceed = true;
				}
				
				// Red out potentials you can't afford...
				const rewardsHolder = dojo.query('.ffo-potential-rewards', $('ffo-top-tray'))[0];
				
				let reducedStrengthGoods = [];
				if (player.hasOcc(139)) {
					// Highest forge item has strength - 1
					const forge = dojo.query('.ffo-forge', rewardsHolder)[0];
					const goods = dojo.query('.ffo-good', forge);
					let reducedStrength = -1;
					for (let i = 0; i < goods.length; i++) {
						const g = goods[i];
						const goodName = g.getAttribute('data-good');
						if (goodName == 'scales' || goodName == 'tree') {
							continue;
						}
						const strength = getStrength(goodName);
						if (strength > reducedStrength) {
							reducedStrength = strength;
							reducedStrengthGoods = [goodName];
						} else if (strength == reducedStrength) {
							reducedStrengthGoods.push(goodName);
						}
					}
				}
				
				const goods = dojo.query('.ffo-good', rewardsHolder);
				for (let i = 0; i < goods.length; i++) {
					const g = goods[i];
					const goodName = g.getAttribute('data-good');
					let strength = getStrength(goodName);
					dojo.removeClass(g, 'ffo-provisional ffo-erroneous');
					if (reducedStrengthGoods.includes(goodName)) {
						strength--;
					}
					if (actualResult < strength) {
						dojo.addClass(g, 'ffo-provisional ffo-erroneous');
					}
				}
			} else {
				// Hunt, you need to get down to 0
				value = -numItems;
				if (huntType == 'whales' || huntType == 'whalesminor') {
					if (player.hasOcc(11)) {
						value -= dojo.query('.ffo-hunt-item.ffo-card.ffo-selected', tray).length;
					}
				}
				if (huntType == 'whales') {
					// subtract 'ore' from all whalingboats
					value -= +oreValue;
				} else if (huntType == 'whalesminor') {
					// subtract 'ore' from one whalingboat
					value -= +oreValue;
				}
				if (+dieValue + value + bonus <= 0) {
					canSucceed = true;
				}
			}
		}
		
		value += bonus;
		
		if ($('ffo-die-modifier')) {
			$('ffo-die-modifier').innerHTML = '';
			if (value > 0) {
				$('ffo-die-modifier').innerHTML = ` + ${value}`;
			} else if (value < 0) {
				$('ffo-die-modifier').innerHTML = ` - ${-value}`;
			}
		}
		if ($('ffo-die-total')) {
			if (value == 0) {
				$('ffo-die-total').innerHTML = ``;
			} else {
				let colour = canSucceed ? '#89a538' : '#c92727';
				$('ffo-die-total').innerHTML = ` = <span style="color: ${colour};">${+dieValue + value}</span>`;
			}
		}
		if ($('hunt_button')) {
			dojo.removeClass($('hunt_button'), 'bgabutton_blue bgabutton_gray bgabutton_green ffo-invalid-button');
			dojo.addClass($('hunt_button'), canSucceed ? 'bgabutton_green' : 'bgabutton_gray ffo-invalid-button');	
		}
	}
	
	function getStrength( good ) {
		switch (good) {
			case 'rune': return 6;
			case 'oil': return 5;
			case 'silverware': return 7;
			case 'hide': return 6;
			case 'silk': return 8;
			case 'linen': return 7;
			case 'chest': return 8;
			case 'wool': return 7;
			case 'spices': return 9;
			case 'skinandbones': return 8;
			case 'jewelry': return 10;
			case 'fur': return 9;
			case 'treasurechest': return 11;
			case 'robe': return 10;
			case 'silverhoard': return 15;
			case 'clothing': return 14;
			case 'ravenstaff': return 9;
			case 'axe': return 11;
			case 'necklace': return 7;
			case 'crucifix': return 8;
			case 'grail': return 12;
			case 'chesspiece': return 9;
			case 'hammer': return 10;
			case 'horseshoe': return 9;
			case 'anchor': return 10;
			case 'belt': return 8;
			case 'crown': return 16;
			case 'shield': return 13;
			case 'horn': return 8;
			case 'helmet': return 8;
			case 'cane': return 8;
			// Norwegian goods
			case 'tools': return 9;
			case 'wadmal': return 13;
			case 'pan': return 8;
			case 'vikingsword': return 7;
			case 'sickle': return 8;
			case 'mail': return 12;
			case 'anvil': return 13;
			case 'antlers': return 8;
			case 'leather': return 12;
			// Yule goods
			case 'table': return 12;
			case 'sleigh': return 15;
			case 'skates': return 6;
			case 'skiis': return 9;
		}
		return 10000;
	}
	
	define({ onEnteringState, onLeavingState, onUpdateActionButtons, updateHuntButtons });
})();