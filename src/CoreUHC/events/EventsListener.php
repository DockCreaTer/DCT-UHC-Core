<?php

namespace CoreUHC\events;

use pocketmine\event\Listener;

use pocketmine\Player;
use pocketmine\Server;

use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;

use pocketmine\math\Vector3;

use pocketmine\item\Item;

use pocketmine\block\Block;

use pocketmine\entity\Living;

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

	public function onMove(PlayerMoveEvent $ev){
		$p = $ev->getPlayer();
		$p->getLevel()->loadChunk($p->x, $p->z);
		//Chunk error in console: $p->getLevel()->requestChunk($p->x, $p->z, $p);
	}

	public function onPlayerLogin(PlayerPreLoginEvent $ev){
		$p = $ev->getPlayer();
		if($this->getPlugin()->match !== null && !$p->isOp() && !isset($this->getPlugin()->whitelist[$p->getName()])){
			$ev->setCancelled();
			$ev->setKickMessage(Main::PREFIX.TF::GOLD."Match is already running. There is ".$this->getPlugin()->seconds2string($this->getPlugin()->match->getTime())." mins/secs left in the ".$this->getPlugin()->match->getStatus()." stage!\n Try again in ".$this->getPlugin()->seconds2string(($this->getPlugin()->match->getNextStatus() - $this->getPlugin()->match->getTime()))." mins/secs!");
		}
	}

	public function onJoin(PlayerJoinEvent $ev){
		$p = $ev->getPlayer();
		$ev->setJoinMessage(null);
		if($this->getPlugin()->match === null) $this->getPlugin()->heal($p);
	}

	public function onQuit(PlayerQuitEvent $ev){
		$p = $ev->getPlayer();
		$ev->setQuitMessage(null);
		if($this->getPlugin()->match !== null) $this->getPlugin()->queue[$p->getName()] = 300;//todo taskkk
	}

	public function onHeal(EntityRegainHealthEvent $ev){
		if($ev->getRegainReason() === EntityRegainHealthEvent::CAUSE_REGEN){
			$ev->setCancelled();
		}
		if($ev->getRegainReason() === EntityRegainHealthEvent::CAUSE_EATING){
			$ev->setCancelled();
		}
		if($ev->getRegainReason() === EntityRegainHealthEvent::CAUSE_SATURATION){
			$ev->setCancelled();
		}
	}
	
	public function onEat(PlayerItemConsumeEvent $ev){
		$item = $ev->getItem();
		$p = $ev->getPlayer();
		if($item instanceof \pocketmine\item\Food){
			$p->setFood($p->getFood() + $item->getFoodRestore());
		}else{
			$p->setFood($p->getFood() + 1);
		}
	}

	public function onHit(EntityDamageEvent $ev){
		$p = $ev->getEntity();
		if($ev instanceof EntityDamageByEntityEvent){
			$damager = $ev->getDamager();
			if($this->getPlugin()->teamsEnabled() && $this->getPlugin()->getTeam($damager)->getName() === $this->getPlugin()->getTeam($p)->getName()){
				$ev->setCancelled();
			}
			if($this->getPlugin()->match->getStatus() === Main::GRACE){
				$ev->setCancelled();
			}
		}
	}

	public function onDeath(PlayerDeathEvent $ev){
		$player = $ev->getEntity();
		$cause = $player->getLastDamageCause();
		$ev->setDeathMessage(null);
		if($player instanceof Player){
			if($ev instanceof EntityDamageByEntityEvent){
				if($this->getPlugin()->match !== null && $this->getPlugin()->match->getAlivePlayers() === 1 && $this->getPlugin()->match->getStatus() === Main::PVP && !$this->getPlugin()->teamsEnabled()){
					foreach($this->getServer()->getOnlinePlayers() as $p){
						$p->sendMessage(Main::PREFIX.$ev->getDamager()->getName()." won the match!");
					}
				}
					$this->getPlugin()->removePlayer($player);
					$this->getPlugin()->updateKills($ev->getDamager());
				}else{
					$this->getPlugin()->removePlayer($player);
				}
				if($this->getPlugin()->match !== null && $this->getPlugin()->match->getAlivePlayers() === 1 && $this->getPlugin()->match->getStatus() === Main::PVP && $this->getPlugin()->teamsEnabled()){
					if($this->getPlugin()->teamCount === 1){
						$key = array_keys($this->getPlugin()->teams);
						$team = $this->getPlugin()->teams[$key[0]];
						foreach($team->getTeammates() as $tm){
							$tm = $this->getServer()->getPlayer($tm);
							// stuf here
						}
						$this->getServer()->broadcastMessage($team->getName()." won the match!");
					}
				}
			}
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
					$this->getServer()->broadcastMessage(Main::PREFIX.$player->getName()." killed theirself!");
					break;
				case EntityDamageEvent::CAUSE_VOID:
					$this->getServer()->broadcastMessage(Main::PREFIX.$player->getName()." fell through the world!");
					break;
				case EntityDamageEvent::CAUSE_FALL:
					if($cause instanceof EntityDamageEvent){
						if($cause->getFinalDamage() > 2){
							$this->getServer()->broadcastMessage(Main::PREFIX.$player->getName()." got their ankles broken!");
							break;
						}
					}
					$this->getServer()->broadcastMessage(Main::PREFIX.$player->getName()." died!");
					break;

				case EntityDamageEvent::CAUSE_SUFFOCATION:
					$this->getServer()->broadcastMessage(Main::PREFIX.$player->getName()." died because they can't phase through blocks!");
					break;

				case EntityDamageEvent::CAUSE_LAVA:
					$this->getServer()->broadcastMessage(Main::PREFIX.$player->getName()." died due to lava!");
					break;

				case EntityDamageEvent::CAUSE_FIRE:
					$this->getServer()->broadcastMessage(Main::PREFIX.$player->getName()." died due to fire!");
					break;

				case EntityDamageEvent::CAUSE_FIRE_TICK:
					$this->getServer()->broadcastMessage(Main::PREFIX.$player->getName()." died due to fire!");
					break;

				case EntityDamageEvent::CAUSE_DROWNING:
					$this->getServer()->broadcastMessage(Main::PREFIX.$player->getName()." couldn't swim!");
					break;

				case EntityDamageEvent::CAUSE_CONTACT:
					if($cause instanceof EntityDamageByBlockEvent){
						if($cause->getDamager()->getId() === Block::CACTUS){
							$this->getServer()->broadcastMessage(Main::PREFIX.$player->getName()." died due to prickly cactus!");
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
