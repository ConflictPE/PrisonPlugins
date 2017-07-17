<?php
/**
 * CrateManager class
 *
 * Created on Mar 16, 2016 at 9:33:35 PM
 *
 * @author Jack
 */

namespace crates\crate;

use crates\Main;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\Item;
use pocketmine\level\particle\FloatingTextParticle;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\Server;
use pocketmine\utils\TextFormat as TF;

class CrateManager {

	private $plugin;

	public function __construct(Main $plugin) {
		$this->plugin = $plugin;
		$this->register();
	}

	public static function clean($string) {
		return strtolower(TF::clean(str_replace(["\n", " "], [" ", "-"], Main::applyColors($string))));
	}

	public static function parsePositions(array $strings) {
		$positions = [];
		foreach($strings as $string) {
			$positions[] = self::parsePosition($string);
		}
		return $positions;
	}

	public static function parsePosition($string) {
		$temp = explode(",", str_replace(" ", "", $string));
		if(Server::getInstance()->isLevelLoaded($temp[0])) {
			return new Position($temp[1], $temp[2], $temp[3], Server::getInstance()->getLevelByName($temp[0]));
		}
		return null;
	}

	public static function parseItems(array $strings) {
		$items = [];
		foreach($strings as $string) {
			$items[] = self::parseItem($string);
		}
		return $items;
	}

	public static function parseItem($string) {
		$temp = explode(",", str_replace(" ", "", $string));
		if(isset($temp[4])) {
			$item = Item::get((int) $temp[0], (int) $temp[1], (int) $temp[2]);
			$item->addEnchantment(Enchantment::getEnchantment((int) $temp[3])->setLevel((int) $temp[4]));
			return $item;
		} elseif(isset($temp[3])) {
			$item = Item::get((int) $temp[0], (int) $temp[1], (int) $temp[2]);
			$item->addEnchantment(Enchantment::getEnchantment((int) $temp[3]));
			return $item;
		} else {
			return Item::get((int) $temp[0], (int) $temp[1], (int) $temp[2]);
		}
	}

	public function register() {
		foreach($this->plugin->crateData as $data) {
			$this->add(Main::applyColors($data["name"]), Main::applyColors($data["description"]), (int) $data["tripwire-id"], self::parsePositions($data["locations"]), self::parseItems($data["prizes"]["items"]), $data["prizes"]["money"]);
		}
	}

	public function add($name, $description, $crateId, array $locations, array $itemPrizes, array $moneyPrizes) {
		foreach($locations as $crate) {
			if($crate instanceof Position) {
				//				if(!$crate->getLevel()->getBlock($crate)->getId() === Block::CHEST) {
				//					$nbt = new Compound(false, [
				//						new Enum("Items", []),
				//						new StringTag("id", Tile::CHEST),
				//						new IntTag("x", $crate->x),
				//						new IntTag("y", $crate->y),
				//						new IntTag("z", $crate->z),
				//					]);
				//					$nbt->Items->setTagType(NBT::TAG_Compound);
				//					$crate->getLevel()->setBlock($crate, Block::get(Block::CHEST));
				//					$tile = Tile::createTile(Tile::CHEST, $crate->getLevel()->getChunk($crate->x >> 4, $crate->z >> 4), $nbt);
				//					$crate->getLevel()->addTile($tile);
				//				}
				$crateClass = new Crate($name, $description, $locations, $crateId, $itemPrizes, $moneyPrizes);
				$this->plugin->crates[self::clean($name)] = $crateClass;
				$this->plugin->text[] = new FloatingTextParticle(new Vector3($crate->x + 0.5, $crate->y + 0.5, $crate->z + 0.5), "", $name);
				$this->plugin->text[] = new FloatingTextParticle(new Vector3($crate->x + 0.5, $crate->y + 0.2, $crate->z + 0.5), "", $description);
			}
		}
	}

	public function getPlugin() {
		return $this->plugin;
	}

	public function __destruct() {
		$this->close();
	}

	public function close() {
		unset($this->plugin);
	}

}