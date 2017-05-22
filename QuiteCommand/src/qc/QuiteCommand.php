<?php
namespace qc;

use pocketmine\plugin\PluginBase;
use qc\command\EffectCommand;
use qc\command\GiveCommand;

class QuiteCommand extends PluginBase{

	public function onEnable(){
		$this->overrideCommands();
	}

	public function overrideCommands(){
		$commandMap = $this->getServer()->getCommandMap();

		$command = new GiveCommand("qgive");
		$commandMap->register("pocketmine", $command);
		$command = new EffectCommand("qeffect");
		$commandMap->register("pocketmine", $command);
	}

}