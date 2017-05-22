<?php

namespace xfury;

use pocketmine\item\Item;
use pocketmine\scheduler\PluginTask;
use pocketmine\utils\TextFormat;

class BlockTourneyTask extends PluginTask{

	public function __construct(MainClass $plugin){
		parent::__construct($plugin);
		$this->plugin = $plugin;
	}

	public function onRun($currentTick){
		if($this->plugin->blockTourney() == true){
			$timestamp = $this->plugin->btSetup["timestamp"];
			$o = array_keys($this->plugin->bT, max($this->plugin->bT));
			$one = ($o[0]);
			$elapsedpre = $timestamp + $this->plugin->btSetup["time"];
			$elapsed = $elapsedpre - time();
			foreach($this->plugin->getServer()->getOnlinePlayers() as $p){
				if($this->plugin->btSetup["prizetype"] == "items"){
					if(isset($this->plugin->bT[$p->getName()])){
						$p->sendPopup(TextFormat::AQUA . "Your blocks mined: " . TextFormat::GOLD . $this->plugin->bT[$p->getName()] . TextFormat::WHITE . " - " . TextFormat::AQUA . "Most blocks mined: " . TextFormat::GOLD . $one . "(" . max($this->plugin->bT) . ")" . TextFormat::WHITE . "\n" . TextFormat::AQUA . "Time left: " . TextFormat::GOLD . $elapsed . " seconds" . TextFormat::WHITE . " - " . TextFormat::AQUA . "Prize: " . TextFormat::GOLD . $this->plugin->btSetup["prizecount"] . " " . Item::fromString($this->plugin->btSetup["prizeid"])->getName() . "/s ");
					}else{
						$p->sendPopup(TextFormat::AQUA . "Your blocks mined: " . TextFormat::GOLD . "0" . TextFormat::WHITE . " - " . TextFormat::AQUA . "Most blocks mined: " . TextFormat::GOLD . $one . "(" . max($this->plugin->bT) . ")" . TextFormat::WHITE . "\n" . TextFormat::AQUA . "Time left: " . TextFormat::GOLD . $elapsed . " seconds" . TextFormat::WHITE . " - " . TextFormat::AQUA . "Prize: " . TextFormat::GOLD . $this->plugin->btSetup["prizecount"] . " " . Item::fromString($this->plugin->btSetup["prizeid"])->getName() . "/s ");
					}
				}elseif($this->plugin->btSetup["prizetype"] == "cash"){
					if(isset($this->plugin->bT[$p->getName()])){
						$p->sendPopup(TextFormat::AQUA . "Your blocks mined: " . TextFormat::GOLD . $this->plugin->bT[$p->getName()] . TextFormat::WHITE . " - " . TextFormat::AQUA . "Most blocks mined: " . TextFormat::GOLD . $one . "(" . max($this->plugin->bT) . ")" . TextFormat::WHITE . "\n" . TextFormat::AQUA . "Time left: " . TextFormat::GOLD . $elapsed . " seconds" . TextFormat::WHITE . " - " . TextFormat::AQUA . "Prize: " . TextFormat::GOLD . $this->plugin->btSetup["cashamount"]);
					}else{
						$p->sendPopup(TextFormat::AQUA . "Your blocks mined: " . TextFormat::GOLD . "0" . TextFormat::WHITE . " - " . TextFormat::AQUA . "Most blocks mined: " . TextFormat::GOLD . $one . "(" . max($this->plugin->bT) . ")" . TextFormat::WHITE . "\n" . TextFormat::AQUA . "Time left: " . TextFormat::GOLD . $elapsed . " seconds" . TextFormat::WHITE . " - " . TextFormat::AQUA . "Prize: " . TextFormat::GOLD . $this->plugin->btSetup["cashamount"]);
					}
				}
			}
			if(time() - $this->plugin->btSetup["timestamp"] >= $this->plugin->btSetup["time"]){
				$win = array_keys($this->plugin->bT, max($this->plugin->bT));
				$winner = $this->plugin->getServer()->getPlayerExact($win[0]);
				$prizeid = $this->plugin->btSetup["prizeid"];
				$prizecount = $this->plugin->btSetup["prizecount"];
				foreach($this->plugin->getServer()->getOnlinePlayers() as $p){
					$p->sendMessage($this->plugin->formatMsg($winner->getName() . " won the block mining tournament with " . $this->plugin->bT[$winner->getName()] . " blocks mined!", true));
				}
				if($this->plugin->btSetup["prizetype"] == "items"){
					$winner->getInventory()->addItem(Item::get($this->plugin->btSetup["prizeid"], 0, $this->plugin->btSetup["prizecount"]));
					unset($this->plugin->btSetup);
					unset($this->plugin->bT);
					return true;
				}
				if($this->plugin->btSetup["prizetype"] == "cash"){
					if($this->plugin->btSetup["cashamount"] == "high"){
						$this->plugin->getServer()->getPluginManager()->getPlugin("EconomyAPI")->getInstance()->addMoney($winner, 3000000);
					}elseif($this->plugin->btSetup["cashamount"] == "normal"){
						$this->plugin->getServer()->getPluginManager()->getPlugin("EconomyAPI")->getInstance()->addMoney($winner, 300000);
					}elseif($this->plugin->btSetup["cashamount"] == "low"){
						$this->plugin->getServer()->getPluginManager()->getPlugin("EconomyAPI")->getInstance()->addMoney($winner, 30000);
					}
					unset($this->plugin->btSetup);
					unset($this->plugin->bT);
					return true;
				}
			}
		}
	}
}