<?php

namespace CoreUHC\events;

use pocketmine\Player;

class MatchManager{

	public function __construct(bool $teams, array $players){
		$this->teams = $teams;
		$this->players = $players;
	}

	public function getPlayers(){
		return $this->players;
	}

	public function teamsEnabled(){
		return $this->teams;
	}

	public function removePlayer(Player $player){
		unset($this->players[$player->getName()]);
	}

	public function addPlayer(Player $player){
		$this->players[$player->getName()] = $player->getName();
	}

	public function getAlivePlayers(){
		return count($this->players);
	}
}