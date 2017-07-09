<?php

namespace falkirks\simplewarp\command;

use falkirks\simplewarp\api\SimpleWarpAPI;
use falkirks\simplewarp\event\WarpDeleteEvent;
use falkirks\simplewarp\permission\SimpleWarpPermissions;
use falkirks\simplewarp\Version;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;

class DelWarpCommand extends Command implements PluginIdentifiableCommand {

	protected $api;

	public function __construct(SimpleWarpAPI $api) {
		parent::__construct($api->executeTranslationItem("delwarp-cmd"), $api->executeTranslationItem("delwarp-desc"), $api->executeTranslationItem("delwarp-usage"));
		$this->api = $api;
	}

	/**
	 * @param CommandSender $sender
	 * @param string $commandLabel
	 * @param string[] $args
	 *
	 * @return mixed
	 */
	public function execute(CommandSender $sender, $commandLabel, array $args) {
		if($sender->hasPermission(SimpleWarpPermissions::DEL_WARP_COMMAND)) {
			if(isset($args[0])) {
				if(isset($this->api->getWarpManager()[$args[0]])) {
					$ev = new WarpDeleteEvent($sender, $this->api->getWarpManager()[$args[0]]);
					$this->getPlugin()->getServer()->getPluginManager()->callEvent($ev);
					if(!$ev->isCancelled()) {
						unset($this->api->getWarpManager()[$args[0]]);
						$sender->sendMessage($this->api->executeTranslationItem("warp-deleted", $args[0]));
					} else {
						$sender->sendMessage($this->api->executeTranslationItem("delwarp-event-cancelled"));
					}
				} else {
					$sender->sendMessage($this->api->executeTranslationItem("warp-doesnt-exist", $args[0]));
				}
			} else {
				$sender->sendMessage($this->getUsage());
				Version::sendVersionMessage($sender);
			}
		} else {
			$sender->sendMessage($this->api->executeTranslationItem("delwarp-noperm"));
		}
	}

	/**
	 * @return \pocketmine\plugin\Plugin
	 */
	public function getPlugin() {
		return $this->api->getSimpleWarp();
	}
}