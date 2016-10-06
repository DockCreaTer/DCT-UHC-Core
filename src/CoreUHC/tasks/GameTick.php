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
		foreach($this->getServer()->getOnlinePlayers() as $p){
			if($this->getPlugin()->match->getStatus() === Main::GRACE){
				$time = $this->getPlugin()->match->getTime();
				$this->getPlugin()->match->setTime(($time - 1));
				$p->sendTip("Grace ends in: ".gmdate("i:s", $time));
			}
		}
	}
}