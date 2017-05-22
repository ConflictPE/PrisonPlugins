<?php namespace primus\pc;

use _64FF00\PurePerms\PPGroup;
use pocketmine\utils\TextFormat;

class GroupManager{

	public $order, $plugin;

	public function __construct($plugin){
		$this->plugin = $plugin;
		$this->pp = $plugin->getServer()->getPluginManager()->getPlugin('PurePerms');
		$this->order = $plugin->getConfig()->get('order');
		$c = 0;
		$plugin->getLogger()->info(TextFormat::BOLD . '---------------[' . TextFormat::RED . '+' . TextFormat::WHITE . ']---------------');
		$plugin->getLogger()->info('Loaded prison ' . TextFormat::UNDERLINE . 'groups' . TextFormat::RESET . TextFormat::WHITE . ':');
		foreach($this->order as $groupName){
			$group = $plugin->getPurePerms()->getGroup($groupName);
			if($group instanceof PPGroup){
				$c++;
				$plugin->getLogger()->info(' ' . $c . '. - ' . TextFormat::GOLD . $group->getName() . TextFormat::WHITE . ' : ' . TextFormat::GREEN . $plugin->getEconomy()->formatMoney($this->getPrice($group)));
			}else{
				$r = $this->pp->addGroup($groupName);
				if($r === 1){
					$plugin->getLogger()->info('Created group: ' . TextFormat::GREEN . $groupName . TextFormat::WHITE . '');
				}elseif($r === -1){
					$plugin->getLogger()->info('Failed to add group: ' . $groupName . ' due to - Invalid name');
				}elseif($r === 0){
					$plugin->getLogger()->info('Failed to add group: ' . $groupName . ' due to - Already exists');
				}else{
					$plugin->getLogger()->info('Failed to add. Please add manualy group: ' . TextFormat::GREEN . $groupName . '');
				}
			}
		}
		$plugin->getLogger()->info(TextFormat::BOLD . '---------------[' . TextFormat::RED . '+' . TextFormat::WHITE . ']---------------');
	}

	public function getPrice(PPGroup $group){
		$pr = $this->plugin->getConfig()->get('prices')[$group->getName()];
		if(!is_numeric($pr)){
			$this->plugin->getLogger()->warning('Could not find price for ' . $group->getName() . ' group returning 0');

			return 0;
		}

		return $pr;
	}

	public function getFirstGroup(){
		if(isset($this->order[0])){
			return $this->getGroup($this->order[0]);
		}else{
			return \false;
		}
	}

	public function getGroup($name){
		return $this->plugin->getPurePerms()->getGroup($name);
	}

	public function getNextGroup(PPGroup $group){
		$c = 0;
		foreach($this->order as $item){
			$c++;
			if($item === $group->getName()) break;
		}
		if(isset($this->order[$c])){
			return $this->getGroup($this->order[$c]);
		}else{
			return \false;
		}
	}

	public function getGroups(){
		$this->plugin->getPurePerms()->getGroups();
	}
}