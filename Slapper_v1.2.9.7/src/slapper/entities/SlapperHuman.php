<?php

namespace slapper\entities;

use pocketmine\entity\Human;
use pocketmine\level\format\Chunk;
use pocketmine\level\format\FullChunk;
use pocketmine\level\Level;
use pocketmine\nbt\tag\Compound;
use pocketmine\network\protocol\AddPlayerPacket;
use pocketmine\network\protocol\PlayerListPacket;
use pocketmine\Player;

class SlapperHuman extends Human {

	public function __construct(FullChunk $chunk, Compound $nbt) {
		parent::__construct($chunk, $nbt);
		$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_IMMOBILE, true);
		$this->setNameTagVisible(true);
		$this->setNameTagAlwaysVisible(true);
	}

	public function getDisplayName() {
		return $this->namedtag["CustomName"] ?? "";
	}

	public function spawnTo(Player $player) {
		if($player !== $this and !isset($this->hasSpawned[$player->getId()])) {
			$this->hasSpawned[$player->getId()] = $player;
			$uuid = $this->getUniqueId();
			$entityId = $this->getId();
			$pk = new AddPlayerPacket();
			$pk->uuid = $uuid;
			$pk->username = $this->getDisplayName();
			$pk->eid = $entityId;
			$pk->x = $this->x;
			$pk->y = $this->y;
			$pk->z = $this->z;
			$pk->yaw = $this->yaw;
			$pk->pitch = $this->pitch;
			$pk->item = $this->getInventory()->getItemInHand();
			$pk->metadata = $this->dataProperties;
			$player->dataPacket($pk);
			$this->inventory->sendArmorContents($player);
			$add = new PlayerListPacket();
			$add->type = 0;
			$add->entries[] = [$uuid, $entityId, $this->getDisplayName(), $this->skinName, $this->skin];
			$player->dataPacket($add);
		}
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
