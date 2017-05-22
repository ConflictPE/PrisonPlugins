<?php

namespace Lambo\CombatLogger;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase implements Listener{

	public $players = [];
	public $interval = 10;
	public $blockedcommands = [];

	public function onEnable(){
		$this->saveDefaultConfig();
		$this->interval = $this->getConfig()->get("interval");
		$cmds = $this->getConfig()->get("blocked-commands");
		foreach($cmds as $cmd){
			$this->blockedcommands[$cmd] = 1;
		}
		$this->getServer()->getLogger()->info("CombatLogger enabled");
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new Scheduler($this, $this->interval), 20);
	}

	public function onDisable(){
		$this->getServer()->getLogger()->info("CombatLogger disabled");
	}

	/**
	 * @param EnityDamageEvent $event
	 *
	 * @priority        MONITOR
	 * @ignoreCancelled true
	 */
	public function EntityDamageEvent(EntityDamageEvent $event){
		if($event instanceof EntityDamageByEntityEvent){
			if($event->getDamager() instanceof Player and $event->getEntity() instanceof Player){
				foreach([$event->getDamager(), $event->getEntity()] as $players){
					$this->setTime($players);
				}
			}
		}
	}

	/**
	 * @param PlayerDeathEvent $event
	 *
	 * @priority        MONITOR
	 * @ignoreCancelled true
	 */
	public function PlayerDeathEvent(PlayerDeathEvent $event){
		if(isset($this->players[$event->getEntity()->getName()])){
			unset($this->players[$event->getEntity()->getName()]);
			/*$cause = $event->getEntity()->getLastDamageCause();
			if($cause instanceof EntityDamageByEntityEvent){
				$e = $cause->getDamager();
				if($e instanceof Player){
					$message = "death.attack.player";
					$params[] = $e->getName();
					$event->setDeathMessage(new TranslationContainer($message, $params));
				}
			}*/
		}
	}

	/**
	 * @param PlayerQuitEvent $event
	 *
	 * @priority        HIGH
	 * @ignoreCancelled true
	 */
	public function PlayerQuitEvent(PlayerQuitEvent $event){
		if(isset($this->players[$event->getPlayer()->getName()])){
			$player = $event->getPlayer();
			if((time() - $this->players[$player->getName()]) < $this->interval){
				$player->kill();
			}
		}
	}

	/**
	 * @param PlayerCommandPreprocessEvent $event
	 *
	 * @priority        HIGH
	 * @ignoreCancelled true
	 */
	public function PlayerCommandPreprocessEvent(PlayerCommandPreprocessEvent $event){
		if(isset($this->players[$event->getPlayer()->getName()])){
			$cmd = strtolower(explode(' ', $event->getMessage())[0]);
			if(isset($this->blockedcommands[$cmd])){
				$event->getPlayer()->sendMessage("§6§l» §r§3You are in combat, therefore this command cannot be executed");
				$event->setCancelled();
			}
		}
	}

	private function setTime(Player $player){
		$msg = "§l§c» §r§bYou are now in combat.";
		if(isset($this->players[$player->getName()])){
			if((time() - $this->players[$player->getName()]) > $this->interval){
				$player->sendMessage($msg);
			}
		}else{
			$player->sendMessage($msg);
		}
		$this->players[$player->getName()] = time();
	}
}