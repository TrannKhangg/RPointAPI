<?php

namespace RPointAPI;

use RPointAPI\RPoint;

use pocketmine\event\plugin\PluginEvent;

class RPointEvent extends PluginEvent{
  
  public function __construct(RPoint $main){
    $this->main = $main;
  }
}
