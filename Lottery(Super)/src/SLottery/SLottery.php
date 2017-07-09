<?php

namespace SLottery;

use onebone\economyapi\EconomyAPI;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;
use pocketmine\level\particle\LavaParticle;
use pocketmine\level\sound\ExplodeSound;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class SLottery extends PluginBase implements Listener {

	public function onEnable() {
		$this->getLogger()->info('§aCPELottery Loading...');
		@mkdir($this->getDataFolder());
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$conf = new Config($this->getDataFolder() . 'Config.yml', Config::YAML, [
			'cost' => 1000,
			'reward' => [
				['id' => 266, 'amount' => 10, 'chance' => 1.10, 'name' => 'Gold Ingots'],
				['id' => 264, 'amount' => 2, 'chance' => 1.0008, 'name' => 'Diamonds'],
				['id' => 15, 'amount' => 10, 'chance' => 1.10, 'name' => 'Iron Ores'],
				['id' => 263, 'amount' => 20, 'chance' => 2.00, 'name' => 'Coals'],
				['id' => 336, 'amount' => 10, 'chance' => 1.88, 'name' => 'Bricks'],
				['id' => 307, 'amount' => 1, 'chance' => 1.15, 'name' => 'Iron ChestPlate'],
				['id' => 311, 'amount' => 1, 'chance' => 1.001, 'name' => 'Diamond ChestPlate'],
				['id' => 313, 'amount' => 1, 'chance' => 1.01, 'name' => 'Diamond boots'],
				['id' => 308, 'amount' => 1, 'chance' => 1.15, 'name' => 'Iron Leggings'],
				['id' => 310, 'amount' => 1, 'chance' => 1.002, 'name' => 'Diamond Helmet'],
				['id' => 315, 'amount' => 1, 'chance' => 1.10, 'name' => 'Golden ChestPlate'],
				['id' => 14, 'amount' => 10, 'chance' => 1.30, 'name' => 'Gold Ores'],
				['id' => 384, 'amount' => 5, 'chance' => 1.007, 'name' => 'Bottle o Enchanting'],
				['id' => 388, 'amount' => 8, 'chance' => 1.02, 'name' => 'Emeralds'],
				['id' => 49, 'amount' => 1, 'chance' => 1.005, 'name' => 'Obsidians'],
				['id' => 4, 'amount' => 30, 'chance' => 2.13, 'name' => 'CobbleStones'],
				['id' => 264, 'amount' => 5, 'chance' => 1.0005, 'name' => 'Diamonds'],
				['id' => 17, 'amount' => 15, 'chance' => 1.37, 'name' => 'Iron Ores'],
				['id' => 266, 'amount' => 15, 'chance' => 1.08, 'name' => 'Gold Ingots'],
				['id' => 14, 'amount' => 15, 'chance' => 1.7, 'name' => 'Gold Ores'],
				['id' => 41, 'amount' => 3, 'chance' => 1.07, 'name' => 'Gold Blocks'],
				['id' => 369, 'amount' => 3, 'chance' => 1.0003, 'name' => 'Lottery Stick'],
				['id' => 364, 'amount' => 8, 'chance' => 2.2, 'name' => 'Steak'],
				['id' => 260, 'amount' => 17, 'chance' => 1.9, 'name' => 'Apples'],
				['id' => 276, 'amount' => 1, 'chance' => 2.0005, 'name' => 'Diamond sword'],
				['id' => 412, 'amount' => 10, 'chance' => 1.8, 'name' => 'Cooked Rabbit'],
				['id' => 372, 'amount' => 3, 'chance' => 1.009, 'name' => 'Nether Wart'],
				['id' => 370, 'amount' => 3, 'chance' => 1.005, 'name' => 'Shout Tokens'],
			],
		]);
		$list = $this->getConf()->get('reward');
		$num = 0;
		foreach($list as $i) {
			$this->itemdata[$num] = ['id' => $i['id'], "meta" => 0, "amount" => $i['amount']];
			$this->chance[$num] = ['id' => $i['id'], 'chance' => $i['chance'], 'name' => $i['name']];
			$num++;
		}
		$this->getLogger()->info('§6CPELottery Loaded');
	}

	public function onTouch(PlayerInteractEvent $event) {
		$player = $event->getPlayer();
		$id = $event->getBlock()->getId();
		$han = $event->getItem()->getId();
		if($han == 378 && $id == 25) {
			$money = EconomyAPI::getInstance()->myMoney($player);
			$cost = $this->getConf()->get('cost');
			if($money >= $cost) {
				$player->sendTip("§6SUPER §eLottery Start!");
				EconomyAPI::getInstance()->reduceMoney($player, $cost);
				$num = mt_rand(0, (count($this->itemdata) - 1));
				$data = $this->itemdata[$num];
				$chance = $this->chance[$num]['chance'] * 1000;
				$name = $this->chance[$num]['name'];
				$p = mt_rand(0, 1000);
				if($p <= $chance) {
					$this->give($player, $data);
					$player->getInventory()->removeItem(Item::get(Item::MAGMA_CREAM, 0, 1));
					$this->getServer()->broadcastMessage("§l");
					$this->getServer()->broadcastMessage("§c§l»§r §b" . $player->getName() . "§7 got §e" . $data['amount'] . " " . $name . " §7from §6Super Lottery§7!");
					$this->getServer()->broadcastMessage("§l");
					$particle = new LavaParticle($event->getBlock());
					for($i = 10; $i < 50; $i++) {
						$event->getBlock()->getLevel()->addParticle($particle);
						$player->getLevel()->addSound(new ExplodeSound($player));
					}
				} else {
					$player->sendMessage("§6- §7Got nothing. Try Again next time.");
					$player->getInventory()->removeItem(Item::get(Item::MAGMA_CREAM, 0, 1));
				}
			} else {
				$player->sendPopup("§cNeed 1000 Coins to Lottery!");
			}
		}
	}

	public function give(Player $player, $data) {
		$item = new Item($data['id'], $data['meta'], $data['amount']);
		$player->getInventory()->addItem($item);
	}

	public function getConf() {
		return new Config($this->getDataFolder() . 'Config.yml', Config::YAML, []);
	}
}