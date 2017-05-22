<?php namespace primus\pc\economy;

use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;

class Economy{

	private $economy, $owner;

	public function __construct(Plugin $plugin){
		$this->owner = $plugin;
		$economy = ["EconomyAPI", "PocketMoney", "MassiveEconomy", "GoldStd"];
		foreach($economy as $e){
			$ins = $plugin->getServer()->getPluginManager()->getPlugin($e);
			if($ins instanceof Plugin && $ins->isEnabled()){
				$this->economy = $ins;
				$plugin->getLogger()->info("Economy plugin - " . TextFormat::GREEN . " $e");
				break;
			}
		}
	}

	public function takeMoney(Player $player, $ammount, $force = \false){
		if($this->getName() === 'EconomyAPI'){
			return $this->economy->reduceMoney($player, $ammount, $force);
		}
		if($this->getName() === 'PocketMoney'){
			return $this->economy->grantMoney($player, $ammount, $force);
		}
		if($this->getName() === 'GoldStd'){
			return $this->economy->grantMoney($player, $ammount, $force);
		}
		if($this->getName() === 'MassiveEconomy'){
			return $this->economy->takeMoney($player, $ammount, $force);
		}

		return \false;
	}

	public function getName(){
		return $this->economy->getDescription()->getName();
	}

	public function getMoney(Player $player){
		if($this->getName() === 'EconomyAPI'){
			return $this->economy->myMoney($player);
		}
		if($this->getName() === 'PocketMoney'){
			return $this->economy->getMoney($player->getName());
		}
		if($this->getName() === 'GoldStd'){
			return $this->economy->getMoney($player);
		}
		if($this->getName() === 'MassiveEconomy'){
			if($this->economy->isPlayerRegistered($player->getName())){
				return $this->economy->getMoney($player->getName());
			}
		}
	}

	public function formatMoney($ammount){
		if($this->getName() === 'EconomyAPI'){
			return $this->getMonetaryUnit() . $ammount;
		}
		if($this->getName() === 'PocketMoney'){
			return $ammount . ' ' . $this->getMonetaryUnit();
		}
		if($this->getName() === 'GoldStd'){
			return $ammount . $this->getMonetaryUnit();
		}
		if($this->getName() === 'MassiveEconomy'){
			return $this->getMonetaryUnit() . $ammount;
		}

		return $ammount;
	}

	public function getMonetaryUnit(){
		if($this->getName() === 'EconomyAPI'){
			return $this->economy->getMonetaryUnit();
		}
		if($this->getName() === 'PocketMoney'){
			return 'PM';
		}
		if($this->getName() === 'GoldStd'){
			return 'G';
		}
		if($this->getName() === 'MassiveEconomy'){
			return $this->economy->getMoneySymbol() != \null ? $this->economy->getMoneySymbol() : '$';
		}
	}

	public function isLoaded(){
		return $this->economy instanceof Plugin;
	}
}