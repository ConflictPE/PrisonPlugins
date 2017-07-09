<?php namespace primus\pc;

use pocketmine\utils\Config;

class PrisonMessages {

	public $messages;

	private $plugin;

	public function __construct($plugin) {
		$this->plugin = $plugin;
		if(file_exists($plugin->getDataFolder() . 'messages.yml')) {
			$this->messages = new Config($plugin->getDataFolder() . "messages.yml", Config::YAML);
		} else {
			$this->messages = new Config($plugin->getDataFOlder() . "messages.yml", Config::YAML, [
				"pc.command.rankup.notEnoughMoney" => "To rankup you need %var0%",
				"pc.command.rankup.topRank" => "You already have highest rank",
				"pc.command.rankup.rankedUp" => "You ranked up to %var0% rank, paid: %var1%",
				"pc.command.rankup.rankedUpBroadcast" => "%var0% Ranked up to %var1%",
				"pc.command.rankup.failed" => "Failed to rankup",
				"pc.command.rankup.noPermission" => "You don't have permissions to use this command",
				"pc.sign.create.success" => "You successfully created Prison's sign",
				"pc.sign.create.noPermission" => "You dont have permission to create Prison's sign",
				"pc.sign.create.groupNotExist" => "Group '%var0%' does not exist",
				"pc.sign.destroy.success" => "You successfully destroyed Prison's sign",
				"pc.sign.destroy.noPermission" => "You dont have permission to destroy Prison's sign",
				"pc.sign.use.shop.sameGroup" => "You already have this group",
				"pc.sign.use.shop.groupNotExist" => "Group '%var0%' no longer exists",
				"pc.sign.use.shop.failed" => "Failed to buy %var0% rank, please try again later.",
				"pc.sign.use.shop.boughtRank" => "You have successfully bought %var0% rank for %var1%",
				"pc.sign.use.shop.notEnoughMoney" => "You dont have enough money to buy this rank",
				"pc.sign.use.rankedUp" => "You have ranked up to next rank: %var0%",
				"pc.sign.use.noPermission" => "You dont have permission to use Prison's signs",
				"pc.sign.use.rankup.topRank" => "You already have the highest rank",
				"pc.sign.use.rankup.notEnoughMoney" => "To rankup you need %var0%",
				"pc.sign.use.notEnoughMoney" => "You dont have enough money to buy this rank",
			]);
		}
	}

	public function getNode($node) {
		return $this->messages->get($node);
	}

	public function getMessage($node, ...$vars) {
		$msg = $this->messages->get($node);
		if($msg != \null) {
			$number = 0;
			foreach($vars as $v) {
				$msg = str_replace("%var$number%", $v, $msg);
				$number++;
			}
			return ' ' . $msg;
		}
		return \null;
	}
}