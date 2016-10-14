<?php

namespace CoreUHC\events;

use pocketmine\Player;

use CoreUHC\Main;

class MatchManager{

	public function __construct(Main $plugin, bool $teams, int $teamSize, array $players, $status, int $time, int $border){
		$this->plugin = $plugin;
		$this->teams = $teams;
		$this->players = $players;
		$this->id = rand(10,60);
		$this->teamSize = $teamSize;
		$this->status = $status;
		$this->time = $time;
		$this->border = $border;
	}

	public function getPlugin(){
		return $this->plugin;
	}

	public function getId(){
		return $this->id;
	}

	public function getPlayers(){
		return $this->players;
	}

	public function teamsEnabled(){
		return $this->teams;
	}

	public function removePlayer(Player $player){
		$key = array_search($player->getName(), $this->players);
		unset($this->players[$key]);
		$this->getPlugin()->getServer()->removeWhitelist($player->getName());
		$player->close(" ",Main::PREFIX."You were eliminated!");
	}

	public function addPlayer(Player $player){
		array_push($this->players, $player->getName());
	}

	public function isInMatch(Player $player){
		$key = array_search($player->getName(), $this->players);
		if(isset($this->players[$key])){
			return true;
		}else{
			return false;
		}
	}

	public function getAlivePlayers(){
		return count($this->players);
	}

	public function getTeamSize(){
		return $this->teamSize;
	}

	public function getStatus(){
		return $this->status;
	}

	public function getNextStatus(){
		if($this->status === Main::GRACE){
			return Main::PVP_TIME;
		}
		if($this->status === Main::PVP){
			return 0;
		}
	}

	public function setStatus($status){
		$this->status = $status;
	}

	public function getTime(){
		return $this->time;
	}

	public function setTime(int $time){
		$this->time = $time;
	}

	public function getBorder(){
		return $this->border;
	}

	public function setBorder(int $int){
		$this->border = $int;
		$x = $this->getPlugin()->level->getSpawnLocation()->getX();
		$z = $this->getPlugin()->level->getSpawnLocation()->getZ();
		$this->getPlugin()->border->changeBorder($x, $z, $int);
	}
}