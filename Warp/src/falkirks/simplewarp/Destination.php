<?php

namespace falkirks\simplewarp;

use falkirks\simplewarp\utils\WeakPosition;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use shoghicp\FastTransfer\FastTransfer;

class Destination{

	/** @var Position */
	protected $position;
	protected $address;
	protected $port;
	protected $message;

	public function __construct(...$params){
		if(is_array($params[0])) $params = $params[0];
		if(isset($params[0])){
			if($params[0] instanceof Position){
				$this->position = $params[0];
				$this->message = (isset($params[1]) ? $params[1] : null);
			}else{
				if(isset($params[1])){
					$this->address = $params[0];
					$this->port = $params[1];
					$this->message = (isset($params[2]) ? $params[2] : null);
				}else{
					throw new \BadMethodCallException;
				}
			}
		}else{
			throw new \BadMethodCallException;
		}
	}

	public function teleport(Player $player){
		if($this->message !== null){
			$player->sendMessage($this->message);
		}

		if($this->position instanceof Position){
			if($this->position->isValid()){
				if($this->position instanceof WeakPosition){
					$this->position->updateProperties();
				}
				//Server::getInstance()->getLogger()->info($this->position->x . " : " . $this->position->y . " : " . $this->position->z);
				$player->teleport($this->position);
			}else{
				$player->sendMessage($this->getApi()->executeTranslationItem("level-not-loaded-warp"));
			}
		}else{
			$plugin = $player->getServer()->getPluginManager()->getPlugin("FastTransfer");
			if($plugin instanceof PluginBase && $plugin->isEnabled() && $plugin instanceof FastTransfer){
				$plugin->transferPlayer($player, $this->address, $this->port);
			}else{
				$player->getServer()->getPluginManager()->getPlugin("SimpleWarp")->getLogger()->warning("In order to use warps tp other servers, you must install " . TextFormat::AQUA . "FastTransfer" . TextFormat::RESET . ".");
				$player->sendPopup(TextFormat::RED . "Warp failed!" . TextFormat::RESET);
			}
		}
	}

	public function isInternal(){
		return $this->position instanceof Position;
	}

	/**
	 * @return Position
	 */
	public function getPosition(){
		return $this->position;
	}

	/**
	 * @return mixed
	 */
	public function getAddress(){
		return $this->address;
	}

	/**
	 * @return mixed
	 */
	public function getPort(){
		return $this->port;
	}

	public function toString(){
		if($this->isInternal()){
			if($this->position instanceof WeakPosition){
				$levelName = $this->position->levelName;
			}else{
				$levelName = $this->position->getLevel()->getName();
			}
			if($this->getApi()->getConfigItem("display-exact-coordinates")){
				return "(X: {$this->getPosition()->x}, Y: {$this->getPosition()->y}, Z: {$this->getPosition()->z}, LEVEL: {$levelName}) ";
			}else{
				return "(X: {$this->getPosition()->getFloorX()}, Y: {$this->getPosition()->getFloorY()}, Z: {$this->getPosition()->getFloorZ()}, LEVEL: " . $levelName . ")";
			}
		}
		return "(IP: {$this->getAddress()}, PORT: {$this->getPort()})";
	}

	/**
	 * @return SimpleWarpApi
	 */
	protected function getApi(){
		return Server::getInstance()->getPluginManager()->getPlugin("SimpleWarp")->getApi();
	}

}