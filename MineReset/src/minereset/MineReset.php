<?php
namespace minereset;

use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class MineReset extends PluginBase implements CommandExecutor, Listener{

	public $sessions;
	/** @var  Config */
	public $mineData;
	/** @var  Mine[] */
	public $mines;
	/** @var  RegionBlocker */
	private $regionBlocker;

	public function onEnable(){
		@mkdir($this->getDataFolder());
		$this->mineData = new Config($this->getDataFolder() . "mines.yml", Config::YAML, []);
		$this->mines = [];
		$this->parseMines();
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->regionBlocker = new RegionBlocker($this);
		$this->sessions = [];
	}

	public function onCommand(CommandSender $sender, Command $cmd, $label, array $args){
		if(isset($args[0])){
			if(!$sender->hasPermission("minereset.commmand." . $args[0])){
				$sender->sendMessage("§6- §cYou do not have permission.");
				return true;
			}else{
				switch($args[0]){
					case "create":
					case "c":
						if($sender instanceof Player){
							if(isset($args[1])){
								if(!isset($this->mines[$args[1]])){
									$this->sessions[$sender->getName()] = [$args[1]];
									$sender->sendMessage("§6- §3Tap a block to set as first position...");
									return true;
								}else{
									$sender->sendMessage("§6- §cThat mine already exists.");
									return true;
								}
							}else{
								$sender->sendMessage("§6- §cYou must specify a name.");
								return true;
							}
						}else{
							$sender->sendMessage("§6- §cPlease run command in game.");
							return true;
						}
						break;
					case "destroy":
					case "d":
						if(isset($args[1])){
							if(isset($this->mines[$args[1]])){
								unset($this->mines[$args[1]]);
								$this->saveConfig();
								$sender->sendMessage("§6- §c" . $args[1] . " has been destroyed.");
								return true;
							}else{
								$sender->sendMessage("§6- §cThat mine doesn't exist.");
								return true;
							}
						}else{
							$sender->sendMessage("§6- §cYou must specify a name.");
							return true;
						}
						break;
					case "set":
					case "s":
						if(isset($args[1])){
							if(isset($args[3])){
								if(isset($this->mines[$args[1]])){
									$sets = array_slice($args, 2);
									$save = [];
									foreach($sets as $key => $item){
										if($key & 1){
											if(isset($save[$sets[$key - 1]])){
												$save[$sets[$key - 1]] += $item;
											}else{
												$save[$sets[$key - 1]] = $item;
											}
										}
									}
									$this->mines[$args[1]]->setData($save);
									$sender->sendMessage("§6- §aMine setted.");
									$this->saveConfig();
									return true;
								}else{
									$sender->sendMessage("§6- §cMine doesn't exist.");
									return true;
								}
							}else{
								$sender->sendMessage("§6- §cYou must provide at least one value.");
								return true;
							}
						}else{
							$sender->sendMessage("§6- §cYou must specify a name.");
							return true;
						}
						break;
					case "reset":
					case "r":
						if(isset($args[1])){
							if(isset($this->mines[$args[1]])){
								$mine = $this->getMineById($args[1]);
								if($mine->isMineSet()){
									if(!$mine->isResetting()){
										$mine->resetMine();
										$sender->sendMessage("§6- §3Mine is now resetting.");
										return true;
									}else{
										$sender->sendMessage("§6- §cMine is already resetting!");
										return true;
									}
								}else{
									$sender->sendMessage("§6- §cMine has not been set.");
									return true;
								}
							}else{
								$sender->sendMessage("§6- §cMine doesn't exist.");
								return true;
							}
						}else{
							$sender->sendMessage("§6- §cYou need to specify a name.");
							return true;
						}
						break;
					case "reset-all":
						$i = 0;
						foreach($this->mines as $n => $mine){
							if($mine->isMineSet() and !$mine->isResetting()){
								$mine->resetMine();
								$i++;
							}
						}
						$sender->sendMessage("§6- §3Resetting {$i} mines.");
						return true;
					case "longreset":
					case "lr":
						$sender->sendMessage("§6- §cLong resetting is no longer supported, if you need it use an older version.");
						return true;
						break;
				}
			}
		}else{
			$sender->sendMessage("§6- §cYou must specify the action to perform.");
			return true;
		}
		return false;
	}

	public function onBlockTap(PlayerInteractEvent $event){
		if(isset($this->sessions[$event->getPlayer()->getName()])){
			if(isset($this->sessions[$event->getPlayer()->getName()][1])){
				$a = $this->sessions[$event->getPlayer()->getName()][1];
				$b = $event->getBlock();
				$name = $this->sessions[$event->getPlayer()->getName()][0];
				$this->mines[$name] = new Mine($this, new Vector3(min($a->getX(), $b->getX()), min($a->getY(), $b->getY()), min($a->getZ(), $b->getZ())), new Vector3(max($a->getX(), $b->getX()), max($a->getY(), $b->getY()), max($a->getZ(), $b->getZ())), $b->getLevel(), $name);
				$event->getPlayer()->sendMessage("Mine created.");
				unset($this->sessions[$event->getPlayer()->getName()]);
				$this->saveConfig();
			}else{
				$this->sessions[$event->getPlayer()->getName()][1] = new Vector3($event->getBlock()->getX(), $event->getBlock()->getY(), $event->getBlock()->getZ());
				$event->getPlayer()->sendMessage("Tap another block to create mine");
			}
		}
	}

	public function saveConfig(){
		$this->mineData->setAll([]);
		foreach($this->mines as $n => $mine){
			$this->mineData->set($n, [
				$mine->getA()->getX(),
				$mine->getB()->getX(),
				$mine->getA()->getY(),
				$mine->getB()->getY(),
				$mine->getA()->getZ(),
				$mine->getB()->getZ(),
				(count($mine->getData()) > 0 ? $mine->getData() : false),
				$mine->getLevel()->getName(),
			]);
		}
		$this->mineData->save();
	}

	public function parseMines(){
		foreach($this->mineData->getAll() as $n => $m){
			$this->mines[$n] = new Mine($this, new Vector3(min($m[0], $m[1]), min($m[2], $m[3]), min($m[4], $m[5])), new Vector3(max($m[0], $m[1]), max($m[2], $m[3]), max($m[4], $m[5])), $this->getServer()->getLevelByName($m[7]), $n, $m[6] ?? []);
		}
	}

	public function scheduleReset(Mine $mine, MineResetTask $mineResetTask){
		$mine->setResetting();
		$this->getServer()->getScheduler()->scheduleAsyncTask($mineResetTask);
	}

	public function getMineById($mineId){
		return $this->mines[$mineId];
	}

	/**
	 * @return RegionBlocker
	 */
	public function getRegionBlocker(){
		return $this->regionBlocker;
	}

}
