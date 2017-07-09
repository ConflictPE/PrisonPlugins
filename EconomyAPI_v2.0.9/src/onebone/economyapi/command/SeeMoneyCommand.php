<?php

namespace onebone\economyapi\command;

use onebone\economyapi\EconomyAPI;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class SeeMoneyCommand extends Command {

	private $plugin;

	public function __construct(EconomyAPI $plugin) {
		$desc = $plugin->getCommandMessage("seemoney");
		parent::__construct("seemoney", $desc["description"], $desc["usage"]);
		$this->setPermission("economyapi.command.seemoney");
		$this->plugin = $plugin;
	}

	public function execute(CommandSender $sender, $label, array $params) {
		if(!$this->plugin->isEnabled())
			return false;
		if(!$this->testPermission($sender)) {
			return false;
		}
		$player = array_shift($params);
		if(trim($player) === "") {
			$sender->sendMessage(TextFormat::RED . "§6- §7Usage: §e" . $this->getUsage());
			return true;
		}
		if(($p = $this->plugin->getServer()->getPlayer($player)) instanceof Player) {
			$player = $p->getName();
		}
		$money = $this->plugin->myMoney($player);
		if($money !== false) {
			$sender->sendMessage($this->plugin->getMessage("seemoney-seemoney", [$player, $money], $sender->getName()));
		} else {
			$sender->sendMessage($this->plugin->getMessage("player-never-connected", [$player], $sender->getName()));
		}
		return true;
	}
}
