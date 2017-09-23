<?php
namespace LDX\TimeCommander;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase {

	public function onEnable() {
		$this->saveDefaultConfig();
		$c = $this->getConfig()->getAll();
		foreach($c["Commands"] as $i) {
			$this->getServer()->getScheduler()->scheduleRepeatingTask(new TimeCommand($this, $i["Command"]), $i["Time"] * 1200);
		}
	}

	public function runCommand($cmd) {
		$this->getServer()->dispatchCommand(new ConsoleCommandSender(), $cmd);
	}
}

?>
