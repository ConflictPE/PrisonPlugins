<?php

namespace onebone\economyapi\command;

use onebone\economyapi\EconomyAPI;
use pocketmine\command\Command;
use pocketmine\Command\CommandSender;
use pocketmine\utils\TextFormat;

class SetLangCommand extends Command {

	private $plugin;

	public function __construct(EconomyAPI $plugin) {
		$desc = $plugin->getCommandMessage("setlang");
		parent::__construct("setlang", $desc["description"], $desc["usage"]);
		$this->setPermission("economyapi.command.setlang");
		$this->plugin = $plugin;
	}

	public function execute(CommandSender $sender, $label, array $params) {
		if(!$this->plugin->isEnabled())
			return false;
		if(!$this->testPermission($sender)) {
			return false;
		}
		$lang = array_shift($params);
		if(trim($lang) === "") {
			$sender->sendMessage(TextFormat::RED . "Usage: " . $this->getUsage());
			return true;
		}
		if($this->plugin->setPlayerLanguage($sender->getName(), $lang)) {
			$sender->sendMessage($this->plugin->getMessage("language-set", [$lang], $sender->getName()));
		} else {
			$sender->sendMessage(TextFormat::RED . "§6- §cThere is no language such as $lang");
		}
		return true;
	}
}
