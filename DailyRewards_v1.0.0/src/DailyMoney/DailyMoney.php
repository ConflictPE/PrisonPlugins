<?php
namespace DailyMoney;

use onebone\economyapi\EconomyAPI;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class DailyMoney extends PluginBase implements Listener{

	public $money;
	private $path, $conf;

	public function onEnable(){

		@mkdir($this->getDataFolder());
		$this->cfg = new Config($this->getDataFolder() . "config.yml", Config::YAML, []);
		if(!$this->cfg->exists("每日簽到金")){
			$this->cfg->set("每日簽到金", "200");
			$this->cfg->save();
		}
		if(!$this->cfg->exists("前綴")){
			$this->cfg->set("前綴", "每日簽到");
			$this->cfg->save();
		}
		$this->Money = $this->cfg->get("每日簽到金");
		$this->Prefix = $this->cfg->get("前綴");
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getLogger()->info("§b每日簽到插件已成功載入");
	}

	public function onCommand(CommandSender $sender, Command $command, $label, array $args){
		$user = $sender->getName();
		$ny = date("Y");
		$nm = date("m");
		$nd = date("d");
		$m = $this->Money;
		$p = $this->Prefix;
		if($sender instanceof Player){
			switch($command->getName()){
				case "rw":
					if(!file_exists($this->getDataFolder() . "$ny.$nm.$nd./")){
						@mkdir($this->getDataFolder() . "$ny.$nm.$nd./");
						file_put_contents($this->getDataFolder() . "$ny.$nm.$nd./" . $user . ".yml", $this->getResource("player.yml"));
						EconomyAPI::getInstance()->addMoney($user, $m);
						$sender->sendMessage("§6- §aYou've recieved §e$m §acoins for daily rewards.");

					}elseif(!file_exists($this->getDataFolder() . "$ny.$nm.$nd./$user.yml")){
						file_put_contents($this->getDataFolder() . "$ny.$nm.$nd./" . $user . ".yml", $this->getResource("player.yml"));
						EconomyAPI::getInstance()->addMoney($user, $m);
						$sender->sendMessage("§6- §aYou've recieved §e$m §acoins for daily rewards.");

					}elseif(file_exists($this->getDataFolder() . "$ny.$nm.$nd./$user.yml")){
						$sender->sendMessage("§6- §cYou already claimed the reward. Come back tomorrow.");

					}
			}
			return true;
		}else{
			$sender->sendMessage(TextFormat::RED . "此指令只能在遊戲中使用");
			return true;
		}
	}
}
						
	
	