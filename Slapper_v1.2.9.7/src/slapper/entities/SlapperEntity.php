<?php

namespace slapper\entities;

use pocketmine\entity\Entity;
use pocketmine\level\format\Chunk;
use pocketmine\level\format\FullChunk;
use pocketmine\level\Level;
use pocketmine\nbt\tag\Compound;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\Player;

class SlapperEntity extends Entity {

	public $entityId = -1;
	public $tagId;
	public $tagUUID;
	public $offset = 0;

	public $offsets = [
		10 => 0.4,
		11 => 0.8,
		12 => 0.6,
		13 => 0.8,
		14 => 0.4,
		15 => 1.4,
		16 => 0.8,
		17 => 0.6,
		18 => 0.4,
		19 => 0.4,
		20 => 2.4,
		21 => 1.2,
		22 => 0.4,
		23 => 1.2,
		24 => 1.2,
		25 => 1.2,
		26 => 1.2,
		27 => 1.2,
		32 => 1.4,
		33 => 1.4,
		34 => 1.4,
		35 => 0.5,
		36 => 1.4,
		37 => 1.0,
		38 => 2.4,
		39 => 0.4,
		40 => 0.2,
		41 => 4.5,
		42 => 1.0,
		43 => 1.4,
		44 => 1.4,
		45 => 1.6,
		46 => 1.4,
		47 => 1.4,
		48 => 2.1,
		65 => 1.0,
		66 => 0.5,
		84 => 0.5,
		90 => 0.5,
	];

	public function __construct(FullChunk $chunk, Compound $nbt) {
		parent::__construct($chunk, $nbt);
		$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_IMMOBILE, true);
		$this->setNameTagVisible(true);
		$this->setNameTagAlwaysVisible(true);
	}

	public function getName() {
		return $this->getNameTag();
	}

	public function spawnTo(Player $player) {
		$pk = new AddEntityPacket();
		$pk->eid = $this->getId();
		$pk->type = $this->entityId;
		$pk->x = $this->x;
		$pk->y = $this->y + $this->offset;
		$pk->z = $this->z;
		$pk->yaw = $this->yaw;
		$pk->pitch = $this->pitch;
		$pk->metadata = $this->dataProperties;
		$player->dataPacket($pk);
		parent::spawnTo($player);
	}

	public function addNametag($name, $player) {
	}

	public function getDisplayName($player) {
		return str_ireplace(["{name}", "{display_name}", "{nametag}"], [
			$player->getName(),
			$player->getDisplayName(),
			$player->getNametag(),
		], $player->hasPermission("slapper.seeId") ? $this->getDataProperty(2) . "\n" . \pocketmine\utils\TextFormat::GREEN . "Entity ID: " . $this->getId() : $this->getDataProperty(2));
	}

	/**
	 * Update the entity without calling all the functions with extra overhead
	 *
	 * ** If you want the entity to do normal entity things you'll have to override this and call the methods yourself **
	 *
	 * @param $currentTick
	 *
	 * @return bool
	 */
	public function onUpdate($currentTick) {
		if($this->closed){
			return false;
		}

		$tickDiff = max(1, $currentTick - $this->lastUpdate);
		$this->lastUpdate = $currentTick;

		$hasUpdate = $this->entityBaseTick($tickDiff);

		return $hasUpdate;
	}

	/**
	 * Update the entity without calling all the functions with extra overhead
	 *
	 * ** If you want the entity to do normal entity things you'll have to override this and call the methods yourself **
	 *
	 * @param int $tickDiff
	 *
	 * @return bool
	 */
	public function entityBaseTick($tickDiff = 1) : bool {
		if(count($this->changedDataProperties) > 0){
			$this->sendData($this->hasSpawned, $this->changedDataProperties);
			$this->changedDataProperties = [];
		}

		if($this->dead === true) {
			$this->despawnFromAll();
			$this->close();
			return true;
		}

		return false;
	}

	/**
	 * Make sure updated data properties are send to players
	 *
	 * @param string $name
	 * @param int $type
	 * @param mixed $value
	 * @param bool $send
	 *
	 * @return bool
	 */
	public function setDataProperty(string $name, int $type, $value, bool $send = true) : bool {
		$this->scheduleUpdate();
		return parent::setDataProperty($name, $type, $value, $send);
	}

}
