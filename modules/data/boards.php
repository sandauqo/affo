<?php

$boards = [
	"homefeast" => [
		"type" => "homefeast",
		"category" => "base",
		"rows" => 4,
		"cols" => 12,
		"points" => 0,
		"negatives" => [],
		"incomeStarts" => [],
		"cells" => [],
		"allowedColours" => ["orange", "red", "coin"],
		"nonTouchingColours" => ["orange", "red"],
		"feast" => true,
	],
	"home" => [
		"type" => "home",
		"category" => "base",
		"rows" => 13,
		"cols" => 13,
		"points" => 0,
		// prettier-ignore
		"negatives" => [
			[0, 7], [0, 8], [0, 9], [0, 10], [0, 11],
			[1, 7], [1, 8], [1, 9], [1, 10], [1, 11],
			[2, 7], [2, 8], [2, 9], [2, 10], [2, 11],
			[3, 7], [3, 8], [3, 9], [3, 10], [3, 11],
			[4, 7], [4, 8], [4, 9], [4, 10], [4, 11],
			[5, 7], [5, 8], [5, 9], [5, 10], [5, 11], 
			[6, 7], [6, 8], [6, 9], [6, 10], [6, 11],
			[7, 0], [7, 1], [7, 2], [7, 3], [7, 4],
			[7, 5], [7, 6], [7, 8], [7, 9], [7, 10], [7, 11],
			[8, 1], [8, 2], [8, 3], [8, 4], [8, 5], [8, 6],
			[8, 7], [8, 9], [8, 10], [8, 11],
			[9, 1], [9, 2], [9, 3], [9, 4], [9, 5],
			[9, 6], [9, 7], [9, 8], [9, 10], [9, 11],
			[10, 1], [10, 2], [10, 3], [10, 4], [10, 5],
			[10, 6], [10, 7], [10, 8], [10, 9], [10, 11],
			[11, 1], [11, 2], [11, 3], [11, 4], [11, 5],
			[11, 6], [11, 7], [11, 8], [11, 9], [11, 10],
		],
		"incomeStarts" => [
			[0, 0],
		],
		"cells" => [
			[
				"coords" => [0, 0],
				"type" => "income",
				"value" => 0,
			],
			[
				"coords" => [1, 1],
				"type" => "income",
				"value" => 1,
			],
			[
				"coords" => [2, 2],
				"type" => "income",
				"value" => 2,
			],
			[
				"coords" => [3, 3],
				"type" => "income",
				"value" => 2,
			],
			[
				"coords" => [4, 4],
				"type" => "income",
				"value" => 3,
			],
			[
				"coords" => [5, 5],
				"type" => "income",
				"value" => 4,
			],
			[
				"coords" => [6, 6],
				"type" => "income",
				"value" => 5,
			],
			[
				"coords" => [7, 7],
				"type" => "income",
				"value" => 6,
			],
			[
				"coords" => [8, 8],
				"type" => "income",
				"value" => 7,
			],
			[
				"coords" => [9, 9],
				"type" => "income",
				"value" => 9,
			],
			[
				"coords" => [10, 10],
				"type" => "income",
				"value" => 12,
			],
			[
				"coords" => [11, 11],
				"type" => "income",
				"value" => 15,
			],
			[
				"coords" => [12, 12],
				"type" => "income",
				"value" => 18,
			],
			[
				"coords" => [1, 2],
				"type" => "bonus",
				"resources" => ["mead"],
			],
			[
				"coords" => [2, 4],
				"type" => "bonus",
				"resources" => ["wood"],
			],
			[
				"coords" => [5, 1],
				"type" => "bonus",
				"resources" => ["stone"],
			],
			[
				"coords" => [0, 6],
				"type" => "bonus",
				"resources" => ["ore"],
			],
			[
				"coords" => [6, 4],
				"type" => "bonus",
				"resources" => ["rune"],
			],
			[
				"coords" => [8, 0],
				"type" => "void",
			],
			[
				"coords" => [9, 0],
				"type" => "void",
			],
			[
				"coords" => [10, 0],
				"type" => "void",
			],
			[
				"coords" => [11, 0],
				"type" => "void",
			],
			[
				"coords" => [12, 0],
				"type" => "void",
			],
			[
				"coords" => [12, 1],
				"type" => "void",
			],
			[
				"coords" => [12, 2],
				"type" => "void",
			],
			[
				"coords" => [12, 3],
				"type" => "void",
			],
			[
				"coords" => [12, 4],
				"type" => "void",
			],
			[
				"coords" => [12, 5],
				"type" => "void",
			],
			[
				"coords" => [12, 6],
				"type" => "void",
			],
			[
				"coords" => [12, 7],
				"type" => "void",
			],
			[
				"coords" => [12, 8],
				"type" => "void",
			],
			[
				"coords" => [0, 12],
				"type" => "void",
			],
			[
				"coords" => [1, 12],
				"type" => "void",
			],
			[
				"coords" => [2, 12],
				"type" => "void",
			],
			[
				"coords" => [3, 12],
				"type" => "void",
			],
			[
				"coords" => [4, 12],
				"type" => "void",
			],
			[
				"coords" => [5, 12],
				"type" => "void",
			],
			[
				"coords" => [6, 12],
				"type" => "void",
			],
			[
				"coords" => [7, 12],
				"type" => "void",
			],
			[
				"coords" => [8, 12],
				"type" => "void",
			],
			[
				"coords" => [9, 12],
				"type" => "void",
			],
			[
				"coords" => [10, 12],
				"type" => "void",
			],
			[
				"coords" => [11, 12],
				"type" => "void",
			],
			[
				"coords" => [12, 12],
				"type" => "void",
			],
		],
		"allowedColours" => ["blue", "green", "coin", "ore"],
		"nonTouchingColours" => ["green"],
		"feast" => false,
	],
	"shetland" => [
		"type" => "shetland",
		"category" => "island",
		"vikings" => 1,
		"rows" => 9,
		"cols" => 9,
		"points" => 6,
		// prettier-ignore
		"negatives" => [
			[0, 5], [0, 6], [0, 7],
			[1, 5], [1, 7],
			[2, 5], [2, 6], [2, 7],
			[3, 5], [3, 6], [3, 7],
			[4, 3], [4, 4],
			[5, 2], [5, 3], [5, 4],
			[6, 0], [6, 1], [6, 2], [6, 6],
			[7, 0], [7, 1], [7, 6],
			[8, 1],
		],
		"incomeStarts" => [
			[0, 1],
		],
		"cells" => [
			[
				"coords" => [0, 0],
				"type" => "void",
			],
			[
				"coords" => [0, 4],
				"type" => "void",
			],
			[
				"coords" => [0, 8],
				"type" => "void",
			],
			[
				"coords" => [1, 4],
				"type" => "void",
			],
			[
				"coords" => [1, 8],
				"type" => "void",
			],
			[
				"coords" => [2, 4],
				"type" => "void",
			],
			[
				"coords" => [2, 8],
				"type" => "void",
			],
			[
				"coords" => [3, 4],
				"type" => "void",
			],
			[
				"coords" => [3, 3],
				"type" => "void",
			],
			[
				"coords" => [3, 2],
				"type" => "void",
			],
			[
				"coords" => [3, 1],
				"type" => "void",
			],
			[
				"coords" => [3, 0],
				"type" => "void",
			],
			[
				"coords" => [3, 8],
				"type" => "void",
			],
			[
				"coords" => [4, 0],
				"type" => "void",
			],
			[
				"coords" => [4, 1],
				"type" => "void",
			],
			[
				"coords" => [5, 0],
				"type" => "void",
			],
			[
				"coords" => [5, 1],
				"type" => "void",
			],
			[
				"coords" => [7, 7],
				"type" => "void",
			],
			[
				"coords" => [7, 8],
				"type" => "void",
			],
			[
				"coords" => [8, 7],
				"type" => "void",
			],
			[
				"coords" => [8, 8],
				"type" => "void",
			],
			[
				"coords" => [0, 1],
				"type" => "income",
				"value" => 0,
			],
			[
				"coords" => [1, 2],
				"type" => "income",
				"value" => 1,
			],
			[
				"coords" => [2, 3],
				"type" => "income",
				"value" => 2,
			],
			[
				"coords" => [3, 4],
				"type" => "income",
				"value" => 3,
			],
			[
				"coords" => [2, 1],
				"type" => "bonus",
				"resources" => ["beans"],
			],
			[
				"coords" => [7, 3],
				"type" => "bonus",
				"resources" => ["cabbage"],
			],
			[
				"coords" => [5, 7],
				"type" => "bonus",
				"resources" => ["silverware"],
			],
			[
				"coords" => [7, 5],
				"type" => "bonus",
				"resources" => ["oil"],
			],
			[
				"coords" => [1, 6],
				"type" => "bonus",
				"resources" => ["gamemeat"],
			],
		],
		"allowedColours" => ["blue", "green", "coin", "ore"],
		"nonTouchingColours" => ["green"],
		"feast" => false,
	],
	"bearisland" => [
		"type" => "bearisland",
		"category" => "island",
		"vikings" => 2,
		"rows" => 9,
		"cols" => 8,
		"points" => 12,
		// prettier-ignore
		"negatives" => [
			[0, 6],
			[1, 6], [1, 7],
			[2, 6], [2, 7],
			[3, 7], [3, 8],
			[4, 0], [4, 1], [4, 8],
			[5, 0], [5, 4], [5, 6], [5, 7], [5, 8],
			[6, 3], [6, 5], [6, 7], [6, 8],
			[7, 3], [7, 6], [7, 7],
		],
		"_income" => 1,
		"incomeStarts" => [
			[1, 4],
		],
		"cells" => [
			[
				"coords" => [0, 0],
				"type" => "void",
			],
			[
				"coords" => [0, 1],
				"type" => "void",
			],
			[
				"coords" => [0, 2],
				"type" => "void",
			],
			[
				"coords" => [0, 3],
				"type" => "void",
			],
			[
				"coords" => [0, 4],
				"type" => "void",
			],
			[
				"coords" => [0, 7],
				"type" => "void",
			],
			[
				"coords" => [0, 8],
				"type" => "void",
			],
			[
				"coords" => [1, 0],
				"type" => "void",
			],
			[
				"coords" => [1, 1],
				"type" => "void",
			],
			[
				"coords" => [1, 8],
				"type" => "void",
			],
			[
				"coords" => [2, 0],
				"type" => "void",
			],
			[
				"coords" => [2, 1],
				"type" => "void",
			],
			[
				"coords" => [2, 8],
				"type" => "void",
			],
			[
				"coords" => [3, 0],
				"type" => "void",
			],
			[
				"coords" => [6, 0],
				"type" => "void",
			],
			[
				"coords" => [7, 0],
				"type" => "void",
			],
			[
				"coords" => [7, 1],
				"type" => "void",
			],
			[
				"coords" => [7, 2],
				"type" => "void",
			],
			[
				"coords" => [7, 8],
				"type" => "void",
			],
			[
				"coords" => [2, 4],
				"type" => "void",
			],
			[
				"coords" => [4, 7],
				"type" => "void",
			],
			
			[
				"coords" => [1, 4],
				"type" => "income",
				"value" => 1,
			],
			[
				"coords" => [2, 5],
				"type" => "income",
				"value" => 3,
			],
			[
				"coords" => [3, 6],
				"type" => "income",
				"value" => 4,
			],
			[
				"coords" => [4, 7],
				"type" => "income",
				"value" => 5,
			],
			[
				"coords" => [4, 5],
				"type" => "bonus",
				"resources" => ["gamemeat"],
			],
			[
				"coords" => [7, 4],
				"type" => "bonus",
				"resources" => ["stockfish"],
			],
			[
				"coords" => [6, 2],
				"type" => "bonus",
				"resources" => ["rune", "ore"],
			],
		],
		"allowedColours" => ["blue", "green", "coin", "ore"],
		"nonTouchingColours" => ["green"],
		"feast" => false,
	],
	"faroeislands" => [
		"type" => "faroeislands",
		"category" => "island",
		"vikings" => 1,
		"rows" => 10,
		"cols" => 9,
		"points" => 4,
		// prettier-ignore
		"negatives" => [
			[1, 6],
			[2, 7],
			[3, 7],
			[5, 8], [5, 5],
			[6, 3], [6, 4], [6, 5],
			[7, 0], [7, 1], [7, 2], [7, 3], [7, 5],
			[8, 5], [8, 7], [8, 8],
		],
		"_income" => 1,
		"incomeStarts" => [
			[0, 1],
		],
		"cells" => [
			[
				"coords" => [0, 9],
				"type" => "void",
			],
			[
				"coords" => [1, 9],
				"type" => "void",
			],
			[
				"coords" => [2, 9],
				"type" => "void",
			],
			[
				"coords" => [3, 9],
				"type" => "void",
			],
			[
				"coords" => [4, 9],
				"type" => "void",
			],
			[
				"coords" => [5, 9],
				"type" => "void",
			],
			[
				"coords" => [6, 9],
				"type" => "void",
			],
			[
				"coords" => [7, 9],
				"type" => "void",
			],
			[
				"coords" => [8, 9],
				"type" => "void",
			],
			[
				"coords" => [0, 0],
				"type" => "void",
			],
			[
				"coords" => [0, 4],
				"type" => "void",
			],
			[
				"coords" => [0, 5],
				"type" => "void",
			],
			[
				"coords" => [0, 6],
				"type" => "void",
			],
			[
				"coords" => [0, 7],
				"type" => "void",
			],
			[
				"coords" => [0, 8],
				"type" => "void",
			],
			[
				"coords" => [1, 0],
				"type" => "void",
			],
			[
				"coords" => [1, 4],
				"type" => "void",
			],
			[
				"coords" => [1, 7],
				"type" => "void",
			],
			[
				"coords" => [1, 8],
				"type" => "void",
			],
			[
				"coords" => [2, 0],
				"type" => "void",
			],
			[
				"coords" => [2, 4],
				"type" => "void",
			],
			[
				"coords" => [2, 8],
				"type" => "void",
			],
			[
				"coords" => [3, 0],
				"type" => "void",
			],
			[
				"coords" => [3, 3],
				"type" => "void",
			],
			[
				"coords" => [3, 8],
				"type" => "void",
			],
			[
				"coords" => [4, 0],
				"type" => "void",
			],
			[
				"coords" => [4, 1],
				"type" => "void",
			],
			[
				"coords" => [4, 2],
				"type" => "void",
			],
			[
				"coords" => [4, 7],
				"type" => "void",
			],
			[
				"coords" => [4, 8],
				"type" => "void",
			],
			[
				"coords" => [5, 7],
				"type" => "void",
			],
			[
				"coords" => [6, 6],
				"type" => "void",
			],
			[
				"coords" => [7, 4],
				"type" => "void",
			],
			[
				"coords" => [8, 0],
				"type" => "void",
			],
			[
				"coords" => [8, 1],
				"type" => "void",
			],
			[
				"coords" => [8, 2],
				"type" => "void",
			],
			[
				"coords" => [8, 3],
				"type" => "void",
			],
			[
				"coords" => [0, 1],
				"type" => "income",
				"value" => 0,
			],
			[
				"coords" => [1, 2],
				"type" => "income",
				"value" => 1,
			],
			[
				"coords" => [2, 3],
				"type" => "income",
				"value" => 1,
			],
			[
				"coords" => [3, 4],
				"type" => "income",
				"value" => 1,
			],
			[
				"coords" => [4, 5],
				"type" => "income",
				"value" => 2,
			],
			[
				"coords" => [5, 6],
				"type" => "income",
				"value" => 2,
			],
			[
				"coords" => [6, 7],
				"type" => "income",
				"value" => 2,
			],
			[
				"coords" => [7, 8],
				"type" => "income",
				"value" => 3,
			],
			[
				"coords" => [8, 9],
				"type" => "income",
				"value" => 4,
			],
			[
				"coords" => [0, 2],
				"type" => "bonus",
				"resources" => ["peas"],
			],
			[
				"coords" => [2, 6],
				"type" => "bonus",
				"resources" => ["oil"],
			],
			[
				"coords" => [5, 3],
				"type" => "bonus",
				"resources" => ["flax"],
			],
			[
				"coords" => [6, 8],
				"type" => "bonus",
				"resources" => ["sheep"],
			],
			[
				"coords" => [8, 6],
				"type" => "bonus",
				"resources" => ["milk"],
			],
			[
				"coords" => [6, 1],
				"type" => "bonus",
				"resources" => ["hide"],
			],
		],
		"allowedColours" => ["blue", "green", "coin", "ore"],
		"nonTouchingColours" => ["green"],
		"feast" => false,
	],
	"baffinisland" => [
		"type" => "baffinisland",
		"category" => "island",
		"vikings" => 3,
		"rows" => 9,
		"cols" => 9,
		"points" => 12,
		// prettier-ignore
		"negatives" => [
			[0, 7], [0, 8],
			[1, 5], [1, 7],
			[2, 6], [2, 8],
			[3, 5], [3, 7], [3, 8],
			[4, 0], [4, 1], [4, 2], [4, 3], [4, 6], [4, 7],
			[5, 2], [5, 3], [5, 4], [5, 6], [5, 7],
			[6, 3], [6, 4],
			[8, 0], [8, 3],
		],
		"incomeStarts" => [
			[0, 0],
		],
		"cells" => [
			[
				"coords" => [0, 2],
				"type" => "void",
			],
			[
				"coords" => [0, 4],
				"type" => "void",
			],
			[
				"coords" => [0, 5],
				"type" => "void",
			],
			[
				"coords" => [0, 6],
				"type" => "void",
			],
			[
				"coords" => [2, 5],
				"type" => "void",
			],
			[
				"coords" => [3, 0],
				"type" => "void",
			],
			[
				"coords" => [5, 5],
				"type" => "void",
			],
			[
				"coords" => [5, 8],
				"type" => "void",
			],
			[
				"coords" => [6, 2],
				"type" => "void",
			],
			[
				"coords" => [6, 5],
				"type" => "void",
			],
			[
				"coords" => [6, 8],
				"type" => "void",
			],
			[
				"coords" => [7, 1],
				"type" => "void",
			],
			[
				"coords" => [7, 2],
				"type" => "void",
			],
			[
				"coords" => [7, 4],
				"type" => "void",
			],
			[
				"coords" => [7, 5],
				"type" => "void",
			],
			[
				"coords" => [7, 8],
				"type" => "void",
			],
			[
				"coords" => [8, 1],
				"type" => "void",
			],
			[
				"coords" => [8, 2],
				"type" => "void",
			],
			[
				"coords" => [8, 4],
				"type" => "void",
			],
			[
				"coords" => [8, 8],
				"type" => "void",
			],
			[
				"coords" => [0, 0],
				"type" => "income",
				"value" => 0,
			],
			[
				"coords" => [1, 1],
				"type" => "income",
				"value" => 1,
			],
			[
				"coords" => [2, 2],
				"type" => "income",
				"value" => 2,
			],
			[
				"coords" => [3, 3],
				"type" => "income",
				"value" => 2,
			],
			[
				"coords" => [4, 4],
				"type" => "income",
				"value" => 4,
			],
			[
				"coords" => [5, 5],
				"type" => "income",
				"value" => 6,
			],
			[
				"coords" => [4, 8],
				"type" => "bonus",
				"resources" => ["ore"],
			],
			[
				"coords" => [7, 6],
				"type" => "bonus",
				"resources" => ["whalemeat"],
			],
			[
				"coords" => [7, 3],
				"type" => "bonus",
				"resources" => ["oil"],
			],
			[
				"coords" => [6, 0],
				"type" => "bonus",
				"resources" => ["skinandbones"],
			],
		],
		"allowedColours" => ["blue", "green", "coin", "ore"],
		"nonTouchingColours" => ["green"],
		"feast" => false,
	],
	"iceland" => [
		"type" => "iceland",
		"category" => "island",
		"vikings" => 2,
		"rows" => 9,
		"cols" => 9,
		"points" => 16,
		"_income" => 1,
		// prettier-ignore
		"negatives" => [
			[0, 6],
			[1, 7],
			[2, 0], [2, 1],
			[3, 0], [3, 1], [3, 2], [3, 7],
			[4, 3], [4, 5], [4, 6],
			[5, 3], [5, 4], [5, 6], [5, 7],
			[6, 3], [6, 4], [6, 5], [6, 7],
			[7, 2], [7, 3], [7, 4], [7, 5], [7, 6],
		],
		"incomeStarts" => [
			[1, 1],
		],
		"cells" => [
			[
				"coords" => [0, 0],
				"type" => "void",
			],
			[
				"coords" => [0, 5],
				"type" => "void",
			],
			[
				"coords" => [1, 0],
				"type" => "void",
			],
			[
				"coords" => [2, 6],
				"type" => "void",
			],
			[
				"coords" => [2, 7],
				"type" => "void",
			],
			[
				"coords" => [4, 7],
				"type" => "void",
			],
			[
				"coords" => [5, 0],
				"type" => "void",
			],
			[
				"coords" => [6, 0],
				"type" => "void",
			],
			[
				"coords" => [7, 0],
				"type" => "void",
			],
			[
				"coords" => [7, 1],
				"type" => "void",
			],
			[
				"coords" => [8, 0],
				"type" => "void",
			],
			[
				"coords" => [8, 1],
				"type" => "void",
			],
			[
				"coords" => [8, 2],
				"type" => "void",
			],
			[
				"coords" => [8, 3],
				"type" => "void",
			],
			[
				"coords" => [8, 4],
				"type" => "void",
			],
			[
				"coords" => [8, 5],
				"type" => "void",
			],
			[
				"coords" => [8, 6],
				"type" => "void",
			],
			[
				"coords" => [8, 7],
				"type" => "void",
			],
			[
				"coords" => [8, 8],
				"type" => "void",
			],
			[
				"coords" => [0, 8],
				"type" => "void",
			],
			[
				"coords" => [1, 8],
				"type" => "void",
			],
			[
				"coords" => [2, 8],
				"type" => "void",
			],
			[
				"coords" => [3, 8],
				"type" => "void",
			],
			[
				"coords" => [4, 8],
				"type" => "void",
			],
			[
				"coords" => [5, 8],
				"type" => "void",
			],
			[
				"coords" => [6, 8],
				"type" => "void",
			],
			[
				"coords" => [7, 8],
				"type" => "void",
			],
			[
				"coords" => [8, 8],
				"type" => "void",
			],
			[
				"coords" => [1, 1],
				"type" => "income",
				"value" => 1,
			],
			[
				"coords" => [2, 2],
				"type" => "income",
				"value" => 2,
			],
			[
				"coords" => [3, 3],
				"type" => "income",
				"value" => 3,
			],
			[
				"coords" => [4, 4],
				"type" => "income",
				"value" => 4,
			],
			[
				"coords" => [5, 5],
				"type" => "income",
				"value" => 5,
			],
			[
				"coords" => [6, 6],
				"type" => "income",
				"value" => 6,
			],
			[
				"coords" => [7, 7],
				"type" => "income",
				"value" => 7,
			],
			[
				"coords" => [8, 8],
				"type" => "income",
				"value" => 8,
			],
			[
				"coords" => [0, 3],
				"type" => "bonus",
				"resources" => ["oil"],
			],
			[
				"coords" => [2, 5],
				"type" => "bonus",
				"resources" => ["stockfish"],
			],
			[
				"coords" => [5, 1],
				"type" => "bonus",
				"resources" => ["ore", "stone"],
			],
		],
		"allowedColours" => ["blue", "green", "coin", "ore"],
		"nonTouchingColours" => ["green"],
		"feast" => false,
	],
	"labrador" => [
		"type" => "labrador",
		"category" => "island",
		"vikings" => 3,
		"rows" => 9,
		"cols" => 9,
		"points" => 36,
		// prettier-ignore
		"negatives" => [
			[0, 1], [0, 3],
			[1, 0], [1, 2], [1, 3],
			[2, 1], [2, 3], [2, 4],
			[3, 0], [3, 2], [3, 4], [3, 6], [3, 7], [3, 8],
			[4, 1], [4, 3], [4, 4], [4, 5], [4, 7], [4, 8],
			[5, 0], [5, 2], [5, 3], [5, 4], [5, 5], [5, 6], [5, 7],
			[6, 1], [6, 3], [6, 4],
			[7, 0], [7, 1], [7, 2], [7, 3], [7, 4], [7, 5],
			[8, 0], [8, 1], [8, 3], [8, 4],
		],
		"incomeStarts" => [
		],
		"cells" => [
			[
				"coords" => [0, 0],
				"type" => "void",
			],
			[
				"coords" => [0, 4],
				"type" => "void",
			],
			[
				"coords" => [0, 5],
				"type" => "void",
			],
			[
				"coords" => [0, 6],
				"type" => "void",
			],
			[
				"coords" => [0, 7],
				"type" => "void",
			],
			[
				"coords" => [0, 8],
				"type" => "void",
			],
			[
				"coords" => [1, 4],
				"type" => "void",
			],
			[
				"coords" => [1, 5],
				"type" => "void",
			],
			[
				"coords" => [1, 6],
				"type" => "void",
			],
			[
				"coords" => [1, 7],
				"type" => "void",
			],
			[
				"coords" => [1, 8],
				"type" => "void",
			],
			[
				"coords" => [2, 0],
				"type" => "void",
			],
			[
				"coords" => [2, 5],
				"type" => "void",
			],
			[
				"coords" => [2, 6],
				"type" => "void",
			],
			[
				"coords" => [2, 7],
				"type" => "void",
			],
			[
				"coords" => [2, 8],
				"type" => "void",
			],
			[
				"coords" => [5, 8],
				"type" => "void",
			],
			[
				"coords" => [6, 2],
				"type" => "void",
			],
			[
				"coords" => [6, 6],
				"type" => "void",
			],
			[
				"coords" => [6, 7],
				"type" => "void",
			],
			[
				"coords" => [6, 8],
				"type" => "void",
			],
			[
				"coords" => [7, 6],
				"type" => "void",
			],
			[
				"coords" => [7, 7],
				"type" => "void",
			],
			[
				"coords" => [7, 8],
				"type" => "void",
			],
			[
				"coords" => [8, 2],
				"type" => "void",
			],
			[
				"coords" => [8, 5],
				"type" => "void",
			],
			[
				"coords" => [8, 6],
				"type" => "void",
			],
			[
				"coords" => [8, 7],
				"type" => "void",
			],
			[
				"coords" => [8, 8],
				"type" => "void",
			],
			[
				"coords" => [1, 1],
				"type" => "bonus",
				"resources" => ["linen"],
			],
			[
				"coords" => [3, 5],
				"type" => "bonus",
				"resources" => ["gamemeat"],
			],
			[
				"coords" => [4, 2],
				"type" => "bonus",
				"resources" => ["chest"],
			],
			[
				"coords" => [6, 0],
				"type" => "bonus",
				"resources" => ["stockfish"],
			],
		],
		"allowedColours" => ["blue", "green", "coin", "ore"],
		"nonTouchingColours" => ["green"],
		"feast" => false,
	],
	"greenland" => [
		"type" => "greenland",
		"category" => "island",
		"vikings" => 2,
		"rows" => 9,
		"cols" => 9,
		"points" => 12,
		// prettier-ignore
		"negatives" => [
			[0, 7],
			[1, 2], [1, 3], [1, 7],
			[2, 2], [2, 7],
			[3, 2], [3, 3], [3, 4],
			[4, 3], [4, 4], [4, 5],
			[5, 4], [5, 5], [5, 6],
			[6, 5], [6, 6], [6, 7],
			[7, 6], [7, 7],
		],
		"incomeStarts" => [
			[3, 1], [3, 5]
		],
		"cells" => [
			[
				"coords" => [0, 0],
				"type" => "void",
			],
			[
				"coords" => [0, 1],
				"type" => "void",
			],
			[
				"coords" => [0, 2],
				"type" => "void",
			],
			[
				"coords" => [1, 0],
				"type" => "void",
			],
			[
				"coords" => [1, 1],
				"type" => "void",
			],
			[
				"coords" => [2, 0],
				"type" => "void",
			],
			[
				"coords" => [3, 0],
				"type" => "void",
			],
			[
				"coords" => [7, 0],
				"type" => "void",
			],
			[
				"coords" => [7, 1],
				"type" => "void",
			],
			[
				"coords" => [7, 4],
				"type" => "void",
			],
			[
				"coords" => [3, 6],
				"type" => "void",
			],
			[
				"coords" => [5, 2],
				"type" => "void",
			],
			[
				"coords" => [8, 0],
				"type" => "void",
			],
			[
				"coords" => [8, 1],
				"type" => "void",
			],
			[
				"coords" => [8, 2],
				"type" => "void",
			],
			[
				"coords" => [8, 3],
				"type" => "void",
			],
			[
				"coords" => [8, 4],
				"type" => "void",
			],
			[
				"coords" => [8, 5],
				"type" => "void",
			],
			[
				"coords" => [8, 6],
				"type" => "void",
			],
			[
				"coords" => [8, 7],
				"type" => "void",
			],
			[
				"coords" => [8, 8],
				"type" => "void",
			],
			[
				"coords" => [0, 8],
				"type" => "void",
			],
			[
				"coords" => [1, 8],
				"type" => "void",
			],
			[
				"coords" => [2, 8],
				"type" => "void",
			],
			[
				"coords" => [3, 8],
				"type" => "void",
			],
			[
				"coords" => [4, 8],
				"type" => "void",
			],
			[
				"coords" => [5, 8],
				"type" => "void",
			],
			[
				"coords" => [6, 8],
				"type" => "void",
			],
			[
				"coords" => [7, 8],
				"type" => "void",
			],
			[
				"coords" => [3, 1],
				"type" => "income",
				"value" => 0,
			],
			[
				"coords" => [4, 2],
				"type" => "income",
				"value" => 1,
			],
			[
				"coords" => [5, 3],
				"type" => "income",
				"value" => 2,
			],
			[
				"coords" => [6, 4],
				"type" => "income",
				"value" => 3,
			],
			[
				"coords" => [7, 5],
				"type" => "income",
				"value" => 4,
			],
			[
				"coords" => [8, 6],
				"type" => "income",
				"value" => 5,
			],
			[
				"coords" => [3, 5],
				"type" => "income",
				"value" => 0,
			],
			[
				"coords" => [4, 6],
				"type" => "income",
				"value" => 1,
			],
			[
				"coords" => [5, 7],
				"type" => "income",
				"value" => 2,
			],
			[
				"coords" => [6, 8],
				"type" => "income",
				"value" => 3,
			],
			[
				"coords" => [1, 5],
				"type" => "bonus",
				"resources" => ["whalemeat"],
			],
			[
				"coords" => [7, 2],
				"type" => "bonus",
				"resources" => ["stockfish"],
			],
		],
		"allowedColours" => ["blue", "green", "coin", "ore"],
		"nonTouchingColours" => ["green"],
		"feast" => false,
	],
	"newfoundland" => [
		"type" => "newfoundland",
		"category" => "island",
		"vikings" => 3,
		"rows" => 9,
		"cols" => 9,
		"points" => 38,
		// prettier-ignore
		"negatives" => [
			[0, 1], [0, 3],
			[1, 1], [1, 2], [1, 4], [1, 6],
			[2, 1], [2, 3], [2, 5], [2, 7], [2, 8],
			[3, 2], [3, 4], [3, 6], [3, 7], [3, 8],
			[4, 1], [4, 2], [4, 3], [4, 5], [4, 8],
			[5, 0], [5, 1], [5, 2], [5, 4], [5, 5], [5, 6],
			[6, 1], [6, 3], [6, 4], [6, 5], [6, 6],
			[7, 0], [7, 1], [7, 2], [7, 3], [7, 4],
			[8, 0], [8, 1], [8, 3],
		],
		"incomeStarts" => [],
		"cells" => [
			[
				"coords" => [0, 0],
				"type" => "void",
			],
			[
				"coords" => [0, 5],
				"type" => "void",
			],
			[
				"coords" => [0, 6],
				"type" => "void",
			],
			[
				"coords" => [0, 7],
				"type" => "void",
			],
			[
				"coords" => [0, 8],
				"type" => "void",
			],
			[
				"coords" => [1, 0],
				"type" => "void",
			],
			[
				"coords" => [1, 7],
				"type" => "void",
			],
			[
				"coords" => [1, 8],
				"type" => "void",
			],
			[
				"coords" => [2, 0],
				"type" => "void",
			],
			[
				"coords" => [3, 0],
				"type" => "void",
			],
			[
				"coords" => [4, 0],
				"type" => "void",
			],
			[
				"coords" => [4, 6],
				"type" => "void",
			],
			[
				"coords" => [4, 7],
				"type" => "void",
			],
			[
				"coords" => [5, 7],
				"type" => "void",
			],
			[
				"coords" => [5, 8],
				"type" => "void",
			],
			[
				"coords" => [6, 0],
				"type" => "void",
			],
			[
				"coords" => [6, 7],
				"type" => "void",
			],
			[
				"coords" => [6, 8],
				"type" => "void",
			],
			[
				"coords" => [7, 5],
				"type" => "void",
			],
			[
				"coords" => [7, 6],
				"type" => "void",
			],
			[
				"coords" => [7, 7],
				"type" => "void",
			],
			[
				"coords" => [7, 8],
				"type" => "void",
			],
			[
				"coords" => [8, 2],
				"type" => "void",
			],
			[
				"coords" => [8, 4],
				"type" => "void",
			],
			[
				"coords" => [8, 5],
				"type" => "void",
			],
			[
				"coords" => [8, 6],
				"type" => "void",
			],
			[
				"coords" => [8, 7],
				"type" => "void",
			],
			[
				"coords" => [8, 8],
				"type" => "void",
			],
			[
				"coords" => [1, 3],
				"type" => "bonus",
				"resources" => ["cane"],
			],
			[
				"coords" => [2, 6],
				"type" => "bonus",
				"resources" => ["skinandbones"],
			],
			[
				"coords" => [6, 2],
				"type" => "bonus",
				"resources" => ["stonehouse"],
			],
		],
		"allowedColours" => ["blue", "green", "coin", "ore"],
		"nonTouchingColours" => ["green"],
		"feast" => false,
	],
	"shed" => [
		"type" => "shed",
		"category" => "shed",
		"rows" => 0,
		"cols" => 0,
		"points" => 8,
		"negatives" => [],
		"incomeStarts" => [],
		"cells" => [],
		"allowedColours" => [],
		"nonTouchingColours" => [],
		"feast" => false,
		"standalone" => [
			["item" => "wood", "value" => -1], 
			["item" => "wood", "value" => -1], 
			["item" => "wood", "value" => -1], 
			["item" => "stone", "value" => -1], 
			["item" => "stone", "value" => -1], 
			["item" => "stone", "value" => -1], 
		],
	],
	"stonehouse" => [
		"type" => "stonehouse",
		"category" => "house",
		"rows" => 4,
		"cols" => 5,
		"points" => 10,
		// prettier-ignore
		"negatives" => [
			[0, 2],
			[1, 1],
			[2, 0], [2, 3],
			[3, 2],
			[4, 0], [4, 1],
		],
		"incomeStarts" => [],
		"cells" => [
			[
				"coords" => [0, 0],
				"type" => "void",
			],
			[
				"coords" => [0, 1],
				"type" => "void",
			],
			[
				"coords" => [0, 3],
				"type" => "void",
			],
			[
				"coords" => [1, 0],
				"type" => "void",
			],
			[
				"coords" => [3, 3],
				"type" => "void",
			],
			[
				"coords" => [4, 2],
				"type" => "void",
			],
			[
				"coords" => [4, 3],
				"type" => "void",
			],
			[
				"coords" => [2, 2],
				"type" => "bonus",
				"resources" => ["hide"],
			],
		],
		"allowedColours" => ["blue", "green", "coin", "red", "orange"],
		"nonTouchingColours" => ["red", "orange"],
		"feast" => false,
		"standalone" => [
			["item" => "wood", "value" => -1], 
			["item" => "stone", "value" => -1], 
		],
	],
	"longhouse" => [
		"type" => "longhouse",
		"category" => "house",
		"rows" => 3,
		"cols" => 11,
		"points" => 17,
		// prettier-ignore
		"negatives" => [
			[1, 0], [3, 0], [7, 0], [9, 0],
			[0, 1], [2, 1], [4, 1], [6, 1], [8, 1], [10, 1], 
			[1, 2], [3, 2], [5, 2], [7, 2], [9, 2],
		],
		"incomeStarts" => [],
		"cells" => [
			[
				"coords" => [10, 0],
				"type" => "void",
			],
			[
				"coords" => [3, 1],
				"type" => "void",
			],
			[
				"coords" => [7, 1],
				"type" => "void",
			],
			[
				"coords" => [1, 1],
				"type" => "bonus",
				"resources" => ["oil"],
			],
			[
				"coords" => [5, 0],
				"type" => "bonus",
				"resources" => ["beans"],
			],
			[
				"coords" => [10, 2],
				"type" => "bonus",
				"resources" => ["peas"],
			],
		],
		"allowedColours" => ["blue", "green", "coin", "red", "orange"],
		"nonTouchingColours" => ["red", "orange"],
		"feast" => false,
	],
	
	// Mini expansion 1
	"lofoten" => [
		"type" => "lofoten",
		"category" => "island",
		"vikings" => 1,
		"rows" => 10,
		"cols" => 10,
		"points" => 4,
		// prettier-ignore
		"negatives" => [
			[3, 1],
			[4, 2],
			[5, 4], [5, 5], [5, 5],
			[6, 3], [6, 5], [6, 8], [6, 8],
			[7, 5], [7, 7],
			[8, 3], [8, 4],
			[9, 4], [9, 5], [9, 6], [9, 8],
		],
		"incomeStarts" => [
			[0, 1]
		],
		"cells" => [
			[
				"coords" => [0, 1],
				"type" => "income",
				"value" => 0,
			],
			[
				"coords" => [1, 2],
				"type" => "income",
				"value" => 1,
			],
			[
				"coords" => [2, 3],
				"type" => "income",
				"value" => 2,
			],
			[
				"coords" => [3, 4],
				"type" => "income",
				"value" => 2,
			],
			[
				"coords" => [4, 5],
				"type" => "income",
				"value" => -1,
			],
			[
				"coords" => [5, 6],
				"type" => "income",
				"value" => -1,
			],
			[
				"coords" => [6, 7],
				"type" => "income",
				"value" => -1,
			],
			[
				"coords" => [7, 8],
				"type" => "income",
				"value" => 3,
			],
			[
				"coords" => [8, 9],
				"type" => "income",
				"value" => 4,
			],
			[
				"coords" => [0, 1],
				"type" => "income",
				"value" => 0,
			],
			[
				"coords" => [0, 2],
				"type" => "void",
			],
			[
				"coords" => [0, 3],
				"type" => "void",
			],
			[
				"coords" => [0, 4],
				"type" => "void",
			],
			[
				"coords" => [0, 5],
				"type" => "void",
			],
			[
				"coords" => [0, 6],
				"type" => "void",
			],
			[
				"coords" => [0, 7],
				"type" => "void",
			],
			[
				"coords" => [0, 8],
				"type" => "void",
			],
			[
				"coords" => [0, 9],
				"type" => "void",
			],
			[
				"coords" => [1, 3],
				"type" => "void",
			],
			[
				"coords" => [1, 4],
				"type" => "void",
			],
			[
				"coords" => [1, 5],
				"type" => "void",
			],
			[
				"coords" => [1, 6],
				"type" => "void",
			],
			[
				"coords" => [1, 7],
				"type" => "void",
			],
			[
				"coords" => [1, 8],
				"type" => "void",
			],
			[
				"coords" => [1, 9],
				"type" => "void",
			],
			[
				"coords" => [2, 0],
				"type" => "void",
			],
			[
				"coords" => [2, 4],
				"type" => "void",
			],
			[
				"coords" => [2, 5],
				"type" => "void",
			],
			[
				"coords" => [2, 6],
				"type" => "void",
			],
			[
				"coords" => [2, 7],
				"type" => "void",
			],
			[
				"coords" => [2, 8],
				"type" => "void",
			],
			[
				"coords" => [2, 9],
				"type" => "void",
			],
			[
				"coords" => [3, 0],
				"type" => "void",
			],
			[
				"coords" => [3, 5],
				"type" => "void",
			],
			[
				"coords" => [3, 6],
				"type" => "void",
			],
			[
				"coords" => [3, 7],
				"type" => "void",
			],
			[
				"coords" => [3, 8],
				"type" => "void",
			],
			[
				"coords" => [3, 9],
				"type" => "void",
			],
			[
				"coords" => [4, 0],
				"type" => "void",
			],
			[
				"coords" => [4, 1],
				"type" => "void",
			],
			[
				"coords" => [4, 5],
				"type" => "void",
			],
			[
				"coords" => [4, 6],
				"type" => "void",
			],
			[
				"coords" => [4, 7],
				"type" => "void",
			],
			[
				"coords" => [4, 8],
				"type" => "void",
			],
			[
				"coords" => [4, 9],
				"type" => "void",
			],
			[
				"coords" => [5, 0],
				"type" => "void",
			],
			[
				"coords" => [5, 1],
				"type" => "void",
			],
			[
				"coords" => [5, 2],
				"type" => "void",
			],
			[
				"coords" => [5, 6],
				"type" => "void",
			],
			[
				"coords" => [5, 7],
				"type" => "void",
			],
			[
				"coords" => [5, 8],
				"type" => "void",
			],
			[
				"coords" => [5, 9],
				"type" => "void",
			],
			[
				"coords" => [6, 0],
				"type" => "void",
			],
			[
				"coords" => [6, 1],
				"type" => "void",
			],
			[
				"coords" => [6, 2],
				"type" => "void",
			],
			[
				"coords" => [6, 6],
				"type" => "void",
			],
			[
				"coords" => [6, 7],
				"type" => "void",
			],
			[
				"coords" => [6, 9],
				"type" => "void",
			],
			[
				"coords" => [7, 0],
				"type" => "void",
			],
			[
				"coords" => [7, 1],
				"type" => "void",
			],
			[
				"coords" => [7, 2],
				"type" => "void",
			],
			[
				"coords" => [7, 6],
				"type" => "void",
			],
			[
				"coords" => [7, 9],
				"type" => "void",
			],
			[
				"coords" => [8, 0],
				"type" => "void",
			],
			[
				"coords" => [8, 1],
				"type" => "void",
			],
			[
				"coords" => [8, 2],
				"type" => "void",
			],
			[
				"coords" => [8, 6],
				"type" => "void",
			],
			[
				"coords" => [8, 9],
				"type" => "void",
			],
			[
				"coords" => [9, 0],
				"type" => "void",
			],
			[
				"coords" => [9, 1],
				"type" => "void",
			],
			[
				"coords" => [9, 2],
				"type" => "void",
			],
			[
				"coords" => [9, 3],
				"type" => "void",
			],
			[
				"coords" => [9, 7],
				"type" => "void",
			],
			[
				"coords" => [9, 9],
				"type" => "void",
			],
			[
				"coords" => [1, 0],
				"type" => "bonus",
				"resources" => ["stockfish"],
			],
			[
				"coords" => [4, 3],
				"type" => "bonus",
				"resources" => ["hide"],
			],
			[
				"coords" => [8, 5],
				"type" => "bonus",
				"resources" => ["wood"],
			],
			[
				"coords" => [8, 7],
				"type" => "bonus",
				"resources" => ["stone"],
			],
		],
		"allowedColours" => ["blue", "green", "coin", "ore"],
		"nonTouchingColours" => ["green"],
		"feast" => false,
	],
	"orkney" => [
		"type" => "orkney",
		"category" => "island",
		"vikings" => 1,
		"rows" => 9,
		"cols" => 10,
		"points" => 12,
		// prettier-ignore
		"negatives" => [
			[0, 4], [0, 6], [0, 7],
			[1, 5], [1, 7], [1, 8],
			[2, 8],
			[3, 0], [3, 2],
			[4, 3], [4, 7],
			[5, 1], [5, 5], [5, 6], [5, 8], 
			[6, 3], [6, 5], [6, 8],
			[7, 1], [7, 3],
			[8, 0], [8, 4],
			[9, 2], [9, 4], [9, 5],
		],
		"incomeStarts" => [
			[0, 2]
		],
		"cells" => [
			[
				"coords" => [0, 2],
				"type" => "income",
				"value" => 0,
			],
			[
				"coords" => [1, 3],
				"type" => "income",
				"value" => 1,
			],
			[
				"coords" => [2, 4],
				"type" => "income",
				"value" => -1,
			],
			[
				"coords" => [3, 5],
				"type" => "income",
				"value" => 2,
			],
			[
				"coords" => [4, 6],
				"type" => "income",
				"value" => 3,
			],
			[
				"coords" => [5, 7],
				"type" => "income",
				"value" => 4,
			],
			[
				"coords" => [0, 0],
				"type" => "void",
			],
			[
				"coords" => [0, 1],
				"type" => "void",
			],
			[
				"coords" => [0, 8],
				"type" => "void",
			],
			[
				"coords" => [1, 0],
				"type" => "void",
			],
			[
				"coords" => [1, 1],
				"type" => "void",
			],
			[
				"coords" => [2, 0],
				"type" => "void",
			],
			[
				"coords" => [2, 1],
				"type" => "void",
			],
			[
				"coords" => [2, 2],
				"type" => "void",
			],
			[
				"coords" => [2, 3],
				"type" => "void",
			],
			[
				"coords" => [2, 4],
				"type" => "void",
			],
			[
				"coords" => [4, 0],
				"type" => "void",
			],
			[
				"coords" => [4, 8],
				"type" => "void",
			],
			[
				"coords" => [5, 0],
				"type" => "void",
			],
			[
				"coords" => [5, 3],
				"type" => "void",
			],
			[
				"coords" => [5, 4],
				"type" => "void",
			],
			[
				"coords" => [5, 7],
				"type" => "void",
			],
			[
				"coords" => [6, 0],
				"type" => "void",
			],
			[
				"coords" => [6, 1],
				"type" => "void",
			],
			[
				"coords" => [6, 4],
				"type" => "void",
			],
			[
				"coords" => [6, 6],
				"type" => "void",
			],
			[
				"coords" => [6, 7],
				"type" => "void",
			],
			[
				"coords" => [7, 0],
				"type" => "void",
			],
			[
				"coords" => [7, 4],
				"type" => "void",
			],
			[
				"coords" => [7, 5],
				"type" => "void",
			],
			[
				"coords" => [7, 6],
				"type" => "void",
			],
			[
				"coords" => [7, 7],
				"type" => "void",
			],
			[
				"coords" => [7, 8],
				"type" => "void",
			],
			[
				"coords" => [8, 2],
				"type" => "void",
			],
			[
				"coords" => [8, 3],
				"type" => "void",
			],
			[
				"coords" => [8, 6],
				"type" => "void",
			],
			[
				"coords" => [8, 7],
				"type" => "void",
			],
			[
				"coords" => [8, 8],
				"type" => "void",
			],
			[
				"coords" => [9, 3],
				"type" => "void",
			],
			[
				"coords" => [9, 6],
				"type" => "void",
			],
			[
				"coords" => [9, 7],
				"type" => "void",
			],
			[
				"coords" => [9, 8],
				"type" => "void",
			],
			[
				"coords" => [3, 7],
				"type" => "bonus",
				"resources" => ["cattle"],
			],
			[
				"coords" => [5, 2],
				"type" => "bonus",
				"resources" => ["weapon"],
			],
			[
				"coords" => [8, 1],
				"type" => "bonus",
				"resources" => ["fruits"],
			],
		],
		"allowedColours" => ["blue", "green", "coin", "ore"],
		"nonTouchingColours" => ["green"],
		"feast" => false,
	],
	"tierradelfuego" => [
		"type" => "tierradelfuego",
		"category" => "island",
		"vikings" => 3,
		"rows" => 7,
		"cols" => 9,
		"points" => 15,
		"_income" => 1,
		// prettier-ignore
		"negatives" => [
			[0, 5], [0, 6],
			[1, 3], [1, 4], [1, 5],
			[2, 6],
			[4, 3], [4, 5], [4, 6],
			[6, 1], [6, 2], [6, 5],
			[7, 0], [7, 1], [7, 3], [7, 4],
			[8, 3], [8, 4], [8, 5], [8, 6],
		],
		"incomeStarts" => [
			[2, 2]
		],
		"cells" => [
			[
				"coords" => [2, 2],
				"type" => "income",
				"value" => 1,
			],
			[
				"coords" => [3, 3],
				"type" => "income",
				"value" => 2,
			],
			[
				"coords" => [4, 4],
				"type" => "income",
				"value" => 3,
			],
			[
				"coords" => [5, 5],
				"type" => "income",
				"value" => 4,
			],
			[
				"coords" => [6, 6],
				"type" => "income",
				"value" => 5,
			],
			[
				"coords" => [0, 0],
				"type" => "void",
			],
			[
				"coords" => [0, 1],
				"type" => "void",
			],
			[
				"coords" => [0, 2],
				"type" => "void",
			],
			[
				"coords" => [0, 4],
				"type" => "void",
			],
			[
				"coords" => [1, 0],
				"type" => "void",
			],
			[
				"coords" => [1, 1],
				"type" => "void",
			],
			[
				"coords" => [1, 2],
				"type" => "void",
			],
			[
				"coords" => [2, 0],
				"type" => "void",
			],
			[
				"coords" => [2, 3],
				"type" => "void",
			],
			[
				"coords" => [3, 0],
				"type" => "void",
			],
			[
				"coords" => [3, 6],
				"type" => "void",
			],
			[
				"coords" => [6, 0],
				"type" => "void",
			],
			[
				"coords" => [6, 6],
				"type" => "void",
			],
			[
				"coords" => [8, 0],
				"type" => "void",
			],
			[
				"coords" => [8, 1],
				"type" => "void",
			],
			[
				"coords" => [8, 2],
				"type" => "void",
			],
			[
				"coords" => [3, 4],
				"type" => "bonus",
				"resources" => ["stone"],
			],
			[
				"coords" => [4, 1],
				"type" => "bonus",
				"resources" => ["saltmeat"],
			],
			[
				"coords" => [5, 6],
				"type" => "bonus",
				"resources" => ["wool"],
			],
			[
				"coords" => [6, 3],
				"type" => "bonus",
				"resources" => ["hide"],
			],
		],
		"allowedColours" => ["blue", "green", "coin", "ore"],
		"nonTouchingColours" => ["green"],
		"feast" => false,
	],

	// Norwegian artisan sheds
	"whaling" => [
		"type" => "whaling",
		"category" => "shed",
		"rows" => 3,
		"cols" => 6, // assume whaling boat 3x3 and see how it goes
		"points" => 7,
		// prettier-ignore
		"negatives" => [
			[4, 0],
			[3, 1],
			[4, 2], [4, 2],
			[0, 0], [0, 1], [0, 2] // fake negatives that whaling boat tile will cover and everything works out
		],
		"incomeStarts" => [
			[3, 0]
		],
		"cells" => [
			[
				"coords" => [5, 0],
				"type" => "void",
			],
			[
				"coords" => [5, 1],
				"type" => "void",
			],
			[
				"coords" => [5, 2],
				"type" => "void",
			],
			[  "coords" => [3, 0],
				"type" => "income",
				"value" => 0,
			],
			[
				"coords" => [4, 1],
				"type" => "income",
				"value" => 0,
			],
			[
				"coords" => [5, 2],
				"type" => "income",
				"value" => 1,
			],
			[
				"coords" => [3, 2],
				"type" => "bonus",
				"resources" => ["stockfish"],
			],
			[
				"coords" => [0, 0],
				"type" => "unique",
				"uniqueType" => "whalingboat",
			],
			[
				"coords" => [1, 0],
				"type" => "unique",
				"uniqueType" => "whalingboat",
			],
			[
				"coords" => [2, 0],
				"type" => "unique",
				"uniqueType" => "whalingboat",
			],
			[
				"coords" => [0, 1],
				"type" => "unique",
				"uniqueType" => "whalingboat",
			],
			[
				"coords" => [1, 1],
				"type" => "unique",
				"uniqueType" => "whalingboat",
			],
			[
				"coords" => [2, 1],
				"type" => "unique",
				"uniqueType" => "whalingboat",
			],
			[
				"coords" => [0, 2],
				"type" => "unique",
				"uniqueType" => "whalingboat",
			],
			[
				"coords" => [1, 2],
				"type" => "unique",
				"uniqueType" => "whalingboat",
			],
			[
				"coords" => [2, 2],
				"type" => "unique",
				"uniqueType" => "whalingboat",
			],
		],
		"allowedColours" => ["blue", "green", "coin", "red", "orange", "whalingboat"],
		"nonTouchingColours" => ["red", "orange"],
		"feast" => false,
	],
	"spinnery" => [
		"type" => "spinnery",
		"category" => "shed",
		"rows" => 3,
		"cols" => 5,
		"points" => 8,
		// prettier-ignore
		"negatives" => [
			[4, 0], [4, 1]
		],
		"incomeStarts" => [
			[1, 0]
		],
		"cells" => [
			[
				"coords" => [0, 2],
				"type" => "void",
			],
			[
				"coords" => [1, 2],
				"type" => "void",
			],
			[
				"coords" => [2, 2],
				"type" => "void",
			],
			[
				"coords" => [3, 2],
				"type" => "void",
			],
			[
				"coords" => [4, 2],
				"type" => "void",
			],
			[
				"coords" => [1, 0],
				"type" => "income",
				"value" => 0,
			],
			[
				"coords" => [2, 1],
				"type" => "income",
				"value" => 0,
			],
			[
				"coords" => [3, 2],
				"type" => "income",
				"value" => 1,
			],
			[
				"coords" => [2, 0],
				"type" => "bonus",
				"resources" => ["flax"],
			],
		],
		"allowedColours" => ["blue", "green", "coin", "red", "orange"],
		"nonTouchingColours" => ["red", "orange"],
		"feast" => false,
		"standalone" => [
			["item" => "linen", "value" => -4], 
		],
	],
	"mason" => [
		"type" => "mason",
		"category" => "shed",
		"rows" => 3,
		"cols" => 5,
		"points" => 7,
		// prettier-ignore
		"negatives" => [
			[0, 0], [0, 2], [2, 2], [3, 2], [3, 2]
		],
		"incomeStarts" => [
			[2, 0]
		],
		"cells" => [
			[
				"coords" => [4, 0],
				"type" => "void",
			],
			[
				"coords" => [4, 1],
				"type" => "void",
			],
			[
				"coords" => [4, 2],
				"type" => "void",
			],
			[
				"coords" => [2, 0],
				"type" => "income",
				"value" => 0,
			],
			[
				"coords" => [3, 1],
				"type" => "income",
				"value" => 0,
			],
			[
				"coords" => [4, 2],
				"type" => "income",
				"value" => 1,
			],
			[
				"coords" => [1, 0],
				"type" => "bonus",
				"resources" => ["stone"],
			],
			[
				"coords" => [1, 2],
				"type" => "bonus",
				"resources" => ["beans"],
			],
		],
		"allowedColours" => ["blue", "green", "coin", "red", "orange"],
		"nonTouchingColours" => ["red", "orange"],
		"feast" => false,
		"standalone" => [
			["item" => "stone", "value" => -1], 
			["item" => "stone", "value" => -1], 
		],
	],
	"beekeeper" => [
		"type" => "beekeeper",
		"category" => "shed",
		"rows" => 4,
		"cols" => 5,
		"points" => 8,
		// prettier-ignore
		"negatives" => [
			[0, 0], [3, 1], [0, 2], [2, 2]
		],
		"incomeStarts" => [
			[2, 1]
		],
		"cells" => [
			[
				"coords" => [1, 0],
				"type" => "void",
			],
			[
				"coords" => [2, 0],
				"type" => "void",
			],
			[
				"coords" => [3, 0],
				"type" => "void",
			],
			[
				"coords" => [4, 0],
				"type" => "void",
			],
			[
				"coords" => [4, 1],
				"type" => "void",
			],
			[
				"coords" => [4, 2],
				"type" => "void",
			],
			[
				"coords" => [0, 3],
				"type" => "void",
			],
			[
				"coords" => [1, 3],
				"type" => "void",
			],
			[
				"coords" => [2, 3],
				"type" => "void",
			],
			[
				"coords" => [3, 3],
				"type" => "void",
			],
			[
				"coords" => [4, 3],
				"type" => "void",
			],
			[
				"coords" => [2, 1],
				"type" => "income",
				"value" => 0,
			],
			[
				"coords" => [3, 2],
				"type" => "income",
				"value" => 0,
			],
			[
				"coords" => [4, 3],
				"type" => "income",
				"value" => 1,
			],
			[
				"coords" => [1, 1],
				"type" => "bonus",
				"resources" => ["mead"],
			],
		],
		"allowedColours" => ["blue", "green", "coin", "red", "orange"],
		"nonTouchingColours" => ["red", "orange"],
		"feast" => false,
		"standalone" => [
			["item" => "mead", "value" => -2], 
		],
	],
	"hermit" => [
		"type" => "hermit",
		"category" => "shed",
		"rows" => 4,
		"cols" => 5,
		"points" => 7,
		// prettier-ignore
		"negatives" => [
			[3, 0], [2, 2], [3, 2], [3, 2], [0, 3], [0, 3], [1, 3]
		],
		"incomeStarts" => [
			[2, 0]
		],
		"cells" => [
			[
				"coords" => [4, 0],
				"type" => "void",
			],
			[
				"coords" => [4, 1],
				"type" => "void",
			],
			[
				"coords" => [4, 2],
				"type" => "void",
			],
			[
				"coords" => [2, 3],
				"type" => "void",
			],
			[
				"coords" => [3, 3],
				"type" => "void",
			],
			[
				"coords" => [4, 3],
				"type" => "void",
			],
			[
				"coords" => [2, 0],
				"type" => "income",
				"value" => 0,
			],
			[
				"coords" => [3, 1],
				"type" => "income",
				"value" => 0,
			],
			[
				"coords" => [4, 2],
				"type" => "income",
				"value" => 1,
			],
			[
				"coords" => [1, 1],
				"type" => "bonus",
				"resources" => ["herbs"],
			],
		],
		"allowedColours" => ["blue", "green", "coin", "red", "orange"],
		"nonTouchingColours" => ["red", "orange"],
		"feast" => false,
	],
	"icehouse" => [
		"type" => "icehouse",
		"category" => "shed",
		"rows" => 3,
		"cols" => 5,
		"points" => 8,
		// prettier-ignore
		"negatives" => [
			[0, 0], [0, 1], [0, 2] // fake
		],
		"incomeStarts" => [
			[2, 0]
		],
		"cells" => [
			[
				"coords" => [4, 0],
				"type" => "void",
			],
			[
				"coords" => [4, 1],
				"type" => "void",
			],
			[
				"coords" => [4, 2],
				"type" => "void",
			],
			[
				"coords" => [2, 0],
				"type" => "income",
				"value" => 0,
			],
			[
				"coords" => [3, 1],
				"type" => "income",
				"value" => 0,
			],
			[
				"coords" => [4, 2],
				"type" => "income",
				"value" => 1,
			],
			[
				"coords" => [3, 0],
				"type" => "bonus",
				"resources" => ["oil"],
			],
			[
				"coords" => [0, 0],
				"type" => "unique",
				"uniqueType" => "whalemeat",
			],
			[
				"coords" => [1, 0],
				"type" => "unique",
				"uniqueType" => "whalemeat",
			],
			[
				"coords" => [2, 0],
				"type" => "unique",
				"uniqueType" => "whalemeat",
			],
			[
				"coords" => [0, 1],
				"type" => "unique",
				"uniqueType" => "whalemeat",
			],
			[
				"coords" => [1, 1],
				"type" => "unique",
				"uniqueType" => "whalemeat",
			],
			[
				"coords" => [2, 1],
				"type" => "unique",
				"uniqueType" => "whalemeat",
			],
			[
				"coords" => [0, 2],
				"type" => "unique",
				"uniqueType" => "whalemeat",
			],
			[
				"coords" => [1, 2],
				"type" => "unique",
				"uniqueType" => "whalemeat",
			],
			[
				"coords" => [2, 2],
				"type" => "unique",
				"uniqueType" => "whalemeat",
			],
		],
		"allowedColours" => ["blue", "green", "coin", "red", "orange"],
		"nonTouchingColours" => ["red", "orange"],
		"feast" => false,
		"standalone" => [
			["item" => "stockfish", "value" => -3], 
		],
	],
	"pigsty" => [
		"type" => "pigsty",
		"category" => "shed",
		"rows" => 3,
		"cols" => 4,
		"points" => 6,
		// prettier-ignore
		"negatives" => [
			[0, 0], [2, 0], [3, 0], [3, 0],
			[0, 1] // fake
		],
		"incomeStarts" => [
			[1, 0]
		],
		"cells" => [
			[
				"coords" => [3, 1],
				"type" => "void",
			],
			[
				"coords" => [3, 2],
				"type" => "void",
			],
			[
				"coords" => [1, 0],
				"type" => "income",
				"value" => 0,
			],
			[
				"coords" => [2, 1],
				"type" => "income",
				"value" => 0,
			],
			[
				"coords" => [3, 2],
				"type" => "income",
				"value" => 1,
			],
			[
				"coords" => [1, 1],
				"type" => "bonus",
				"resources" => ["saltmeat"],
			],
			[
				"coords" => [0, 1],
				"type" => "unique",
				"uniqueType" => "pig",
			],
			[
				"coords" => [2, 1],
				"type" => "unique",
				"uniqueType" => "pig",
			],
			[
				"coords" => [0, 2],
				"type" => "unique",
				"uniqueType" => "pig",
			],
			[
				"coords" => [1, 2],
				"type" => "unique",
				"uniqueType" => "pig",
			],
			[
				"coords" => [2, 2],
				"type" => "unique",
				"uniqueType" => "pig",
			],
		],
		"allowedColours" => ["blue", "green", "coin", "red", "orange"],
		"nonTouchingColours" => ["red", "orange"],
		"feast" => false,
		"standalone" => [
			["item" => "beans", "value" => -3], 
		],
	],
	"hunting" => [
		"type" => "hunting",
		"category" => "shed",
		"rows" => 4,
		"cols" => 5,
		"points" => 8,
		// prettier-ignore
		"negatives" => [
			[0, 0], [0, 3], [3, 3]
		],
		"incomeStarts" => [
			[2, 1]
		],
		"cells" => [
			[
				"coords" => [2, 0],
				"type" => "void",
			],
			[
				"coords" => [3, 0],
				"type" => "void",
			],
			[
				"coords" => [4, 0],
				"type" => "void",
			],
			[
				"coords" => [4, 1],
				"type" => "void",
			],
			[
				"coords" => [4, 2],
				"type" => "void",
			],
			[
				"coords" => [2, 3],
				"type" => "void",
			],
			[
				"coords" => [4, 3],
				"type" => "void",
			],
			[
				"coords" => [2, 1],
				"type" => "income",
				"value" => 0,
			],
			[
				"coords" => [3, 2],
				"type" => "income",
				"value" => 0,
			],
			[
				"coords" => [4, 3],
				"type" => "income",
				"value" => 1,
			],
			[
				"coords" => [1, 0],
				"type" => "bonus",
				"resources" => ["peas"],
			],
			[
				"coords" => [3, 1],
				"type" => "bonus",
				"resources" => ["snare"],
			],
			[
				"coords" => [1, 3],
				"type" => "bonus",
				"resources" => ["bow"],
			],
		],
		"allowedColours" => ["blue", "green", "coin", "red", "orange"],
		"nonTouchingColours" => ["red", "orange"],
		"feast" => false,
		"standalone" => [
			["item" => "hide", "value" => -3], 
		],
	],
	"armoury" => [
		"type" => "armoury",
		"category" => "shed",
		"rows" => 4,
		"cols" => 4,
		"points" => 6,
		// prettier-ignore
		"negatives" => [
			[3, 1], [3, 2], [1, 3], [2, 3],
		],
		"incomeStarts" => [
			[1, 1]
		],
		"cells" => [
			[
				"coords" => [0, 0],
				"type" => "void",
			],
			[
				"coords" => [3, 0],
				"type" => "void",
			],
			[
				"coords" => [3, 3],
				"type" => "void",
			],
			[
				"coords" => [1, 1],
				"type" => "income",
				"value" => 0,
			],
			[
				"coords" => [2, 2],
				"type" => "income",
				"value" => 0,
			],
			[
				"coords" => [3, 3],
				"type" => "income",
				"value" => 1,
			],
			[
				"coords" => [2, 1],
				"type" => "bonus",
				"resources" => ["ore"],
			],
			[
				"coords" => [0, 3],
				"type" => "bonus",
				"resources" => ["sword"],
			],
		],
		"allowedColours" => ["blue", "green", "coin", "red", "orange"],
		"nonTouchingColours" => ["red", "orange"],
		"feast" => false,
		"standalone" => [
			["item" => "ore", "value" => -2], 
		],
	],
	"cowshed" => [
		"type" => "cowshed",
		"category" => "shed",
		"rows" => 4,
		"cols" => 5,
		"points" => 8,
		// prettier-ignore
		"negatives" => [
			[1, 0], [2, 0], [4, 0],
			[0, 1], [1, 1], [2, 1], // 3 "fake" negatives that cattle tile will cover
		],
		"incomeStarts" => [
			[3, 0]
		],
		"cells" => [
			[
				"coords" => [4, 1],
				"type" => "void",
			],
			[
				"coords" => [4, 2],
				"type" => "void",
			],
			[
				"coords" => [4, 3],
				"type" => "void",
			],
			[
				"coords" => [3, 0],
				"type" => "income",
				"value" => 0,
			],
			[
				"coords" => [4, 1],
				"type" => "income",
				"value" => 1,
			],
			[
				"coords" => [0, 0],
				"type" => "bonus",
				"resources" => ["milk"],
			],
			[
				"coords" => [0, 1],
				"type" => "unique",
				"uniqueType" => "cattle",
			],
			[
				"coords" => [1, 1],
				"type" => "unique",
				"uniqueType" => "cattle",
			],
			[
				"coords" => [2, 1],
				"type" => "unique",
				"uniqueType" => "cattle",
			],
			[
				"coords" => [3, 1],
				"type" => "unique",
				"uniqueType" => "cattle",
			],
			[
				"coords" => [0, 2],
				"type" => "unique",
				"uniqueType" => "cattle",
			],
			[
				"coords" => [1, 2],
				"type" => "unique",
				"uniqueType" => "cattle",
			],
			[
				"coords" => [2, 2],
				"type" => "unique",
				"uniqueType" => "cattle",
			],
			[
				"coords" => [3, 2],
				"type" => "unique",
				"uniqueType" => "cattle",
			],
			[
				"coords" => [0, 3],
				"type" => "unique",
				"uniqueType" => "cattle",
			],
			[
				"coords" => [1, 3],
				"type" => "unique",
				"uniqueType" => "cattle",
			],
			[
				"coords" => [2, 3],
				"type" => "unique",
				"uniqueType" => "cattle",
			],
			[
				"coords" => [3, 3],
				"type" => "unique",
				"uniqueType" => "cattle",
			],
		],
		"allowedColours" => ["blue", "green", "coin", "red", "orange"],
		"nonTouchingColours" => ["red", "orange"],
		"feast" => false,
	],
	"lumberjack" => [
		"type" => "lumberjack",
		"category" => "shed",
		"rows" => 3,
		"cols" => 4,
		"points" => 7,
		// prettier-ignore
		"negatives" => [
			[2, 0], [3, 0], [0, 2], [1, 2], [2, 2]
		],
		"incomeStarts" => [
			[1, 0],
		],
		"cells" => [
			[
				"coords" => [3, 2],
				"type" => "void",
			],
			[
				"coords" => [1, 0],
				"type" => "income",
				"value" => 0,
			],
			[
				"coords" => [2, 1],
				"type" => "income",
				"value" => 0,
			],
			[
				"coords" => [3, 2],
				"type" => "income",
				"value" => 1,
			],
			[
				"coords" => [0, 0],
				"type" => "bonus",
				"resources" => ["wood"],
			],
			[
				"coords" => [3, 1],
				"type" => "bonus",
				"resources" => ["weapon"],
			],
		],
		"allowedColours" => ["blue", "green", "coin", "red", "orange"],
		"nonTouchingColours" => ["red", "orange"],
		"feast" => false,
		"standalone" => [
			["item" => "wood", "value" => -1], 
			["item" => "wood", "value" => -1], 
		],
	],
	"barn" => [
		"type" => "barn",
		"category" => "shed",
		"rows" => 3,
		"cols" => 6,
		"points" => 6,
		// prettier-ignore
		"negatives" => [
			[0, 1], [1, 1], [2, 1], [3, 1], [4, 1], [0, 2], // 6 "fake" negatives that horse tile will cover
		],
		"incomeStarts" => [
			[3, 0],
		],
		"cells" => [
			[
				"coords" => [0, 0],
				"type" => "void",
			],
			[
				"coords" => [1, 0],
				"type" => "void",
			],
			[
				"coords" => [5, 1],
				"type" => "void",
			],
			[
				"coords" => [5, 2],
				"type" => "void",
			],
			[
				"coords" => [3, 0],
				"type" => "income",
				"value" => 0,
			],
			[
				"coords" => [4, 1],
				"type" => "income",
				"value" => 0,
			],
			[
				"coords" => [5, 2],
				"type" => "income",
				"value" => 2,
			],
			[
				"coords" => [2, 0],
				"type" => "bonus",
				"resources" => ["grain"],
			],
			[
				"coords" => [0, 1],
				"type" => "unique",
				"uniqueType" => "horse",
			],
			[
				"coords" => [1, 1],
				"type" => "unique",
				"uniqueType" => "horse",
			],
			[
				"coords" => [2, 1],
				"type" => "unique",
				"uniqueType" => "horse",
			],
			[
				"coords" => [3, 1],
				"type" => "unique",
				"uniqueType" => "horse",
			],
			[
				"coords" => [4, 1],
				"type" => "unique",
				"uniqueType" => "horse",
			],
			[
				"coords" => [0, 2],
				"type" => "unique",
				"uniqueType" => "horse",
			],
			[
				"coords" => [1, 2],
				"type" => "unique",
				"uniqueType" => "horse",
			],
			[
				"coords" => [2, 2],
				"type" => "unique",
				"uniqueType" => "horse",
			],
			[
				"coords" => [3, 2],
				"type" => "unique",
				"uniqueType" => "horse",
			],
			[
				"coords" => [4, 2],
				"type" => "unique",
				"uniqueType" => "horse",
			],


		],
		"allowedColours" => ["blue", "green", "coin", "red", "orange"],
		"nonTouchingColours" => ["red", "orange"],
		"feast" => false,
	],
	// Norwegian islands
	"isleofman" => [
		"type" => "isleofman",
		"category" => "island",
		"vikings" => 1,
		"rows" => 9,
		"cols" => 10,
		"points" => 10,
		"_income" => 0,
		// prettier-ignore
		"negatives" => [
			[2, 4],
			[3, 5], [3, 6],
			[4, 5], [4, 7], [4, 8], [4, 8],
			[5, 6], [5, 8],
			[6, 5], [6, 6], [6, 7], [6, 8],
			[7, 7], [7, 8],
			[8, 6], [8, 7], [8, 8],
			[9, 6], [9, 7],
		],
		"incomeStarts" => [
			[4, 0],
		],
		"cells" => [
			[
				"coords" => [0, 1],
				"type" => "void",
			],
			[
				"coords" => [0, 2],
				"type" => "void",
			],
			[
				"coords" => [0, 3],
				"type" => "void",
			],
			[
				"coords" => [0, 4],
				"type" => "void",
			],
			[
				"coords" => [0, 5],
				"type" => "void",
			],
			[
				"coords" => [0, 6],
				"type" => "void",
			],
			[
				"coords" => [0, 7],
				"type" => "void",
			],
			[
				"coords" => [0, 8],
				"type" => "void",
			],
			[
				"coords" => [1, 4],
				"type" => "void",
			],
			[
				"coords" => [1, 5],
				"type" => "void",
			],
			[
				"coords" => [1, 6],
				"type" => "void",
			],
			[
				"coords" => [1, 7],
				"type" => "void",
			],
			[
				"coords" => [1, 8],
				"type" => "void",
			],
			[
				"coords" => [2, 5],
				"type" => "void",
			],
			[
				"coords" => [2, 6],
				"type" => "void",
			],
			[
				"coords" => [2, 7],
				"type" => "void",
			],
			[
				"coords" => [2, 8],
				"type" => "void",
			],
			[
				"coords" => [3, 7],
				"type" => "void",
			],
			[
				"coords" => [3, 8],
				"type" => "void",
			],
			[
				"coords" => [5, 0],
				"type" => "void",
			],
			[
				"coords" => [6, 0],
				"type" => "void",
			],
			[
				"coords" => [6, 1],
				"type" => "void",
			],
			[
				"coords" => [7, 0],
				"type" => "void",
			],
			[
				"coords" => [7, 1],
				"type" => "void",
			],
			[
				"coords" => [7, 2],
				"type" => "void",
			],
			[
				"coords" => [8, 0],
				"type" => "void",
			],
			[
				"coords" => [8, 1],
				"type" => "void",
			],
			[
				"coords" => [8, 2],
				"type" => "void",
			],
			[
				"coords" => [8, 3],
				"type" => "void",
			],
			[
				"coords" => [9, 0],
				"type" => "void",
			],
			[
				"coords" => [9, 1],
				"type" => "void",
			],
			[
				"coords" => [9, 2],
				"type" => "void",
			],
			[
				"coords" => [9, 3],
				"type" => "void",
			],
			[
				"coords" => [9, 4],
				"type" => "void",
			],
			[
				"coords" => [9, 5],
				"type" => "void",
			],
			[
				"coords" => [9, 8],
				"type" => "void",
			],
			[
				"coords" => [4, 0],
				"type" => "income",
				"value" => 0,
			],
			[
				"coords" => [5, 1],
				"type" => "income",
				"value" => 2,
			],
			[
				"coords" => [6, 2],
				"type" => "income",
				"value" => 3,
			],
			[
				"coords" => [7, 3],
				"type" => "income",
				"value" => 4,
			],
			[
				"coords" => [8, 4],
				"type" => "income",
				"value" => 5,
			],
			[
				"coords" => [9, 5],
				"type" => "income",
				"value" => 6,
			],
			[
				"coords" => [0, 0],
				"type" => "bonus",
				"resources" => ["mead"],
			],
			[
				"coords" => [4, 2],
				"type" => "bonus",
				"resources" => ["stone"],
			],
			[
				"coords" => [4, 6],
				"type" => "bonus",
				"resources" => ["grain"],
			],
			[
				"coords" => [7, 5],
				"type" => "bonus",
				"resources" => ["pig"],
			],
		],
		"allowedColours" => ["blue", "green", "coin", "ore"],
		"nonTouchingColours" => ["green"],
		"feast" => false,
	],
	"limerick" => [
		"type" => "limerick",
		"category" => "island",
		"vikings" => 1,
		"rows" => 9,
		"cols" => 8,
		"points" => 10,
		"_income" => 0,
		// prettier-ignore
		"negatives" => [
			[0, 0], [0, 3], [0, 5], [0, 6], [0, 6],
			[1, 1], [1, 3], [1, 4],
			[2, 6], [2, 7], [2, 8],
			[3, 4], [3, 6], [3, 7], [3, 8],
			[4, 5], [4, 6], [4, 7], [4, 8],
			[5, 5], [5, 6], [5, 7], [5, 8],
			[6, 4], [6, 5], [6, 6],
		],
		"incomeStarts" => [
			[3, 0],
		],
		"cells" => [
			[
				"coords" => [0, 7],
				"type" => "void",
			],
			[
				"coords" => [0, 8],
				"type" => "void",
			],
			[
				"coords" => [1, 5],
				"type" => "void",
			],
			[
				"coords" => [1, 6],
				"type" => "void",
			],
			[
				"coords" => [1, 7],
				"type" => "void",
			],
			[
				"coords" => [1, 8],
				"type" => "void",
			],
			[
				"coords" => [2, 0],
				"type" => "void",
			],
			[
				"coords" => [2, 1],
				"type" => "void",
			],
			[
				"coords" => [2, 2],
				"type" => "void",
			],
			[
				"coords" => [2, 3],
				"type" => "void",
			],
			[
				"coords" => [2, 4],
				"type" => "void",
			],
			[
				"coords" => [2, 5],
				"type" => "void",
			],
			[
				"coords" => [6, 8],
				"type" => "void",
			],
			[
				"coords" => [7, 0],
				"type" => "void",
			],
			[
				"coords" => [7, 1],
				"type" => "void",
			],
			[
				"coords" => [7, 2],
				"type" => "void",
			],
			[
				"coords" => [7, 3],
				"type" => "void",
			],
			[
				"coords" => [7, 4],
				"type" => "void",
			],
			[
				"coords" => [7, 5],
				"type" => "void",
			],
			[
				"coords" => [7, 6],
				"type" => "void",
			],
			[
				"coords" => [7, 7],
				"type" => "void",
			],
			[
				"coords" => [7, 8],
				"type" => "void",
			],
			[
				"coords" => [3, 0],
				"type" => "income",
				"value" => 0,
			],
			[
				"coords" => [4, 1],
				"type" => "income",
				"value" => 1,
			],
			[
				"coords" => [5, 2],
				"type" => "income",
				"value" => 2,
			],
			[
				"coords" => [6, 3],
				"type" => "income",
				"value" => 3,
			],
			[
				"coords" => [7, 4],
				"type" => "income",
				"value" => 4,
			],
			[
				"coords" => [0, 4],
				"type" => "bonus",
				"resources" => ["oil"],
			],
			[
				"coords" => [3, 5],
				"type" => "bonus",
				"resources" => ["gamemeat"],
			],
			[
				"coords" => [5, 4],
				"type" => "bonus",
				"resources" => ["tools"],
			],
			[
				"coords" => [6, 7],
				"type" => "bonus",
				"resources" => ["ore"],
			],
		],
		"allowedColours" => ["blue", "green", "coin", "ore"],
		"nonTouchingColours" => ["green"],
		"feast" => false,
	],
	"isleofskye" => [
		"type" => "isleofskye",
		"category" => "island",
		"vikings" => 1,
		"rows" => 9,
		"cols" => 10,
		"points" => 10,
		"_income" => 0,
		// prettier-ignore
		"negatives" => [
			[1, 8], [1, 8],
			[4, 8],
			[5, 1], [5, 2],
			[6, 0], [6, 1], [6, 2], [6, 3], [6, 4], [6, 6], [6, 7], [6, 7],
			[7, 0], [7, 1],
			[8, 2], [8, 4], [8, 5], [8, 6], [8, 8],
			[9, 3], [9, 3], [9, 6], [9, 7], [8, 8], 
		],
		"incomeStarts" => [
			[1, 4],
		],
		"cells" => [
			[
				"coords" => [0, 0],
				"type" => "void",
			],
			[
				"coords" => [0, 1],
				"type" => "void",
			],
			[
				"coords" => [0, 2],
				"type" => "void",
			],
			[
				"coords" => [0, 3],
				"type" => "void",
			],
			[
				"coords" => [0, 8],
				"type" => "void",
			],
			[
				"coords" => [1, 0],
				"type" => "void",
			],
			[
				"coords" => [1, 1],
				"type" => "void",
			],
			[
				"coords" => [1, 2],
				"type" => "void",
			],
			[
				"coords" => [1, 3],
				"type" => "void",
			],
			[
				"coords" => [2, 0],
				"type" => "void",
			],
			[
				"coords" => [2, 1],
				"type" => "void",
			],
			[
				"coords" => [2, 2],
				"type" => "void",
			],
			[
				"coords" => [2, 4],
				"type" => "void",
			],
			[
				"coords" => [2, 8],
				"type" => "void",
			],
			[
				"coords" => [3, 0],
				"type" => "void",
			],
			[
				"coords" => [3, 1],
				"type" => "void",
			],
			[
				"coords" => [4, 0],
				"type" => "void",
			],
			[
				"coords" => [5, 0],
				"type" => "void",
			],
			[
				"coords" => [5, 5],
				"type" => "void",
			],
			[
				"coords" => [5, 6],
				"type" => "void",
			],
			[
				"coords" => [5, 7],
				"type" => "void",
			],
			[
				"coords" => [5, 8],
				"type" => "void",
			],
			[
				"coords" => [6, 8],
				"type" => "void",
			],
			[
				"coords" => [7, 3],
				"type" => "void",
			],
			[
				"coords" => [7, 4],
				"type" => "void",
			],
			[
				"coords" => [7, 5],
				"type" => "void",
			],
			[
				"coords" => [7, 6],
				"type" => "void",
			],
			[
				"coords" => [7, 7],
				"type" => "void",
			],
			[
				"coords" => [7, 8],
				"type" => "void",
			],
			[
				"coords" => [8, 0],
				"type" => "void",
			],
			[
				"coords" => [8, 3],
				"type" => "void",
			],
			[
				"coords" => [9, 0],
				"type" => "void",
			],
			[
				"coords" => [9, 1],
				"type" => "void",
			],
			[
				"coords" => [9, 4],
				"type" => "void",
			],
			[
				"coords" => [1, 4],
				"type" => "income",
				"value" => 0,
			],
			[
				"coords" => [2, 5],
				"type" => "income",
				"value" => 1,
			],
			[
				"coords" => [3, 6],
				"type" => "income",
				"value" => 2,
			],
			[
				"coords" => [4, 7],
				"type" => "income",
				"value" => 3,
			],
			[
				"coords" => [5, 8],
				"type" => "income",
				"value" => 4,
			],
			[
				"coords" => [3, 2],
				"type" => "bonus",
				"resources" => ["rune"],
			],
			[
				"coords" => [3, 8],
				"type" => "bonus",
				"resources" => ["wool"],
			],
			[
				"coords" => [5, 4],
				"type" => "bonus",
				"resources" => ["stockfish"],
			],
			[
				"coords" => [8, 1],
				"type" => "bonus",
				"resources" => ["flax"],
			],
			[
				"coords" => [9, 5],
				"type" => "bonus",
				"resources" => ["wood"],
			],
		],
		"allowedColours" => ["blue", "green", "coin", "ore"],
		"nonTouchingColours" => ["green"],
		"feast" => false,
	],
	"wexford" => [
		"type" => "wexford",
		"category" => "island",
		"vikings" => 2,
		"rows" => 9,
		"cols" => 10,
		"points" => 15,
		"_income" => 0,
		// prettier-ignore
		"negatives" => [
			[0, 6], [0, 7], [0, 8],
			[1, 6], [1, 8],
			[2, 7], [2, 8],
			[3, 7], [3, 8],
			[4, 7], 
			[5, 2], [5, 3],
			[6, 0], [6, 2], [6, 3], [6, 3],
			[7, 0], [7, 1], [7, 2], [7, 7], [7, 8],
			[8, 0], [8, 7], [8, 8],
			[9, 0], [9, 0],
		],
		"incomeStarts" => [
			[0, 0],
		],
		"cells" => [
			[
				"coords" => [4, 8],
				"type" => "void",
			],
			[
				"coords" => [5, 4],
				"type" => "void",
			],
			[
				"coords" => [5, 6],
				"type" => "void",
			],
			[
				"coords" => [5, 7],
				"type" => "void",
			],
			[
				"coords" => [5, 8],
				"type" => "void",
			],
			[
				"coords" => [6, 4],
				"type" => "void",
			],
			[
				"coords" => [6, 5],
				"type" => "void",
			],
			[
				"coords" => [6, 6],
				"type" => "void",
			],
			[
				"coords" => [6, 7],
				"type" => "void",
			],
			[
				"coords" => [6, 8],
				"type" => "void",
			],
			[
				"coords" => [7, 3],
				"type" => "void",
			],
			[
				"coords" => [7, 4],
				"type" => "void",
			],
			[
				"coords" => [7, 5],
				"type" => "void",
			],
			[
				"coords" => [7, 6],
				"type" => "void",
			],
			[
				"coords" => [8, 1],
				"type" => "void",
			],
			[
				"coords" => [8, 2],
				"type" => "void",
			],
			[
				"coords" => [8, 3],
				"type" => "void",
			],
			[
				"coords" => [8, 4],
				"type" => "void",
			],
			[
				"coords" => [8, 5],
				"type" => "void",
			],
			[
				"coords" => [8, 6],
				"type" => "void",
			],
			[
				"coords" => [9, 1],
				"type" => "void",
			],
			[
				"coords" => [9, 2],
				"type" => "void",
			],
			[
				"coords" => [9, 3],
				"type" => "void",
			],
			[
				"coords" => [9, 4],
				"type" => "void",
			],
			[
				"coords" => [9, 5],
				"type" => "void",
			],
			[
				"coords" => [9, 6],
				"type" => "void",
			],
			[
				"coords" => [9, 7],
				"type" => "void",
			],
			[
				"coords" => [9, 8],
				"type" => "void",
			],
			[
				"coords" => [0, 0],
				"type" => "income",
				"value" => 0,
			],
			[
				"coords" => [1, 1],
				"type" => "income",
				"value" => 1,
			],
			[
				"coords" => [2, 2],
				"type" => "income",
				"value" => 2,
			],
			[
				"coords" => [3, 3],
				"type" => "income",
				"value" => 3,
			],
			[
				"coords" => [4, 4],
				"type" => "income",
				"value" => 4,
			],
			[
				"coords" => [5, 5],
				"type" => "income",
				"value" => 5,
			],
			[
				"coords" => [6, 6],
				"type" => "income",
				"value" => 6,
			],
			[
				"coords" => [1, 7],
				"type" => "bonus",
				"resources" => ["silverware"],
			],
			[
				"coords" => [3, 5],
				"type" => "bonus",
				"resources" => ["horse"],
			],
			[
				"coords" => [6, 1],
				"type" => "bonus",
				"resources" => ["cabbage"],
			],
		],
		"allowedColours" => ["blue", "green", "coin", "ore"],
		"nonTouchingColours" => ["green"],
		"feast" => false,
	],
	"outerhebrides" => [
		"type" => "outerhebrides",
		"category" => "island",
		"vikings" => 2,
		"rows" => 9,
		"cols" => 10,
		"points" => 13,
		"_income" => 0,
		// prettier-ignore
		"negatives" => [
			[0, 3], [0, 4],
			[1, 5],
			[2, 2], [2, 3], [2, 5],
			[3, 6],
			[4, 3], [4, 4], [4, 5], [4, 7],
			[5, 6], [5, 7],
			[6, 6], [6, 7], [6, 8],
			[7, 2], [7, 3], [7, 8],
			[8, 5], [8, 8],
			[9, 5], [9, 5], 
		],
		"incomeStarts" => [
			[2, 0],
		],
		"cells" => [
			[
				"coords" => [0, 0],
				"type" => "void",
			],
			[
				"coords" => [0, 1],
				"type" => "void",
			],
			[
				"coords" => [0, 2],
				"type" => "void",
			],
			[
				"coords" => [0, 5],
				"type" => "void",
			],
			[
				"coords" => [0, 6],
				"type" => "void",
			],
			[
				"coords" => [0, 7],
				"type" => "void",
			],
			[
				"coords" => [0, 8],
				"type" => "void",
			],
			[
				"coords" => [1, 6],
				"type" => "void",
			],
			[
				"coords" => [1, 7],
				"type" => "void",
			],
			[
				"coords" => [1, 8],
				"type" => "void",
			],
			[
				"coords" => [2, 6],
				"type" => "void",
			],
			[
				"coords" => [2, 7],
				"type" => "void",
			],
			[
				"coords" => [2, 8],
				"type" => "void",
			],
			[
				"coords" => [3, 7],
				"type" => "void",
			],
			[
				"coords" => [3, 8],
				"type" => "void",
			],
			[
				"coords" => [4, 8],
				"type" => "void",
			],
			[
				"coords" => [5, 8],
				"type" => "void",
			],
			[
				"coords" => [7, 0],
				"type" => "void",
			],
			[
				"coords" => [8, 0],
				"type" => "void",
			],
			[
				"coords" => [8, 1],
				"type" => "void",
			],
			[
				"coords" => [8, 2],
				"type" => "void",
			],
			[
				"coords" => [8, 3],
				"type" => "void",
			],
			[
				"coords" => [8, 4],
				"type" => "void",
			],
			[
				"coords" => [9, 0],
				"type" => "void",
			],
			[
				"coords" => [9, 1],
				"type" => "void",
			],
			[
				"coords" => [9, 2],
				"type" => "void",
			],
			[
				"coords" => [9, 3],
				"type" => "void",
			],
			[
				"coords" => [9, 4],
				"type" => "void",
			],
			[
				"coords" => [9, 6],
				"type" => "void",
			],
			[
				"coords" => [9, 7],
				"type" => "void",
			],
			[
				"coords" => [9, 8],
				"type" => "void",
			],
			[
				"coords" => [2, 0],
				"type" => "income",
				"value" => 0,
			],
			[
				"coords" => [3, 1],
				"type" => "income",
				"value" => 1,
			],
			[
				"coords" => [4, 2],
				"type" => "income",
				"value" => 2,
			],
			[
				"coords" => [5, 3],
				"type" => "income",
				"value" => 3,
			],
			[
				"coords" => [6, 4],
				"type" => "income",
				"value" => 4,
			],
			[
				"coords" => [7, 5],
				"type" => "income",
				"value" => 5,
			],
			[
				"coords" => [8, 6],
				"type" => "income",
				"value" => 6,
			],
			[
				"coords" => [9, 7],
				"type" => "income",
				"value" => 7,
			],
			[
				"coords" => [3, 3],
				"type" => "bonus",
				"resources" => ["oil"],
			],
			[
				"coords" => [4, 6],
				"type" => "bonus",
				"resources" => ["herbs"],
			],
			[
				"coords" => [6, 2],
				"type" => "bonus",
				"resources" => ["stone"],
			],
			[
				"coords" => [7, 7],
				"type" => "bonus",
				"resources" => ["sheep"],
			],
		],
		"allowedColours" => ["blue", "green", "coin", "ore"],
		"nonTouchingColours" => ["green"],
		"feast" => false,
	],
	"cork" => [
		"type" => "cork",
		"category" => "island",
		"vikings" => 3,
		"rows" => 8,
		"cols" => 10,
		"points" => 30,
		"_income" => 0,
		// prettier-ignore
		"negatives" => [
			[0, 6], [0, 7],
			[1, 1], [1, 7],
			[2, 1], [2, 6], [2, 7],
			[3, 1], [3, 2], [3, 4], [3, 6], [3, 7],
			[4, 4], [4, 6], 
			[5, 1], [5, 3], [5, 6], [5, 7],
			[6, 0], [6, 2], [6, 3], [6, 5], [6, 7],
			[7, 0], [7, 1], [7, 3], [7, 5], [7, 7],
			[8, 1], [8, 3], [8, 3], [8, 5], [8, 6],
			[9, 0], [9, 2], [9, 2], [9, 5], [9, 6], [9, 7],
		],
		"incomeStarts" => [],
		"cells" => [
			[
				"coords" => [0, 0],
				"type" => "void",
			],
			[
				"coords" => [0, 1],
				"type" => "void",
			],
			[
				"coords" => [0, 5],
				"type" => "void",
			],
			[
				"coords" => [1, 0],
				"type" => "void",
			],
			[
				"coords" => [1, 5],
				"type" => "void",
			],
			[
				"coords" => [2, 0],
				"type" => "void",
			],
			[
				"coords" => [2, 5],
				"type" => "void",
			],
			[
				"coords" => [3, 0],
				"type" => "void",
			],
			[
				"coords" => [3, 5],
				"type" => "void",
			],
			[
				"coords" => [4, 0],
				"type" => "void",
			],
			[
				"coords" => [4, 5],
				"type" => "void",
			],
			[
				"coords" => [5, 0],
				"type" => "void",
			],
			[
				"coords" => [5, 4],
				"type" => "void",
			],
			[
				"coords" => [5, 5],
				"type" => "void",
			],
			[
				"coords" => [6, 1],
				"type" => "void",
			],
			[
				"coords" => [6, 4],
				"type" => "void",
			],
			[
				"coords" => [7, 2],
				"type" => "void",
			],
			[
				"coords" => [7, 4],
				"type" => "void",
			],
			[
				"coords" => [8, 2],
				"type" => "void",
			],
			[
				"coords" => [8, 4],
				"type" => "void",
			],
			[
				"coords" => [9, 3],
				"type" => "void",
			],
			[
				"coords" => [9, 4],
				"type" => "void",
			],
			[
				"coords" => [1, 3],
				"type" => "bonus",
				"resources" => ["leather"],
			],
			[
				"coords" => [5, 2],
				"type" => "bonus",
				"resources" => ["whalingboat"],
			],
			[
				"coords" => [7, 6],
				"type" => "bonus",
				"resources" => ["shed"],
			],
			[
				"coords" => [8, 0],
				"type" => "bonus",
				"resources" => ["milk"],
			],
		],
		"allowedColours" => ["blue", "green", "coin", "ore"],
		"nonTouchingColours" => ["green"],
		"feast" => false,
	],
	"islay" => [
		"type" => "islay",
		"category" => "island",
		"vikings" => 2,
		"rows" => 9,
		"cols" => 10,
		"points" => 15,
		"_income" => 0,
		// prettier-ignore
		"negatives" => [
			[1, 5], [1, 6], [1, 7],
			[2, 5], [2, 7], [2, 8], [2, 8],
			[3, 6], [3, 7], 
			[4, 5], [4, 8],
			[5, 5], [5, 8],
			[6, 6], [6, 7],
			[7, 5], 
			[8, 1], [8, 2], [8, 3], [8, 4], [8, 7], [8, 8],
			[9, 6], [9, 7], [9, 8],
		],
		"incomeStarts" => [
			[3, 0],
		],
		"cells" => [
			[
				"coords" => [0, 0],
				"type" => "void",
			],
			[
				"coords" => [0, 1],
				"type" => "void",
			],
			[
				"coords" => [0, 2],
				"type" => "void",
			],
			[
				"coords" => [0, 5],
				"type" => "void",
			],
			[
				"coords" => [0, 6],
				"type" => "void",
			],
			[
				"coords" => [0, 7],
				"type" => "void",
			],
			[
				"coords" => [0, 8],
				"type" => "void",
			],
			[
				"coords" => [1, 0],
				"type" => "void",
			],
			[
				"coords" => [1, 1],
				"type" => "void",
			],
			[
				"coords" => [1, 2],
				"type" => "void",
			],
			[
				"coords" => [1, 8],
				"type" => "void",
			],
			[
				"coords" => [2, 0],
				"type" => "void",
			],
			[
				"coords" => [2, 1],
				"type" => "void",
			],
			[
				"coords" => [2, 2],
				"type" => "void",
			],
			[
				"coords" => [2, 3],
				"type" => "void",
			],
			[
				"coords" => [2, 4],
				"type" => "void",
			],
			[
				"coords" => [3, 2],
				"type" => "void",
			],
			[
				"coords" => [3, 3],
				"type" => "void",
			],
			[
				"coords" => [3, 4],
				"type" => "void",
			],
			[
				"coords" => [3, 5],
				"type" => "void",
			],
			[
				"coords" => [3, 8],
				"type" => "void",
			],
			[
				"coords" => [7, 0],
				"type" => "void",
			],
			[
				"coords" => [7, 6],
				"type" => "void",
			],
			[
				"coords" => [7, 7],
				"type" => "void",
			],
			[
				"coords" => [7, 8],
				"type" => "void",
			],
			[
				"coords" => [8, 0],
				"type" => "void",
			],
			[
				"coords" => [8, 5],
				"type" => "void",
			],
			[
				"coords" => [9, 0],
				"type" => "void",
			],
			[
				"coords" => [9, 1],
				"type" => "void",
			],
			[
				"coords" => [9, 2],
				"type" => "void",
			],
			[
				"coords" => [9, 3],
				"type" => "void",
			],
			[
				"coords" => [9, 4],
				"type" => "void",
			],
			[
				"coords" => [9, 5],
				"type" => "void",
			],
			[
				"coords" => [3, 0],
				"type" => "income",
				"value" => 0,
			],
			[
				"coords" => [4, 1],
				"type" => "income",
				"value" => 1,
			],
			[
				"coords" => [5, 2],
				"type" => "income",
				"value" => 2,
			],
			[
				"coords" => [6, 3],
				"type" => "income",
				"value" => 3,
			],
			[
				"coords" => [7, 4],
				"type" => "income",
				"value" => 4,
			],
			[
				"coords" => [8, 5],
				"type" => "income",
				"value" => 5,
			],
			[
				"coords" => [2, 6],
				"type" => "bonus",
				"resources" => ["milk"],
			],
			[
				"coords" => [5, 7],
				"type" => "bonus",
				"resources" => ["antlers"],
			],
			[
				"coords" => [6, 5],
				"type" => "bonus",
				"resources" => ["ore"],
			],
			[
				"coords" => [7, 2],
				"type" => "bonus",
				"resources" => ["ore"],
			],
		],
		"allowedColours" => ["blue", "green", "coin", "ore"],
		"nonTouchingColours" => ["green"],
		"feast" => false,
	],
	"waterford" => [
		"type" => "waterford",
		"category" => "island",
		"vikings" => 3,
		"rows" => 9,
		"cols" => 10,
		"points" => 31,
		"_income" => 0,
		// prettier-ignore
		"negatives" => [
			[0, 0], [0, 1], [0, 2], [0, 3], [0, 7], [0, 8],
			[1, 0], [1, 1], [1, 2], [1, 3], [1, 7], [1, 8],
			[2, 0], [2, 3], [2, 5], [2, 7],
			[3, 0], [3, 5], [3, 6], [3, 7], 
			[4, 0], [4, 5], [4, 6],
			[5, 0], [5, 3], 
			[6, 5],
			[7, 0], [7, 1], [7, 2], [7, 2], [7, 4], [7, 5],
			[8, 0], [8, 1], [8, 3], [8, 4],
			[9, 0], [9, 1], [9, 3], [9, 3],
		],
		"incomeStarts" => [],
		"cells" => [
			[
				"coords" => [2, 8],
				"type" => "void",
			],
			[
				"coords" => [3, 8],
				"type" => "void",
			],
			[
				"coords" => [4, 7],
				"type" => "void",
			],
			[
				"coords" => [4, 8],
				"type" => "void",
			],
			[
				"coords" => [5, 7],
				"type" => "void",
			],
			[
				"coords" => [5, 8],
				"type" => "void",
			],
			[
				"coords" => [6, 0],
				"type" => "void",
			],
			[
				"coords" => [6, 1],
				"type" => "void",
			],
			[
				"coords" => [6, 2],
				"type" => "void",
			],
			[
				"coords" => [6, 3],
				"type" => "void",
			],
			[
				"coords" => [6, 6],
				"type" => "void",
			],
			[
				"coords" => [6, 7],
				"type" => "void",
			],
			[
				"coords" => [6, 8],
				"type" => "void",
			],
			[
				"coords" => [7, 3],
				"type" => "void",
			],
			[
				"coords" => [7, 6],
				"type" => "void",
			],
			[
				"coords" => [7, 7],
				"type" => "void",
			],
			[
				"coords" => [7, 8],
				"type" => "void",
			],
			[
				"coords" => [8, 2],
				"type" => "void",
			],
			[
				"coords" => [8, 5],
				"type" => "void",
			],
			[
				"coords" => [8, 6],
				"type" => "void",
			],
			[
				"coords" => [8, 7],
				"type" => "void",
			],
			[
				"coords" => [8, 8],
				"type" => "void",
			],
			[
				"coords" => [9, 2],
				"type" => "void",
			],
			[
				"coords" => [9, 4],
				"type" => "void",
			],
			[
				"coords" => [9, 5],
				"type" => "void",
			],
			[
				"coords" => [9, 6],
				"type" => "void",
			],
			[
				"coords" => [9, 7],
				"type" => "void",
			],
			[
				"coords" => [9, 8],
				"type" => "void",
			],
			[
				"coords" => [1, 5],
				"type" => "bonus",
				"resources" => ["wadmal"],
			],
			[
				"coords" => [3, 2],
				"type" => "bonus",
				"resources" => ["longhouse"],
			],
			[
				"coords" => [4, 2],
				"type" => "bonus",
				"resources" => ["longhouse"],
			],
			[
				"coords" => [5, 5],
				"type" => "bonus",
				"resources" => ["knarr"],
			],
			[
				"coords" => [5, 6],
				"type" => "bonus",
				"resources" => ["knarr"],
			],
		],
		"allowedColours" => ["blue", "green", "coin", "ore"],
		"nonTouchingColours" => ["green"],
		"feast" => false,
	],
	"isleofmull" => [
		"type" => "isleofmull",
		"category" => "island",
		"vikings" => 2,
		"rows" => 9,
		"cols" => 10,
		"points" => 9,
		"_income" => 0,
		// prettier-ignore
		"negatives" => [
			[1, 0],
			[2, 5], [2, 5], [2, 7], [2, 7],
			[3, 7], [3, 8], [3, 8],
			[4, 3], [4, 5], [4, 6], [4, 7], [4, 8],
			[5, 3], [5, 6], [5, 7],
			[6, 0], [6, 6], [6, 7],
			[8, 1], [8, 3], [8, 4], [8, 4],
			[9, 3], 
		],
		"incomeStarts" => [
			[3, 0],
		],
		"cells" => [
			[
				"coords" => [0, 0],
				"type" => "void",
			],
			[
				"coords" => [0, 2],
				"type" => "void",
			],
			[
				"coords" => [0, 3],
				"type" => "void",
			],
			[
				"coords" => [0, 4],
				"type" => "void",
			],
			[
				"coords" => [0, 5],
				"type" => "void",
			],
			[
				"coords" => [0, 6],
				"type" => "void",
			],
			[
				"coords" => [0, 7],
				"type" => "void",
			],
			[
				"coords" => [0, 8],
				"type" => "void",
			],
			[
				"coords" => [1, 2],
				"type" => "void",
			],
			[
				"coords" => [1, 3],
				"type" => "void",
			],
			[
				"coords" => [1, 4],
				"type" => "void",
			],
			[
				"coords" => [1, 5],
				"type" => "void",
			],
			[
				"coords" => [1, 6],
				"type" => "void",
			],
			[
				"coords" => [1, 7],
				"type" => "void",
			],
			[
				"coords" => [1, 8],
				"type" => "void",
			],
			[
				"coords" => [2, 2],
				"type" => "void",
			],
			[
				"coords" => [2, 3],
				"type" => "void",
			],
			[
				"coords" => [2, 4],
				"type" => "void",
			],
			[
				"coords" => [2, 6],
				"type" => "void",
			],
			[
				"coords" => [2, 8],
				"type" => "void",
			],
			[
				"coords" => [3, 2],
				"type" => "void",
			],
			[
				"coords" => [3, 4],
				"type" => "void",
			],
			[
				"coords" => [3, 6],
				"type" => "void",
			],
			[
				"coords" => [4, 2],
				"type" => "void",
			],
			[
				"coords" => [4, 4],
				"type" => "void",
			],
			[
				"coords" => [5, 2],
				"type" => "void",
			],
			[
				"coords" => [5, 5],
				"type" => "void",
			],
			[
				"coords" => [6, 8],
				"type" => "void",
			],
			[
				"coords" => [7, 0],
				"type" => "void",
			],
			[
				"coords" => [7, 5],
				"type" => "void",
			],
			[
				"coords" => [7, 6],
				"type" => "void",
			],
			[
				"coords" => [7, 7],
				"type" => "void",
			],
			[
				"coords" => [7, 8],
				"type" => "void",
			],
			[
				"coords" => [8, 0],
				"type" => "void",
			],
			[
				"coords" => [8, 6],
				"type" => "void",
			],
			[
				"coords" => [8, 7],
				"type" => "void",
			],
			[
				"coords" => [8, 8],
				"type" => "void",
			],
			[
				"coords" => [9, 0],
				"type" => "void",
			],
			[
				"coords" => [9, 1],
				"type" => "void",
			],
			[
				"coords" => [9, 4],
				"type" => "void",
			],
			[
				"coords" => [9, 5],
				"type" => "void",
			],
			[
				"coords" => [9, 6],
				"type" => "void",
			],
			[
				"coords" => [9, 7],
				"type" => "void",
			],
			[
				"coords" => [9, 8],
				"type" => "void",
			],
			[
				"coords" => [0, 1],
				"type" => "bonus",
				"resources" => ["eggs"],
			],
			[
				"coords" => [3, 3],
				"type" => "bonus",
				"resources" => ["gullgubber"],
			],
			[
				"coords" => [3, 5],
				"type" => "bonus",
				"resources" => ["ore"],
			],
			[
				"coords" => [5, 8],
				"type" => "bonus",
				"resources" => ["wood"],
			],
			[
				"coords" => [6, 5],
				"type" => "bonus",
				"resources" => ["saltmeat"],
			],
			[
				"coords" => [8, 2],
				"type" => "bonus",
				"resources" => ["altar"], //gives stone too
			],
			[
				"coords" => [9, 2],
				"type" => "bonus",
				"resources" => ["altar"], //gives stone too
			],
			[
				"coords" => [3, 0],
				"type" => "income",
				"value" => 0,
			],
			[
				"coords" => [4, 1],
				"type" => "income",
				"value" => 1,
			],
			[
				"coords" => [5, 2],
				"type" => "income",
				"value" => -1,
			],
			[
				"coords" => [6, 3],
				"type" => "income",
				"value" => 2,
			],
			[
				"coords" => [7, 4],
				"type" => "income",
				"value" => 3,
			],
			[
				"coords" => [8, 5],
				"type" => "income",
				"value" => 4,
			],
		],
		"allowedColours" => ["blue", "green", "coin", "ore"],
		"nonTouchingColours" => ["green"],
		"feast" => false,
	],
	"caithness" => [
		"type" => "caithness",
		"category" => "island",
		"vikings" => 3,
		"rows" => 8,
		"cols" => 11,
		"points" => 24,
		"_income" => 0,
		// prettier-ignore
		"negatives" => [
			[3, 0], [3, 1], [3, 2],
			[4, 0], [4, 1], [4, 2],
			[5, 1], [5, 2], [5, 3], [5, 3],
			[6, 1], [6, 2], [6, 3], [6, 4], [6, 4], [6, 5], [6, 5],
			[7, 3], [7, 4], [7, 5],
			[8, 3], [8, 4],
			[9, 4], [9, 5], [9, 6],
			[10, 4], [10, 5], [10, 5], [10, 6], [10, 6],
		],
		"incomeStarts" => [
			[0, 2],
		],
		"cells" => [
			[
				"coords" => [0, 0],
				"type" => "void",
			],
			[
				"coords" => [0, 3],
				"type" => "void",
			],
			[
				"coords" => [0, 4],
				"type" => "void",
			],
			[
				"coords" => [0, 5],
				"type" => "void",
			],
			[
				"coords" => [0, 6],
				"type" => "void",
			],
			[
				"coords" => [0, 7],
				"type" => "void",
			],
			[
				"coords" => [1, 0],
				"type" => "void",
			],
			[
				"coords" => [1, 6],
				"type" => "void",
			],
			[
				"coords" => [1, 7],
				"type" => "void",
			],
			[
				"coords" => [2, 0],
				"type" => "void",
			],
			[
				"coords" => [2, 7],
				"type" => "void",
			],
			[
				"coords" => [3, 7],
				"type" => "void",
			],
			[
				"coords" => [4, 7],
				"type" => "void",
			],
			[
				"coords" => [5, 6],
				"type" => "void",
			],
			[
				"coords" => [6, 0],
				"type" => "void",
			],
			[
				"coords" => [6, 6],
				"type" => "void",
			],
			[
				"coords" => [7, 0],
				"type" => "void",
			],
			[
				"coords" => [7, 1],
				"type" => "void",
			],
			[
				"coords" => [7, 6],
				"type" => "void",
			],
			[
				"coords" => [8, 0],
				"type" => "void",
			],
			[
				"coords" => [8, 1],
				"type" => "void",
			],
			[
				"coords" => [8, 2],
				"type" => "void",
			],
			[
				"coords" => [8, 6],
				"type" => "void",
			],
			[
				"coords" => [9, 0],
				"type" => "void",
			],
			[
				"coords" => [9, 1],
				"type" => "void",
			],
			[
				"coords" => [9, 2],
				"type" => "void",
			],
			[
				"coords" => [9, 3],
				"type" => "void",
			],
			[
				"coords" => [10, 0],
				"type" => "void",
			],
			[
				"coords" => [10, 1],
				"type" => "void",
			],
			[
				"coords" => [10, 2],
				"type" => "void",
			],
			[
				"coords" => [10, 3],
				"type" => "void",
			],
			[
				"coords" => [4, 5],
				"type" => "bonus",
				"resources" => ["fur"],
			],
			[
				"coords" => [5, 0],
				"type" => "bonus",
				"resources" => ["sword"],
			],
			[
				"coords" => [7, 2],
				"type" => "bonus",
				"resources" => ["stone"],
			],
			[
				"coords" => [8, 5],
				"type" => "bonus",
				"resources" => ["amber"],
			],
			[
				"coords" => [0, 2],
				"type" => "income",
				"value" => 0,
			],
			[
				"coords" => [1, 3],
				"type" => "income",
				"value" => 1,
			],
			[
				"coords" => [2, 4],
				"type" => "income",
				"value" => 2,
			],
			[
				"coords" => [3, 5],
				"type" => "income",
				"value" => 3,
			],
			[
				"coords" => [4, 6],
				"type" => "income",
				"value" => 4,
			],
			[
				"coords" => [5, 7],
				"type" => "income",
				"value" => 5,
			],
		],
		"allowedColours" => ["blue", "green", "coin", "ore"],
		"nonTouchingColours" => ["green"],
		"feast" => false,
	],
];

// adjust altered stuff from base game
if (FeastForOdin::$game->isNorwegiansExpansion()) {
	$boards['bearisland']['points'] = 16;
	$boards['faroeislands']['points'] = 10;
	$boards['baffinisland']['points'] = 10;
}

return $boards;