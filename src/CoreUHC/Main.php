<?php

namespace CoreUHC;

use pocketmine\plugin\PluginBase;

use pocketmine\event\Listener;

use pocketmine\utils\TextFormat as TF;
use pocketmine\utils\Config;

use pockemtine\Player;

use CoreUHC\events\TeamManager;

class Main extends PluginBase implements Listener{

	const PREFIX = TF::GRAY."[".TF::AQUA."CoreUHC".TF::GRAY."]".TF::WHITE;
	const WORLD = "UHC-world";

	public $teams = [];

	public function onEnable(){
		$this->getServer()->getLogger()->info(self::PREFIX."Enabled!");
		@mkdir($this->getDataFolder());
		$this->config = new Config($this->getDataFolder(). "config.yml", Config::YAML, ["UHC-world" => "UHC", "Team-enabled" => false]);
		$this->level = $this->config->get(self::WORLD);
	}

	public function getTeam(Player $player){
		// TODO: get a players team
	}

	public function setTeam(Player $player, string $team, array $teammates){
		$this->teams[$team] = new TeamManager($team, $teammates);
	}

	public function teamsEnabled(){
		if($this->config->get("Team-enabled") === true){
			return true;
		}else{
			return false;
		}
	}

	public function setWorld(){
		if($this->getServer()->isLevelGenerated($this->level)){
			$this->level = $this->getServer()->getLevelByName($this->level);
		}else{
			$this->level = null;
		}
	}

	public function startMatch(){
		foreach($this->getServer()->getOnlinePlayers() as $p){
			if($this->level === null){
				$this->getServer()->broadcastMessage(self::PREFIX."UHC level is not set or loaded! Please load the world/set it to start a match!");
				$this->setWorld();
				return;
			}
			$p->teleport($this->level->getSafeSpawn());
		}
	}	
}