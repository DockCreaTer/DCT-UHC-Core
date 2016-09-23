<?php
namespace CoreUHC;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\block\IronOre;
use pocketmine\block\GoldOre;
use pocketmine\block\Sand;
use pocketmine\block\Gravel;
use pocketmine\item\Item;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\math\Vector3;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\level\Position;
use pocketmine\utils\TextFormat;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\command\Command;
use pocketmine\level\Level;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\Server;
class Main extends PluginBase implements Listener {
  public function onEnable() {
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
   }
  public function onBreak(BlockBreakEvent $event) {
    if($event->getBlock()->getId() == 15) {
      $drops = array(Item::get(265, 0, 2));
      $event->setDrops($drops);
    }
    if($event->getBlock()->getId() == 17) {
      $drops = array(Item::get(5, 0, rand(1, 3)));
      $event->setDrops($drops);
    }
    if($event->getBlock()->getId() == 14) {
      $drops = array(Item::get(266, 0, 3));
      $event->setDrops($drops);
    }
    if($event->getBlock()->getId() == 18) {
      $drops = array(Item::get(260, 0, 1));
      $event->setDrops($drops);
    }
    if($event->getBlock()->getId() == 56) {
      $drops = array(Item::get(264, 0, 2));
      $event->setDrops($drops);
    }
    if($event->getBlock()->getId() == 83) {
      $drops = array(Item::get(116, 0, 1));
      $event->setDrops($drops);
    }
    if($event->getBlock()->getId() == 12) {
      $drops = array(Item::get(BOW, 0, 1));
      $event->setDrops($drops);
      }
    if($event->getBlock()->getId() == 13) {
      $drops = array(Item::get(ARROW, 0, 4));
      $event->setDrops($drops);
    }
  }
public function onJoin(PlayerJoinEvent $event){
$player = $event->getPlayer();
$name = $player->getName();
$this->getServer()->broadcastPopup("§a+ ".$event->getPlayer()->getName()." joined.");
$event->setJoinMessage("");
}
public function onCommand(CommandSender $sender, Command $cmd, $label, array $args) {
	$cmd = strtolower($cmd->getName());
$players = $sender->getName();
	switch($cmd){
case 'uhc':
if ($sender->isOp()){
switch($args[0]){
case "reset":
foreach($this->getServer()->getOnlinePlayers() as $p){
$p->setHealth(20);
$p->setFood(20);
$p->getInventory()->clearAll();
}
$this->getServer()->broadcastMessage("§a>> reset player succesfuly!");
return true;
break;
case "help":
$sender->sendMessage("<<§aUHC Core §6 Commands>> ");
$sender->sendMessage("§a/uhc reset :§6 Reset the UHC Player");
$sender->sendMessage("§a/uhc kit :§6 Give player Build UHC kit");
$sender->sendMessage("§a/uhc tpall :§6 Tpall player to you for the DeathMatch!");
return true;
break;
case "kit":
foreach($this->getServer()->getOnlinePlayers() as $p){
$p->getInventory()->clearAll();
$p->getInventory()->addItem(Item::get(ITEM::IRON_SWORD));
$p->getInventory()->addItem(Item::get(ITEM::GOLDEN_APPLE, 0, 10));
$p->getInventory()->addItem(Item::get(257, 0, 1));
$p->getInventory()->addItem(Item::get(364, 0, 64));
$p->getInventory()->addItem(Item::get(ITEM::BOW, 0, 1));
$p->getInventory()->addItem(Item::get(ITEM::ARROW, 0, 32));
$p->getInventory()->addItem(Item::get(4, 0, 64));
$p->getInventory()->addItem(Item::get(346, 0, 1));
$p->getInventory()->addItem(Item::get(116, 0, 1));
$p->getInventory()->addItem(Item::get(351, 4, 32));
$p->getInventory()->setHelmet(Item::get(310, 0, 1));
$p->getInventory()->setChestplate(Item::get(307, 0, 1));
$p->getInventory()->setLeggings(Item::get(308, 0, 1));
$p->getInventory()->setBoots(Item::get(313, 0, 1));
$p->getInventory()->sendArmorContents($player);
}	            
$this->getServer()->broadcastMessage("§a[UHC]§6 Get kit BuildUHC!");
return true;
break;
case "food":
foreach($this->getServer()->getOnlinePlayers() as $p){
$p->getInventory()->addItem(Item::get(397,0,64));
}
return true;
break;
case "tpall":
foreach($this->getServer()->getOnlinePlayers() as $p){
$p->teleport(new Vector3($sender->x, $sender->y, $sender->z));
$this->getServer()->broadcastMessage("§a[UHC] §6 deathmatch start good luck!");
}
return true;
break;
}
}else{
	$sender->sendMessage("§cYou are not allowed to do this.");
}
return true;
break;
}
}
  public function onDeath(PlayerDeathEvent $event) {
    $player = $event->getPlayer();
    $name = $player->getName();
    
    $player->setGamemode(3);
}
}
