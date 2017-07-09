<?php
/*
 * This file is a part of LaunchPad.
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

use pocketmine\scheduler\PluginTask;

class PopupTask extends PluginTask {

	private $plugin;

	public function __construct(LaunchPad $plugin) {
		$this->plugin = $plugin;
		parent::__construct($plugin);
	}

	public function onRun($tick) {
		foreach($this->plugin->getServer()->getOnlinePlayers() as $p) {
			if(in_array($p->getName(), $this->plugin->create_mode)) {
				$p->sendPopup($this->plugin->colourMessage("&aTap a block to create a &bLaunch Pad&a!"));
			}
			if(in_array($p->getName(), $this->plugin->destroy_mode)) {
				$p->sendPopup($this->plugin->colourMessage("&aTap a block to destroy the &bLaunch Pad&a!"));
			}
		}
	}

}

?>