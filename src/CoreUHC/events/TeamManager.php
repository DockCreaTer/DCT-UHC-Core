<?php

namespace CoreUHC\events;

use pocketmine\Player;

class TeamManager{

	public function __construct(string $team){
		$this->team = $team;
		$this->teammates = [];
		$this->leader = null;
	}

	public function getName(){
		return $this->team;
	}

	public function getTeammates(){
		return $this->teammates;
	}

	public function removePlayer(Player $player){
		$key = array_search($player->getName(), $this->teammates);
		unset($this->teammates[$key]);
	}

	public function addPlayer(Player $player){
		array_push($this->teammates, $player->getName());
	}

	public function setLeader(Player $player){
		$this->leader = $player;
	}

	public function getLeader(){
		return $this->leader;
	}

	public function getPlayerCount(){
		return count($this->teammates);
	}
}