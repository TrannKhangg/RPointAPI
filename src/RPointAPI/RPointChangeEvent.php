<?php

namespace RPointAPI;

use RPointAPI\RPoint;

use pocketmine\Player;

use RPointAPI\RPointEvent;

class RPointChangeEvent extends RPointEvent{
  
  public function __construct(RPoint $main, $player){
    $this->main = $main;
    $this->player = $player;
  }
  
  public function getPlayer(){
    return $this->player;
  }
  
  public function getRPoint(){
    return $this->main->myRPoint($this->player);
  }
}
