<?php

namespace CoreUHC\commands;

use CoreUHC\Main;

use pocketmine\command\CommandSender;

use CoreUHC\commands\CoreUHCCommandListener;

use pocketmine\utils\TextFormat as TF;

class CoreUHCMainCommand extends CoreUHCCommandListener{
	
	private $plugin;
	
	public function __construct(Main $plugin){
		$this->plugin = $plugin;
		$this->server = $this->plugin->getServer();
		parent::__construct($plugin, "uhc", "CoreUHC Main command!", "/uhc", "u");
        $this->setPermission("coreuhc.command.main");
    }
	
	public function getPlugin(){
		return $this->plugin;
	}

	public function getServer(){
		return $this->server; 
	}
	
	public function execute(CommandSender $sender, $commandLabel, array $args){
		if(isset($args[0]) && strtolower($args[0]) === "start"){
			if($this->getPlugin()->match !== null){
				$sender->sendMessage(Main::PREFIX."The match has already started!");
				return;
			}
			$sender->sendMessage(Main::PREFIX."Starting match!");
			$this->getPlugin()->startMatch();
		}

		if(isset($args[0]) && strtolower($args[0]) === "stop"){
			$sender->sendMessage(Main::PREFIX."Stopping match!");
			$this->getPlugin()->endMatch();
		}
	}
}