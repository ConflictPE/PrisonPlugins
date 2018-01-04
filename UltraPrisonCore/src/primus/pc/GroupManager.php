<?php namespace primus\pc;

use _64FF00\PurePerms\PPGroup;
use pocketmine\utils\TextFormat;

class GroupManager {

	public $order, $plugin;

	public function __construct($plugin) {
		$this->plugin = $plugin;
		$this->pp = $plugin->getServer()->getPluginManager()->getPlugin('PurePerms');
		$this->order = $plugin->getConfig()->get('order');
		foreach($this->order as $groupName) {
			$group = $plugin->getPurePerms()->getGroup($groupName);
			if(!($group instanceof PPGroup)) {
				$this->pp->addGroup($groupName);
			}
		}
	}

	public function getPrice(PPGroup $group) {
		$pr = $this->plugin->getConfig()->get('prices')[$group->getName()];
		if(!is_numeric($pr)) {
			$this->plugin->getLogger()->warning('Could not find price for ' . $group->getName() . ' group returning 0');
			return 0;
		}
		return $pr;
	}

	public function getFirstGroup() {
		if(isset($this->order[0])) {
			return $this->getGroup($this->order[0]);
		} else {
			return \false;
		}
	}

	public function getGroup($name) {
		return $this->plugin->getPurePerms()->getGroup($name);
	}

	public function getNextGroup(PPGroup $group) {
		$c = 0;
		foreach($this->order as $item) {
			$c++;
			if($item === $group->getName())
				break;
		}
		if(isset($this->order[$c])) {
			return $this->getGroup($this->order[$c]);
		} else {
			return \false;
		}
	}

	public function getGroups() {
		$this->plugin->getPurePerms()->getGroups();
	}
}