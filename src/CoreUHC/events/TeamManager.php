<?php

namespace CoreUHC\events;

use pocketmine\Player;

class TeamManager{

	public function __construct(string $team, array $teammates){
		$this->team = $team;
		$this->teammates = $teammates;
	}

	public function getName(){
		return $this->team;
	}

	public function getTeammates(){
		return $this->teammates;
	}

	public function addPlayer(Player $player){
		array_push($this->teammates, $player->getName());
	}
}