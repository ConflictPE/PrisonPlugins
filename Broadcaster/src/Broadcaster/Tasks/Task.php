<?php
/*
 * Broadcaster (v1.16) by EvolSoft
 * Developer: EvolSoft (Flavius12)
 * Website: http://www.evolsoft.tk
 * Date: 28/05/2015 01:31 PM (UTC)
 * Copyright & License: (C) 2014-2015 EvolSoft
 * Licensed under MIT (https://github.com/EvolSoft/Broadcaster/blob/master/LICENSE)
 */
namespace Broadcaster\Tasks;
use Broadcaster\Main;
use pocketmine\scheduler\PluginTask;
use pocketmine\Server;

class Task extends PluginTask {

	public function __construct(Main $plugin) {
		parent::__construct($plugin);
		$this->plugin = $plugin;
		$this->length = -1;
		$this->cfg = $this->plugin->getConfig()->getAll();
	}

	public function onRun($currentTick) {
		$this->plugin = $this->getOwner();
		if($this->cfg["broadcast-enabled"] == true) {
			$this->length = $this->length + 1;
			$messages = $this->cfg["messages"];
			$messagekey = $this->length;
			$message = $messages[$messagekey];
			if($this->length == count($messages) - 1)
				$this->length = -1;

			foreach($this->getOwner()->getServer()->getOnlinePlayers() as $p) {
				$p->sendDirectMessage($this->plugin->translateColors("&", $this->plugin->broadcast($this->cfg, $message)))
			}
		}
	}

}

?>
