<?php

namespace CoreUHC\events;

use pocketmine\event\Listener;

use pocketmine\Player;
use pocketmine\Server;

use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;

use pocketmine\item\Item;

use CoreUHC\Main;

use pocketmine\utils\TextFormat as TF;

use pocketmine\level\Position;

class EventsListener implements Listener{
	
	private $plugin;
	
	public function __construct(Main $plugin) {
		$this->plugin = $plugin;
		$this->server = $plugin->getServer();
	}

	public function getServer(){
		return $this->server;
	}

	public function getPlugin(){
		return $this->plugin;
	}

	public function onHit(EntityDamageEvent $ev){
		$p = $ev->getEntity();
		if($ev instanceof EntityDamageByEntityEvent){
			$damager = $ev->getDamager();
			if($this->getPlugin()->getTeam($damager)->getName() === $this->getPlugin()->getTeam($p)->getName()){
				$ev->setCancelled();
			}
		}
	}
}