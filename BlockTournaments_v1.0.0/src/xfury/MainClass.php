<?php

namespace xfury;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class MainClass extends PluginBase {

	public $bT = [];
	public $btSetup = [];

	public $plugin;

	public function onEnable() {
		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
		$this->cmds = new Commands($this);
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new BlockTourneyTask($this), 5);
		$this->getServer()->getLogger()->info("Block Mining Tournaments enabled!");
	}

	public function formatMsg($string, $def = false) {
		if($def == true) {
			return TextFormat::AQUA . "BMT" . TextFormat::GRAY . "> " . TextFormat::GREEN . $string;
		} else {
			return TextFormat::AQUA . "BMT" . TextFormat::GRAY . "> " . TextFormat::RED . $string;
		}
	}

	public function blockTourney($default = false) {
		if(isset($this->btSetup["time"])) {
			return true;
		}
	}
}