<?php

namespace xfury;

use AddNoteBlock\block\NoteBlock;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\item\Item;
use pocketmine\utils\TextFormat;

class Commands implements CommandExecutor{

	public $plugin;

	public function __construct(MainClass $plugin){
		$this->plugin = $plugin;
		$plugin->getCommand("bt")->setExecutor($this);
	}

	public function onCommand(CommandSender $sender, Command $cmd, $label, array $args){
		$smcmd = strtolower($cmd);
		$economy = $this->plugin->getServer()->getPluginManager()->getPlugin("EconomyAPI")->getInstance();
		switch($smcmd){
			case "bt":
				if(!isset($args[2])){
					$sender->sendMessage($this->plugin->formatMsg("Usage: /bt start <time> <prize money>", false));
					return true;
				}
				switch(strtolower($args[0])){
					case "stop":
						if(!$this->plugin->blockTourney() == true){
							$sender->sendMessage($this->plugin->formatMsg("There is no tournament going on right now!"));
							return true;
						}
						/*$top = array_keys($this->plugin->bT, max($this->plugin->bT));
						$vt = ($top[0]);
						$winner = $this->plugin->getServer()->getPlayerExact($vt);
						foreach($this->plugin->getServer()->getOnlinePlayers() as $p){
							$p->sendMessage($this->plugin->formatMsg($winner->getName()." won the block mining tournament with ".$this->plugin->bT[$winner->getName()]." blocks mined!", true));
						}
						if($this->plugin->btSetup["prizetype"] == "items"){
							$wi = Item::get($this->plugin->btSetup["prizeid"],0,$this->plugin->btSetup["prizecount"]);
							if(isset($this->plugin->btSetup["customname"])){
								$wi->setCustomName($this->plugin->btSetup["customname"]);
							}
							$winner->getInventory()->addItem($wi);
							unset($this->plugin->btSetup);
							unset($this->plugin->bT);
							return true;
						}
						if($this->plugin->btSetup["prizetype"] == "cash"){
							$this->plugin->getServer()->getPluginManager()->getPlugin("EconomyAPI")->getInstance()->addMoney($winner, $this->plugin->btSetup["cashamount"]);
							unset($this->plugin->btSetup);
							unset($this->plugin->bT);
							return true;
						}*/
						$win = array_keys($this->plugin->bT, max($this->plugin->bT));
						$winner = $this->plugin->getServer()->getPlayerExact($win[0]);
						$prizeid = $this->plugin->btSetup["prizeid"];
						$prizecount = $this->plugin->btSetup["prizecount"];
						foreach($this->plugin->getServer()->getOnlinePlayers() as $p){
							$p->sendMessage($this->plugin->formatMsg($winner->getName() . " won the block mining tournament with " . $this->plugin->bT[$winner->getName()] . " blocks mined!", true));
							//$p->sendMessage($this->plugin->formatMsg($second->getName()." finished in second with ".$this->plugin->bT[$second->getName()]." blocks mined!", true));
							//$p->sendMessage($this->plugin->formatMsg("And ".$third->getName()." finished in third with ".$this->plugin->bT[$third->getName()]." blocks mined!", true));
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
						break;
					case "start":
						if(!is_numeric($args[1])){
							$sender->sendMessage($this->plugin->formatMsg("Tournament time must be numeric!"));
							return true;
						}
						if($args[1] > 15){
							$sender->sendMessage($this->plugin->formatMsg("Tournaments can only be 15 minutes long!"));
							return true;
						}
						if($args[1] < 10){
							$sender->sendMessage($this->plugin->formatMsg("Tournaments must be at least 10 minutes long!"));
							return true;
						}
						$time = ($args[1] * 60);
						// if(strtolower($args[2]) == "items"){
						// 	if(!is_numeric($args[3])){
						// 		$sender->sendMessage($this->plugin->formatMsg("Prize ID must be numeric!"));
						// 		return true;
						// 	}
						// 	if(!is_numeric($args[4])){
						// 		$sender->sendMessage($this->plugin->formatMsg("Prize Count must be numeric!"));
						// 		return true;
						// 	}
						// 	if($args[1] > 10){
						// 		$sender->sendMessage($this->plugin->formatMsg("You can only give a maximum of 10 items in a tourney!"));
						// 		return true;
						// 	}
						// 	$item = Item::get($args[3],0,$args[4]);
						// 	if(!$sender->getInventory()->contains($item)){
						// 		$sender->sendMessage($this->plugin->formatMsg("You don't have enough items!"));
						// 		return true;
						// 	}
						// 	$prizename = Item::fromString($args[3]);
						// 	$this->plugin->btSetup["time"] = $time;
						// 	$this->plugin->btSetup["timestamp"] = time();
						// 	$this->plugin->btSetup["prizetype"] = "items";
						// 	$this->plugin->btSetup["prizeid"] = $args[3];
						// 	$this->plugin->btSetup["prizecount"] = $args[4];
						// 	$this->plugin->btSetup["customname"] = $args[5];
						// 	$sender->getInventory()->removeItem($item);
						// 	foreach($this->plugin->getServer()->getOnlinePlayers() as $pl){
						// 		$pl->sendMessage($this->plugin->formatMsg("Block mining tournament has started! Mine as many blocks as you can to win! Prize: ".TextFormat::GOLD.$args[4]." ".$prizename->getName()."/s named '".($args[5]), true));
						// 	}
						// 	return true;
						// }
						// if(strtolower($args[2]) == "cash"){
						$cash = (int) $args[2];
						if(!is_int($cash)){
							$sender->sendMessage($this->plugin->formatMsg("Invalid Cash amount! Cash must be be between $60,000-$6,000,000!"));
						}elseif($cash < 60000){
							$sender->sendMessage($this->plugin->formatMsg("Invalid Cash amount! Cash must be be more than $60,000!"));
						}elseif($cash > 6000000){
							$sender->sendMessage($this->plugin->formatMsg("Invalid Cash amount! Cash must be be less than $6,000,000!"));
						}else{
							if($economy->myMoney($sender) < $cash){
								$sender->sendMessage($this->plugin->formatMsg("You don't have enough money to start a block mining tournamnet for ${$cash}!"));
							}else{
								// if(!strtolower($args[3]) == "small"){
								// 	$sender->sendMessage($this->plugin->formatMsg("Invalid Cash amount type! <small:normal:huge>"));
								// 	return true;
								// }
								// if(!strtolower($args[3]) == "normal"){
								// 	$sender->sendMessage($this->plugin->formatMsg("Invalid Cash amount type! <small:normal:huge>"));
								// 	return true;
								// }
								// if(!strtolower($args[3]) == "huge"){
								// 	$sender->sendMessage($this->plugin->formatMsg("Invalid Cash amount type! <small:normal:huge>"));
								// 	return true;
								// }
								// switch(strtolower($args[3])){
								// case "small":
								// 	if($economy->myMoney($sender) < 30000){
								// 		$sender->sendMessage($this->plugin->formatMsg("You need at least $60,000 to start a small block mining tournament!"));
								// 		return true;
								// 	}
								// 	if(count($this->plugin->getServer()->getOnlinePlayers()) < 3){
								// 		$sender->sendMessage($this->plugin->formatMsg("At least 10 players are needed online to start a small block mining tournament!"));
								// 		return true;
								// 	}
								// 	$economy->reduceMoney($sender, 30000);
								// break;
								// case "normal":
								// 	if($economy->myMoney($sender) < 300000){
								// 		$sender->sendMessage($this->plugin->formatMsg("You need at least $600,000 to start a normal block mining tournament!"));
								// 		return true;
								// 	}
								// 	if(count($this->plugin->getServer()->getOnlinePlayers()) < 25){
								// 		$sender->sendMessage($this->plugin->formatMsg("At least 25 players are needed online to start a normal block mining tournament!"));
								// 		return true;
								// 	}
								// 	$economy->reduceMoney($sender, 300000);
								// break;
								// case "huge":
								// 	if($economy->myMoney($sender) < 3000000){
								// 		$sender->sendMessage($this->plugin->formatMsg("You need at least $6,000,000 to start a huge block mining tournament!"));
								// 		return true;
								// 	}
								// 	if(count($this->plugin->getServer()->getOnlinePlayers()) < 50){
								// 		$sender->sendMessage($this->plugin->formatMsg("At least 50 players are needed online to start a huge block mining tournament!"));
								// 		return true;
								// 	}
								// 	$economy->reduceMoney($sender, 3000000);
								// break;
								// }
								$economy->reduceMoney($sender, $cash);
								$this->plugin->btSetup["time"] = $time;
								$this->plugin->btSetup["timestamp"] = time();
								$this->plugin->btSetup["prizetype"] = "cash";
								$this->plugin->btSetup["cashamount"] = $cash;
								foreach($this->plugin->getServer()->getOnlinePlayers() as $pl){
									$pl->sendMessage($this->plugin->formatMsg("A block mining tournament has started! Mine as many blocks as you can to win! Prize: " . TextFormat::GOLD . "$" . ($cash), true));
								}
							}
						}
						return true;
						break;
				}
				break;
		}
	}
}