<?php

namespace primus\pc;

use _64FF00\PurePerms\PPGroup;
use pocketmine\IPlayer;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use primus\pc\commands\RankUp;
use primus\pc\economy\Economy;
use primus\pc\handlers\EventListener;
use primus\pc\utils\HUD;

class PrisonCore extends PluginBase {

	public $prefix;

	public $signs, $messages, $signFormat;
	/** @var array */
	public $exemptHud = [];
	protected $groupManager, $economy, $pp;

	public function onLoad() {
		$this->getServer()->getLogger()->info('[' . $this->getDescription()->getName() . '] Loading...');
	}

	public function onEnable() {
		@mkdir($this->getDataFolder());
		$this->saveDefaultConfig();
		$this->prefix = $this->getConfig()->get('prefix');
		$this->signFormat = new Config($this->getDataFolder() . 'SignFormat.yml', Config::YAML, yaml_parse(stream_get_contents($resource = $this->getResource("SignFormat.yml"))));
		$this->signs = new Config($this->getDataFolder() . 'signs.yml', Config::YAML);
		@fclose($resource);
		$this->economy = new Economy($this);
		if($this->economy->isLoaded() !== \true) {
			$this->getLogger()->info('Economy - ' . TextFormat::RED . 'You must have one of these plugins: EconomyAPI, PocketMoney, MassiveEconomy, GoldStd');
			$this->setEnabled(\false);
			return;
		}
		$this->pp = $this->getServer()->getPluginManager()->getPlugin('PurePerms');
		if($this->pp instanceof PluginBase) {
			if($this->pp->isEnabled() !== \true) {
				$this->getLogger()->info('Permission Manager - ' . TextFormat::RED . 'Cant load PurePerms due to it\'s disabled');
				$this->setEnabled(\false);
				return;
			} else {
			}
			$this->getLogger()->info('Permission Manager - ' . TextFormat::GREEN . 'PurePerms API Loaded');
		} else {
			$this->getLogger()->info('Permission Manager - ' . TextFormat::RED . 'You must have installed newest version of PurePerms');
			$this->setEnabled(\false);
			return;
		}
		$this->groupManager = new GroupManager($this);
		$this->messages = new PrisonMessages($this);
		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new HUD($this, $this->getConfig()->get("hud-text", ""), $this->getConfig()->get("hud-sub-text", "")), 15);
		$this->getLogger()->info("HUD Enabled!");
		$this->registerCommands();
		$this->getLogger()->info('Enabled.');
	}

	public function registerCommands() {
		$commandMap = $this->getServer()->getCommandMap();
		$commandMap->register("rankup", new RankUp($this, "rankup", "Rankup to next rank"));
		$commandMap->register("hud", new \primus\pc\commands\Hud($this));
	}

	public function onDisable() {
		$this->getLogger()->info('Disabling...');
		if($this->signs->save()) {
			$this->getLogger()->info('Signs saved.');
		}
		$this->getLogger()->info('Disabled.');
	}

	/**
	 *
	 * @return array
	 */
	public function getSigns() {
		return $this->signs;
	}

	/**
	 *
	 * @param IPlayer $player
	 * @param PPGroup $group
	 * @param string|null $levelName
	 */
	public function setGroup(IPlayer $player, PPGroup $group, $levelName = \null) {
		$lastGroup = $this->getPurePerms()->getUserDataMgr()->getGroup($player);
		$this->getPurePerms()->getUserDataMgr()->setGroup($player, $group, $levelName);
		return $lastGroup->getName() !== $this->getPurePerms()->getUserDataMgr()->getGroup($player)->getName();
	}

	public function getPurePerms() {
		return $this->pp;
	}

	/**
	 *
	 * @param PPUser $prisoner
	 */
	public function rankUp(IPlayer $p) {
		$nextGroup = $this->getGroupManager()->getNextGroup($this->getPurePerms()->getUserDataMgr()->getGroup($p, \null));
		if($nextGroup === \false)
			return $nextGroup;
		$this->getEconomy()->takeMoney($p, $this->getGroupManager()->getPrice($nextGroup));
		$this->getPurePerms()->getUserDataMgr()->setGroup($p, $nextGroup, \null);
		return $nextGroup;
	}

	/**
	 * @return GroupManager
	 */
	public function getGroupManager() {
		return $this->groupManager;
	}

	/**
	 *
	 * @return Economy
	 */
	public function getEconomy() {
		return $this->economy;
	}

	/**
	 *
	 * @param IPlayer $player
	 *
	 * @return PPGroup|null
	 */
	public function getGroup(IPlayer $player) {
		return $this->getPurePerms()->getUserDataMgr()->getGroup($player);
	}

}