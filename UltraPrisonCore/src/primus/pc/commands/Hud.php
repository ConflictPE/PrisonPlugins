<?php
/**
 * Created by PhpStorm.
 * User: Jack
 * Date: 23/10/2016
 * Time: 1:36 AM
 */

namespace primus\pc\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use primus\pc\PrisonCore;

class Hud extends Command implements PluginIdentifiableCommand {

	/** @var PrisonCore */
	private $plugin;

	public function __construct(PrisonCore $plugin) {
		parent::__construct("hud", "Toggle your HUD!", "/hud");
		$this->plugin = $plugin;
		$this->setPermission("pc.command.hud");
	}

	/**
	 * @return PrisonCore
	 */
	public function getPlugin() {
		return $this->plugin;
	}

	public function execute(CommandSender $sender, $commandLabel, array $args) {
		if($sender instanceof Player) {
			$name = $sender->getName();
			if(isset($this->plugin->exemptHud[$name])) {
				unset($this->plugin->exemptHud[$name]);
				$sender->sendMessage(TextFormat::GOLD . "- " . TextFormat::GREEN . "Enabled HUD successfully!");
				return true;
			} else {
				$this->plugin->exemptHud[$name] = $name;
				$sender->sendMessage(TextFormat::GOLD . "- " . TextFormat::GREEN . "Disabled HUD successfully!");
				return true;
			}
		} else {
			$sender->sendMessage(TextFormat::RED . "Please run this command in-game");
			return true;
		}
	}

}