<?php

namespace slapper\entities;

use pocketmine\entity\Human;
use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\protocol\AddPlayerPacket;
use pocketmine\network\protocol\PlayerListPacket;
use pocketmine\Player;

class SlapperHuman extends Human {

	public function __construct(Level $level, CompoundTag $nbt) {
		parent::__construct($level, $nbt);
		$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_IMMOBILE, true);
		$this->setNameTagVisible(true);
		$this->setNameTagAlwaysVisible(true);
	}

	public function getDisplayName() {
		return $this->namedtag->CustomName;
	}

	public function spawnTo(Player $player) {
		if($player !== $this and !isset($this->hasSpawned[$player->getLoaderId()])) {
			$this->hasSpawned[$player->getLoaderId()] = $player;
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
			$add->entries[] = [$uuid, $entityId, $this->getDisplayName(), $this->skinId, $this->skin];
			$player->dataPacket($add);
		}
	}
}
