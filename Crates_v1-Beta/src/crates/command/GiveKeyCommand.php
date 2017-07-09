<?php
/**
 * GiveKeyCommand class
 *
 * Created on Mar 21, 2016 at 8:41:56 PM
 *
 * @author Jack
 */

namespace crates\command;

use crates\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as TF;

class GiveKeyCommand implements CommandExecutor {

	private $plugin;

	public function __construct(Main $plugin) {
		$this->plugin = $plugin;
	}

	public function getPlugin() {
		return $this->plugin;
	}

	public function onCommand(CommandSender $sender, Command $cmd, $label, array $args) {
		if($sender->hasPermission("crates.command.givekey")) {
			if(isset($args[1])) {
				if(isset($this->plugin->settings["keys"][$args[1]])) {
					$amount = 1;
					if(isset($args[2])) {
						$amount = (int) $args[2];
					}
					$name = strtolower((string) $args[0]);
					$id = (int) $args[1];
					$existingKeys = $this->plugin->getKeyData($name);
					$this->plugin->saveKeyData($name, [$id => (isset($existingKeys[$id]) ? $existingKeys[$id] : 0) + $amount]);
					$sender->sendMessage(TF::GREEN . "Key given.");
					//					$this->plugin->giveKey($args[0], $args[1], $amount);
					return true;
				} else {
					$sender->sendMessage(TF::RED . "Couldn't find a crate key with ID " . $args[1] . "!");
					return true;
				}
			} else {
				$sender->sendMessage(TF::RED . "Please specify a player and a crate key ID!");
				return true;
			}
		} else {
			$sender->sendMessage(TF::RED . "You do not have permission to use this command!");
			return true;
		}
	}

}
