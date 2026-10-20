<?php

trait SelectGoods {
    function selectGoods( $goods, $goods2 = [] ) {
        self::checkAction( 'selectGoods' );
        $action_id = $this->getGameStateValue('action_id');
        $player_id = self::getActivePlayerId();
        $player = FfoPlayer::getFullPlayer($player_id);
        $state = $this->gamestate->state();
        $command = $this->getString( 'currentCommand' );
        $components = explode('-', $command);
        $source = 'action-'.$action_id;
        if ($components[0] == 'occupation') {
            if ($components[1] == 31) {
                if (count($goods) !== 1) {
                    throw new BgaUserException( clienttranslate("You must select exactly one good") );
                }
                
                $good_type = $goods[0];
                if ($this->isAnimalThatCanGetPregnant($good_type)) {
                    throw new BgaVisibleSystemException( "You cannot take sheep, cattle or horses" );
                }
                
                // Do you have 3 of it?
                $player_goods = &$player["goods"];
                $amt = 0;
                foreach ($player_goods as $g) {
                    if ($g["type"] == $good_type) {
                        $amt++;
                    }
                }
                if ($amt != 3) {
                    throw new BgaVisibleSystemException( "You must have exactly 3 of the good" );
                }
                
                // Take it!
                $this->gain($player, $good_type, 1, 'occupation-31');
                
                FfoPlayer::saveFullPlayer($player);
            } 
            else if ($components[1] == 66) {
                // Field Farmer
                if (count($goods) !== 1) {
                    throw new BgaUserException( clienttranslate("You must select exactly one good") );
                }
                
                $good_type = $goods[0];
                foreach ($player["goods"] as $g) {
                    if ($g["type"] == $good_type) {
                        if ($g["colour"] != "orange") {
                            throw new BgaUserException( clienttranslate("You can only upgrade orange tiles") );
                        }
                        break;
                    }
                }
                
                // Spend it
                $this->spend($player, $good_type, 1, "occupation-66");
                $this->spend($player, "coin", 1, "occupation-66");
                
                if ($good_type == "peas") {
                    $new_good_type = "wool";
                } else if ($good_type == "flax") {
                    $new_good_type = "linen";
                } else if ($good_type == "beans") {
                    $new_good_type = "skinandbones";
                } else if ($good_type == "grain") {
                    $new_good_type = "fur";
                } else if ($good_type == "cabbage") {
                    $new_good_type = "robe";
                } else if ($good_type == "fruits") {
                    $new_good_type = "cattle";
                } else {
                    throw new BgaVisibleSystemException( "Can't upgrade that tile" );
                }
                
                $this->gain($player, $new_good_type, 1, "occupation-66");
                
                FfoPlayer::saveFullPlayer($player);
            } 
            else {
                throw new BgaVisibleSystemException( "Action does not exist" );
            }
        } else if ($components[0] == 'anytwoanimals') {
            if (count($goods) != 2) {
                throw new BgaVisibleSystemException( "You must pick exactly two animals" );
            }
            foreach ($goods as $good) {
                if (in_array($good, ['pig', 'sheep', 'horse', 'cattle'])) {
                    $this->gain( $player, $good, 1, $components[0] );
                }
                if ($good == 'cattle' && Occupation::has($player_id, 174)) {
                    // Bosphorus traveller
                    Action::unshift(["state-bosporustraveller"]);
                }
            }
            FfoPlayer::saveFullPlayer($player);
        } else {
            $asset_diffs = [];
            
            // Check the user has all of these goods
            $player_goods = &$player["goods"];
            $upgrading_goods = [];

            foreach ($goods as $t) {
                foreach ($player_goods as $k => $pg) {
                    if ($pg["type"] == $t) {
                        $upgrading_goods[] = $pg;
                        $asset_diffs[] = "($player_id, '$pg[type]', -1, '$source')";
                        $this->releaseEasterEgg($pg);
                        unset($player_goods[$k]);
                        continue 2;
                    }
                }
                throw new BgaVisibleSystemException( "You do not own that good ($t)" );
            }
            
            $upgrading_goods2 = [];
            if (!empty($goods2)) {
                foreach ($goods2 as $t) {
                    foreach ($player_goods as $k => $pg) {
                        if ($pg["type"] == $t) {
                            $upgrading_goods2[] = $pg;
                            $asset_diffs[] = "($player_id, '$pg[type]', -1, '$source')";
                            $this->releaseEasterEgg($pg);
                            unset($player_goods[$k]);
                            continue 2;
                        }
                    }
                    throw new BgaVisibleSystemException( "You do not own that good ($t)" );
                }
            }
            
            if ($components[0] == 'trade') {
                $seen_types = [];
                
                // Check the goods are different and green
                foreach ($upgrading_goods as $g) {
                    if (isset($seen_types[$g["type"]])) {
                        throw new BgaUserException( clienttranslate("You must upgrade different tiles") );
                    }
                    $seen_types[$g["type"]] = 1;
                    if ($g["colour"] != "green") {
                        throw new BgaUserException( clienttranslate("You can only upgrade green tiles") );
                    }
                }
                $num_steps = 1;
            } 
            else if ($components[0] == 'upgrade' || $components[0] == 'nwupgrade' || $components[0] == 'meatupgrade' || $components[0] == 'sameupgrade' || $components[0] == 'payupgrade') {
                $num_tiles = $components[1];
                $num_steps = $components[2];
                
                $allowed_types = $this->getString('allowed_upgrade_types');
                if (isset($allowed_types) && $allowed_types) {
                    $this->setString('allowed_upgrade_types', '');
                    
                    $allowed_types = explode(',', $allowed_types);
                } else {
                    $allowed_types = null;
                }
                
                
                // Correct number of tiles
                if (count($upgrading_goods) > $num_tiles) {
                    throw new BgaUserException( clienttranslate("You cannot upgrade that many tiles") );
                }
                
                if ($components[0] == 'upgrade' && count($upgrading_goods) == 0) {
                    if (count($components) >= 4) {
                        // Compulsory upgrade!
                        throw new BgaUserException( clienttranslate("You must upgrade at least 1 tile") );
                    }
                }
                
                if ($components[0] == 'payupgrade') {
                    if (! $this->canAfford($player, "coin", 1)) {
                        throw new BgaUserException( clienttranslate("You cannot afford that action") );
                    }
                    $this->spend($player, "coin", 1);
                }
                
                // Cannot upgrade blue tiles
                $last_type = null;
                foreach ($upgrading_goods as $g) {
                    if (isset($allowed_types)) {
                        if (! in_array($g["type"], $allowed_types)) {
                            throw new BgaVisibleSystemException( "You cannot upgrade that good: " . implode(',', $allowed_types) );
                        }
                    }
                    if ($num_steps >= 1) {
                        if ($g["colour"] == "blue") {
                            throw new BgaUserException( clienttranslate("You cannot upgrade blue tiles") );
                        }
                    }
                    if ($components[0] == 'nwupgrade') {
                        if (in_array($g["type"], ["rune", "oil", "mead", "peas"]))
                            throw new BgaUserException( clienttranslate("You cannot upgrade tiles in the left column") );
                    } else if ($components[0] == 'meatupgrade') {
                        // Can only select meats!
                        if (! in_array($g["type"], ["whalemeat", "gamemeat", "saltmeat"])) {
                            throw new BgaUserException( clienttranslate("You can only upgrade meat goods with the Ship's Cook") );
                        }
                    } else if ($components[0] == 'sameupgrade') {
                        // Can only upgrade 1 type!
                        // Note that preggers and non-preggers are considered the same
                        if (isset($last_type) && $last_type != $g["type"] && $last_type != "pregnant".$g["type"] && "pregnant".$last_type != $g["type"]) {
                            throw new BgaUserException( clienttranslate("You can only upgrade one type of good") );
                        }
                        $last_type = $g["type"];
                    }
                }
            } else if ($components[0] == 'flip') {
                // is 78 occ the only flip occ?
                // do not let sheep, cattle and horse to flip (pigs ok?)

				$how_many_can_flip = $components[1];

				if (count($upgrading_goods) > $how_many_can_flip) {
					throw new BgaVisibleSystemException( "You can only flip ${how_many_can_flip} tiles" );
				}

                foreach ($upgrading_goods as $good) {
					if ($this->isAnimalThatCanGetPregnant($good['type'])) {
						throw new BgaVisibleSystemException( "You cannot flip sheep, cattle or horses" );
					}
				}

            } else if ($components[0] == 'oncetwiceupgrade') {

                if (count($upgrading_goods) > 2) {
                    throw new BgaUserException( clienttranslate("You cannot upgrade that many tiles") );
                }
                if (count($upgrading_goods2) > 1) {
                    throw new BgaUserException( clienttranslate("You cannot upgrade that many tiles") );
                }
                if (empty($upgrading_goods) && empty($upgrading_goods2)) {
                    throw new BgaUserException( clienttranslate("You must upgrade at least 1 tile") );
                }
            } else {
                throw new BgaVisibleSystemException( "Action does not exist ($command)" );
            }
            
            // Perform the upgrade!
            $data_goods = self::getDataGoods();
            $new_goods = [];
            if ($components[0] == 'flip') {
                foreach ($upgrading_goods as $g) {
                    $new_good = $g;
                    if (! empty($g["special"])) {
                        // Same good!
                    } else if ($g["type"] == "sheep") {
                        $new_good = $data_goods["pregnantsheep"];
                    } else if ($g["type"] == "cattle") {
                        $new_good = $data_goods["pregnantcattle"];
                    } else if ($g["type"] == "horse") {
                        $new_good = $data_goods["pregnanthorse"];
                    } else if ($g["type"] == "pregnantsheep") {
                        $new_good = $data_goods["sheep"];
                    } else if ($g["type"] == "pregnantcattle") {
                        $new_good = $data_goods["cattle"];
                    } else if ($g["type"] == "pregnanthorse") {
                        $new_good = $data_goods["horse"];

                    // TODO: adjust findGood function instead of this
                    } else if ($g["type"] == "herbs") {
                        $new_good = $data_goods["pig"];
                    } else if ($g["type"] == "pig") {
                        $new_good = $data_goods["herbs"];
                    } else if ($g["type"] == "antlers") {
                        $new_good = $data_goods["tools"];
                    } else if ($g["type"] == "tools") {
                        $new_good = $data_goods["antlers"];

                    } else {
                        if ($g["colour"] == "blue") {
                            $colour = "green";
                        } else if ($g["colour"] == "green") {
                            $colour = "blue";
                        } else if ($g["colour"] == "orange") {
                            $colour = "red";
                        } else if ($g["colour"] == "red") {
                            $colour = "orange";
                        }
                        $new_good = self::findGood($g["cols"], $g["rows"], $colour);
                    }
                    $new_good = $this->rollEasterEgg($new_good);
                    $player_goods[] = $new_good;
                    $new_goods[] = $new_good;
                    $asset_diffs[] = "($player_id, '$new_good[type]', 1, '$source')";
                }
            } 
            else if ($components[0] == 'nwupgrade') {
                // nw!
                foreach ($upgrading_goods as $g) {
                    $nw_upgrades = [
                        "flax" => "mead",
                        "stockfish" => "oil",
                        "hide" => "rune",
                        "beans" => "stockfish",
                        "milk" => "hide",
                        "wool" => "silverware",
                        "grain" => "milk",
                        "saltmeat" => "wool",
                        "linen" => "chest",
                        "cabbage" => "saltmeat",
                        "gamemeat" => "linen",
                        "skinandbones" => "silk",
                        "sheep" => "skinandbones",
                        "pregnantsheep" => "skinandbones",
                        "fur" => "spices",
                        "fruits" => "sheep",
                        "whalemeat" => "fur",
                        "robe" => "jewelry",
                        "cattle" => "robe",
                        "pregnantcattle" => "robe",
                        "clothing" => "treasurechest",
                    ];
                    if (! isset($nw_upgrades[$g["type"]])) {
                        throw new BgaUserException( clienttranslate("You cannot upgrade that tile") );
                    }
                    $new_good = $data_goods[$nw_upgrades[$g["type"]]];
                    $player_goods[] = $new_good;
                    $new_goods[] = $new_good;
                    $asset_diffs[] = "($player_id, '$new_good[type]', 1, '$source')";
                }
            } else if ($components[0] == 'oncetwiceupgrade') {
                foreach ($upgrading_goods as $g) {
                    $new_good = $g;
                    for ($i = 0; $i < 1; $i++) {
                        $new_good = $data_goods[$new_good["upgrade"]];
                        // If the new good is blue, stop!
                        if ($new_good["colour"] == "blue") {
                            break;
                        }
                    }
                    $new_good = $this->rollEasterEgg($new_good);
                    $player_goods[] = $new_good;
                    $new_goods[] = $new_good;
                    $asset_diffs[] = "($player_id, '$new_good[type]', 1, '$source')";
                }
                foreach ($upgrading_goods2 as $g) {
                    $new_good = $g;
                    for ($i = 0; $i < 2; $i++) {
                        $new_good = $data_goods[$new_good["upgrade"]];
                        // If the new good is blue, stop!
                        if ($new_good["colour"] == "blue") {
                            break;
                        }
                    }
                    $new_good = $this->rollEasterEgg($new_good);
                    $player_goods[] = $new_good;
                    $new_goods[] = $new_good;
                    $asset_diffs[] = "($player_id, '$new_good[type]', 1, '$source')";
                }
                $upgrading_goods = array_merge($upgrading_goods, $upgrading_goods2);
            }
            else {
                foreach ($upgrading_goods as $g) {
                    $new_good = $g;
                    for ($i = 0; $i < $num_steps; $i++) {
                        $new_good = $data_goods[$new_good["upgrade"]];
                        // If the new good is blue, stop!
                        if ($new_good["colour"] == "blue") {
                            break;
                        }
                    }
                    $new_good = $this->rollEasterEgg($new_good);
                    $player_goods[] = $new_good;
                    $new_goods[] = $new_good;
                    $asset_diffs[] = "($player_id, '$new_good[type]', 1, '$source')";
                }
            }

            if (count($asset_diffs) > 0) {
                self::DbQuery("INSERT INTO asset_diffs (
                    player_id, asset, amount, source
                ) VALUES ".implode(',', $asset_diffs));
            }
            
            if (Occupation::has($player_id, 190)) {
                foreach ($new_goods as $g) {
                    if ($g["type"] == "spices") {
                        $this->gain( $player, "oil", 1, "occupation-190" );
                    }
                }
            }
            
            if (Occupation::has($player_id, 28)) {
                foreach ($new_goods as $g) {
                    if ($g["type"] == "chest" || $g["type"] == "treasurechest") {
                        Action::unshift(["exchange-coin-oil:occupation-28"]);
                    }
                }
            }
            
            if (Occupation::has($player_id, 152)) {
                if (($components[0] == 'upgrade' || $components[0] == 'sameupgrade') && 
                $num_steps == 1 &&
                ($num_tiles == 3 || $num_tiles == 4)) {
                    // Merchant gives you another upgrade option
                    $types = [];
                    foreach ($new_goods as $g) {
                        $types[$g["type"]] = 1;
                    }
                    $this->setString('allowed_upgrade_types', implode(',', array_keys($types)));
                    Action::unshift(['upgrade-1-1']);
                }
            }
            
            if (Occupation::has($player_id, 175)) {
                foreach ($new_goods as $g) {
                    if ($g["type"] == "stockfish") {
                        Action::unshift(['exchange-coin-oil']);
                    }
                    break;
                }
            }

            self::notifyAllPlayers( "spendGoods", '', array(
                'player_id' => $player_id,
                'goods' => $upgrading_goods,
                'source' => $source,
            ));
            
            self::notifyAllPlayers( "takeGoods", '', array(
                'player_id' => $player_id,
                'goods' => $new_goods,
                'source' => $source,
            ));
            
            FfoPlayer::saveFullPlayer($player);
        }
            
        if (Occupation::has($player_id, 150)) {
            $foundFish = 0;
            $foundSaltmeat = 0;
            foreach ($new_goods as $g) {
                if ($g["type"] == "stockfish") {
                    $foundFish++;
                }
                if ($g["type"] == "saltmeat") {
                    $foundSaltmeat++;
                }
            }
            if ($foundFish > 1) {
                Action::unshift(['exchange-stockfish-gamemeat']);
            }
            if ($foundSaltmeat > 1) {
                Action::unshift(['exchange-saltmeat-gamemeat']);
            }
        }

        $this->gamestate->nextState( 'done' );
    }

    private function isAnimalThatCanGetPregnant($good_type) 
    {
        if ($good_type == "sheep" 
            || $good_type == "pregnantsheep" 
            || $good_type == "cattle" 
            || $good_type == "pregnantcattle" 
            || $good_type == "horse" 
            || $good_type == "pregnanthorse") {
            return true;
        }

        return false;
    }

}
