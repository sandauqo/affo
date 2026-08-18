<?php

$machinestates = [
	// The initial state. Please do not modify.
	1 => [
		"name" => "gameSetup",
		"description" => "",
		"type" => "manager",
		"action" => "stGameSetup",
		"transitions" => ["" => 2],
	],

	2 => [
		"name" => "preGame",
		"description" => "",
		"type" => "game",
		"action" => "stPreGame",
		"transitions" => ["bestOfTwoStartingOcc" => 3, "harvestSetup" => 8, "newViking" => 10],
	],

	3 => [
		"name" => "bestOfTwoStartingOcc",
		"description" => clienttranslate('Other players must pick one occupation to keep'),
		"descriptionmyturn" => clienttranslate('${you} must pick one occupation to keep'),
		"type" => "multipleactiveplayer",
		"action" => "stBestOfTwoStartingOcc",
		"args" => "argBestOfTwoStartingOcc",
		"possibleactions" => ["pickOneOccupationToKeep"],
		"transitions" => ["nextPhase" => 2],
	],
	
	8 => [
		"name" => "drawPersonalResource", //Harvest mini exp
		"description" => clienttranslate('${actplayer} must select a personal resource'),
		"descriptionmyturn" => clienttranslate('${you} must select your personal resource'),
		"type" => "activeplayer",
		"action" => "stDrawPersonalResource",
		"args" => "argDrawPersonalResource",
		"possibleactions" => ["drawPersonalResource"],
		"transitions" => ["checkNextPlayer" => 9],
	],

	9 => [
		"name" => "nextPlayerDrawPersonalResource",
		"type" => "game",
		"action" => "stNextPlayerDrawPersonalResource",
		"transitions" => ["nextPlayer" => 8, "nextPhase" => 10, "drawSecondResource" => 11],
	],

	11 => [
		"name" => "drawPersonalResource2", //Harvest mini exp short game
		"description" => clienttranslate('${actplayer} must select a second personal resource'),
		"descriptionmyturn" => clienttranslate('${you} must select your second personal resource'),
		"type" => "activeplayer",
		"action" => "stDrawPersonalResource2",
		"args" => "argDrawPersonalResource",
		"possibleactions" => ["drawPersonalResource"],
		"transitions" => ["checkNextPlayer" => 12],
	],

	12 => [
		"name" => "nextPlayerDrawPersonalResource2",
		"type" => "game",
		"action" => "stNextPlayerDrawPersonalResource2",
		"transitions" => ["nextPlayer" => 11, "norwegiansSetup" => 13, "nonNorwegiansSetup" => 15],
	],

	13 => [
		"name" => "buildArtisanShed",
		"description" => clienttranslate('${actplayer} must build an artisan shed'),
		"descriptionmyturn" => clienttranslate('${you} must build an artisan shed'),
		"type" => "activeplayer",
		"args" => "argBuildArtisanShed",
		"possibleactions" => ["buildArtisanShed"],
		"transitions" => ["checkNextPlayer" => 14],
	],

	14 => [
		"name" => "nextPlayerBuildArtisanShed",
		"type" => "game",
		"action" => "stNextPlayerBuildArtisanShed",
		"transitions" => ["nextPlayer" => 13, "nextPhase" => 10],
	],

	15 => [
		"name" => "drawTwoBrown",
		"type" => "game",
		"action" => "stDrawTwoBrown",
		"transitions" => ["discardOneBrown" => 16],
	],

	16 => [
		"name" => "discardOneBrown",
		"description" => clienttranslate('Other players must pick one occupation to keep'),
		"descriptionmyturn" => clienttranslate('${you} must pick one occupation to keep'),
		"type" => "multipleactiveplayer",
		"possibleactions" => ["pickOneOccupationToKeep"],
		"transitions" => ["nextPlayer" => 14, "nextPhase" => 10],
	],

	10 => [
		"name" => "newViking",
		"type" => "game",
		"action" => "stNewViking",
		"updateGameProgression" => true,
		"transitions" => ["" => 20],
	],

	20 => [
		"name" => "harvestGoods",
		"type" => "game",
		"action" => "stHarvestGoods",
		"args" => "argHarvestGoods",
		"transitions" => ["" => 51],
	],
	
	30 => [
		"name" => "turnExplorationBoards",
		"type" => "game",
		"action" => "stTurnExplorationBoards",
		"transitions" => [ "" => 40 ],
	],
	
	40 => [
		"name" => "drawWeapon",
		"type" => "game",
		"action" => "stDrawWeapon",
		"transitions" => [ "" => 51 ],
	],
	
	48 => [
		"name" => "activatePreGameOccupation",
		"description" => clienttranslate('${actplayer} may play an occupation'),
		"descriptionmyturn" => clienttranslate('${you} may play an occupation'),
		"type" => "activeplayer",
		"possibleactions" => ["playOccupation", "pass"],
		"transitions" => [ "done" => 51, "pass" => 50 ],
	],

	49 => [
		"name" => "setFirstPlayer",
		"type" => "game",
		"action" => "stSetFirstPlayer",
		"transitions" => [ "" => 50 ],
	],
	
	50 => [
		"name" => "pickAction",
		"description" => clienttranslate('${actplayer} must take an action'),
		"descriptionmyturn" => clienttranslate('${you} must take an action'),
		"type" => "activeplayer",
		"action" => "stPickAction",
		"possibleactions" => ["pass", "action", "vikingsNumber"],
		"transitions" => ["action" => 51, "pass" => 60, "vikingsNumber" => 52, "zombiepass" => 60, "command-playOccupation" => 1001, "tutor" => 50, 'activatePreGameOccupation' => 48],
	],
	
	51 => [
		"name" => "doCommand",
		"type" => "game",
		"action" => "stDoCommand",
		"transitions" => [
			"finished" => 59,
			"finished_harvest" => 30,
			"finished_postactions" => 70,
			"finished_bonus" => 120,
			"finished_drawweapon" => 49,
			"finished_breeding" => 100,
			"finished_feast" => 110,
			"finished_feastGameEnd" => 150,
			
			"pickAction" => 50,
			"next" => 51,
			"command-playOccupation" => 1001,
			"command-pickmodal" => 1002,
			"command-exchange" => 1003,
			"command-forge" => 1004,
			"command-forgebuy" => 1005,
			"command-mountain" => 1006,
			"command-trade" => 1007,
			"command-upgrade" => 1008,
			"command-emigrate" => 1012,
			"command-swapwhalingboatknarr" => 1013,
			"command-clone" => 1014,
			"command-personalfeast" => 1015,
			"command-storeman" => 1016,
			"command-meatupgrade" => 1018,
			"command-inspector" => 1019,
			"command-hornturner" => 1021,
			"command-wharfowner" => 1023,
			"command-fineblacksmith" => 1024,
			"command-shipbuilder" => 1025,
			"command-dragonslayer" => 1026,
			"command-privatechef" => 1027,
			"command-fieldfarmer" => 1028,
			"command-earl" => 1029,
			"command-cowherd" => 1030,
			"command-bonecollector" => 1031,
			"command-breeder" => 1032,
			"command-shepherdboy" => 1033,
			"command-metalsmith" => 1034,
			"command-hidebuyer" => 1035,
			"command-preacher" => 1036,
			"command-hornblower" => 1037,
			"command-flip" => 1038,
			"command-sameupgrade" => 1039,
			"command-quartermaster" => 1040,
			"command-longshipbuilder" => 1041,
			"command-harborguard" => 1042,
			"command-spicemerchant" => 1043,
			"command-meatmerchant" => 1044,
			"command-sower" => 1045,
			"command-flaxbaker" => 1046,
			"command-disheartenedwarrior" => 1047,
			"command-ironsmith" => 1048,
			"command-emigratepassable" => 1049,
			"command-silkstitcher" => 1050,
			"command-laborer" => 1051,
			"command-payupgrade" => 1052,
			"command-meatcurer" => 1053,
			"command-snarespecialist" => 1054,
			"command-deerstalker" => 1055,
			"command-lancebearer" => 1056,
			"command-armedfighter" => 1057,
			"command-spicetrader" => 1058,
			"command-sponsor" => 1059,
			"command-beforeorafter-draw" => 1060,
			"command-beforeorafter-play" => 1061,
			"command-hornblower2" => 1062,
			"command-hunt" => 1063,
			"command-peacemaker" => 1064,
			"command-slowpoke" => 1065,
			"command-bosporustraveller" => 1067,
			"command-swordfighter" => 1068,
			"command-follower" => 1069,
			"command-clerk" => 1070,
			"command-nwupgrade" => 1071,
			"command-latecomer" => 1072,
			"command-preceptor" => 1073,
			"command-berserker" => 1074,
			"command-oreboatman" => 1075,
			"command-fishcook" => 1076,
			"command-soberman" => 1077,
			"command-explore" => 1078,
			"command-explorepassable" => 1079,
			"command-buylongshipfifthcol" => 1080,
			"command-slaughter" => 1081,
			"command-resources" => 1082,
			"command-forgeminor" => 1083,
			"command-emigrateminor" => 1084,
			"command-anytwoanimals" => 1085,
			"command-discardocc" => 1086,
			"command-coinsoroccs" => 1087,
			"command-furmerchant" => 1088, // used only for norwegians 114 action
			"command-armsdealer" => 1089,
			"command-oncetwiceupgrade" => 1090,
			"command-buywhalingboatfifthcol" => 1091,
			"command-emigrateminorpassable" => 1092,
			"command-takeImmediateAction" => 1093,
			"command-corkshedbonus" => 1094,
			"command-attack" => 1098,
			"command-attack-reward" => 1096,
			"command-attack-reward2" => 1097,
		],
	],
	
	52 => [
		"name" => "chooseVikingsNumber",
		"description" => clienttranslate('${actplayer} must choose a number of vikings to play'),
		"descriptionmyturn" => clienttranslate('${you} must choose a number of vikings to play'),
		"type" => "activeplayer",
		"possibleactions" => ["action", "undo"],
		"transitions" => ["action" => 51, "undo" => 50, "zombiepass" => 60],
	],

	59 => [
		"name" => "confirmTurn",
		"description" => clienttranslate('${actplayer} must confirm their turn'),
		"descriptionmyturn" => clienttranslate('${you} must confirm your turn'),
		"type" => "activeplayer",
		"action" => "stConfirmTurn",
		"args" => "argConfirmTurn",
		"possibleactions" => [ "affirm", "undo" ],
		"transitions" => ["affirm" => 60, "undo" => 50, "zombiepass" => 60],
	],
	
	60 => [
		"name" => "nextPlayer",
		"type" => "game",
		"action" => "stNextPlayer",
		"transitions" => ["nextPlayer" => 50, "nextPhase" => 51],
	],
	
	70 => [
		"name" => "determineStartPlayer",
		"type" => "game",
		"action" => "stDetermineStartPlayer",
		"transitions" => [ "" => 80 ],
	],
	
	80 => [
		"name" => "income-prepare",
		"type" => "game",
		"action" => "stSetAllMultiactive",
		"transitions" => [ "" => 81 ],
	],
		
	81 => [
		"name" => "income-placegoods",
		"description" => clienttranslate('Other players may place goods on their boards'),
		"descriptionmyturn" => clienttranslate('${you} may place goods on your boards before receiving income'),
		"action" => "stPreIncome",
		"type" => "multipleactiveplayer",
		"possibleactions" => [ "pass" ],
		"transitions" => [ "pass" => 83 ],
	],
	
	83 => [
		"name" => "income",
		"type" => "game",
		"action" => "stIncome",
		"transitions" => [ "" => 90 ],
	],
	
	90 => [
		"name" => "breeding",
		"type" => "game",
		"action" => "stBreeding",
		"transitions" => [ "" => 51 ],
	],
	
	100 => [
		"name" => "feast-prepare",
		"type" => "game",
		"action" => "stSetAllMultiactive",
		"transitions" => [ "" => 101 ],
	],
	
	101 => [
		"name" => "feast-placegoods",
		"description" => clienttranslate('Other players may place goods on their feast boards'),
		"descriptionmyturn" => clienttranslate('${you} may place goods on your feast board'),
		"action" => "stPreFeast",
		"type" => "multipleactiveplayer",
		"possibleactions" => [ "pass" ],
		"transitions" => [ "pass" => 102, ],
	],
	
	102 => [
		"name" => "feast",
		"type" => "game",
		"action" => "stFeast",
		"transitions" => [ "done" => 51 ],
	],
	
	110 => [
		"name" => "bonus-prepare",
		"type" => "game",
		"action" => "stSetAllMultiactive",
		"transitions" => [ "" => 111 ],
	],
		
	111 => [
		"name" => "bonus-placegoods",
		"description" => clienttranslate('Other players may place goods on their boards'),
		"descriptionmyturn" => clienttranslate('${you} may place goods on your boards before receiving bonuses'),
		"type" => "multipleactiveplayer",
		"possibleactions" => [ "pass" ],
		"transitions" => [ "pass" => 112 ],
	],
	
	112 => [
		"name" => "bonus",
		"type" => "game",
		"action" => "stBonus",
		"transitions" => [ "" => 51 ],
	],
	
	120 => [
		"name" => "updateMountainStrips",
		"type" => "game",
		"action" => "stUpdateMountainStrips",
		"transitions" => [ "" => 130 ],
	],
	
	130 => [
		"name" => "removePlacedVikings",
		"type" => "game",
		"action" => "stRemovePlacedVikings",
		"transitions" => [ "" => 140 ],
	],
	
	140 => [
		"name" => "nextRound",
		"type" => "game",
		"action" => "stNextRound",
		"transitions" => [ "" => 10 ],
	],
	
	150 => [
		"name" => "gameEnd-prepare",
		"type" => "game",
		"action" => "stSetAllMultiactive",
		"transitions" => [ "" => 151 ],
	],
		
	151 => [
		"name" => "gameEnd-placegoods",
		"description" => clienttranslate('Other players may place goods on their boards'),
		"descriptionmyturn" => clienttranslate('${you} may place goods on your boards before the end of the game'),
		"type" => "multipleactiveplayer",
		"possibleactions" => [ "pass" ],
		"transitions" => [ "pass" => 152 ],
	],
	
	152 => [
		"name" => "gameEnd-scoring",
		"type" => "game",
		"action" => "stGameEndScoring",
		"transitions" => [ "" => 99 ],
	],
	
	1001 => [
		"name" => "command-playOccupation",
		"description" => clienttranslate('${actplayer} may play an occupation'),
		"descriptionmyturn" => clienttranslate('${you} may play an occupation'),
		"type" => "activeplayer",
		"action" => "stPlayOccupation",
		"possibleactions" => ["pass", "playOccupation", "undo", "takeImmediateAction"],
		"transitions" => ["done" => 51, "pass" => 51, "zombiepass" => 51, "undo" => 50, "takeImmediateAction" => 1093],
	],
	
	1002 => [
		"name" => "command-pickmodal",
		"description" => clienttranslate('${actplayer} must take an action'),
		"descriptionmyturn" => clienttranslate('${you} must choose which action to perform'),
		"type" => "activeplayer",
		"action" => "stPickModal",
		"args" => "argPickModal",
		"possibleactions" => ["selectMode", "undo", "pass", "takeImmediateAction"],
		"transitions" => ["done" => 51, "pass" => 51, "zombiepass" => 51, "undo" => 50, "takeImmediateAction" => 1093],
	],
	
	1003 => [
		"name" => "command-exchange",
		"description" => clienttranslate('${actplayer} may make an exchange'),
		"descriptionmyturn" => clienttranslate('${you} may make an exchange'),
		"type" => "activeplayer",
		"action" => "stExchange",
		"args" => "argExchange",
		"possibleactions" => ["pass", "affirm", "undo"],
		"transitions" => ["affirm" => 51, "pass" => 51, "zombiepass" => 51, "undo" => 50],
	],
	
	1004 => [
		"name" => "command-forge",
		"description" => clienttranslate('${actplayer} must take a special tile from the forge'),
		"descriptionmyturn" => clienttranslate('${you} must take a special tile from the forge'),
		"type" => "activeplayer",
		"possibleactions" => ["pass", "selectSpecialTile", "undo"],
		"transitions" => ["done" => 51, "pass" => 51, "zombiepass" => 51, "undo" => 50],
	],
	
	1005 => [
		"name" => "command-forgebuy",
		"description" => clienttranslate('${actplayer} may buy a special tile from the oval board'),
		"descriptionmyturn" => clienttranslate('${you} may buy a special tile from the oval board'),
		"type" => "activeplayer",
		"action" => "stForge",
		"possibleactions" => ["pass", "selectSpecialTile", "undo"],
		"transitions" => ["done" => 51, "pass" => 51, "zombiepass" => 51, "undo" => 50],
	],
	
	1006 => [
		"name" => "command-mountain",
		"description" => clienttranslate('${actplayer} must take ${num} resource(s) from a mountain strip'),
		"descriptionmyturn" => clienttranslate('${you} must take ${num} resource(s) from a mountain strip'),
		"type" => "activeplayer",
		"action" => "stMountain",
		"args" => "argMountain",
		"possibleactions" => ["selectStrip", "undo"],
		"transitions" => ["done" => 51, "pass" => 51, "zombiepass" => 51, "undo" => 50],
	],
	
	1007 => [
		"name" => "command-trade",
		"description" => clienttranslate('${actplayer} may choose green goods to upgrade'),
		"descriptionmyturn" => clienttranslate('${you} may choose green goods to upgrade'),
		"type" => "activeplayer",
		"action" => "stUpgrade",
		"possibleactions" => ["selectGoods", "undo"],
		"transitions" => ["done" => 51, "pass" => 51, "zombiepass" => 51, "undo" => 50],
	],
	
	1008 => [
		"name" => "command-upgrade",
		"description" => clienttranslate('${actplayer} may choose ${num_tiles} good(s) to upgrade ${num_steps} time(s) each'),
		"descriptionmyturn" => clienttranslate('${you} may choose ${num_tiles} good(s) to upgrade ${num_steps} time(s) each'),
		"type" => "activeplayer",
		"action" => "stUpgrade",
		"args" => "argUpgrade",
		"possibleactions" => ["selectGoods", "selectMode", "undo"],
		"transitions" => ["done" => 51, "pass" => 51, "zombiepass" => 51, "undo" => 50],
	],
	
	1018 => [
		"name" => "command-meatupgrade",
		"description" => clienttranslate('${actplayer} may choose 1 meat good to upgrade'),
		"descriptionmyturn" => clienttranslate('${you} may choose 1 meat good to upgrade'),
		"type" => "activeplayer",
		"action" => "stMeatUpgrade",
		"args" => "argUpgrade",
		"possibleactions" => ["selectGoods", "undo"],
		"transitions" => ["done" => 51, "pass" => 51, "zombiepass" => 51, "undo" => 50],
	],
	
	1038 => [
		"name" => "command-flip",
		"description" => clienttranslate('${actplayer} may choose up to ${num_tiles} good(s) to flip'),
		"descriptionmyturn" => clienttranslate('${you} may choose up to ${num_tiles} good(s) to flip'),
		"type" => "activeplayer",
		"action" => "stUpgrade",
		"args" => "argUpgrade",
		"possibleactions" => ["selectGoods", "undo"],
		"transitions" => ["done" => 51, "pass" => 51, "zombiepass" => 51, "undo" => 50],
	],
	
	1039 => [
		"name" => "command-sameupgrade",
		"description" => clienttranslate('${actplayer} may choose ${num_tiles} good(s) to upgrade ${num_steps} time(s) each'),
		"descriptionmyturn" => clienttranslate('${you} may choose ${num_tiles} good(s) to upgrade ${num_steps} time(s) each'),
		"type" => "activeplayer",
		"action" => "stUpgrade",
		"args" => "argUpgrade",
		"possibleactions" => ["selectGoods", "undo"],
		"transitions" => ["done" => 51, "pass" => 51, "zombiepass" => 51, "undo" => 50],
	],
	
	1063 => [
		"name" => "command-prehunt",
		"description" => clienttranslate('${actplayer} must perform ${hunt_type_desc}'),
		"descriptionmyturn" => clienttranslate('${you} must perform ${hunt_type_desc}'),
		"type" => "activeplayer",
		"args" => "argHunt",
		"possibleactions" => ["affirm", "undo"],
		"transitions" => ["undo" => 50, "affirm" => 1009, "zombiepass" => 51],
	],
	
	1009 => [
		"name" => "command-hunt-roll",
		"description" => '',
		"descriptionmyturn" => '',
		"type" => "activeplayer",
		"action" => "stHuntRoll",
		// zombie pass?
		"transitions" => ["" => 1010],
	],
	
	1010 => [
		"name" => "command-hunt",
		"description" => clienttranslate('${actplayer} must perform ${hunt_type_desc}'),
		"descriptionmyturn" => clienttranslate('${you} must perform ${hunt_type_desc}'),
		"type" => "activeplayer",
		"args" => "argHunt",
		"possibleactions" => ["reroll", "declareSuccess", "declareFailure", "selectSpecialTile"],
		"transitions" => ["done" => 51, "declareFailure" => 51, "raid" => 1011, "reroll" => 1009, "zombiepass" => 51],
	],
	
	1011 => [
		"name" => "command-hunt-reward",
		"description" => clienttranslate('${actplayer} must pick a reward'),
		"descriptionmyturn" => clienttranslate('${you} must pick a reward (strength: ${raid_value})'),
		"type" => "activeplayer",
		"args" => "argHuntReward",
		"possibleactions" => ["selectSpecialTile", "goBack"],
		"transitions" => ["goBack" => 1009, "done" => 51, "raider" => 1066, "zombiepass" => 51],
	],
	
	1066 => [
		"name" => "command-hunt-reward2",
		"description" => clienttranslate('${actplayer} may pick a second reward'),
		"descriptionmyturn" => clienttranslate('${you} may pick a second reward (strength: ${raid_value})'),
		"type" => "activeplayer",
		"args" => "argHuntReward",
		"possibleactions" => ["selectSpecialTile", "pass"],
		"transitions" => ["done" => 51, "pass" => 51, "zombiepass" => 51],
	],
	
	1098 => [
		"name" => "command-preattack",
		"description" => clienttranslate('${actplayer} must perform ${hunt_type_desc}'),
		"descriptionmyturn" => clienttranslate('${you} must perform ${hunt_type_desc}'),
		"type" => "activeplayer",
		"args" => "argHunt",
		"possibleactions" => ["affirm", "undo"],
		"transitions" => ["undo" => 50, "affirm" => 1099, "zombiepass" => 51],
	],
	
	1099 => [
		"name" => "command-attack-roll",
		"description" => '',
		"descriptionmyturn" => '',
		"type" => "activeplayer",
		"action" => "stHuntRoll",
		// zombie pass?
		"transitions" => ["" => 1095],
	],

	1095 => [
		"name" => "command-attack",
		"description" => clienttranslate('${actplayer} must perform ${hunt_type_desc}'),
		"descriptionmyturn" => clienttranslate('${you} must perform ${hunt_type_desc}'),
		"type" => "activeplayer",
		"args" => "argHunt",
		"possibleactions" => ["reroll", "declareFailure", "declareSuccess"],
		"transitions" => ["done" => 51, "declareFailure" => 51, "reroll" => 1099, "zombiepass" => 51, "raider" => 51],
	],

	1096 => [
		"name" => "command-attack-reward",
		"description" => clienttranslate('${actplayer} must pick a reward'),
		"descriptionmyturn" => clienttranslate('${you} must pick a reward (strength: ${raid_value})'),
		"type" => "activeplayer",
		"args" => "argHuntReward",
		"possibleactions" => ["selectAttackReward"],
		"transitions" => [ "done" => 51, "raider" => 1066, "zombiepass" => 51],
	],
	
	1097 => [
		"name" => "command-attack-reward2",
		"description" => clienttranslate('${actplayer} may pick a second reward'),
		"descriptionmyturn" => clienttranslate('${you} may pick a second reward (strength: ${raid_value})'),
		"type" => "activeplayer",
		"args" => "argHuntReward",
		"possibleactions" => ["selectSpecialTile", "pass"],
		"transitions" => ["done" => 51, "pass" => 51, "zombiepass" => 51],
	],

	1012 => [
		"name" => "command-emigrate",
		"description" => clienttranslate('${actplayer} must pick a boat to emigrate'),
		"descriptionmyturn" => clienttranslate('${you} must pick a boat to emigrate'),
		"type" => "activeplayer",
		"possibleactions" => ["selectShip", "undo"],
		"transitions" => ["done" => 51, "zombiepass" => 51, "undo" => 50],
	],
	
	1013 => [
		"name" => "command-swapwhalingboatknarr",
		"description" => clienttranslate('${actplayer} may swap a whaling boat for a knarr'),
		"descriptionmyturn" => clienttranslate('${you} may swap a whaling boat for a knarr'),
		"type" => "activeplayer",
		"action" => "stSwapWhalingBoatKnarr",
		"possibleactions" => ["selectShip", "pass", "undo"],
		"transitions" => ["done" => 51, "pass" => 51, "zombiepass" => 51, "undo" => 50],
	],
	
	1014 => [
		"name" => "command-clone",
		"description" => clienttranslate('${actplayer} must chose an action to copy'),
		"descriptionmyturn" => clienttranslate('${you} must chose an action to copy'),
		"type" => "activeplayer",
		"possibleactions" => ["action", "undo"],
		"transitions" => ["action" => 51, "zombiepass" => 51, "undo" => 50],
	],
	
	1015 => [
		"name" => "command-personalfeast",
		"description" => clienttranslate('${actplayer} may place goods on their feast board'),
		"descriptionmyturn" => clienttranslate('${you} may place goods on your feast board'),
		"type" => "activeplayer",
		"possibleactions" => [ "pass", "undo" ],
		"transitions" => [ "pass" => 51, "zombiepass" => 51, "undo" => 50 ],
	],
	
	1016 => [
		"name" => "command-storeman",
		"description" => clienttranslate('${actplayer} must take a good'),
		"descriptionmyturn" => clienttranslate('${you} must take a good'),
		"type" => "activeplayer",
		"action" => "stStoreman",
		"possibleactions" => [ "selectGoods", "undo" ],
		"transitions" => [ "pass" => 51, "done" => 51, "zombiepass" => 51, "undo" => 50 ],
	],
	
	1019 => [
		"name" => "command-inspector",
		"description" => clienttranslate('${actplayer} may return a viking to the Thing space'),
		"descriptionmyturn" => clienttranslate('${you} may return a viking to the Thing space'),
		"type" => "activeplayer",
		"possibleactions" => [ "action", "pass", "undo" ],
		"transitions" => [ "pass" => 51, "another" => 1020, "zombiepass" => 51, "undo" => 50 ],
	],
	
	1020 => [
		"name" => "command-inspector2",
		"description" => clienttranslate('${actplayer} may return a second viking to their Thing space'),
		"descriptionmyturn" => clienttranslate('${you} may return a second viking to their Thing space'),
		"type" => "activeplayer",
		"possibleactions" => [ "affirm", "pass", "undo" ],
		"transitions" => [ "pass" => 51, "affirm" => 51, "zombiepass" => 51, "undo" => 50 ],
	],
	
	1021 => [
		"name" => "command-hornturner",
		"description" => clienttranslate('${actplayer} may make an exchange'),
		"descriptionmyturn" => clienttranslate('${you} may make an exchange'),
		"type" => "activeplayer",
		"action" => "stCheckSkip",
		"possibleactions" => [ "affirm", "pass", "undo" ],
		"transitions" => [ "pass" => 1022, "affirm" => 1022, "zombiepass" => 51, "undo" => 50 ],
	],
	
	1022 => [
		"name" => "command-hornturner2",
		"description" => clienttranslate('${actplayer} may make an exchange'),
		"descriptionmyturn" => clienttranslate('${you} may make an exchange'),
		"type" => "activeplayer",
		"action" => "stCheckSkip",
		"possibleactions" => [ "affirm", "pass", "undo" ],
		"transitions" => [ "pass" => 51, "affirm" => 51, "zombiepass" => 51, "undo" => 50 ],
	],
	
	1023 => [
		"name" => "command-wharfowner",
		"description" => clienttranslate('${actplayer} may make an exchange'),
		"descriptionmyturn" => clienttranslate('${you} may make an exchange'),
		"type" => "activeplayer",
		"action" => "stCheckSkip",
		"possibleactions" => [ "selectShip", "pass", "undo" ],
		"transitions" => [ "done" => 51, "pass" => 51, "zombiepass" => 51, "undo" => 50 ],
	],
	
	1024 => [
		"name" => "command-fineblacksmith",
		"description" => clienttranslate('${actplayer} may make an exchange'),
		"descriptionmyturn" => clienttranslate('${you} may make an exchange'),
		"type" => "activeplayer",
		"action" => "stCheckSkip",
		"possibleactions" => [ "selectSpecialTile", "pass", "undo" ],
		"transitions" => [ "pass" => 51, "done" => 51, "zombiepass" => 51, "undo" => 50 ],
	],
	
	1025 => [
		"name" => "command-shipbuilder",
		"description" => clienttranslate('${actplayer} may make an exchange'),
		"descriptionmyturn" => clienttranslate('${you} may make an exchange'),
		"type" => "activeplayer",
		"action" => "stCheckSkip",
		"possibleactions" => [ "selectShip", "pass", "undo" ],
		"transitions" => [ "done" => 51, "pass" => 51, "zombiepass" => 51, "undo" => 50 ],
	],
	
	1026 => [
		"name" => "command-dragonslayer",
		"description" => clienttranslate('${actplayer} may make an exchange'),
		"descriptionmyturn" => clienttranslate('${you} may make an exchange'),
		"type" => "activeplayer",
		"action" => "stCheckSkip",
		"possibleactions" => [ "affirm", "pass", "undo" ],
		"transitions" => [ "pass" => 51, "affirm" => 51, "zombiepass" => 51, "undo" => 50 ],
	],
	
	1027 => [
		"name" => "command-privatechef",
		"description" => clienttranslate('${actplayer} may make an exchange'),
		"descriptionmyturn" => clienttranslate('${you} may make an exchange'),
		"type" => "activeplayer",
		"action" => "stCheckSkip",
		"possibleactions" => [ "affirm", "pass", "undo" ],
		"transitions" => [ "pass" => 51, "affirm" => 51, "zombiepass" => 51, "undo" => 50 ],
	],
	
	1028 => [
		"name" => "command-fieldfarmer",
		"description" => clienttranslate('${actplayer} may upgrade 1 orange good for 1 silver'),
		"descriptionmyturn" => clienttranslate('${you} may upgrade 1 orange good for 1 silver'),
		"type" => "activeplayer",
		"action" => "stCheckSkip",
		"possibleactions" => [ "selectGoods", "pass", "undo" ],
		"transitions" => [ "pass" => 51, "done" => 51, "zombiepass" => 51, "undo" => 50 ],
	],
	
	1029 => [
		"name" => "command-earl",
		"description" => clienttranslate('${actplayer} may buy a stonehouse'),
		"descriptionmyturn" => clienttranslate('${you} may buy a stonehouse'),
		"type" => "activeplayer",
		"action" => "stCheckSkip",
		"possibleactions" => [ "affirm", "pass", "undo" ],
		"transitions" => [ "pass" => 51, "affirm" => 51, "zombiepass" => 51, "undo" => 50 ],
	],
	
	1030 => [
		"name" => "command-cowherd",
		"description" => clienttranslate('${actplayer} may make an exchange'),
		"descriptionmyturn" => clienttranslate('${you} may make an exchange'),
		"type" => "activeplayer",
		"action" => "stCheckSkip",
		"possibleactions" => [ "affirm", "pass", "undo" ],
		"transitions" => [ "pass" => 51, "affirm" => 51, "zombiepass" => 51, "undo" => 50 ],
	],
	
	1031 => [
		"name" => "command-bonecollector",
		"description" => clienttranslate('${actplayer} may make an exchange'),
		"descriptionmyturn" => clienttranslate('${you} may make an exchange'),
		"type" => "activeplayer",
		"action" => "stCheckSkip",
		"possibleactions" => [ "affirm", "pass", "undo" ],
		"transitions" => [ "pass" => 51, "affirm" => 51, "zombiepass" => 51, "undo" => 50 ],
	],
	
	1032 => [
		"name" => "command-breeder",
		"description" => clienttranslate('${actplayer} may make an exchange'),
		"descriptionmyturn" => clienttranslate('${you} may make an exchange'),
		"type" => "activeplayer",
		"action" => "stCheckSkip",
		"possibleactions" => [ "affirm", "pass", "undo" ],
		"transitions" => [ "pass" => 51, "affirm" => 51, "zombiepass" => 51, "undo" => 50 ],
	],
	
	1033 => [
		"name" => "command-shepherdboy",
		"description" => clienttranslate('${actplayer} may make an exchange'),
		"descriptionmyturn" => clienttranslate('${you} may make an exchange'),
		"type" => "activeplayer",
		"action" => "stCheckSkip",
		"possibleactions" => [ "affirm", "pass", "undo" ],
		"transitions" => [ "pass" => 51, "affirm" => 51, "zombiepass" => 51, "undo" => 50 ],
	],
	
	1034 => [
		"name" => "command-metalsmith",
		"description" => clienttranslate('${actplayer} may make an exchange'),
		"descriptionmyturn" => clienttranslate('${you} may make an exchange'),
		"type" => "activeplayer",
		"possibleactions" => [ "selectMode", "pass", "undo" ],
		"transitions" => [ "pass" => 51, "done" => 51, "zombiepass" => 51, "undo" => 50 ],
	],
	
	1035 => [
		"name" => "command-hidebuyer",
		"description" => clienttranslate('${actplayer} may make an exchange'),
		"descriptionmyturn" => clienttranslate('${you} may make an exchange'),
		"type" => "activeplayer",
		"action" => "stCheckSkip",
		"possibleactions" => [ "selectMode", "pass", "undo" ],
		"transitions" => [ "pass" => 51, "done" => 51, "zombiepass" => 51, "undo" => 50 ],
	],
	
	1036 => [
		"name" => "command-preacher",
		"description" => clienttranslate('${actplayer} may make an exchange'),
		"descriptionmyturn" => clienttranslate('${you} may make an exchange'),
		"type" => "activeplayer",
		"possibleactions" => [ "selectMode", "pass", "undo" ],
		"transitions" => [ "pass" => 51, "done" => 51, "zombiepass" => 51, "undo" => 50 ],
	],
	
	1037 => [
		"name" => "command-hornblower",
		"description" => clienttranslate('${actplayer} may make an exchange'),
		"descriptionmyturn" => clienttranslate('${you} may make an exchange'),
		"type" => "activeplayer",
		"action" => "stCheckSkip",
		"possibleactions" => [ "affirm", "pass", "undo" ],
		"transitions" => [ "pass" => 51, "affirm" => 51, "zombiepass" => 51, "undo" => 50 ],
	],
	
	1062 => [
		"name" => "command-hornblower2",
		"description" => clienttranslate('${actplayer} may make an exchange'),
		"descriptionmyturn" => clienttranslate('${you} may make an exchange'),
		"type" => "activeplayer",
		"action" => "stCheckSkip",
		"possibleactions" => [ "affirm", "pass", "undo" ],
		"transitions" => [ "pass" => 51, "affirm" => 51, "zombiepass" => 51, "undo" => 50 ],
	],
	
	
	1040 => [
		"name" => "command-quartermaster",
		"description" => clienttranslate('${actplayer} may buy a longship'),
		"descriptionmyturn" => clienttranslate('${you} may buy a longship'),
		"type" => "activeplayer",
		"action" => "stCheckSkip",
		"possibleactions" => [ "affirm", "pass", "undo" ],
		"transitions" => [ "pass" => 51, "affirm" => 51, "zombiepass" => 51, "undo" => 50 ],
	],
	
	1041 => [
		"name" => "command-longshipbuilder",
		"description" => clienttranslate('${actplayer} may buy a longship'),
		"descriptionmyturn" => clienttranslate('${you} may buy a longship'),
		"type" => "activeplayer",
		"action" => "stCheckSkip",
		"possibleactions" => [ "affirm", "pass", "undo" ],
		"transitions" => [ "pass" => 51, "affirm" => 51, "zombiepass" => 51, "undo" => 50 ],
	],
	
	1042 => [
		"name" => "command-harborguard",
		"description" => clienttranslate('${actplayer} may pick a boat to emigrate'),
		"descriptionmyturn" => clienttranslate('${you} may pick a boat to emigrate'),
		"type" => "activeplayer",
		"action" => "stCheckSkip",
		"possibleactions" => [ "selectShip", "pass", "undo" ],
		"transitions" => [ "pass" => 51, "done" => 51, "zombiepass" => 51, "undo" => 50 ],
	],
	
	1043 => [
		"name" => "command-spicemerchant",
		"description" => clienttranslate('${actplayer} may make an exchange'),
		"descriptionmyturn" => clienttranslate('${you} may make an exchange'),
		"type" => "activeplayer",
		"action" => "stCheckSkip",
		"possibleactions" => [ "affirm", "pass", "undo" ],
		"transitions" => [ "affirm" => 51, "pass" => 51, "zombiepass" => 51, "undo" => 50 ],
	],
	
	1044 => [
		"name" => "command-meatmerchant",
		"description" => clienttranslate('${actplayer} may make an exchange'),
		"descriptionmyturn" => clienttranslate('${you} may make an exchange'),
		"type" => "activeplayer",
		"action" => "stCheckSkip",
		"possibleactions" => [ "selectMode", "pass", "undo" ],
		"transitions" => [ "done" => 51, "pass" => 51, "zombiepass" => 51, "undo" => 50 ],
	],
	
	1045 => [
		"name" => "command-sower",
		"description" => clienttranslate('${actplayer} may make an exchange'),
		"descriptionmyturn" => clienttranslate('${you} may make an exchange'),
		"type" => "activeplayer",
		"possibleactions" => [ "selectMode", "affirm", "pass", "undo" ],
		"transitions" => [ "done" => 51, "affirm" => 51, "pass" => 51, "zombiepass" => 51, "undo" => 50 ],
	],
	
	1046 => [
		"name" => "command-flaxbaker",
		"description" => clienttranslate('${actplayer} may make an exchange'),
		"descriptionmyturn" => clienttranslate('${you} may make an exchange'),
		"type" => "activeplayer",
		"action" => "stCheckSkip",
		"possibleactions" => [ "affirm", "pass", "undo" ],
		"transitions" => [ "affirm" => 51, "pass" => 51, "zombiepass" => 51, "undo" => 50 ],
	],
	
	1047 => [
		"name" => "command-disheartenedwarrior",
		"description" => clienttranslate('${actplayer} must take a good'),
		"descriptionmyturn" => clienttranslate('${you} must take a good'),
		"type" => "activeplayer",
		"possibleactions" => [ "selectMode", "undo" ],
		"transitions" => [ "pass" => 51, "done" => 51, "zombiepass" => 51, "undo" => 50 ],
	],
	
	1048 => [
		"name" => "command-ironsmith",
		"description" => clienttranslate('${actplayer} may return a viking to the Thing space'),
		"descriptionmyturn" => clienttranslate('${you} may return a viking to the Thing space'),
		"type" => "activeplayer",
		"action" => "stCheckSkip",
		"possibleactions" => [ "affirm", "pass", "undo" ],
		"transitions" => [ "pass" => 51, "affirm" => 51, "zombiepass" => 51, "undo" => 50 ],
	],
	
	1049 => [
		"name" => "command-emigratepassable",
		"description" => clienttranslate('${actplayer} may pick a boat to emigrate'),
		"descriptionmyturn" => clienttranslate('${you} may pick a boat to emigrate'),
		"type" => "activeplayer",
		"action" => "stCheckSkip",
		"possibleactions" => ["selectShip", "pass", "undo"],
		"transitions" => ["done" => 51, "pass" => 51, "zombiepass" => 51, "undo" => 50],
	],
	
	1050 => [
		"name" => "command-silkstitcher",
		"description" => clienttranslate('${actplayer} may make an exchange'),
		"descriptionmyturn" => clienttranslate('${you} may make an exchange'),
		"type" => "activeplayer",
		"action" => "stCheckSkip",
		"possibleactions" => [ "affirm", "pass", "undo" ],
		"transitions" => [ "affirm" => 51, "pass" => 51, "zombiepass" => 51, "undo" => 50 ],
	],
	
	1051 => [
		"name" => "command-laborer",
		"description" => clienttranslate('${actplayer} may make an exchange'),
		"descriptionmyturn" => clienttranslate('${you} may make an exchange'),
		"type" => "activeplayer",
		"action" => "stCheckSkip",
		"possibleactions" => [ "affirm", "pass", "undo" ],
		"transitions" => [ "affirm" => 51, "pass" => 51, "zombiepass" => 51, "undo" => 50 ],
	],
	
	1052 => [
		"name" => "command-payupgrade",
		"description" => clienttranslate('${actplayer} may choose ${num_tiles} good(s) to upgrade ${num_steps} time(s) each'),
		"descriptionmyturn" => clienttranslate('${you} may choose ${num_tiles} good(s) to upgrade ${num_steps} time(s) each'),
		"type" => "activeplayer",
		"action" => "stUpgrade",
		"args" => "argUpgrade",
		"possibleactions" => ["selectGoods", "undo", "pass"],
		"transitions" => ["done" => 51, "pass" => 51, "zombiepass" => 51, "undo" => 50],
	],
	
	1053 => [
		"name" => "command-meatcurer",
		"description" => clienttranslate('${actplayer} may make an exchange'),
		"descriptionmyturn" => clienttranslate('${you} may make an exchange'),
		"type" => "activeplayer",
		"action" => "stCheckSkip",
		"possibleactions" => [ "affirm", "pass", "undo" ],
		"transitions" => [ "pass" => 51, "affirm" => 51, "zombiepass" => 51, "undo" => 50 ],
	],

	1054 => [
		"name" => "command-snarespecialist",
		"description" => clienttranslate('${actplayer} may return a viking to the Thing space'),
		"descriptionmyturn" => clienttranslate('${you} may return a viking to the Thing space'),
		"type" => "activeplayer",
		"action" => "stCheckSkip",
		"possibleactions" => [ "affirm", "pass", "undo" ],
		"transitions" => [ "pass" => 51, "affirm" => 51, "zombiepass" => 51, "undo" => 50 ],
	],
	
	1055 => [
		"name" => "command-deerstalker",
		"description" => clienttranslate('${actplayer} may make an exchange'),
		"descriptionmyturn" => clienttranslate('${you} may make an exchange'),
		"type" => "activeplayer",
		"action" => "stCheckSkip",
		"possibleactions" => [ "affirm", "pass", "undo" ],
		"transitions" => [ "pass" => 51, "affirm" => 51, "zombiepass" => 51, "undo" => 50 ],
	],
	
	1056 => [
		"name" => "command-lancebearer",
		"description" => clienttranslate('${actplayer} must take a good'),
		"descriptionmyturn" => clienttranslate('${you} must take a good'),
		"type" => "activeplayer",
		"possibleactions" => [ "selectMode", "pass", "undo" ],
		"transitions" => [ "pass" => 51, "done" => 51, "zombiepass" => 51, "undo" => 50 ],
	],
	
	1057 => [
		"name" => "command-armedfighter",
		"description" => clienttranslate('${actplayer} may take ore or stone from a mountain strip'),
		"descriptionmyturn" => clienttranslate('${you} may take ore or stone from a mountain strip'),
		"type" => "activeplayer",
		"action" => "stCheckSkip",
		"possibleactions" => ["selectStrip", "pass", "undo"],
		"transitions" => ["done" => 51, "pass" => 51, "zombiepass" => 51, "undo" => 50],
	],
	
	1058 => [
		"name" => "command-spicetrader",
		"description" => clienttranslate('${actplayer} may make an exchange'),
		"descriptionmyturn" => clienttranslate('${you} may make an exchange'),
		"type" => "activeplayer",
		"action" => "stCheckSkip",
		"possibleactions" => [ "affirm", "pass", "undo" ],
		"transitions" => [ "pass" => 51, "affirm" => 51, "zombiepass" => 51, "undo" => 50 ],
	],
	
	1059 => [
		"name" => "command-sponsor",
		"description" => clienttranslate('${actplayer} may make an exchange'),
		"descriptionmyturn" => clienttranslate('${you} may make an exchange'),
		"type" => "activeplayer",
		"action" => "stCheckSkip",
		"possibleactions" => [ "selectMode", "pass", "undo" ],
		"transitions" => [ "pass" => 51, "done" => 51, "zombiepass" => 51, "undo" => 50 ],
	],
	
	1060 => [
		"name" => "command-beforeorafter-draw",
		"description" => clienttranslate('${actplayer} must choose when to draw an occupation'),
		"descriptionmyturn" => clienttranslate('${you} must choose when to draw an occupation'),
		"type" => "activeplayer",
		"possibleactions" => [ "selectMode", "undo" ],
		"transitions" => [ "done" => 51, "zombiepass" => 51, "undo" => 50 ],
	],
	
	1061 => [
		"name" => "command-beforeorafter-play",
		"description" => clienttranslate('${actplayer} must choose when to play an occupation'),
		"descriptionmyturn" => clienttranslate('${you} must choose when to play an occupation'),
		"type" => "activeplayer",
		"action" => "stBeforeOrAfterPlay",
		"possibleactions" => [ "selectMode", "undo" ],
		"transitions" => [ "done" => 51, "zombiepass" => 51, "undo" => 50 ],
	],
	
	1064 => [
		"name" => "command-peacemaker",
		"description" => clienttranslate('${actplayer} may take a resource'),
		"descriptionmyturn" => clienttranslate('${you} may take a resource'),
		"type" => "activeplayer",
		"possibleactions" => [ "selectMode", "pass", "undo" ],
		"transitions" => [ "pass" => 51, "done" => 51, "zombiepass" => 51, "undo" => 50 ],
	],
	
	1065 => [
		"name" => "command-slowpoke",
		"description" => clienttranslate('${actplayer} may make an exchange'),
		"descriptionmyturn" => clienttranslate('${you} may make an exchange'),
		"type" => "activeplayer",
		"action" => "stCheckSkip",
		"possibleactions" => [ "selectMode", "pass", "undo" ],
		"transitions" => [ "pass" => 51, "done" => 51, "zombiepass" => 51, "undo" => 50 ],
	],
	
	1067 => [
		"name" => "command-bosporustraveller",
		"description" => clienttranslate('${actplayer} may make an exchange'),
		"descriptionmyturn" => clienttranslate('${you} may make an exchange'),
		"type" => "activeplayer",
		"action" => "stCheckSkip",
		"possibleactions" => [ "selectMode", "pass", "undo" ],
		"transitions" => [ "pass" => 51, "done" => 51, "zombiepass" => 51, "undo" => 50 ],
	],
	
	1068 => [
		"name" => "command-swordfighter",
		"description" => clienttranslate('${actplayer} may remove an ore to receive +2 on the roll'),
		"descriptionmyturn" => clienttranslate('${you} may remove an ore to receive +2 on the roll'),
		"type" => "activeplayer",
		"action" => "stCheckSkip",
		"possibleactions" => ["selectShip", "pass", "undo"],
		"transitions" => ["done" => 1063, "pass" => 1063, "zombiepass" => 51, "undo" => 50],
	],
	
	1069 => [
		"name" => "command-follower",
		"description" => clienttranslate('${actplayer} must chose an action to copy'),
		"descriptionmyturn" => clienttranslate('${you} must chose an action to copy'),
		"type" => "activeplayer",
		"possibleactions" => ["action", "pass", "undo"],
		"transitions" => ["action" => 51, "pass" => 51, "zombiepass" => 51, "undo" => 50],
	],
	
	1070 => [
		"name" => "command-clerk",
		"description" => clienttranslate('${actplayer} must take an action'),
		"descriptionmyturn" => clienttranslate('${you} must choose which action to perform'),
		"type" => "activeplayer",
		"action" => "stCheckSkip",
		"args" => "argPickModal",
		"possibleactions" => ["selectMode", "undo", "pass"],
		"transitions" => ["done" => 51, "pass" => 51, "zombiepass" => 51, "undo" => 50],
	],
	
	1071 => [
		"name" => "command-nwupgrade",
		"description" => clienttranslate('${actplayer} may choose ${num_tiles} good(s) to upgrade ${num_steps} time(s) each'),
		"descriptionmyturn" => clienttranslate('${you} may choose ${num_tiles} good(s) to upgrade ${num_steps} time(s) each'),
		"type" => "activeplayer",
		"action" => "stUpgrade",
		"args" => "argUpgrade",
		"possibleactions" => ["selectGoods", "selectMode", "undo"],
		"transitions" => ["done" => 51, "pass" => 51, "zombiepass" => 51, "undo" => 50],
	],
	
	1072 => [
		"name" => "command-latecomer",
		"description" => clienttranslate('${actplayer} may chose an action space to use with Latecomer for 1 silver'),
		"descriptionmyturn" => clienttranslate('${you} may chose an action space to use with Latecomer for 1 silver'),
		"type" => "activeplayer",
		"possibleactions" => ["action", "pass"],
		"transitions" => ["action" => 51, "pass" => 51, "zombiepass" => 51],
	],
	
	1073 => [
		"name" => "command-preceptor",
		"description" => clienttranslate('${actplayer} must choose to draw or play an occupation'),
		"descriptionmyturn" => clienttranslate('${you} must choose to draw or play an occupation'),
		"type" => "activeplayer",
		"action" => "stCheckSkip",
		"args" => "argPickModal",
		"possibleactions" => ["selectMode", "undo", "pass"],
		"transitions" => ["done" => 51, "pass" => 51, "zombiepass" => 51, "undo" => 50],
	],
	
	1074 => [
		"name" => "command-berserker",
		"description" => clienttranslate('${actplayer} may remove an ore to receive +3 on the roll'),
		"descriptionmyturn" => clienttranslate('${you} may remove an ore from the raiding longship to receive +3 on the roll'),
		"type" => "activeplayer",
		"action" => "stCheckSkip",
		"possibleactions" => ["selectShip", "pass", "undo"],
		"transitions" => ["done" => 1063, "pass" => 1063, "zombiepass" => 51, "undo" => 50],
	],
	
	1075 => [
		"name" => "command-oreboatman",
		"description" => clienttranslate('${actplayer} may place ${num} ore on their boards'),
		"descriptionmyturn" => clienttranslate('${you} may place ${num} ore on your boards'),
		"type" => "activeplayer",
		"action" => "stCheckSkip",
		"args" => "argOreBoatman",
		"possibleactions" => ["pass"],
		"transitions" => ["done" => 51, "pass" => 51, "zombiepass" => 51],
	],
	
	1076 => [
		"name" => "command-fishcook",
		"description" => clienttranslate('${actplayer} may place ${num} stock fish on their houses'),
		"descriptionmyturn" => clienttranslate('${you} may place ${num} stock fish on your houses'),
		"type" => "activeplayer",
		"action" => "stCheckSkip",
		"args" => "argFishCook",
		"possibleactions" => ["pass"],
		"transitions" => ["done" => 51, "pass" => 51, "zombiepass" => 51],
	],
	
	1077 => [
		"name" => "command-soberman",
		"description" => clienttranslate('${actplayer} may forgo placing mead to gain 1 silver'),
		"descriptionmyturn" => clienttranslate('${you} may forgo placing mead to gain 1 silver'),
		"type" => "activeplayer",
		"possibleactions" => ["pass", "affirm"],
		"transitions" => ["done" => 51, "affirm" => 51, "pass" => 51, "zombiepass" => 51],
	],
	
	1078 => [
		"name" => "command-explore",
		"description" => clienttranslate('${actplayer} must select an island to explore'),
		"descriptionmyturn" => clienttranslate('${you} must select an island to explore'),
		"type" => "activeplayer",
		"args" => "argExplore",
		"action" => "stExplore",
		"possibleactions" => ["selectIsland", "undo"],
		"transitions" => ["done" => 51, "pass" => 51, "zombiepass" => 51, "undo" => 50],
	],

	1079 => [
		"name" => "command-explorepassable",
		"description" => clienttranslate('${actplayer} must select an island to explore'),
		"descriptionmyturn" => clienttranslate('${you} must select an island to explore'),
		"type" => "activeplayer",
		"args" => "argExplore",
		"action" => "stExplore",
		"possibleactions" => ["selectIsland", "undo", "pass"],
		"transitions" => ["done" => 51, "pass" => 51, "zombiepass" => 51, "undo" => 50],
	],

	1080 => [
		"name" => "command-buylongshipfifthcol",
		"description" => clienttranslate('${actplayer} has to select a whaling boat to trade for a longship'),
		"descriptionmyturn" => clienttranslate('${you} have select a whaling boat to trade for a longship'),
		"type" => "activeplayer",
		"action" => "stBuyLongshipFifthCol",
		"possibleactions" => ["selectShip", "undo"],
		"transitions" => ["done" => 51, "zombiepass" => 51, "undo" => 50],
	],
	
	1081 => [
		"name" => "command-slaughter",
		"description" => clienttranslate('${actplayer} may choose which animal to slaughter'),
		"descriptionmyturn" => clienttranslate('${you} may choose which animal to slaughter'),
		"type" => "activeplayer",
		"action" => "stSlaughter",
		"args" => "argSlaughter",
		"possibleactions" => ["selectForSlaughter", "undo"],
		"transitions" => ["done" => 51, "pass" => 51, "zombiepass" => 51, "undo" => 50],
	],

	1082 => [
		"name" => "command-resources",
		"description" => clienttranslate('${actplayer} may choose 2 different building resources to collect'),
		"descriptionmyturn" => clienttranslate('${you} may choose 2 different building resources to collect'),
		"type" => "activeplayer",
		"action" => "stResources",
		"possibleactions" => ["selectResources", "undo"],
		"transitions" => ["done" => 51, "pass" => 51, "zombiepass" => 51, "undo" => 50],
	],

	1083 => [
		"name" => "command-forgeminor",
		"description" => clienttranslate('${actplayer} must take a special tile from the forge where strength < 9'),
		"descriptionmyturn" => clienttranslate('${you} must take a special tile from the forge where strength < 9'),
		"type" => "activeplayer",
		"possibleactions" => ["pass", "selectSpecialTile", "undo"],
		"transitions" => ["done" => 51, "pass" => 51, "zombiepass" => 51, "undo" => 50],
	],

	1084 => [
		"name" => "command-emigrateminor",
		"description" => clienttranslate('${actplayer} must pick a whaling boat to emigrate'),
		"descriptionmyturn" => clienttranslate('${you} must pick a whaling boat to emigrate'),
		"type" => "activeplayer",
		"action" => "stCheckSkip",
		"possibleactions" => ["selectShip", "undo"],
		"transitions" => ["done" => 51, "zombiepass" => 51, "undo" => 50],
	],

	1085 => [
		"name" => "command-anytwoanimals",
		"description" => clienttranslate('${actplayer} must pick two animals'),
		"descriptionmyturn" => clienttranslate('${you} must pick two animals'),
		"type" => "activeplayer",
		"possibleactions" => ["pass", "selectGoods", "undo"],
		"transitions" => ["done" => 51, "pass" => 51, "zombiepass" => 51, "undo" => 50],
	],

	1086 => [
		"name" => "command-discardocc",
		"description" => clienttranslate('${actplayer} must pick an occupation to discard'),
		"descriptionmyturn" => clienttranslate('${you} must pick an occupation to discard'),
		"type" => "activeplayer",
		"possibleactions" => ["pass", "discardOccupation", "undo"],
		"transitions" => ["done" => 51, "pass" => 51, "zombiepass" => 51, "undo" => 50],
	],

	1087 => [
		"name" => "command-coinsoroccs",
		"description" => clienttranslate('${actplayer} must take an action'),
		"descriptionmyturn" => clienttranslate('${you} must choose which action to perform'),
		"type" => "activeplayer",
		"possibleactions" => ["pass", "selectMode", "undo"],
		"transitions" => ["done" => 51, "pass" => 51, "zombiepass" => 51, "undo" => 50],
	],

	1088 => [
		"name" => "command-furmerchant",
		"description" => clienttranslate('${actplayer} must take an action'),
		"descriptionmyturn" => clienttranslate('${you} must choose which action to perform'),
		"type" => "activeplayer",
	    "args" => "argFurMerchant",
		"possibleactions" => ["pass", "selectMode", "undo"],
		"transitions" => ["done" => 51, "pass" => 51, "zombiepass" => 51, "undo" => 50],
	],

	1089 => [
		"name" => "command-armsdealer",
		"description" => clienttranslate('${actplayer} must select which 2 weapon cards to discard'),
		"descriptionmyturn" => clienttranslate('${you} must select which 2 weapon cards to discard'),
		"type" => "activeplayer",
		"action" => "stCheckSkip",
		"possibleactions" => [ "selectWeapon", "undo" ],
		"transitions" => [ "done" => 51, "zombiepass" => 51, "undo" => 50 ],
	],

	1090 => [
		"name" => "command-oncetwiceupgrade",
		"description" => clienttranslate('${actplayer} may choose which tiles to upgrade'),
		"descriptionmyturn" => clienttranslate('${you} may choose which tiles to upgrade'),
		"type" => "activeplayer",
		"action" => "stUpgrade",
		"args" => "argOnceTwiceUpgrade",
		"possibleactions" => ["selectGoods", "undo"],
		"transitions" => ["done" => 51, "pass" => 51, "zombiepass" => 51, "undo" => 50],
	],

	1091 => [
		"name" => "command-buywhalingboatfifthcol",
		"description" => clienttranslate('${actplayer} has to choose if they want a whaling boat'),
		"descriptionmyturn" => clienttranslate('${you} have choose if you want a whaling boat'),
		"type" => "activeplayer",
		"action" => "stBuyWhalingboatFifthCol",
		"possibleactions" => ["selectMode", "pass", "undo" ],
		"transitions" => ["done" => 51, "pass" => 51, "zombiepass" => 51, "undo" => 50],
	],

	1092 => [
		"name" => "command-emigrateminorpassable",
		"description" => clienttranslate('${actplayer} must pick a whaling boat to emigrate'),
		"descriptionmyturn" => clienttranslate('${you} must pick a whaling boat to emigrate'),
		"type" => "activeplayer",
		"action" => "stCheckSkip",
		"possibleactions" => ["pass", "selectShip", "undo"],
		"transitions" => ["done" => 51, "pass" => 51, "zombiepass" => 51, "undo" => 50],
	],

	1093 => [
		"name" => "command-resolveModifierImmediatelly", // Modifier occ, if need ore immediately for forgeminor action and play 2 vikings
		"description" => clienttranslate('${actplayer} must satisfy action requirements via newly played occupation'),
		"descriptionmyturn" => clienttranslate('${you} must satisfy action requirements via newly played occupation'),
		"type" => "activeplayer",
		"action" => "stPlayOccupation",
		"args" => "argTEST",
		"possibleactions" => ["affirm", "undo" ],
		"transitions" => ["affirm" => 51, "zombiepass" => 51, "undo" => 50],
	],

	1094 => [
		"name" => "command-corkshedbonus",
		"description" => clienttranslate('${actplayer} may take a shed'),
		"descriptionmyturn" => clienttranslate('${you} may take a shed'),
		"type" => "activeplayer",
		"action" => "stCheckSkip",
		"possibleactions" => [ "selectMode", "pass", "undo" ],
		"transitions" => [ "pass" => 51, "done" => 51, "zombiepass" => 51, "undo" => 50 ],
	],

	1100 => [
		"name" => "doubleaction",
		"type" => "game",
		// "action" => "stDoubleAction",
		"transitions" => ["buywhalingboatfifthcol" => 1091],
	],

	// Final state.
	// Please do not modify (and do not overload action/args methods).
	99 => [
		"name" => "gameEnd",
		"description" => clienttranslate("End of game"),
		"type" => "manager",
		"action" => "stGameEnd",
		"args" => "argGameEnd",
	],
];
