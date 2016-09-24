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
		if(isset($args[0]) && strtolower($args[0]) === "create"){
			if(isset($this->getPlugin()->playerTeam[$sender->getName()])){
				$sender->sendMessage(Main::PREFIX."You already are in a team/own a team!");
				return;
			}
			$this->getPlugin()->createTeam($sender, "Team".$count);
			$count++;
		}
	}
}