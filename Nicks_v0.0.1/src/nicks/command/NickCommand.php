<?php

namespace nicks\command;

use nicks\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\utils\TextFormat;

class NickCommand implements CommandExecutor, PluginIdentifiableCommand {

	/** @var Main */
	private $plugin;

	public function __construct(Main $plugin) {
		$this->plugin = $plugin;
		$plugin->getCommand("nick")->setExecutor($this);
	}

	/**
	 * @return Main
	 */
	public function getPlugin() {
		return $this->plugin;
	}

	public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
		$subCommand = array_shift($args);
		switch(strtolower($subCommand)) {
			case "set":
			case "new":
			case "on":
				if($sender->hasPermission("nick.command.set")) {
					if(isset($args[0])) {
						$player = array_shift($args);
						if(isset($args[0])) {
							$this->getPlugin()->setNick($player, implode(" ", $args));
							$sender->sendMessage(TextFormat::GOLD . "- " . TextFormat::GREEN . "Set {$player}'s nick successfully!");
							return true;
						} else {
							$sender->sendMessage(TextFormat::GOLD . "- " . TextFormat::RED . "Please specify a nick!");
							return true;
						}
					} else {
						$sender->sendMessage(TextFormat::GOLD . "- " . TextFormat::RED . "Please specify a player!");
						return true;
					}
				}
				break;
			case "reset":
			case "off":
			case "remove":
				if($sender->hasPermission("nick.command.remove")) {
					if(isset($args[0])) {
						$player = array_shift($args);
						$this->getPlugin()->removeNick($player);
						$sender->sendMessage(TextFormat::GOLD . "- " . TextFormat::GREEN . "Removed {$player}'s nick successfully!");
						return true;
					} else {
						$sender->sendMessage(TextFormat::GOLD . "- " . TextFormat::RED . "Please specify a player!");
						return true;
					}
				}
				break;
			default:
				$sender->sendMessage(TextFormat::GOLD . "- " . TextFormat::RED . "Usage: /nick <set|remove> <player> <nick>");
				return true;
				break;
		}
	}

}