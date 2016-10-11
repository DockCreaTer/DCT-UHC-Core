<?php
namespace CoreUHC\tasks;

use pocketmine\scheduler\PluginTask;
use pocketmine\plugin\Plugin;

use pocketmine\Player;

use CoreUHC\Main;

use pocketmine\utils\TextFormat as TF;

use pocketmine\level\Position;

class HeartBeatTask extends PluginTask{

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
		if($this->getPlugin()->match === null){
			foreach($this->getServer()->getOnlinePlayers() as $p){
				$p->sendTip(Main::PREFIX.TF::GOLD."The match will start soon. Players online: ".TF::AQUA.count($this->getServer()->getOnlinePlayers()).TF::WHITE."/".TF::AQUA.$this->getServer()->getMaxPlayers());
			}
		}
	}
}