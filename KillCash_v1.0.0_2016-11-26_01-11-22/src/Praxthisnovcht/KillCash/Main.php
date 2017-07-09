<?php

namespace Praxthisnovcht\KillCash;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class Main extends PluginBase implements Listener {

	public $economy = false;

	/** @var Config */
	public $config;

	public function onEnable() {
		@mkdir($this->getDataFolder());
		$this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML, [
			"enable" => "true",
			"economy-plugin" => "Economy",
			"money" => 20,
			"message" => "You kill {name} and earn {money} coins",
		]);
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		if($this->config->get("economy-plugin") == "EconomyAPI") {
			if(is_dir($this->getServer()->getPluginPath() . "EconomyAPI")) {
				$this->getLogger()->info(TextFormat::GREEN . "KillCash successful loaded with Economy!API");
				$this->economy = true;
			} else {
				$this->getLogger()->info(TextFormat::RED . "KillCash not loaded, I can't find EconomyAPI");
				$this->economy = false;
			}
		}
	}

	public function onDisable() {
		$this->getLogger()->info(TextFormat::RED . "KillCash unloaded!");
	}

	public function onPlayerDeath(PlayerDeathEvent $event) {
		if($this->economy == true && $this->config->get("enable") == "true") {
			$entity = $event->getEntity();
			$cause = $entity->getLastDamageCause();
			if($cause instanceof EntityDamageByEntityEvent) {
				$killer = $cause->getDamager();
				if($killer instanceof Player) {
					if($this->config->get("economy-plugin") == "EconomyAPI") {
						$killer->sendMessage("§c| §bYou killed §6" . $entity->getPlayer()->getName() . "§b.§a (+ $20)");
						$this->getServer()->getPluginManager()->getPlugin("EconomyAPI")->addMoney($killer->getName(), $this->config->get("money"));
						return true;
					}
				}
			} else {
				return true;
			}
		}
	}
}