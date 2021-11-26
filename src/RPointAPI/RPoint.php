<?php
/*
Hi I am a plugin developer. The plugin is still in beta. If you have any problems, please give me feedback: https://www.facebook.com/profile.php?id=100071316150096 
*/
#Tên : Duy Onichan, Biệt danh : pmmdst, Plugin hiện còn trong giai đoạn Beta. Nếu bạn có bất cứ vấn đề gì, hãy góp ý kiến tại: https://www.facebook.com/profile.php?id=100071316150096

namespace RPointAPI;

use pocketmine\Server;
use pocketmine\Player;

use pocketmine\plugin\PluginBase;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\event\Listener;

use pocketmine\utils\Config;

use pocketmine\event\player\PlayerJoinEvent;

use RPointAPI\RPointChangeEvent;
use RPointAPI\RPointEvent;

class RPoint extends PluginBase implements Listener {
  
  public function onEnable(){
    $this->getLogger()->info("RPointAPI v0.0.2 đã bật, hãy trải nghiệm ngay!");
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
    $this->rpoint = new Config($this->getDataFolder() . "rpoint.yml", Config::YAML);
  }
  
  public function onDisable(){
    $this->getLogger()->info("RPointAPI v0.0.2 đã tắt...");
  }
  
#----------------------------------------------------------------------------------------
  
  public function onJoin(PlayerJoinEvent $event){
    $player = $event->getPlayer();
    if(!$this->rpoint->exists($player->getName())){
      $this->rpoint->set($player->getName(), 0);
      $this->rpoint->save();
      $this->getServer()->getPluginManager()->callEvent(new RPointChangeEvent($this, $player));
    }
  }
  
  public function reduceRPoint($player, $rpoint){
    if($player instanceof Player){
      if(is_numeric($rpoint)){
         $this->rpoint->set($player->getName(), ($this->rpoint->get($player->getName()) - $rpoint));
         $this->getServer()->getPluginManager()->callEvent(new RPointChangeEvent($this, $player));
      }
    }
  }
  
  public function addRPoint($player, $rpoint){
    if($player instanceof Player){
      if(is_numeric($rpoint)){
         $this->rpoint->set($player->getName(), ($this->rpoint->get($player->getName()) + $rpoint));
         $this->getServer()->getPluginManager()->callEvent(new RPointChangeEvent($this, $player));
      }
    }
  }
  
  public function myRPoint($player){
    if($player instanceof Player){
      
      return ($this->rpoint->get($player->getName()));
    }
  }
  
  public function getAllRPoint(){
    return $this->rpoint->getAll();
  }
  
#----------------------------------------------------------------------------------------
  
  public function onCommand(CommandSender $sender, Command $cmd, String $label, array $args): bool{
    switch($cmd->getName()){
      case "myrpoint":
        if($sender instanceof Player){
          $rpoint = $this->myRPoint($sender);
          $sender->sendMessage("§b§l•§6 Số§e RPoint§6 của bạn:§e " . $rpoint);
        }else{
          $sender->sendMessage("Yêu cầu : Sử dụng lệnh trong game!");
        }
        break;
        
        case "setrpoint":
          if($sender instanceof Player){
            if($sender->hasPermission("setrpoint.pmmdst")){
              if(isset($args[0])){
                if(isset($args[1])){
                  $player = $this->getServer()->getPlayer($args[0]);
                  if(!is_numeric($args[1])){
                    $sender->sendMessage("§b§l•§6 Lỗi : Kí tự phải là 1 chữ số!");
                    return true;
                  }
                  if(!$player instanceof Player){
                    $sender->sendMessage("§b§l•§6 Người chơi§a " . $args[0] . " §6không hoạt động!");
                    return true;
                  }
                  
                  $this->rpoint->set($player->getName(), $args[1]);
                  $this->rpoint->save();
                  $sender->sendMessage("§b§l•§6 Thành công chỉnh số §eRPoint §6của người chơi§a " . $args[0] . " §6thành§e " . $args[1]);
                  $player->sendMessage("§b§l•§6 Số §eRPoint§6 của bạn được chỉnh thành§e " . $args[1]);
                  $this->getServer()->getPluginManager()->callEvent(new RPointChangeEvent($this, $player));
                }else{
                  $sender->sendMessage("§b§l•§6 Lệnh: §e/setrpoint {người chơi} {số lượng}");
                }
              }else{
                $sender->sendMessage("§b§l•§6 Lệnh: §e/setrpoint {người chơi} {số lượng}");
              }
            }
          }else{
            $sender->sendMessage("Yêu cầu : Sử dụng lệnh trong game!");
          }
          break;
          
          case "toprpoint":
            $rpointall = $this->getAllRPoint();
            arsort($rpointall);
            $rpointall = array_slice($rpointall, 0, 9);
            $top = 1;
            foreach($rpointall as $name => $count){
              $sender->sendMessage("§b§l•§6 Xếp hạng " . $top . "§6§l Thuộc về người chơi§a " . $name . "§6§l với§e " . $count . " RPoint");
              $top++;
            }
            break;
            
            case "payrpoint":
              if($sender instanceof Player){
                if(isset($args[0])){
                  if(isset($args[1])){
                    $player2 = $this->getServer()->getPlayer($args[0]);
                    $rpoint = $this->myRPoint($sender);
                    if(!$player2 instanceof Player){
                      $sender->sendMessage("§b§l•§6 Người chơi§a " . $args[0] . " §6không hoạt động!");
                      return true;
                    }
                    if(!is_numeric($args[1])){
                      $sender->sendMessage("§b§l•§6Yêu cầu : Kí tự phải là 1 chữ số");
                      return true;
                    }
                    if($args[0] === $sender->getName()){
                      $sender->sendMessage("§b§l•§6 Không thể tự trao §eRPoint§6 cho bản thân!");
                      return true;
                    }
                    if($rpoint >= $args[1]){
                      $this->reduceRPoint($sender, $args[1]);
                      $this->addRPoint($player2, $args[1]);
                      $sender->sendMessage("§b§l•§6 Thành công trao§e " . $args[1] . " §e§lRPoint §6cho§a " . $args[0]);
                      $player2->sendMessage("§b§l•§6 Người chơi§a " . $sender->getName() . " §6đã trao cho bạn§e " . $args[1] . " RPoint!");
                    }else{
                      $sender->sendMessage("§b§l•§6 Lỗi : Không đủ số RPoint!");
                      return true;
                    }
                  }else{
                    $sender->sendMessage("§b§l•§6 Lệnh: §e/payrpoint {người chơi} {số lượng}");
                  }
                }else{
                  $sender->sendMessage("§b§l•§6 Lệnh: §e/payrpoint {người chơi} {số lượng}");
                }
              }else{
                $sender->sendMessage("Yêu cầu : Sử dụng lệnh trong game!");
              }
              break;
    }
    return true;
  }
}
