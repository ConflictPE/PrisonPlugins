<?php

namespace falkirks\simplewarp\command\essentials;

use EssentialsPE\Loader;
use falkirks\simplewarp\api\SimpleWarpAPI;
use falkirks\simplewarp\command\WarpCommand;
use falkirks\simplewarp\Version;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class EssentialsWarpCommand extends WarpCommand {

	/** @var Command */
	private $essCommand;

	public function __construct(SimpleWarpAPI $api, Command $essCommand) {
		parent::__construct($api);
		$this->essCommand = $essCommand;
	}

	public function execute(CommandSender $sender, $commandLabel, array $args) {
		if(isset($args[0])) {
			$ess = $this->getPlugin()->getServer()->getPluginManager()->getPlugin("EssentialsPE");
			if(isset($this->api->getWarpManager()[$args[0]])) {
				parent::execute($sender, $commandLabel, $args);
				if($ess instanceof Loader && $ess->warpExists($args[0]) && $sender->hasPermission("simplewarp.essentials.notice")) {
					$sender->sendMessage($this->api->executeTranslationItem("ess-warp-conflict", $args[0]));
				}
			} elseif($ess instanceof Loader && ($name = $this->getEssWarpName($ess, $args[0])) !== null) {
				$args[0] = $name;
				$this->getEssCommand()->execute($sender, $commandLabel, $args);
			} else {
				$sender->sendMessage($this->api->executeTranslationItem("ess-warp-doesnt-exist"));
			}
		} else {
			$sender->sendMessage($this->getUsage());
			Version::sendVersionMessage($sender);
		}
	}

	/**
	 * @return Command
	 */
	public function getEssCommand() {
		return $this->essCommand;
	}

	private function getEssWarpName(Loader $loader, $string) {
		if($loader->warpExists($string)) {
			return $string;
		}
		if(substr($string, 0, 4) === "ess:" && $loader->warpExists(substr($string, 4))) {
			return substr($string, 4);
		}
		return null;
	}
}