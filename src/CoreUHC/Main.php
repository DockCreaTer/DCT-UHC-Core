<?php

namespace CoreUHC;

use pocketmine\plugin\PluginBase;

use pocketmine\event\Listener;

use pocketmine\utils\TextFormat as TF;
use pocketmine\utils\Config;

use pocketmine\level\Position;

use pocketmine\Player;

use pocketmine\entity\Effect;

use CoreUHC\events\TeamManager;
use CoreUHC\events\EventsListener;
use CoreUHC\events\MatchManager;
use CoreUHC\events\BorderListener;
use CoreUHC\commands\CoreUHCTeamCommand;
use CoreUHC\commands\CoreUHCMainCommand;
use CoreUHC\tasks\GameTick;
use CoreUHC\tasks\HeartBeatTask;

class Main extends PluginBase implements Listener{

	const PREFIX = TF::GRAY."[".TF::AQUA."CoreUHC".TF::GRAY."]" . TF::WHITE;
	const WORLD = "UHC-world";

	const GRACE = "Grace";
	const WAITING = "Waiting...";
	const PVP = "PvP";
	const ENDED = "Ended";

	const GRACE_TIME = 1200;//Will change
	const PVP_TIME = 1800;//Will change

	const BORDER = 1000;

	public $teams = [];

	public $kills = [];

	public $playerTeam = [];

	public $teamCount = 1;

	public $requester = [];

	public $waiting = [];

	public $teamLimit = 2;// changeable soon(scenarios)

	public $time = 0;

	public $task = null;

	public $whitelist = [];

	public $queue = [];

	public function onEnable(){
		@mkdir($this->getDataFolder());
		$this->config = new Config($this->getDataFolder(). "config.yml", Config::YAML, ["UHC-world" => "UHC", "Team-enabled" => false, "team-size" => 2]);
		$this->level = $this->config->get(self::WORLD);
		$this->match = null;
		$this->commands = [new CoreUHCTeamCommand($this), new CoreUHCMainCommand($this)];
		$this->registerCommands();
		$this->getServer()->getPluginManager()->registerEvents(new EventsListener($this), $this);
		if($this->teamsEnabled()) $this->teamLimit = $this->config->get("team-size");	
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new HeartBeatTask($this), 20);
		$this->border = null;	
		$this->getServer()->getLogger()->info(self::PREFIX."Plugin has been enabled!");
		/*if(!$this->getServer()->isLevelGenerated($this->level)){
			$this->getServer()->generateLevel($this->level, rand(50,10000), \pocketmine\level\generator\Generator::getGeneratorName(\pocketmine\level\generator\Generator::getGenerator("Default")), []);
			$this->getServer()->loadLevel($this->level);
		}*/
	}

	public function onDisable(){
		
	}

	public function registerCommands(){
		if(count($this->commands) === 0){
			return;
		}
		$this->getServer()->getCommandMap()->registerAll("uhc", $this->commands);
	}

	public function getTeam(Player $player){
		if(isset($this->playerTeam[$player->getName()])){
			return $this->playerTeam[$player->getName()];
		}else{
			return;
		}
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

	public function getTeamById(int $id){
		return $this->teams["Team".$id];
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
		if(!$player->isOnline()) return;
		$player->setMaxHealth($player->getMaxHealth());
		$player->setHealth($player->getMaxHealth());
		$player->setFood(20);
		$player->removeAllEffects();
	}

	public function removeTeam(string $team){
		foreach($this->teams[$team]->getTeammates() as $tm){
			$player = $this->getServer()->getPlayer($tm);
			$player->sendMessage(Main::PREFIX."Team has been disbanded by ".$this->teams[$team]->getLeader()->getName()."!");
			unset($this->playerTeam[$player->getName()]);
		}
		unset($this->teams[$team]);
		$this->teamCount--;
	}

	public function createTask(){
    	$task = new GameTick($this);
   	 	$h = $this->getServer()->getScheduler()->scheduleRepeatingTask($task, 20);
    	$task->setHandler($h);
    	$this->task = $task->getTaskId();
	}

	public function cancelTask(){
    	$this->getServer()->getScheduler()->cancelTask($this->task);
    	unset($this->task);
	}

	public function newMatch($teams = false, $teamSize = 0, array $players, $status = self::GRACE, $time = self::GRACE_TIME, $border = self::BORDER){
		$this->match = new MatchManager($this, $teams, $teamSize, $players, $status, $time, $border);// Soon: scenarios!
		$this->createTask();
		$this->border = new BorderListener($this->level->getSpawnLocation()->getX(), $this->level->getSpawnLocation()->getZ(), self::BORDER);
	}

	public function removePlayer(Player $player){
		if($this->match === null) return;
		if(!$player->isOnline()) return;
		if($this->teamsEnabled()){
			unset($this->playerTeam[$player->getName()]);
			$this->getTeam($player)->removePlayer($player);
		}
		unset($this->kills[$player->getName()]);
		foreach($this->getServer()->getOnlinePlayers() as $p){
			$p->sendMessage(Main::PREFIX.$player->getName()." has been eliminated!");
		}
		$this->match->removePlayer($player);
	}

	public function getKills(Player $player){
		return $this->kills[$player->getName()];
	}

	public function updateKills(Player $player){
		$this->kills[$player->getName()]++;
	}

	public function giveEffects(Player $p){
		$effect = Effect::getEffect(Effect::DAMAGE_RESISTANCE);
		$effect->setDuration(20*45);
		$effect->setAmplifier(10);
		$p->addEffect($effect);
	}

	public function seconds2string($int){
    	$m = floor($int / 60);
    	$s = floor($int % 60);
    	if($m <= 0 && $s <= 0){
    		return 0;
    	}else{
    		return (($m < 10 ? "0" : "") . $m . ":" . ($s < 10 ? "0" : "") . $s);
    	}
    }

    public function endMatch(){
    	/* maybe a task? */
    	$this->getServer()->shutdown();
    }

	public function startMatch(){
		$randx = mt_rand(-255, 255);
		$randz = mt_rand(-255, 255);
		$teams = false;
		$this->setWorld();
		foreach($this->getServer()->getOnlinePlayers() as $p){
			if($this->level === null){
				$this->getServer()->broadcastMessage(self::PREFIX."UHC level is not set or loaded! Please load the world/set it to start a match!");
				return;
			}
			$p->teleport($this->level->getSpawnLocation());
			$p->teleport($p->add(0, $p->getLevel()->getHighestBlockAt($p->getX(), $p->getZ()) + 1));
			$this->level->generateChunk($p->x, $p->z);
			$this->heal($p);
			$this->giveEffects($p);
			if($this->teamsEnabled()){
				if(!isset($this->playerTeam[$p->getName()])){
					$p->close(" ",Main::PREFIX."You were not on a team!");
				}
				$leader = $this->getTeam($p)->getLeader();
				$leader->teleport(new Position($randz, 100, $randx));
	 /*Maybe?*/ foreach($this->getTeam($leader)->getTeammates() as $tm){
	 				$tm = $this->getServer()->getPlayer($tm);
					$tm->teleport($leader);
				}
				$teams = true;
				//echo var_dump($this->teams);
			}else{
				$p->teleport(new Position($randz, 100, $randx));
			}
			$this->kills[$p->getName()] = 0;
			$this->whitelist[$p->getName()] = $p->getName();
		}
		$teamSize = $this->teamLimit;
		$this->newMatch($teams, $teamSize, $this->getServer()->getOnlinePlayers(), self::GRACE, self::GRACE_TIME, self::BORDER);
		$this->getServer()->broadcastMessage(self::PREFIX."The UHC match has started!");
	}	
}
