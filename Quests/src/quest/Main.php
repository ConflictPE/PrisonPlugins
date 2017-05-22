<?php

namespace quest;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use SQLite3;

class DB extends SQLite3{

	public function __construct($filepath){

		$this->open($filepath);
	}
}

class Main extends PluginBase implements Listener{

	public $queue = [];

	public $cfg;

	public $db;

	public $kills;

	public function onEnable(){

		if(!is_dir($this->getDataFolder())){
			mkdir($this->getDataFolder());

		}

		if(!is_dir($this->getDataFolder() . "players")){
			mkdir($this->getDataFolder() . "players");
		}

		$this->cfg = new Config($this->getDataFolder() . "quests.yml", Config::YAML, [

			"no-access" => "§cYou do not have access to this Quest",
			"1" => [
				"type" => "kill",
				"name" => "Assault_20_Players",
				"message" => "§9Hey, Bring me 20 lifes and i will give you $200..",
				"receive-message" => "§cDo it fast and quiet..",
				"required" => 20,
				"reward" => [
					"givemoney {player} 200",
				],

				"finish-message" => "§aThanks for doing this",
			],

			"2" => [
				"type" => "item",
				"name" => "45_Stone_Block",
				"message" => "§bHello there!, can you bring me 45 Stone blocks for $200 please? and remember, it is stone block not cobblestone :)",
				"receive-message" => "§aThanks for taking this Quest!",
				"required" => [
					"1:0:45",
				],
				"reward" => [
					"givemoney {player} 200",
				],
				"finish-message" => "§aThanks, nice to work with you!",
			],
		]);

		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function onCommand(CommandSender $s, Command $cmd, $label, array $args){

		if($cmd->getName() == "quest"){

			if(isset($args[0])){

				switch($args[0]){

					case "see":

						if(isset($args[1])){

							if($this->cfg->exists($args[1])){

								if($this->dataExists($s->getName(), $args[1])){
									$s->sendMessage("§6- §cYou are already taken this quest");
									return true;
								}

								if(!$s->hasPermission("quest.access." . $args[1])){
									$s->sendMessage("§6- " . $this->cfg->get("no-access"));
									return false;
								}

								$conf = $this->cfg->get($args[1]);

								if(!(isset($this->queue[strtolower($s->getName())][$args[1]]))){

									$s->sendMessage("" . $conf["message"]);
									$this->queue[strtolower($s->getName())][$args[1]] = time();

								}else{

									$s->sendMessage("" . $conf["receive-message"]);
									unset($this->queue[$s->getName()][$args[1]]);

									if(file_exists($this->getDataFolder() . "players/" . strtolower($s->getName()) . ".sq3")){

										$playerDat = $this->getUserData($s->getName());

										$stmt = $playerDat->prepare("INSERT OR REPLACE INTO database (quest) VALUES (:quest);");
										$stmt->bindValue(":quest", $args[1]);
										$stmt->execute();

									}else{

										$db = new \SQLite3($this->getDataFolder() . "players/" . strtolower($s->getName()) . ".sq3");
										$db->exec("CREATE TABLE IF NOT EXISTS database (quest);");
										$stmt = $db->prepare("INSERT OR REPLACE INTO database (quest) VALUES (:quest);");
										$stmt->bindValue(":quest", $args[1]);
										$stmt->execute();
									}
								}

							}else{
								$s->sendMessage("§6- §cThis quest coming soon!");
							}
						}else{
							$s->sendMessage("§6- §cUsage: /quest see <quest_id>");
						}
						break;

					case "done":
						if(isset($args[1])){

							if(file_exists($this->getDataFolder() . "players/" . strtolower($s->getName()) . ".sq3")){

								$playerDat = $this->getUserData($s->getName());

								if($this->dataExists($s->getName(), $args[1])){

									$conf = $this->cfg->get($args[1]);
									$type = $conf["type"];
									switch($type){
										case "kill":

											if($this->kills[$s->getName()] >= $conf["required"]){

												$this->kills[$s->getName()] = $this->kills[$s->getName()] - $conf["required"];

												$playerDat = $this->getUserData($s->getName());

												$playerDat->query("DELETE FROM database WHERE quest='$args[1]';");

												foreach($conf["reward"] as $command){

													$this->getServer()->dispatchCommand(new ConsoleCommandSender(), $this->translate($command, $s));
													$s->sendMessage($conf["finish-message"]);
												}
											}else{
												$s->sendMessage("§6- §cNot enough Lifes, cancel");
											}
											break;

										case "item":

											foreach($conf["required"] as $item){
												$is = explode(":", $item);
												$item = Item::get($is[0], $is[1], $is[2]);
											}

											if($s->getInventory()->contains($item)){
												$s->getInventory()->removeItem($item);

												$tempData = $this->getUserData($s->getName());
												$tempData->query("DELETE FROM database WHERE quest='$args[1]';");

												$s->sendMessage($conf["finish-message"]);

												foreach($conf["reward"] as $command){
													$this->getServer()->dispatchCommand(new ConsoleCommandSender(), $this->translate($command, $s));
												}

											}else{
												$s->sendMessage("§6- §cYou haven't reached the requirement.");
											}
											break;

									}
								}else{
									$s->sendMessage("§6- §cNo quest name " . $args[1] . " found in your quests list");
								}
							}
						}else{
							$s->sendMessage("§6- §cUsage: /quest done <quest_id>");
						}
						break;

					case "list":
						if(file_exists($this->getDataFolder() . "players/" . strtolower($s->getName()) . ".sq3")){

							$playerDat = $this->getUserData($s->getName());
							$query = $playerDat->query("SELECT quest FROM database");
							while($res = $query->fetchArray(SQLITE3_ASSOC)){
								$s->sendMessage("§6- §a" . $res["quest"]);
							}
						}else{
							$s->sendMessage("§6- §cCannot find for you a data file, use /quest see <id> to get a quest and register that file");
						}

						break;

					case "cancel":
						if(isset($args[1])){

							if(file_exists($this->getDataFolder() . "players/" . strtolower($playern) . ".sq3")){

								if($this->dataExists($s->getName(), $args[1])){

									$playerDat = $this->getUserData($s->getName());
									$playerDat->query("DELETE FROM database WHERE quest='$args[1]';");
									$s->sendMessage("§6- §aSucceed Cancel Quest!");
								}else{
									$s->sendMessage("§cYou do not have that quest on your list");
								}
							}else{
								$s->sendMessage("§cYou do not have any database");
							}
						}else{
							$s->sendMessage("§cusage: /quest cancel <id>");
						}
						break;

					default:
						$s->sendMessage("Usage: /quest <see|done|cancel|list>");

						break;
				}
			}else{
				$s->sendMessage("§6- §cUsage /quest <see|done|cancel|list>");
			}
		}
	}

	public function getUserData($playern){

		return new DB($this->getDataFolder() . "players/" . strtolower($playern) . ".sq3");
	}

	public function dataExists($playern, $quest){

		$name = strtolower($playern);

		if(file_exists($this->getDataFolder() . "players/" . strtolower($playern) . ".sq3")){
			$data = $this->getUserData($name);
			$data->exec("CREATE TABLE IF NOT EXISTS database (quest);");
			$res = $data->query("SELECT * FROM database WHERE quest='$quest';");
			$ar = $res->fetchArray(SQLITE3_ASSOC);

			if(empty($ar) == false){
				return true;
			}
		}
	}

	public function translate($chat, Player $player){
		$msg = str_replace("{player}", $player->getName(), $chat);
		return $msg;
	}

	public function onKill(PlayerDeathEvent $ev){
		$lastdmg = $ev->getEntity()->getLastDamageCause();
		$p = $ev->getPlayer();

		if($lastdmg instanceof EntityDamageByEntityEvent){

			$dmgr = $lastdmg->getDamager();

			if(isset($this->kills[strtolower($dmgr->getName())])){
				$this->kills[strtolower($dmgr->getName())]++;
			}else{
				$this->kills[strtolower($dmgr->getName())] = 1;
			}
		}
	}
}