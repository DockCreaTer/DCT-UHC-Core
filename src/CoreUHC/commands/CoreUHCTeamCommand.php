<?php

namespace CoreUHC\commands;

use CoreUHC\Main;

use pocketmine\command\CommandSender;

use CoreUHC\commands\CoreUHCCommandListener;

use pocketmine\utils\TextFormat as TF;

class CoreUHCTeamCommand extends CoreUHCCommandListener{
	
	private $plugin;
	
	public function __construct(Main $plugin){
		$this->plugin = $plugin;
		$this->server = $this->plugin->getServer();
		parent::__construct($plugin, "team", "CoreUHC Team command!", "/team", "t");
        $this->setPermission("coreuhc.command.team");
    }
	
	public function getPlugin(){
		return $this->plugin;
	}

	public function getServer(){
		return $this->server; 
	}
	
	public function execute(CommandSender $sender, $commandLabel, array $args){
		$count = $this->getPlugin()->teamCount;
		if(!$this->getPlugin()->teamsEnabled()){
			$sender->sendMessage(Main::PREFIX."Teams are not enabled!");
			return;
		}
		if(isset($args[0]) && strtolower($args[0]) === "create"){
			if(isset($this->getPlugin()->playerTeam[$sender->getName()])){
				$sender->sendMessage(Main::PREFIX."You already are in a team/own a team!");
				return;
			}
			$this->getPlugin()->createTeam($sender, "Team".$count);
			$count++;
		}
		if(isset($args[0]) && strtolower($args[0]) === "invite"){
			if($this->getPlugin()->isInTeam($sender) && $this->getPlugin()->getTeam($sender)->getLeader()->getName() === $sender->getName()){
				if(count($this->getPlugin()->getTeam($sender)->getPlayerCount()) === $this->getPlugin()->teamLimit){
					$sender->sendMessage(Main::PREFIX."You have the max player limit!");	
					return;
				}
				if(isset($args[1])){
					$player = $this->getServer()->getPlayer($args[1]);
					if($player === null){
						$sender->sendMessage(Main::PREFIX."That player isn't online!");
						return;
					}
					$player->sendMessage(Main::PREFIX.$sender->getName()." sent you a team request please do /team accept to accept!");
					$sender->sendMessage(Main::PREFIX."Sent a team request to ".$sender->getName()."!");
					$this->getPlugin()->handleRequest($sender, $player);
				}else{
					$sender->sendMessage(Main::PREFIX."Please specify a player!");
				}
			}else{
				$sender->sendMessage(Main::PREFIX."Please join/create a team to use this command!");
			}
		}
		if(isset($args[0]) && strtolower($args[0]) === "accept"){
			if(isset($this->getPlugin()->waiting[$sender->getName()])){
				$requester = $this->getServer()->getPlayer($this->getPlugin()->requester[$sender->getName()]);
				$sender->sendMessage(Main::PREFIX."Accepted ".$requester->getName()."'s team request!");
				$requester->sendMessage(Main::PREFIX.$sender->getName()." joined your team!");
				$this->getPlugin()->playerTeam[$sender->getName()] = $this->teams[$this->getPlugin()->getTeam($requester)->getName()];
				$this->getTeam($requester)->addPlayer($sender);
				$this->getPlugin()->closeRequest($sender);
				// teammate message?
			}else{
				$sender->sendMessage(Main::PREFIX."You don't have any team request!");
			}
		}
	}
}