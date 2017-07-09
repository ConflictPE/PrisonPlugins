<?php

namespace Lottery;

use onebone\economyapi\EconomyAPI;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;
use pocketmine\level\particle\HappyVillagerParticle;
use pocketmine\level\particle\LavaParticle;
use pocketmine\level\sound\EndermanTeleportSound;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class Lottery extends PluginBase implements Listener {

	public function onEnable() {
		$this->getLogger()->info('§aCPELottery Loading...');
		@mkdir($this->getDataFolder());
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$conf = new Config($this->getDataFolder() . 'Config.yml', Config::YAML, [
			'cost' => 500,
			'reward' => [
				['id' => 266, 'amount' => 10, 'chance' => 0.10, 'name' => 'Gold Ingots'],
				['id' => 264, 'amount' => 2, 'chance' => 0.0008, 'name' => 'Diamonds'],
				['id' => 15, 'amount' => 10, 'chance' => 0.10, 'name' => 'Iron Ores'],
				['id' => 263, 'amount' => 20, 'chance' => 1.00, 'name' => 'Coals'],
				['id' => 336, 'amount' => 10, 'chance' => 0.88, 'name' => 'Bricks'],
				['id' => 307, 'amount' => 1, 'chance' => 0.15, 'name' => 'Iron ChestPlate'],
				['id' => 311, 'amount' => 1, 'chance' => 0.001, 'name' => 'Diamond ChestPlate'],
				['id' => 313, 'amount' => 1, 'chance' => 0.01, 'name' => 'Diamond boots'],
				['id' => 308, 'amount' => 1, 'chance' => 0.15, 'name' => 'Iron Leggings'],
				['id' => 310, 'amount' => 1, 'chance' => 0.002, 'name' => 'Diamond Helmet'],
				['id' => 315, 'amount' => 1, 'chance' => 0.10, 'name' => 'Golden ChestPlate'],
				['id' => 14, 'amount' => 10, 'chance' => 0.30, 'name' => 'Gold Ores'],
				['id' => 384, 'amount' => 5, 'chance' => 0.007, 'name' => 'Bottle o Enchanting'],
				['id' => 388, 'amount' => 8, 'chance' => 0.02, 'name' => 'Emeralds'],
				['id' => 49, 'amount' => 1, 'chance' => 0.005, 'name' => 'Obsidians'],
				['id' => 4, 'amount' => 30, 'chance' => 1.13, 'name' => 'CobbleStones'],
				['id' => 264, 'amount' => 5, 'chance' => 0.0005, 'name' => 'Diamonds'],
				['id' => 17, 'amount' => 15, 'chance' => 0.37, 'name' => 'Iron Ores'],
				['id' => 266, 'amount' => 15, 'chance' => 0.08, 'name' => 'Gold Ingots'],
				['id' => 14, 'amount' => 15, 'chance' => 0.7, 'name' => 'Gold Ores'],
				['id' => 41, 'amount' => 3, 'chance' => 0.07, 'name' => 'Gold Blocks'],
				['id' => 369, 'amount' => 3, 'chance' => 0.0003, 'name' => 'Lottery Stick'],
				['id' => 364, 'amount' => 8, 'chance' => 1.2, 'name' => 'Steak'],
				['id' => 260, 'amount' => 17, 'chance' => 0.9, 'name' => 'Apples'],
				['id' => 276, 'amount' => 1, 'chance' => 0.0005, 'name' => 'Diamond sword'],
				['id' => 412, 'amount' => 10, 'chance' => 0.8, 'name' => 'Cooked Rabbit'],
				['id' => 372, 'amount' => 3, 'chance' => 0.009, 'name' => 'Nether Wart'],
				['id' => 370, 'amount' => 3, 'chance' => 0.005, 'name' => 'Shout Tokens'],
			],
		]);
		$list = $this->getConf()->get('reward');
		$num = 0;
		foreach($list as $i) {
			$this->itemdata[$num] = ['id' => $i['id'], "meta" => 0, "amount" => $i['amount']];
			$this->chance[$num] = ['id' => $i['id'], 'chance' => $i['chance'], 'name' => $i['name']];
			$num++;
		}
		$this->getLogger()->info('§6CPELottery Loaded!!!');
	}

	public function onTouch(PlayerInteractEvent $event) {
		$player = $event->getPlayer();
		$id = $event->getBlock()->getId();
		$han = $event->getItem()->getId();
		if($han == 341 && $id == 25) {
			$money = EconomyAPI::getInstance()->myMoney($player);
			$cost = $this->getConf()->get('cost');
			if($money >= $cost) {
				$player->sendPopup("§eNormal Lottery Start!");
				EconomyAPI::getInstance()->reduceMoney($player, $cost);
				$num = mt_rand(0, (count($this->itemdata) - 1));
				$data = $this->itemdata[$num];
				$chance = $this->chance[$num]['chance'] * 1000;
				$name = $this->chance[$num]['name'];
				$p = mt_rand(0, 1000);
				if($p <= $chance) {
					$this->give($player, $data);
					$player->getInventory()->removeItem(Item::get(Item::SLIMEBALL, 0, 1));
					$player->sendMessage("§b§l»§r §fYou got §e" . $data['amount'] . " " . $name . " §ffrom Lottery.");
					$particle = new LavaParticle($event->getBlock());
					for($i = 0; $i < 10; $i++) {
						$event->getBlock()->getLevel()->addParticle($particle);
						$player->getLevel()->addSound(new EndermanTeleportSound($player));
					}
				} else {
					$bl = $event->getBlock();
					$blx = $bl->getX();
					$bly = $bl->getY();
					$blz = $bl->getZ();
					$player->sendMessage("§6- §7Nothing. Try Again next time. :/");
					$player->getInventory()->removeItem(Item::get(Item::SLIMEBALL, 0, 1));
					for($i = 0; $i < 50; $i++) {
						$event->getBlock()->getLevel()->addParticle(new HappyVillagerParticle(new Vector3($blx, $bly + 0.9, $blz)));
					}
				}
			} else {
				$player->sendPopup("§cNeed 500 Coins to Lottery!");
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

	public function onDamage(EntityDamageEvent $event) {
		if($event->getCause() == 4) {
			$event->setCancelled(true);
		}
	}
}