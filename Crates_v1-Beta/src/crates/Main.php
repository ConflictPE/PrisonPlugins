<?php
/**
 * Main class
 *
 * Created on Mar 16, 2016 at 9:33:25 PM
 *
 * @author Jack
 */

namespace crates;

use crates\command\GiveKeyCommand;
use crates\crate\Crate;
use crates\crate\CrateManager;
use pocketmine\math\Vector3;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as TF;

class Main extends PluginBase {

	/**
	 * Folder in which the player data is stored
	 */
	const DATA_FOLDER = "players" . DIRECTORY_SEPARATOR;
	/**
	 * Data extension that player data is stored in
	 */
	const DATA_EXTENSION = ".yml";
	public $economy;
	public $settings = [];
	public $crateData = [];
	/** @var Crate[] */
	public $crates = [];
	public $text = [];
	private $crateManager;
	public $openCrates = [];

	public static function applyColors($string, $symbol = "&") {
		$string = str_replace($symbol . "0", TF::BLACK, $string);
		$string = str_replace($symbol . "1", TF::DARK_BLUE, $string);
		$string = str_replace($symbol . "2", TF::DARK_GREEN, $string);
		$string = str_replace($symbol . "3", TF::DARK_AQUA, $string);
		$string = str_replace($symbol . "4", TF::DARK_RED, $string);
		$string = str_replace($symbol . "5", TF::DARK_PURPLE, $string);
		$string = str_replace($symbol . "6", TF::GOLD, $string);
		$string = str_replace($symbol . "7", TF::GRAY, $string);
		$string = str_replace($symbol . "8", TF::DARK_GRAY, $string);
		$string = str_replace($symbol . "9", TF::BLUE, $string);
		$string = str_replace($symbol . "a", TF::GREEN, $string);
		$string = str_replace($symbol . "b", TF::AQUA, $string);
		$string = str_replace($symbol . "c", TF::RED, $string);
		$string = str_replace($symbol . "d", TF::LIGHT_PURPLE, $string);
		$string = str_replace($symbol . "e", TF::YELLOW, $string);
		$string = str_replace($symbol . "f", TF::WHITE, $string);
		$string = str_replace($symbol . "k", TF::OBFUSCATED, $string);
		$string = str_replace($symbol . "l", TF::BOLD, $string);
		$string = str_replace($symbol . "m", TF::STRIKETHROUGH, $string);
		$string = str_replace($symbol . "n", TF::UNDERLINE, $string);
		$string = str_replace($symbol . "o", TF::ITALIC, $string);
		$string = str_replace($symbol . "r", TF::RESET, $string);
		return $string;
	}

	public static function itemArray2StringTags(array $array) {
		$stringTags = [];
		foreach($array as $data) {
			$stringTags[count($stringTags)] = NBT::putItemHelper($data);
		}
		return $stringTags;
	}

	public static function intArray2ShortTags(array $array) {
		$shortTags = [];
		foreach($array as $data) {
			$shortTags[] = new ShortTag(count($shortTags), $data);
		}
		return $shortTags;
	}

	public function onEnable() {
		if(!is_dir($this->getDataFolder() . self::DATA_FOLDER)) {
			@mkdir($this->getDataFolder() . self::DATA_FOLDER);
		}
		$this->loadConfigs();
		new EventListener($this);
		$this->getCommand("givekey")->setExecutor(new GiveKeyCommand($this));
		$this->setCrateManager();
		$this->loadEconomy();
	}

	public function loadConfigs() {
		$this->saveResource("Settings.yml");
		$this->settings = (new Config($this->getDataFolder() . "Settings.yml", Config::YAML))->getAll();
		$this->saveResource("Crates.json");
		$this->crateData = (new Config($this->getDataFolder() . "Crates.json", Config::JSON))->getAll();
	}

	public function loadEconomy() {
		if(($plugin = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI")) instanceof Plugin) {
			$this->economy = $plugin;
		}
	}

	public function onDisable() {
		$this->crateManager->close();
		unset($this->crateManager);
	}

	public function getEconomy() {
		return $this->economy;
	}

	public function getCrateManager() {
		return $this->crateManager;
	}

	public function setCrateManager() {
		if(!$this->crateManager instanceof CrateManager) {
			$this->crateManager = new CrateManager($this);
		}
		return;
	}

	public function giveKey(Player $player, $id, $amount = 1) {
		$this->saveKeyData($player->getName(), [$id => (isset($this->getKeyData($player->getName())[$id]) ? $this->getKeyData($player->getName())[$id] : 0) + $amount,]);
	}

	/**
	 * Saves a players keys config
	 *
	 * @param string $player
	 * @param        $args
	 */
	public function saveKeyData($player, $args) {
		$config = new Config($this->getDataFolder() . self::DATA_FOLDER . strtolower($player) . self::DATA_EXTENSION, Config::YAML);
		foreach($args as $key => $data) {
			$config->set($key, $data);
		}
		$config->save();
	}

	/**
	 * Get a players key data
	 *
	 * @param string $player
	 *
	 * @return array
	 */
	public function getKeyData($player) {
		return (new Config($this->getDataFolder() . self::DATA_FOLDER . strtolower($player) . self::DATA_EXTENSION, Config::YAML))->getAll();
	}

	/**
	 * Creates a keys data file for a player
	 *
	 * @param string $player
	 */
	public function makeKeyData($player) {
		$config = new Config($this->getDataFolder() . self::DATA_FOLDER . strtolower($player) . self::DATA_EXTENSION, Config::YAML);
		foreach($this->settings as $key => $name) {
			$config->set($key, 0);
		}
		$config->save();
	}

	public function getCrate(Vector3 $pos) {
		foreach($this->crates as $crate) {
			foreach($crate->getLocations() as $loc) {
				if($loc->equals($pos))
					return $crate;
			}
		}
	}

}