<?php

namespace primus\pc\utils;

use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use primus\pc\PrisonCore;

/**
 * Created by PhpStorm.
 * User: primus
 * Date: 7/31/16
 * Time: 8:24 AM
 */
class HUD extends Task {

	/**
	 * @var HUD
	 */
	private static $instance;

	protected $active = false;

	/** @var \SplObjectStorage|Player[] */
	protected $viewers;

	/** @var PrisonCore */
	protected $plugin;

	protected $text = "";

	protected $sub_text = "";

	private $variables = [
		"{money}",
		"{rumoney}",
		"{nextgroupprice}",
		"{nextrank}",
		"{rank}",
		"{name}",
	];

	public function __construct(PrisonCore $core, string $text = "", string $sub_title = "") {
		self::$instance = $this;
		$this->viewers = new \SplObjectStorage();
		$this->plugin = $core;
		$this->text = $text;
		$this->sub_text = $sub_title;
		$this->active = true;
	}

	public static function get() {
		return self::$instance;
	}

	public function getPlugin() : PrisonCore {
		return $this->plugin;
	}

	public function getViewers() {
		return $this->viewers;
	}

	public function onRun($currentTick) {
		if(!$this->active)
			return;
		$this->render();
	}

	/**
	 * Draws the HUD for each viewer. To show constant HUD call this function every 15 ticks.
	 */
	public function render() {
		foreach($this->viewers as $viewer) {
			if(isset($this->plugin->exemptHud[$viewer->getName()]))
				continue;
			if(!$viewer instanceof Player)
				$this->viewers->detach($viewer);
			if(!$viewer->isOnline())
				$this->viewers->detach($viewer);
			$viewer->sendTip($this->parseVariables($viewer, $this->text));
			$viewer->sendPopup($this->parseVariables($viewer, $this->sub_text));
		}
	}

	public function parseVariables(Player $player, string $text) : STRING {
		$group = $this->plugin->getPurePerms()->getUserDataMgr()->getGroup($player, \null);
		$nextGroup = $this->plugin->getGroupManager()->getNextGroup($group);
		if(!$nextGroup) {
			$nextGroupPrice = 0;
			$nextGroup = "";
		} else {
			$nextGroupPrice = $this->plugin->getGroupManager()->getPrice($nextGroup);
			$nextGroup = $nextGroup->getName();
		}
		$money = $this->plugin->getEconomy()->getMoney($player);
		$left = $nextGroupPrice - $money;
		$left = $left <= 0 ? "§aRankup now!" : "§b$" . $left . " §3left to rankup.";
		$inv = $player->getInventory();
		return str_replace($this->variables, [
			$money,
			$left,
			$nextGroupPrice,
			$nextGroup,
			$group->getName(),
			$player->getDisplayName(),
		], $text);
	}

	/**
	 * @return string
	 */
	public function getSubText() {
		return $this->sub_text;
	}

	/**
	 * @param string $sub_text
	 */
	public function setSubText($sub_text) {
		$this->sub_text = $sub_text;
	}

	/**
	 * @return string
	 */
	public function getText() {
		return $this->text;
	}

	/**
	 * @param string $text
	 */
	public function setText($text) {
		$this->text = $text;
	}

	public function isActive() : BOOL {
		return $this->active === true;
	}

	public function setActive(bool $value) {
		$this->active = $value;
	}

	public function addViewer(Player $player) {
		if(!$this->isViewer($player))
			$this->viewers->attach($player);
	}

	public function isViewer(Player $player) {
		return $this->viewers->contains($player);
	}

	public function removeViewer(Player $player) {
		$this->viewers->detach($player);
	}

}