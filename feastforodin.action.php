<?php

class action_feastforodin extends APP_GameAction
{ 
  // Constructor: please do not modify
  public function __default()
  {
      if( self::isArg( 'notifwindow') )
      {
          $this->view = "common_notifwindow";
          $this->viewArgs['table'] = self::getArg( "table", AT_posint, true );
      }
      else
      {
          $this->view = "feastforodin_feastforodin";
          self::trace( "Complete reinitialization of board game" );
    }
  } 

  public function action() {
    self::setAjaxMode();
    
    $actionId = self::getArg("actionId", AT_posint, true);
    
    $this->game->actAction( $actionId );

    self::ajaxResponse();
  }
  
  public function pass() {
    self::setAjaxMode();
    
    $this->game->actPass( );

    self::ajaxResponse();
  }
  
  public function undo() {
    self::setAjaxMode();
    
    $this->game->actUndo( );

    self::ajaxResponse();
  }
  
  public function goBack() {
    self::setAjaxMode();
    
    $this->game->actGoBack( );

    self::ajaxResponse();
  }

  public function affirm() {
    self::setAjaxMode();
    
    $this->game->actAffirm( );

    self::ajaxResponse();
  }
  
  public function declareFailure() {
    self::setAjaxMode();
    
    $this->game->declareFailure();

    self::ajaxResponse();
  }
  
  public function reroll() {
    self::setAjaxMode();
    
    $this->game->actReroll( );

    self::ajaxResponse();
  }
  
  public function playOccupation() {
    self::setAjaxMode();
    
    $occupationId = self::getArg("occupationId", AT_posint, true);

    $playOrToken = self::getArg("playOrToken", AT_posint, false);

    $tutor = self::getArg("tutor", AT_posint, false);
    
    $this->game->actPlayOccupation( $occupationId, $playOrToken, $tutor );

    self::ajaxResponse();
  }
  
  public function useOccupation() {
    self::setAjaxMode();
    
    $occupationId = self::getArg("occupationId", AT_posint, true);
    $modeId = self::getArg("modeId", AT_posint, true);
    $data = self::getArg("data", AT_alphanum, false);

    $this->game->actUseOccupation( $occupationId, $modeId, $data );

    self::ajaxResponse();
  }
  
  public function hunt() {
    self::setAjaxMode();
    
    $numResources = self::getArg("numResources", AT_posint, true);
    $numSwords = self::getArg("numSwords", AT_posint, true);
    $numSpears = self::getArg("numSpears", AT_posint, true);
    $numSnares = self::getArg("numSnares", AT_posint, true);
    $numBows = self::getArg("numBows", AT_posint, true);
    
    $this->game->actDeclareSuccess( $numResources, [
      "sword" => $numSwords,
      "spear" => $numSpears,
      "snare" => $numSnares,
      "bow" => $numBows,
    ] );

    self::ajaxResponse();
  }
  
  public function selectMode() {
    self::setAjaxMode();
    
    $modeId = self::getArg("modeId", AT_posint, true);
    
    $this->game->actSelectMode( $modeId );

    self::ajaxResponse();
  }
  
  public function selectSpecialTile() {
    self::setAjaxMode();
    
    $specialTile = self::getArg("specialTile", AT_alphanum, true);
    
    $numResources = self::getArg("numResources", AT_posint, false);
    $numSwords = self::getArg("numSwords", AT_posint, false);
    $numSpears = self::getArg("numSpears", AT_posint, false);
    $numSnares = self::getArg("numSnares", AT_posint, false);
    $numBows = self::getArg("numBows", AT_posint, false);

    $this->game->actSelectSpecialTile( $specialTile, $numResources, [
      "sword" => $numSwords,
      "spear" => $numSpears,
      "snare" => $numSnares,
      "bow" => $numBows,
    ] );

    self::ajaxResponse();
  }

  public function selectAttackReward() {
    self::setAjaxMode();
    
    $specialTile = self::getArg("specialTile", AT_alphanum, true);
    $specialTile2 = self::getArg("specialTile2", AT_alphanum, false);
    
    $numResources = self::getArg("numResources", AT_posint, false);
    $numSwords = self::getArg("numSwords", AT_posint, false);
    $numSpears = self::getArg("numSpears", AT_posint, false);
    $numSnares = self::getArg("numSnares", AT_posint, false);
    $numBows = self::getArg("numBows", AT_posint, false);

    $this->game->actSelectAttackReward( $specialTile, $specialTile2, $numResources, [
      "sword" => $numSwords,
      "spear" => $numSpears,
      "snare" => $numSnares,
      "bow" => $numBows,
    ] );

    self::ajaxResponse();
  }
  
  public function selectStrip() {
    self::setAjaxMode();
    
    $stripId = self::getArg("stripId", AT_posint, true);
    
    $this->game->actSelectStrip( $stripId );

    self::ajaxResponse();
  }
  
  public function selectShip() {
    self::setAjaxMode();
    
    $shipId = self::getArg("shipId", AT_posint, true);
    
    $this->game->actSelectShip( $shipId );

    self::ajaxResponse();
  }
  
  public function buyShip() {
    self::setAjaxMode();
    
    $shipType = self::getArg("shipType", AT_alphanum, true);
    
    $this->game->actBuyShip( $shipType );

    self::ajaxResponse();
  }
  
  public function setAutoPassAction() {
    self::setAjaxMode();
    
    $actionType = self::getArg("actionType", AT_alphanum, true);
    
    $this->game->actSetAutoPassAction( $actionType );

    self::ajaxResponse();
  }

  public function equipBoard() {
    self::setAjaxMode();
    
    $boardIndex = self::getArg("boardIndex", AT_posint, true);
    $asset = self::getArg("asset", AT_alphanum, true);
    
    if (! in_array($asset, ["stone", "wood", "ore", "mead", "linen", "beans", "stockfish", "hide"])) {
      throw new BgaVisibleSystemException( "Invalid asset type ($asset)");
    }
    
    $this->game->actEquipBoard( $boardIndex, $asset );

    self::ajaxResponse();
  }
  
  public function unequipBoard() {
    self::setAjaxMode();
    
    $boardIndex = self::getArg("boardIndex", AT_posint, true);
    $asset = self::getArg("asset", AT_alphanum, true);
    
    if (! in_array($asset, ["stone", "wood", "ore", "mead", "linen", "beans", "stockfish", "hide"])) {
      throw new BgaVisibleSystemException( "Invalid asset type ($asset)");
    }
    
    $this->game->actUnequipBoard( $boardIndex, $asset );

    self::ajaxResponse();
  }

  public function equipShip() {
    self::setAjaxMode();
    
    $shipId = self::getArg("shipId", AT_posint, true);
    
    $this->game->actEquipShip( $shipId );

    self::ajaxResponse();
  }
  
  public function unequipShip() {
    self::setAjaxMode();
    
    $shipId = self::getArg("shipId", AT_posint, true);
    
    $this->game->actUnequipShip( $shipId );

    self::ajaxResponse();
  }
  
  public function selectGoods() {
    self::setAjaxMode();
    
    $goodsList = self::getArg("goods", AT_alphanum_dash, true);
    
    // Check that each good is a valid good
    if ($goodsList == '') {
      $goods = [];
    } else {
      $goods = explode("-", $goodsList);

      foreach ($goods as $g) {
        if (! preg_match('/^[a-z]+$/', $g)) {
          throw new BgaVisibleSystemException( "Invalid goods selection");
        }
      }
    }

    $goodsList2 = self::getArg("goods2", AT_alphanum_dash, false);
    
    if ($goodsList2 == '') {
      $goods2 = [];
    } else {
      $goods2 = explode("-", $goodsList2);

      foreach ($goods2 as $g) {
        if (! preg_match('/^[a-z]+$/', $g)) {
          throw new BgaVisibleSystemException( "Invalid goods selection");
        }
      }
    }

    $this->game->actSelectGoods( $goods, $goods2 );

    self::ajaxResponse();
  }
  
  public function saveBoards() {
    self::setAjaxMode();
      
    $request = self::getArg("request", AT_json, true);
    
    // Check valid json?
    //throw new BgaVisibleSystemException( json_encode($request) );
    
    $this->game->actSaveBoards( $request );
    
    if (!empty($request["pass"])) {
      // Also pass!
      $this->game->actPass( );
    }

    self::ajaxResponse();
  }
  
  public function selectIsland() {
    self::setAjaxMode();
    
    $island = self::getArg("island", AT_alphanum, true);
    
    $this->game->actSelectIsland( $island );

    self::ajaxResponse();
  }

  public function chooseVikingsNumber() {
    self::setAjaxMode();
    
    $number = self::getArg("vikingNumber", AT_posint, true);
    
    $this->game->actChooseVikingsNumber((int)$number );

    self::ajaxResponse();
  }

  public function selectForSlaughter() {
    self::setAjaxMode();
    
    $animal = self::getArg("selectedAnimal", AT_alphanum, true);
    
    $this->game->actSelectForSlaughter($animal);

    self::ajaxResponse();
  }

  public function selectResources() {
    self::setAjaxMode();
    
    $goodsList = self::getArg("resources", AT_alphanum_dash, true);
    
    // Check that each resource is a valid resource
    if ($goodsList == '') {
      $goods = [];
    } else {
      $goods = explode("-", $goodsList);

      foreach ($goods as $g) {
        if (! preg_match('/^[a-z]+$/', $g)) {
          throw new BgaVisibleSystemException( "Invalid resources selection");
        }
      }
    }
    
    $this->game->actSelectResources( $goods );

    self::ajaxResponse();
  }

  public function selectWeapons() {
    self::setAjaxMode();
    
    $weaponsList = self::getArg("weapons", AT_alphanum_dash, true);
    
    if ($weaponsList == '') {
      $weapons = [];
    } else {
      $weapons = explode("-", $weaponsList);
    }
    
    $this->game->actSelectWeapons( $weapons );

    self::ajaxResponse();
  }

  public function discardOccupation() {
    self::setAjaxMode();
    
    $occupationId = self::getArg("occupationId", AT_posint, true);
    
    $this->game->actDiscardOccupation( $occupationId );

    self::ajaxResponse();
  }

  public function drawPersonalResource() {
    self::setAjaxMode();
    
    $resourceId = self::getArg("resource", AT_alphanum_dash, true);

    $isSecond = self::getArg("isSecond", AT_alphanum_dash, true);
    
    $this->game->actDrawPersonalResource( $resourceId, $isSecond );

    self::ajaxResponse(); 
  }

  public function buildArtisanShed() {
    self::setAjaxMode();
    
    $shed = self::getArg("shed", AT_alphanum_dash, true);
    
    $this->game->actBuildArtisanShed( $shed );

    self::ajaxResponse(); 
  }

  public function chooseBestOfTwoStartingOcc() {
    self::setAjaxMode();
    
    $occId = self::getArg("occId", AT_posint, true);
    
    $this->game->actChooseBestOfTwoStartingOcc( $occId );

    self::ajaxResponse();
  }
}
