<?php
namespace CoreUHC\tasks;

use pocketmine\scheduler\PluginTask;
use pocketmine\plugin\Plugin;

use pocketmine\Player;

use CoreUHC\Main;

use pocketmine\utils\TextFormat as TF;

use pocketmine\level\Position;

class GameTick extends PluginTask{

private $plugin;
	
	public function __construct(Main $plugin){
		parent::__construct($plugin);
		$this->plugin = $plugin;
		$this->server = $plugin->getServer();
	}

	public function getPlugin(){
		return $this->plugin;
	}

	public function getServer(){
		return $this->server;
	}

	public function onRun($tick){
		$time = $this->getPlugin()->match->getTime();
		$this->getPlugin()->match->setTime(($time - 1));
		foreach($this->getServer()->getOnlinePlayers() as $p){
			if($this->getPlugin()->match->getStatus() === Main::GRACE){
				$p->sendTip(Main::PREFIX.TF::GOLD."Grace ends in: ".TF::AQUA.$this->getPlugin()->seconds2string($time).TF::GOLD." Players left: ".TF::AQUA.$this->getPlugin()->match->getAlivePlayers().TF::GOLD." X: ".TF::AQUA.round($p->x).TF::GOLD." Y: ".TF::AQUA.round($p->y).TF::GOLD." Z: ".TF::AQUA.round($p->z));
				if($time === 0){
					$this->getPlugin()->match->setTime(Main::PVP_TIME);
					$this->getPlugin()->match->setStatus(Main::PVP);
					$this->getServer()->broadcastMessage(Main::PREFIX."Grace is now over. PvP is enabled!");
				}
			}
			if($this->getPlugin()->match->getStatus() === Main::PVP){
				$p->sendTip(Main::PREFIX.TF::GOLD."Match ends in: ".TF::AQUA.$this->getPlugin()->seconds2string($time).TF::GOLD." Players left: ".TF::AQUA.$this->getPlugin()->match->getAlivePlayers().TF::GOLD." X: ".TF::AQUA.round($p->x).TF::GOLD." Y: ".TF::AQUA.round($p->y).TF::GOLD." Z: ".TF::AQUA.round($p->z));
				switch($time){
					case 1500:
					$this->getPlugin()->match->setBorder(($this->getPlugin()->match->getBorder() - 200));
					break;
					case 1200:
					$this->getPlugin()->match->setBorder(($this->getPlugin()->match->getBorder() - 200));
					break;
					case 900:
					$this->getPlugin()->match->setBorder(($this->getPlugin()->match->getBorder() - 200));
					break;
					case 600:
					$this->getPlugin()->match->setBorder(($this->getPlugin()->match->getBorder() - 200));
					break;
					case 300:
					$this->getPlugin()->match->setBorder(($this->getPlugin()->match->getBorder() - 200));
					break;
					case 90:
					$this->getPlugin()->match->setBorder(25);
					break;
				}
				if($time <= 5 && $time > 0){
					$this->getServer()->broadcastMessage(Main::PREFIX.TF::GOLD."Match ending in ".TF::AQUA.$time);
				}
				if($time === 1 && $time > 0){
					$this->getPlugin()->endMatch();
				}
			}
		}
	}
}