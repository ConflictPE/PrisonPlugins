<?php
namespace Lambo\CombatLogger;

use pocketmine\Player;
use pocketmine\scheduler\PluginTask;

class Scheduler extends PluginTask{

	public function __construct($plugin){
		$this->plugin = $plugin;
		parent::__construct($plugin);
	}

	public function onRun($currentTick){
		foreach($this->plugin->players as $player => $time){
			if((time() - $time) > $this->plugin->interval){
				$p = $this->plugin->getServer()->getPlayer($player);
				if($p instanceof Player){
					$p->sendMessage("§l§6»§r§7 You are no longer in combat");
					unset($this->plugin->players[$player]);
				}else unset($this->plugin->players[$player]);
			}
		}
	}
}