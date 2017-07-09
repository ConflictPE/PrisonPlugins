<?php

namespace MyPlot\subcommand;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class AddHelperSubCommand extends SubCommand {

	public function canUse(CommandSender $sender) {
		return ($sender instanceof Player) and $sender->hasPermission("myplot.command.addhelper");
	}

	public function execute(CommandSender $sender, array $args) {
		if(count($args) !== 1)
			return false;
		$helper = $args[0];
		$player = $sender->getServer()->getPlayer($sender->getName());
		$plot = $this->getPlugin()->getPlotByPosition($player->getPosition());
		if($plot === null) {
			$sender->sendMessage(TextFormat::RED . $this->translateString("notinplot"));
			return true;
		}
		if($plot->owner !== $sender->getName() and !$sender->hasPermission("myplot.admin.addhelper")) {
			$sender->sendMessage(TextFormat::RED . $this->translateString("notowner"));
			return true;
		}
		if(!$plot->addHelper($helper)) {
			$sender->sendMessage($this->translateString("addhelper.alreadyone", [$helper]));
			return true;
		}
		if($this->getPlugin()->getProvider()->savePlot($plot)) {
			$sender->sendMessage($this->translateString("addhelper.success", [$helper]));
		} else {
			$sender->sendMessage(TextFormat::RED . $this->translateString("error"));
		}
		return true;
	}
}
