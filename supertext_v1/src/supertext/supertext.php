<?php
namespace supertext;

use pocketmine\level\particle\FloatingTextParticle;
use pocketmine\math\Vector3;
use pocketmine\utils\Config;

class supertext extends \pocketmine\plugin\PluginBase implements \pocketmine\event\Listener{//To Do teleport

	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->path = $this->getDataFolder();
		@mkdir($this->path);
		$this->cfgA = new Config($this->path . "configA.yml", Config::YAML, []);
		$this->cfgB = new Config($this->path . "configB.yml", Config::YAML, []);
		$this->readconfig();
	}

	public function readconfig(){
		$this->dataA = $this->cfgA->getAll();
		$this->dataB = $this->cfgB->getAll();
		foreach($this->dataB as $k1 => $v1){
			$this->timer[$k1] = $v1["time"];
		}
	}

	public function onjoin(\pocketmine\event\player\PlayerJoinEvent $ev){
		$p = $ev->getPlayer();
		$this->updatealltext($p);
	}

	public function updatealltext($p){
		if(isset($this->dataA)){
			foreach($this->dataA as $k1 => $v1){
				$this->simplesendtext($p, $k1);
			}
		}
		if(isset($this->dataB)){
			foreach($this->dataB as $k2 => $v2){
				$this->simpleupdatetext($p, $k2);
			}
		}
	}

	public function simplesendtext($p, $key){
		$pos = new Vector3($this->dataA[$key]["pos"]["xa"], ($this->dataA[$key]["pos"]["ya"]) - 1, $this->dataA[$key]["pos"]["za"]);
		$par = new FloatingTextParticle($pos, null, $this->dataA[$key]["text"]);
		$this->getServer()->getLevelByName($this->dataA[$key]["pos"]["level"])->addParticle($par, [$p]);
	}

	public function simpleupdatetext($p, $key){
		if(isset($this->now[$key])){
			$pos = new Vector3($this->dataB[$key]["pos"]["xa"], $this->dataB[$key]["pos"]["ya"], $this->dataB[$key]["pos"]["za"]);
			$par = new FloatingTextParticle($pos, null, $this->dataA[$key]["text"]);
			$this->getServer()->getLevelByName($this->dataB[$key]["pos"]["level"])->addParticle($par, [$p]);
		}
	}

	public function timer(){
		if(isset($this->timer)){
			//$time=;
			//$date=;
			$allplayer = $this->getServer()->getOnlinePlayers();
			$onlineplayer = count($allplayer);
			//$allplayer=;
			foreach($this->timer as $k => $v){
				$leftsecond = $v--;
				if($leftsecond < 0){
					if(isset($this->towhich[$k])){
						$newtowhich = $this->towhich[$k] + 1;
						if(isset($this->dataB[$k]["text"][$newtowhich])){
							$this->now[$k] = $this->dataB[$k]["text"][$newtowhich];
							$this->towhich[$k] = $newtowhich;
						}else{
							$this->towhich[$k] = 1;
							$this->now[$k] = $this->dataB[$k]["text"][1];
						}
					}else{
						$this->towhich[$k] = 1;
						$this->now[$k] = $this->dataB[$k]["text"][1];
					}
					foreach($allplayer as $p){
						$this->simpleupdatetext($p, $k);
					}
				}else{
					$this->timer[$k] = $v--;
				}
			}
		}
		//To test Delay Task(by judging tick)
		$this->getServer()->getScheduler()->scheduleDelayedTask(new callbacktask([
			$this,
			"timer",
		]), $this->getServer()->getTicksPerSecond());
	}

	public function onCommand(\pocketmine\command\CommandSender $sender, \pocketmine\command\Command $command, $label, array $args){
		switch($command->getName()){
			case 'Atext':
				if(isset($args[0])){
					switch(strtolower($args[0])){
						case 'set':
							if(isset($args[1]) and isset($args[2])){
								$setting["text"] = $args[2];
								$setting["pos"]["xa"] = $sender->getX();
								$setting["pos"]["ya"] = $sender->getY();
								$setting["pos"]["za"] = $sender->getZ();
								$setting["pos"]["level"] = $sender->getLevel()->getFolderName();
								$bbb = "$args[1]";
								$this->setdata("A", $bbb, $setting);
								$sender->sendMessage("§6- §aAdded a Floating Text.");
							}else{
								$sender->sendMessage("§6- §cInvalid Command. §bAtext set <name> <text>");
							}
							break;
						case 'del':
							if(isset($args[1])){
								if(isset($this->dataA[$args[1]])){
									//to test if it is changeable
									$this->dataA[$args[1]]["text"] = "this floatingtext has been delete.\nit will disappear next time you join";
									foreach($this->getServer()->getOnlinePlayers() as $p){
										$this->simplesendtext($p, $args[1]);
									}
									$setting = null;
									$vv = "delable";
									$this->setdata("A", $args[1], $vv);
									$sender->sendMessage("success");
								}else{
									$sender->sendMessage("error/useage:\n/Atext del <name>");
								}
							}else{
								$sender->sendMessage("error:no such text");
							}
							break;
						case 'list':
							if(isset($this->dataA)){
								foreach($this->dataA as $kn => $v){
									$sender->sendMessage("name:" . $kn . ";      world:" . $v["pos"]["level"] . ";     text:" . $v["text"]);
								}
							}else{
								$sender->sendMessage("no data!~~");
							}
							break;
						default:
							$sender->sendMessage("useage:\n/Atext set <name> <text>\n/Atext list\n/Atext del <name>");
					}
				}else{
					$sender->sendMessage("useage:\n/Atext add <name> <text>\n/Atext list\n/Atext del <name>");
				}
				break;
			case 'Btext':
				if(isset($args[0])){
					switch(strtolower($args[0])){
						case 'add':
							break;
						case 'set':
							break;
						case 'del':
							break;
						case 'list':
							break;
						default:
					}
				}else{
				}
				break;
		}
	}

	public function setdata($type, $name, $value){
		if($type == "A"){
			$cfgA = new Config($this->path . "configA.yml", Config::YAML, [$name => []]);
			if($value === "delable"){
				unset($this->dataA[$name]);
				$this->cfgA->remove($name);
				$this->cfgA->save();
			}else{
				$cfgA->set($name, $value);
				$cfgA->save();
				$this->dataA[$name] = $value;
				if(!$value == null){
					foreach($this->getServer()->getOnlinePlayers() as $p){
						$this->simplesendtext($p, $name);
					}
				}
			}
		}
		if($type == "B"){
			$cfgB = new Config($this->path . "configB.yml", Config::YAML, [$name => []]);
			if($value === "delable"){
				unset($this->dataA[$name]);
				$this->cfgB->remove($name);
				$this->cfgA->save();
			}else{
				$cfgB->set($name, $value);
				$cfgB->save();
				$this->dataB[$name] = $value;
			}
		}
	}
}