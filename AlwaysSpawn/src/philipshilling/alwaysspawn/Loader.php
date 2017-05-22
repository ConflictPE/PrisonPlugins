<?php
namespace philipshilling\alwaysspawn;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\math\Vector3;
use pocketmine\plugin\PluginBase as Plugin;

class Loader extends Plugin implements Listener{

	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getServer()->getLogger()->info("AlwaysSpawn Enabled!");
	}

	public function onPlayerLogin(PlayerLoginEvent $event){
		$player = $event->getPlayer();
		$x = $this->getServer()->getDefaultLevel()->getSafeSpawn()->getX();
		$y = $this->getServer()->getDefaultLevel()->getSafeSpawn()->getY();
		$z = $this->getServer()->getDefaultLevel()->getSafeSpawn()->getZ();
		$level = $this->getServer()->getDefaultLevel();
		$player->setLevel($level);
		$player->teleport(new Vector3($x, $y, $z, $level));
	}

	public function onDisable(){
		$this->getServer()->getLogger()->info("AlwaysSpawn is no longer enabled! Did the server stop?");
	}
}