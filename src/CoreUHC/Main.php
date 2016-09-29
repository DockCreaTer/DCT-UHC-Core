<?php

namespace CoreUHC;

use pocketmine\plugin\PluginBase;

use pocketmine\event\Listener;

use pocketmine\utils\TextFormat as TF;
use pocketmine\utils\Config;

use pocketmine\level\Position;

use pocketmine\Player;

use CoreUHC\events\TeamManager;
use CoreUHC\events\EventsListener;
use CoreUHC\events\MatchManager;
use CoreUHC\commands\CoreUHCTeamCommand;
use CoreUHC\commands\CoreUHCMainCommand;

class Main extends PluginBase implements Listener{

	const PREFIX = TF::GRAY."[".TF::AQUA."CoreUHC".TF::GRAY."]".TF::WHITE;
	const WORLD = "UHC-world";

	public $teams = [];

	public $playerTeam = [];

	public $teamCount = 1;

	public $requester = [];

	public $waiting = [];

	public $teamLimit = 2;// changeable soon(scenarios)

	public function onEnable(){
		@mkdir($this->getDataFolder());
		$this->config = new Config($this->getDataFolder(). "config.yml", Config::YAML, ["UHC-world" => "UHC", "Team-enabled" => false, "team-size" => 2]);
		$this->level = $this->config->get(self::WORLD);
		$this->match = null;
		$this->commands = [new CoreUHCTeamCommand($this), new CoreUHCMainCommand($this)];
		$this->registerCommands();
		$this->getServer()->getPluginManager()->registerEvents(new EventsListener($this), $this);
		if($this->teamsEnabled()) $this->teamLimit = $this->config->get("team-size");		
		$this->getServer()->getLogger()->info(self::PREFIX."Enabled!");
	}

	public function registerCommands(){
		if(count($this->commands) === 0){
			return;
		}
		$this->getServer()->getCommandMap()->registerAll("uhc", $this->commands);
	}

	public function getTeam(Player $player){
		return $this->playerTeam[$player->getName()];
	}

	public function isInTeam(Player $player){
		if(isset($this->playerTeam[$player->getName()])){
			return true;
		}else{
			return false;
		}
	}

	public function closeRequest(Player $player){
		unset($this->requester[$player->getName()]);
		unset($this->waiting[$player->getName()]);
	}

	public function handleRequest(Player $requester, Player $player){
		$this->closeRequest($player);
		$this->requester[$player->getName()] = $requester->getName();
		$this->waiting[$player->getName()] = true;
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
			$this->getServer()->loadLevel($this->level);
			$this->level = $this->getServer()->getLevelByName($this->level);
		}else{
			$this->level = null;
		}
	}

	public function heal(Player $player){
		$player->setMaxHealth($player->getMaxHealth());
		$player->setHealth($player->getMaxHealth());
		$player->setFood(20);
		$player->removeAllEffects();
	}

	public function removeTeam(string $team){
		foreach($this->teams[$team]->getTeammates() as $tm){
			$player = $this->getServer()->getPlayer($tm);
			unset($this->playerTeam[$player->getName()]);
		}
		unset($this->teams[$team]);
	}

	public function newMatch($teams = false, $teamSize = 0, array $players){
		$this->match = new MatchManager($this, $teams, $teamSize, $players);// Soon: scenarios!
		$this->getLogger()->info("[Debug]Created match: ".$this->match->getId()."!");
	}

	public function startMatch(){
		$randx = mt_rand(0, 255);
		$randz = mt_rand(0, 255);
		$teams = false;
		foreach($this->getServer()->getOnlinePlayers() as $p){
			if($this->level === null){
				$this->getServer()->broadcastMessage(self::PREFIX."UHC level is not set or loaded! Please load the world/set it to start a match!");
				$this->setWorld();
				return;
			}
			$p->teleport($this->level->getSafeSpawn());
			if($this->teamsEnabled()){
				if(!isset($this->playerTeam[$p->getName()])){
					$p->close("",Main::PREFIX."You were not on a team!");
				}
				$leader = $this->playerTeam[$p->getName()]->getLeader();
				$leader->teleport(new Position($randz, 60, $randx));
	 /*Maybe?*/ foreach($this->playerTeam[$leader->getName()]->getTeammates() as $tm){
					$tm->teleport($leader);
				}
				$teams = true;
			}
		}
		$teamSize = $this->teamLimit;
		$this->newMatch($teams, $teamSize, $this->getServer()->getOnlinePlayers());
		$this->heal($p);
	}	
}