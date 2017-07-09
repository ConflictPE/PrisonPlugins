<?php

namespace MyPlot;

use MyPlot\provider\DataProvider;
use MyPlot\provider\EconomyProvider;
use MyPlot\provider\EconomySProvider;
use MyPlot\provider\PocketMoneyProvider;
use MyPlot\provider\SQLiteDataProvider;
use MyPlot\task\ClearPlotTask;
use pocketmine\event\level\LevelLoadEvent;
use pocketmine\event\Listener;
use pocketmine\lang\BaseLang;
use pocketmine\level\generator\biome\Biome;
use pocketmine\level\generator\Generator;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\permission\Permission;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;

class MyPlot extends PluginBase implements Listener {

	/** @var MyPlot */
	private static $instance;

	/** @var PlotLevelSettings[] */
	private $levels = [];

	/** @var DataProvider */
	private $dataProvider = null;

	/** @var EconomyProvider */
	private $economyProvider = null;

	/** @var BaseLang */
	private $baseLang = null;

	/**
	 * @api
	 * @return MyPlot
	 */
	public static function getInstance() {
		return self::$instance;
	}

	/**
	 * @api
	 * @return BaseLang
	 */
	public function getLanguage() {
		return $this->baseLang;
	}

	/**
	 * Returns the DataProvider that is being used
	 *
	 * @api
	 * @return DataProvider
	 */
	public function getProvider() {
		return $this->dataProvider;
	}

	/**
	 * Returns the EconomyProvider that is being used
	 *
	 * @api
	 * @return EconomyProvider
	 */
	public function getEconomyProvider() {
		return $this->economyProvider;
	}

	/**
	 * Returns a PlotLevelSettings object which contains all the settings of a level
	 *
	 * @api
	 *
	 * @param string $levelName
	 *
	 * @return PlotLevelSettings|null
	 */
	public function getLevelSettings($levelName) {
		if(isset($this->levels[$levelName])) {
			return $this->levels[$levelName];
		}
		return null;
	}

	/**
	 * Checks if a plot level is loaded
	 *
	 * @api
	 *
	 * @param string $levelName
	 *
	 * @return bool
	 */
	public function isLevelLoaded($levelName) {
		return isset($this->levels[$levelName]);
	}

	/**
	 * Generate a new plot level with optional settings
	 *
	 * @api
	 *
	 * @param string $levelName
	 * @param array $settings
	 *
	 * @return bool
	 */
	public function generateLevel($levelName, $settings = []) {
		if($this->getServer()->isLevelGenerated($levelName) === true) {
			return false;
		}
		if(empty($settings)) {
			$settings = $this->getConfig()->get("DefaultWorld");
		}
		$settings = [
			"preset" => json_encode($settings),
		];
		return $this->getServer()->generateLevel($levelName, null, MyPlotGenerator::class, $settings);
	}

	/**
	 * Saves provided plot if changed
	 *
	 * @api
	 *
	 * @param Plot $plot
	 *
	 * @return bool
	 */
	public function savePlot(Plot $plot) {
		return $this->dataProvider->savePlot($plot);
	}

	/**
	 * Get all the plots a player owns (in a certain level if $levelName is provided)
	 *
	 * @api
	 *
	 * @param string $username
	 * @param string $levelName
	 *
	 * @return Plot[]
	 */
	public function getPlotsOfPlayer($username, $levelName = "") {
		return $this->dataProvider->getPlotsByOwner($username, $levelName);
	}

	/**
	 * Get the next free plot in a level
	 *
	 * @api
	 *
	 * @param string $levelName
	 * @param int $limitXZ
	 *
	 * @return Plot|null
	 */
	public function getNextFreePlot($levelName, $limitXZ = 0) {
		return $this->dataProvider->getNextFreePlot($levelName, $limitXZ);
	}

	/**
	 * Finds the plot at a certain position or null if there is no plot at that position
	 *
	 * @api
	 *
	 * @param Position $position
	 *
	 * @return Plot|null
	 */
	public function getPlotByPosition(Position $position) {
		$x = $position->x;
		$z = $position->z;
		$levelName = $position->level->getName();
		$plotLevel = $this->getLevelSettings($levelName);
		if($plotLevel === null) {
			return null;
		}
		$plotSize = $plotLevel->plotSize;
		$roadWidth = $plotLevel->roadWidth;
		$totalSize = $plotSize + $roadWidth;
		if($x >= 0) {
			$X = floor($x / $totalSize);
			$difX = $x % $totalSize;
		} else {
			$X = ceil(($x - $plotSize + 1) / $totalSize);
			$difX = abs(($x - $plotSize + 1) % $totalSize);
		}
		if($z >= 0) {
			$Z = floor($z / $totalSize);
			$difZ = $z % $totalSize;
		} else {
			$Z = ceil(($z - $plotSize + 1) / $totalSize);
			$difZ = abs(($z - $plotSize + 1) % $totalSize);
		}
		if(($difX > $plotSize - 1) or ($difZ > $plotSize - 1)) {
			return null;
		}
		return $this->dataProvider->getPlot($levelName, $X, $Z);
	}

	/**
	 *  Get the begin position of a plot
	 *
	 * @api
	 *
	 * @param Plot $plot
	 *
	 * @return Position|null
	 */
	public function getPlotPosition(Plot $plot) {
		$plotLevel = $this->getLevelSettings($plot->levelName);
		if($plotLevel === null) {
			return null;
		}
		$plotSize = $plotLevel->plotSize;
		$roadWidth = $plotLevel->roadWidth;
		$totalSize = $plotSize + $roadWidth;
		$x = $totalSize * $plot->X;
		$z = $totalSize * $plot->Z;
		$level = $this->getServer()->getLevelByName($plot->levelName);
		return new Position($x, $plotLevel->groundHeight, $z, $level);
	}

	/**
	 * Teleport a player to a plot
	 *
	 * @api
	 *
	 * @param Player $player
	 * @param Plot $plot
	 *
	 * @return bool
	 */
	public function teleportPlayerToPlot(Player $player, Plot $plot) {
		$plotLevel = $this->getLevelSettings($plot->levelName);
		if($plotLevel === null) {
			return false;
		}
		$pos = $this->getPlotPosition($plot);
		$plotSize = $plotLevel->plotSize;
		$pos->x += floor($plotSize / 2);
		$pos->z -= 1;
		$pos->y += 1;
		$player->teleport($pos);
		return true;
	}

	/**
	 * Reset all the blocks inside a plot
	 *
	 * @api
	 *
	 * @param Plot $plot
	 * @param int $maxBlocksPerTick
	 *
	 * @return bool
	 */
	public function clearPlot(Plot $plot, $maxBlocksPerTick = 256) {
		if(!$this->isLevelLoaded($plot->levelName)) {
			return false;
		}
		$task = new ClearPlotTask($this, $plot, $maxBlocksPerTick);
		$task->onRun(0);
		return true;
	}

	/**
	 * Delete the plot data
	 *
	 * @param Plot $plot
	 *
	 * @return bool
	 */
	public function disposePlot(Plot $plot) {
		return $this->dataProvider->deletePlot($plot);
	}

	/**
	 * Clear and dispose a plot
	 *
	 * @param Plot $plot
	 * @param int $maxBlocksPerTick
	 *
	 * @return bool
	 */
	public function resetPlot(Plot $plot, $maxBlocksPerTick = 256) {
		if($this->disposePlot($plot)) {
			return $this->clearPlot($plot, $maxBlocksPerTick);
		}
		return false;
	}

	/**
	 * Changes the biome of a plot
	 *
	 * @api
	 *
	 * @param Plot $plot
	 * @param Biome $biome
	 *
	 * @return bool
	 */
	public function setPlotBiome(Plot $plot, Biome $biome) {
		$plotLevel = $this->getLevelSettings($plot->levelName);
		if($plotLevel === null) {
			return false;
		}
		$level = $this->getServer()->getLevelByName($plot->levelName);
		$pos = $this->getPlotPosition($plot);
		$plotSize = $plotLevel->plotSize;
		$xMax = $pos->x + $plotSize;
		$zMax = $pos->z + $plotSize;
		$chunkIndexes = [];
		for($x = $pos->x; $x < $xMax; $x++) {
			for($z = $pos->z; $z < $zMax; $z++) {
				$level->setBiomeId($x, $z, $biome->getId());
			}
		}
		foreach($chunkIndexes as $index) {
			Level::getXZ($index, $X, $Z);
			$chunk = $level->getChunk($X, $Z);
			foreach($level->getChunkPlayers($X, $Z) as $player) {
				$player->onChunkChanged($chunk);
			}
		}
		$plot->biome = $biome->getName();
		$this->dataProvider->savePlot($plot);
		return true;
	}

	/**
	 * Returns the PlotLevelSettings of all the loaded levels
	 *
	 * @api
	 * @return string[]
	 */
	public function getPlotLevels() {
		return $this->levels;
	}

	/**
	 * Get the maximum number of plots a player can claim
	 *
	 * @param Player $player
	 *
	 * @return int
	 */
	public function getMaxPlotsOfPlayer(Player $player) {
		if($player->hasPermission("myplot.claimplots.unlimited"))
			return PHP_INT_MAX;
		/** @var Permission[] $perms */
		$perms = array_merge($this->getServer()->getPluginManager()->getDefaultPermissions($player->isOp()), $player->getEffectivePermissions());
		$perms = array_filter($perms, function($name) {
			return (substr($name, 0, 18) === "myplot.claimplots.");
		}, ARRAY_FILTER_USE_KEY);
		if(count($perms) == 0)
			return 0;
		krsort($perms);
		foreach($perms as $name => $perm) {
			$maxPlots = substr($name, 18);
			if(is_numeric($maxPlots)) {
				return $maxPlots;
			}
		}
		return 0;
	}

	/* -------------------------- Non-API part -------------------------- */
	public function onEnable() {
		$this->getLogger()->info("Loading MyPlot");
		self::$instance = $this;
		$this->saveDefaultConfig();
		$this->reloadConfig();
		@mkdir($this->getDataFolder());
		@mkdir($this->getDataFolder() . "worlds");
		Generator::addGenerator(MyPlotGenerator::class, "myplot");
		$lang = $this->getConfig()->get("language", BaseLang::FALLBACK_LANGUAGE);
		$this->baseLang = new BaseLang($lang, $this->getFile() . "resources/");
		// Initialize DataProvider
		$cacheSize = $this->getConfig()->get("PlotCacheSize");
		switch(strtolower($this->getConfig()->get("DataProvider"))) {
			case "sqlite":
			default:
				$this->dataProvider = new SQLiteDataProvider($this, $cacheSize);
				break;
		}
		// Initialize EconomyProvider
		if($this->getConfig()->get("UseEconomy") == true) {
			if($this->getServer()->getPluginManager()->getPlugin("EconomyAPI") !== null) {
				$this->economyProvider = new EconomySProvider();
			} elseif(($plugin = $this->getServer()->getPluginManager()->getPlugin("PocketMoney")) !== null) {
				$this->economyProvider = new PocketMoneyProvider($plugin);
			}
		}
		$eventListener = new EventListener($this);
		$this->getServer()->getPluginManager()->registerEvents($eventListener, $this);
		foreach($this->getServer()->getLevels() as $level) {
			$eventListener->onLevelLoad(new LevelLoadEvent($level));
		}
		$this->getServer()->getCommandMap()->register(Commands::class, new Commands($this));
	}

	public function addLevelSettings($levelName, PlotLevelSettings $settings) {
		$this->levels[$levelName] = $settings;
	}

	public function unloadLevelSettings($levelName) {
		if(isset($this->levels[$levelName])) {
			unset($this->levels[$levelName]);
			return true;
		}
		return false;
	}

	public function onDisable() {
		if($this->dataProvider !== null) {
			$this->dataProvider->close();
		}
	}
}
