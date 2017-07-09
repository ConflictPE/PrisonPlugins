<?php
/**
 * EventListener class
 *
 * Created on Mar 16, 2016 at 9:34:37 PM
 *
 * @author Jack
 */

namespace crates;

use crates\crate\Crate;
use crates\task\DespawnItemTask;
use pocketmine\entity\Entity;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\Item;
use pocketmine\level\particle\HappyVillagerParticle;
use pocketmine\math\Vector3;
use pocketmine\network\protocol\AddItemEntityPacket;
use pocketmine\network\protocol\BlockEventPacket;
use pocketmine\network\protocol\SetEntityDataPacket;
use pocketmine\tile\Chest;
use pocketmine\utils\TextFormat as TF;

class EventListener implements Listener {

	private $plugin;

	public function __construct(Main $plugin) {
		$this->plugin = $plugin;
		$plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
	}

	public function getPlugin() {
		return $this->plugin;
	}

	public function onJoin(PlayerJoinEvent $event) {
		$player = $event->getPlayer();
		foreach($this->plugin->text as $particle) {
			foreach($particle->encode() as $pk) {
				$player->dataPacket($pk);
			}
		}
	}

	public function onInteract(PlayerInteractEvent $event) {
		$player = $event->getPlayer();
		$tile = $player->getLevel()->getTile($event->getBlock());
		if($tile instanceof Chest) {
			$block = new Vector3($tile->x, $tile->y, $tile->z);
			$crate = $this->plugin->getCrate($block);
			if($crate instanceof Crate) {
				$event->setCancelled(true);
				$id = $crate->getId();
				if(((isset($this->plugin->getKeyData($player->getName())[$id]) ? $this->plugin->getKeyData($player->getName())[$id] : 0)) >= 1) {
					$this->plugin->saveKeyData($player->getName(), [$id => $this->plugin->getKeyData($player->getName())[$id] - 1,]);
					if(mt_rand(1, 3) <= 2) {
						$prize = $crate->getRandomItem();
					} else {
						if((bool) $this->plugin->settings["economy"]["enabled"]) {
							$prize = $crate->getRandomMoney();
						} else {
							$prize = $crate->getRandomItem();
						}
					}
					$name = "";
					if($prize instanceof Item) {
						$player->getInventory()->addItem($prize);
						$name = TF::YELLOW . "{$prize->getCount()}x " . ($prize->hasCustomName() ? $prize->getCustomName() : $prize->getName());
					} elseif(is_int($prize)) {
						$this->plugin->getEconomy()->addMoney($player, $prize, true);
						$amount = $prize;
						$prize = Item::get(Item::PAPER);
						$name = TF::GREEN . "$ {$amount}";
						$prize->addEnchantment(Enchantment::getEnchantment(Enchantment::TYPE_INVALID));
					}
					$player->sendMessage(Main::applyColors(str_replace("{name}", Main::applyColors($this->plugin->settings["keys"][$id]), $this->plugin->settings["messages"]["open-crate"])));
					$eid = Entity::$entityCount++;
					$pk = new BlockEventPacket();
					$pk->x = $block->x;
					$pk->y = $block->y;
					$pk->z = $block->z;
					$pk->case1 = 1;
					$pk->case2 = 2;
					$player->dataPacket($pk);
					$pk = new AddItemEntityPacket();
					$pk->eid = $eid;
					$pk->item = $prize;
					$pk->x = $block->x + 0.5;
					$pk->y = $block->y + 1.2;
					$pk->z = $block->z + 0.5;
					$pk->speedZ = 0;
					$pk->speedY = 0;
					$pk->speedZ = 0;
					$player->dataPacket($pk);
					$pk = new SetEntityDataPacket();
					$pk->eid = $eid;
					$flags = 0;
					$flags |= 1 << Entity::DATA_FLAG_INVISIBLE;
					$flags |= 1 << Entity::DATA_FLAG_CAN_SHOW_NAMETAG;
					$flags |= 1 << Entity::DATA_FLAG_ALWAYS_SHOW_NAMETAG;
					$flags |= 1 << Entity::DATA_FLAG_IMMOBILE;
					$pk->metadata = [
						Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, $flags],
						Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $name],
					];
					$player->dataPacket($pk);
					$particle = new HappyVillagerParticle(new Vector3($block->x + 0.5, $block->y, $block->z + 0.5));
					$player->dataPacket($particle->encode());
					new DespawnItemTask($this->plugin, $player, $eid, new Vector3($tile->x, $tile->y, $tile->z));
					$player->sendMessage(Main::applyColors(str_replace([
						"{amount}",
						"{name}",
					], [
						(isset($this->plugin->getKeyData($player->getName())[$id]) ? $this->plugin->getKeyData($player->getName())[$id] : 0),
						Main::applyColors($this->plugin->settings["keys"][$id]),
					], $this->plugin->settings["messages"]["keys-remaining"])));
				} else {
					$player->sendMessage(Main::applyColors($this->plugin->settings["messages"]["no-crate-key"]));
				}
			}
		}
	}

	public function onHold(PlayerItemHeldEvent $event) {
		$player = $event->getPlayer();
		$item = $event->getItem();
		if($item->getId() === Item::TRIPWIRE_HOOK) {
			$player->getInventory()->remove($item);
		}
	}

	/**
	 * @priority HIGHEST
	 */
	public function onBreak(BlockBreakEvent $event) {
		if($event->isCancelled()) {
			return;
		}
		$player = $event->getPlayer();
		$chance = mt_rand(1, 10000);
		if($chance === 1) {
			$this->plugin->giveKey($player, 1);
			$player->sendMessage(TF::GREEN . "You found a " . Main::applyColors($this->plugin->settings["keys"][1]) . TF::RESET . TF::GREEN . "!");
		}
	}

}