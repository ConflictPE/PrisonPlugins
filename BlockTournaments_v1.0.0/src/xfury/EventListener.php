<?php

namespace xfury;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;

class EventListener implements Listener{

	public function __construct(MainClass $plugin){
		$this->plugin = $plugin;
	}

	/**
	 * @priority        HIGHEST
	 * @ignoreCancelled true
	 */
	public function blockBreak(BlockBreakEvent $e){
		// $nb = $this->plugin->getServer()->getPluginManager()->getPlugin("AddNoteBlock");
		$p = $e->getPlayer();
		$b = $e->getBlock();
		if($this->plugin->blockTourney() == true){
			if(!isset($this->plugin->bT[$p->getName()])){
				$this->plugin->bT[$p->getName()] = 1;
			}else{
				$this->plugin->bT[$p->getName()] = $this->plugin->bT[$p->getName()] + 1;
			}
		}
	}
}
	