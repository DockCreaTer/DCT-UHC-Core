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

	public $playerTeam = [];

	public function onEnable(){
		$this->getServer()->getLogger()->info(self::PREFIX."Enabled!");
		@mkdir($this->getDataFolder());
		$this->config = new Config($this->getDataFolder(). "config.yml", Config::YAML, ["UHC-world" => "UHC", "Team-enabled" => false]);
		$this->level = $this->config->get(self::WORLD);
	}

	public function getTeam(Player $player){
		return $this->playerTeam[$player->getName()];
	}

	public function setTeam(Player $player, string $team){
		$this->playerTeam[$player->getName()] = $this->teams[$team];
		$player->sendMessage(self::PREFIX."You are now on team: ".$team);
	}

	public function createTeam(Player $creator, string $team){
		if(isset($this->teams[$team])){
			$creator->sendMessage(self::PREFIX."This is already a team!");
			return;
		}
		$this->teams[$team] = new TeamManager($team);
		$this->teams[$team]->addPlayer($creator);
		$this->teams[$team]->setLeader($creator);
		$this->playerTeam[$creator->getName()] = $this->teams[$team];
		$creator->sendMessage(self::PREFIX."Created team: ".$team);
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