<?php

include "player-actions/selectIsland.php";
include "player-actions/declareSuccess.php";
include "player-actions/selectSpecialTile.php";
include "player-actions/useOccupation.php";
include "player-actions/undo.php";
include "player-actions/goBack.php";
include "player-actions/selectMode.php";
include "player-actions/playOccupation.php";
include "player-actions/action.php";
include "player-actions/pass.php";
include "player-actions/affirm.php";
include "player-actions/declareFailure.php";
include "player-actions/reroll.php";
include "player-actions/selectStrip.php";
include "player-actions/selectGoods.php";
include "player-actions/selectShip.php";
include "player-actions/equipShip.php";
include "player-actions/equipBoard.php";
include "player-actions/unequipShip.php";
include "player-actions/unequipBoard.php";
include "player-actions/buyShip.php";
include "player-actions/saveBoards.php";
include "player-actions/chooseVikingsNumber.php";
include "player-actions/selectForSlaughter.php";
include "player-actions/selectResources.php";
include "player-actions/selectWeapons.php";
include "player-actions/discardOccupation.php";
include "player-actions/selectAttackReward.php";
include "player-actions/setAutoPassableAction.php";
include "player-actions/drawPersonalResource.php";
include "player-actions/chooseBestOfTwoStartingOcc.php";
include "player-actions/buildArtisanShed.php";


trait PlayerActions {

	use SelectIsland;
	use DeclareSuccess;
	use SelectSpecialTile;
	use UseOccupation;
	use ActUndo;
	use GoBack;
	use SelectMode;
	use PlayOccupation;
	use ActAction;
	use Pass;
	use Affirm;
	use DeclareFailure;
	use Reroll;
	use SelectStrip;
	use SelectGoods;
	use SelectShip;
	use EquipShip;
	use EquipBoard;
	use UnequipShip;
	use UnequipBoard;
	use BuyShip;
	use SaveBoards;
	use ChooseVikingsNumber;
	use SelectForSlaughter;
	use SelectResources;
	use SelectWeapons;
	use DiscardOccupation;
	use SelectAttackReward;
	use SetAutoPassAction;
	use DrawPersonalResource;
	use ChooseBestOfTwoStartingOcc;
	use BuildArtisanShed;


	function actSelectIsland( $island ) {
		$this->selectIsland($island);
	}

	function actUndo() {
		$this->undo();
	}

	function actGoBack() {
		$this->goBack();
	}
	
	function actSelectMode( $mode_id ) {
		$this->selectMode($mode_id);
	}

	function actPlayOccupation( $occupation_id, $playOrToken = null, $tutor ) {
		$this->playOccupation($occupation_id, $playOrToken, $tutor);
	}

	function actAction( $action_id  ) {
		$this->action($action_id);
	}

	function actPass( $check = true ) {
		$this->pass($check);
	}

	function actAffirm( $check = true ) {
		$this->affirm($check);
	}
	
	function actDeclareFailure( $check = true ) {
		$this->declareFailure($check);
	}
	
	function actReroll( $check = true ) {
		$this->reroll($check);
	}
	
	function actDeclareSuccess( $num_resources, $num_cards_per_type ) {
		$this->declareSuccess($num_resources, $num_cards_per_type);
	}
	
	function actSelectSpecialTile( $special_tile, $num_resources, $num_cards_per_type ) {
		$this->selectSpecialTile($special_tile, $num_resources, $num_cards_per_type);
	}
	
	function actSelectAttackReward( $special_tile, $special_tile2, $num_resources, $num_cards_per_type ) {
		$this->selectAttackReward($special_tile, $special_tile2, $num_resources, $num_cards_per_type);
	}

	function actSelectStrip( $strip_id ) {
		$this->selectStrip($strip_id);
	}
	
	function actSelectGoods( $goods, $goods2 = [] ) {
		$this->selectGoods($goods, $goods2);
	}
	
	function actSelectShip( $ship_id ) {
		$this->selectShip($ship_id);
	}
	
	function actEquipShip( $ship_id ) {
		$this->equipShip($ship_id);
	}

	function actEquipBoard( $board_index, $asset ) {
		$this->equipBoard($board_index, $asset);
	}
	
	function actUnequipShip( $ship_id ) {
		$this->unequipShip($ship_id);
	}

	function actUnequipBoard( $board_index, $asset ) {
		$this->unequipBoard($board_index, $asset);
	}
	
	function actBuyShip( $ship_type ) {
		$this->buyShip($ship_type);
	}

	function actSetAutoPassAction( $action_type ) {
		$this->setAutoPassAction($action_type);
	}
	
	function actSaveBoards( $request ) {
		$this->saveBoards($request);
	}
	
	function actUseOccupation( $occupation_id, $mode_id, $data ) {
		$this->useOccupation($occupation_id, $mode_id, $data);
	}

	function actChooseVikingsNumber( $number ) {
		$this->chooseVikingsNumber($number);
	}

	function actSelectForSlaughter($animal) {
		$this->selectForSlaughter($animal);
	}

	function actSelectResources($resources) {
		$this->selectResources($resources);
	}

	function actSelectWeapons($weapons) {
		$this->selectWeapons($weapons);
	}

	function actDiscardOccupation( $occupation_id, $playOrToken = null ) {
		$this->discardOccupation($occupation_id, $playOrToken);
	}

	function actDrawPersonalResource($resource, $isSecond) {
		$this->drawPersonalResource($resource, $isSecond);
	}

	function actChooseBestOfTwoStartingOcc($resource) {
		$this->chooseBestOfTwoStartingOcc($resource);
	}

	function actBuildArtisanShed($resource) {
		$this->buildArtisanShed($resource);
	}
}