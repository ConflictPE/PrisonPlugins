<?php namespace primus\pc\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\utils\TextFormat;
use primus\pc\PrisonCore;

class RankUp extends Command implements PluginIdentifiableCommand{

	/** * @param PrisonCore $plugin * @param $name * @param $description */
	public function __construct(PrisonCore $plugin, $name, $description){
		$this->plugin = $plugin;
		parent::__construct($name, $description);
		$this->setPermission("pc.command.rankup");
		$this->setAliases(['ru', 'ranku', 'rup']);
		$this->setPermissionMessage($plugin->prefix . TextFormat::RED . $plugin->messages->getMessage('pc.command.rankup.noPermission'));
	}

	/** * @param CommandSender $sender * @param $label * @param array $args * @return bool */
	public function execute(CommandSender $sender, $label, array $args){
		if($sender instanceof ConsoleCommandSender){
			$sender->sendMessage('Please run this command in-game');

			return \true;
		}
		if(!$this->testPermission($sender)) return \false;
		$group = $this->plugin->getGroup($sender);
		$rank = $this->plugin->getGroupManager()->getNextGroup($group);
		if(!$rank){
			$sender->sendMessage($this->plugin->prefix . $this->plugin->messages->getMessage('pc.command.rankup.topRank'));

			return \true;
		}
		if($this->plugin->getEconomy()->getMoney($sender) >= $this->plugin->getGroupManager()->getPrice($rank)){
			if($this->plugin->rankUp($sender)){
				$sender->sendMessage($this->plugin->prefix . $this->plugin->messages->getMessage('pc.command.rankup.rankedUp', $rank->getName(), $this->plugin->getEconomy()->formatMoney($this->plugin->getGroupManager()->getPrice($rank))));
				if($this->plugin->getConfig()->get('broadcastMessageOnRankUp')) $this->plugin->getServer()->broadcastMessage($this->plugin->messages->getMessage('pc.command.rankup.rankedUpBroadcast', $sender->getName(), $rank->getName()));

				return \true;
			}else{
				$sender->sendMessage($this->plugin->prefix . $this->plugin->messages->getMessage('pc.command.rankup.failed'));

				return \true;
			}
		}else{
			$sender->sendMessage($this->plugin->prefix . $this->plugin->messages->getMessage('pc.command.rankup.notEnoughMoney', $this->plugin->getEconomy()->formatMoney($this->plugin->getGroupManager()->getPrice($rank))));

			return \true;
		}
	}

	public function getPlugin(){
		return $this->plugin;
	}
}