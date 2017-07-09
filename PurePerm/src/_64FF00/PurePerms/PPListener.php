<?php

namespace _64FF00\PurePerms;

use _64FF00\PurePerms\event\PPGroupChangedEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\TranslationContainer;
use pocketmine\utils\TextFormat;

class PPListener implements Listener {

	/*
		PurePerms by 64FF00 (Twitter: @64FF00)

		  888  888    .d8888b.      d8888  8888888888 8888888888 .d8888b.   .d8888b.
		  888  888   d88P  Y88b    d8P888  888        888       d88P  Y88b d88P  Y88b
		888888888888 888          d8P 888  888        888       888    888 888    888
		  888  888   888d888b.   d8P  888  8888888    8888888   888    888 888    888
		  888  888   888P "Y88b d88   888  888        888       888    888 888    888
		888888888888 888    888 8888888888 888        888       888    888 888    888
		  888  888   Y88b  d88P       888  888        888       Y88b  d88P Y88b  d88P
		  888  888    "Y8888P"        888  888        888        "Y8888P"   "Y8888P"
	*/
	private $plugin;

	/**
	 * @param PurePerms $plugin
	 */
	public function __construct(PurePerms $plugin) {
		$this->plugin = $plugin;
	}

	/**
	 * @param PPGroupChangedEvent $event
	 *
	 * @priority LOWEST
	 */
	public function onGroupChanged(PPGroupChangedEvent $event) {
		$player = $event->getPlayer();
		$this->plugin->updatePermissions($player);
	}

	/**
	 * @param EntityLevelChangeEvent $event
	 *
	 * @priority LOWEST
	 */
	public function onLevelChange(EntityLevelChangeEvent $event) {
		$player = $event->getEntity();
		$this->plugin->updatePermissions($player);
	}

	public function onPlayerCommand(PlayerCommandPreprocessEvent $event) {
		$message = $event->getMessage();
		$player = $event->getPlayer();
		if($message{0} === "/") {
			$command = substr($message, 1);
			$args = explode(" ", $command);
			if(!$this->plugin->getNoeulAPI()->isAuthed($event->getPlayer())) {
				$event->setCancelled(true);
				if($args[0] === "ppsudo" or $args[0] === "help") {
					$this->plugin->getServer()->dispatchCommand($player, $command);
				} else {
					$this->plugin->getNoeulAPI()->sendAuthMsg($player);
				}
			} else {
				$disableOp = $this->plugin->getConfigValue("disable-op");
				if($disableOp and $args[0] === "op") {
					$event->setCancelled(true);
					$player->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.permission"));
				}
			}
		}
	}

	/**
	 * @param PlayerLoginEvent $event
	 *
	 * @priority LOWEST
	 */
	public function onPlayerLogin(PlayerLoginEvent $event) {
		$player = $event->getPlayer();
		$this->plugin->registerPlayer($player);
		if($this->plugin->getNoeulAPI()->isNoeulEnabled())
			$this->plugin->getNoeulAPI()->deAuth($player);
	}

	public function onPlayerMove(PlayerMoveEvent $event) {
		if(!$this->plugin->getNoeulAPI()->isAuthed($event->getPlayer())) {
			$this->plugin->getNoeulAPI()->sendAuthMsg($event->getPlayer());
			$event->setCancelled(true);
			$event->getPlayer()->onGround = true;
		}
	}

	/**
	 * @param PlayerQuitEvent $event
	 *
	 * @priority HIGHEST
	 */
	public function onPlayerQuit(PlayerQuitEvent $event) {
		$player = $event->getPlayer();
		$this->plugin->unregisterPlayer($player);
	}
}