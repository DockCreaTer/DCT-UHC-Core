<?php

namespace CoreUHC\events;

use pocketmine\Player;

use CoreUHC\Main;

class MatchManager{

	public function __construct(Main $plugin, bool $teams, int $teamSize, array $players){
		$this->plugin = $plugin;
		$this->teams = $teams;
		$this->players = $players;
		$this->id = rand(10,60);
		$this->teamSize = $teamSize;
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
		unset($this->players[$player->getName()]);
	}

	public function addPlayer(Player $player){
		$this->players[$player->getName()] = $player->getName();
	}

	public function getAlivePlayers(){
		return count($this->players);
	}

	public function getTeamSize(){
		return $this->teamSize;
	}
}