<?php
/*
 * This file is a part of LaunchPad.
 * Copyright (C) 2015 CyberCube-HK
 *
 * LaunchPad is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * LaunchPad is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with LaunchPad. If not, see <http://www.gnu.org/licenses/>.
 */

namespace hoyinm14mc\launchpad;

use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;

class LaunchPadCommand extends PluginBase implements CommandExecutor {

	private $plugin;

	public function __construct(LaunchPad $plugin) {
		$this->plugin = $plugin;
	}

	public function onCommand(CommandSender $issuer, Command $cmd, $label, array $args) {
		switch($cmd->getName()) {
			case "launchpad":
				if(isset($args[0]) !== true) {
					return false;
				}
				switch($args[0]) {
					case "create":
						if($issuer instanceof Player !== true) {
							$issuer->sendMessage($this->plugin->colourMessage("&eCommand only works in-game!"));
							return true;
						}
						if($issuer->hasPermission("launchpad.command") !== true || $issuer->hasPermission("launchpad.create") !== true) {
							$issuer->sendMessage($this->plugin->colourMessage("&cYou don't have permission for this!"));
							return true;
						}
						$this->plugin->create_mode[$issuer->getName()] = $issuer->getName();
						$issuer->sendMessage($this->plugin->colourMessage("&aTap a block to create a launchpad!"));
						return true;
						break;
					case "destroy":
						if($issuer instanceof Player !== true) {
							$issuer->sendMessage($this->plugin->colourMessage("&eCommand only works in-game!"));
							return true;
						}
						if($issuer->hasPermission("launchpad.command") !== true || $issuer->hasPermission("launchpad.destroy") !== true) {
							$issuer->sendMessage($this->plugin->colourMessage("&cYou don't have permission for this!"));
							return true;
						}
						$this->plugin->destroy_mode[$issuer->getName()] = $issuer->getName();
						$issuer->sendMessage($this->plugin->colourMessage("&aTap a block to destroy the launchpad!"));
						return true;
						break;
					case "version":
						if($issuer->hasPermission("launchpad.command") !== true) {
							$issuer->sendMessage($this->plugin->colourMessage("&cYou don't have permission for this!"));
							return true;
						}
						$issuer->sendMessage($this->plugin->colourMessage("LaunchPad v" . $this->plugin->getDescription()->getVersion() . " by hoyinm14mc"));
						return true;
						break;
				}
				break;
		}
	}

}

?>