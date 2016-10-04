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

	public function onDeath(PlayerDeathEvent $ev){
		$player = $ev->getEntity();
		$cause = $player->getLastDamageCause();
		$ev->setDeathMessage(null);
		switch($cause === null ? EntityDamageEvent::CAUSE_CUSTOM : $cause->getCause()){
				case EntityDamageEvent::CAUSE_ENTITY_ATTACK:
					if($cause instanceof EntityDamageByEntityEvent){
						$e = $cause->getDamager();
						if($e instanceof Player){
							$this->getServer()->broadcastMessage(Main::PREFIX.$player->getName()." was killed by ".$e->getName()."!");
							break;
						}elseif($e instanceof Living){
							$this->getServer()->broadcastMessage(Main::PREFIX.$player->getName()." was killed by ".$e->getNameTag()."!");
							break;
						}else{
							$this->getServer()->broadcastMessage(Main::PREFIX.$player->getName()." died!");
						}
					}
					break;
				case EntityDamageEvent::CAUSE_PROJECTILE:
					if($cause instanceof EntityDamageByEntityEvent){
						$e = $cause->getDamager();
						if($e instanceof Player){
							$this->getServer()->broadcastMessage(Main::PREFIX.$player->getName()." was shot by ".$e->getName()."!");
						}elseif($e instanceof Living){
							$this->getServer()->broadcastMessage(Main::PREFIX.$player->getName()." died due to an arrow!");
							break;
						}else{
							$this->getServer()->broadcastMessage(Main::PREFIX.$player->getName()." died due to an arrow!");
						}
					}
					break;
				case EntityDamageEvent::CAUSE_SUICIDE:
					$this->getServer()->broadcastMessage(Main::PREFIX.$player->getName()." killed themselves!");;
					break;
				case EntityDamageEvent::CAUSE_VOID:
					$this->getServer()->broadcastMessage(Main::PREFIX.$player->getName()." fell through the world! ELIMINATED!");
					break;
				case EntityDamageEvent::CAUSE_FALL:
					if($cause instanceof EntityDamageEvent){
						if($cause->getFinalDamage() > 2){
							$this->getServer()->broadcastMessage(Main::PREFIX.$player->getName()." got their ankle broken!");
							break;
						}
					}
					$this->getServer()->broadcastMessage(Main::PREFIX.$player->getName()." died!");
					break;

				case EntityDamageEvent::CAUSE_SUFFOCATION:
					$this->getServer()->broadcastMessage(Main::PREFIX.$player->getName()." died because they can't phase through blocks!");
					break;

				case EntityDamageEvent::CAUSE_LAVA:
					$this->getServer()->broadcastMessage(Main::PREFIX.$player->getName()." died due to LAVA!");
					break;

				case EntityDamageEvent::CAUSE_FIRE:
					$this->getServer()->broadcastMessage(Main::PREFIX.$player->getName()." died due to fire!");
					break;

				case EntityDamageEvent::CAUSE_FIRE_TICK:
					$this->getServer()->broadcastMessage(Main::PREFIX.$player->getName()." died due to fire!");
					break;

				case EntityDamageEvent::CAUSE_DROWNING:
					$this->getServer()->broadcastMessage(Main::PREFIX.$player->getName()." couldn't swim, so they died!");
					break;

				case EntityDamageEvent::CAUSE_CONTACT:
					if($cause instanceof EntityDamageByBlockEvent){
						if($cause->getDamager()->getId() === Block::CACTUS){
							$this->getServer()->broadcastMessage(Main::PREFIX.$player->getName()." died due to cacti!");
						}
					}
					break;

				case EntityDamageEvent::CAUSE_BLOCK_EXPLOSION:
				case EntityDamageEvent::CAUSE_ENTITY_EXPLOSION:
					if($cause instanceof EntityDamageByEntityEvent){
						$e = $cause->getDamager();
						if($e instanceof Player){
							$this->getServer()->broadcastMessage(Main::PREFIX.$player->getName()." was blown up by ".$e->getName()."!");
						}elseif($e instanceof Living){
							$this->getServer()->broadcastMessage(Main::PREFIX.$player->getName()." blew up!");
							break;
						}
					}else{
						$this->getServer()->broadcastMessage(Main::PREFIX.$player->getName()." died!");
					}
					break;

				case EntityDamageEvent::CAUSE_MAGIC:
					$this->getServer()->broadcastMessage(Main::PREFIX.$player->getName()." died!");
					break;

				case EntityDamageEvent::CAUSE_CUSTOM:
					$this->getServer()->broadcastMessage(Main::PREFIX.$player->getName()." died!");
					break;
		}
	}
}
