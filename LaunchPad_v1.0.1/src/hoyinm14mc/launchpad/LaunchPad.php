<?php
/*
 * This file is the main class of LaunchPad.
 * Copyright (C) 2015 CyberCube-HK
 *
 * LaunchPad is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * LaunchPad is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with LaunchPad. If not, see <http://www.gnu.org/licenses/>.
 */

namespace hoyinm14mc\launchpad;

use pocketmine\level\sound\BlazeShootSound;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;

class LaunchPad extends PluginBase {

	public $protect = [];

	public $create_mode = [];

	public $destroy_mode = [];

	public function onEnable() {
		if(!is_dir($this->getDataFolder())) {
			mkdir($this->getDataFolder());
		}
		$this->saveDefaultConfig();
		$this->reloadConfig();
		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
		$this->getCommand("launchpad")->setExecutor(new LaunchPadCommand($this));
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new PopupTask($this), 7);
		$this->getLogger()->info($this->colourMessage("&aLoaded Successfully!"));
	}

	public function colourMessage($msg) {
		return str_replace("&", "§", $msg);
	}

	public function launch(Player $player, $base = 1) {
		if($this->getConfig()->get("SOUND") !== false) {
			$player->getLevel()->addSound(new BlazeShootSound($player));
		}
		if($player instanceof Player)
			$this->protect[$player->getName()] = $player->getName();
		switch($player->getDirection()) {
			case 0:
				$player->knockback($player, 0, 1, 0, $base);
				break;
			case 1:
				$player->knockback($player, 0, 0, 1, $base);
				break;
			case 2:
				$player->knockback($player, 0, -1, 0, $base);
				break;
			case 3:
				$player->knockback($player, 0, 0, -1, $base);
				break;
		}
	}

}

?>