<?php
/**
 * DespawnItemTask.php class
 *
 * Created on 24/05/2016 at 8:40 PM
 *
 * @author Jack
 */

namespace crates\task;

use crates\Main;
use pocketmine\math\Vector3;
use pocketmine\network\protocol\RemoveEntityPacket;
use pocketmine\network\protocol\TileEventPacket;
use pocketmine\Player;
use pocketmine\scheduler\PluginTask;

class DespawnItemTask extends PluginTask {

	/** @var Main */
	private $plugin;

	/** @var Player */
	private $player;

	/** @var int */
	private $eid;

	private $pos;

	public function __construct(Main $plugin, Player $player, int $eid, Vector3 $pos) {
		parent::__construct($plugin);
		$this->plugin = $plugin;
		$this->player = $player;
		$this->eid = $eid;
		$this->pos = $pos;
		$plugin->getServer()->getScheduler()->scheduleDelayedTask($this, 20 * 5);
	}

	/**
	 * @return Main
	 */
	public function getPlugin() {
		return $this->plugin;
	}

	public function onRun($currentTick) {
		$pk = new RemoveEntityPacket();
		$pk->eid = $this->eid;
		$this->player->dataPacket($pk);
		$pk = new TileEventPacket();
		$pk->x = $this->pos->x;
		$pk->y = $this->pos->y;
		$pk->z = $this->pos->z;
		$pk->case1 = 1;
		$pk->case2 = 0;
		$this->player->dataPacket($pk);
		unset($this->plugin->openCrates[$this->player->getName()]);
	}

}