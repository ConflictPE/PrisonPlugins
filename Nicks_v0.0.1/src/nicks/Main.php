<?php

namespace nicks;

use nicks\command\NickCommand;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as TF;

class Main extends PluginBase {

	const DATA_FILE = "data.yml";
	/** @var Config */
	protected $data;

	/* Resource files */
	/** @var NickCommand */
	private $command;

	/**
	 * Apply minecraft color codes to a string from our custom ones
	 *
	 * @param string $string
	 * @param string $symbol
	 *
	 * @return mixed
	 */
	public static function translateColors($string, $symbol = "&") {
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

	/**
	 * Replace minecraft color codes to our custom ones
	 *
	 * @param        $string
	 * @param string $symbol
	 *
	 * @return mixed
	 */
	public static function untranslateColors($string, $symbol = "&") {
		$string = str_replace(TF::BLACK, $symbol . "0", $string);
		$string = str_replace(TF::DARK_BLUE, $symbol . "1", $string);
		$string = str_replace(TF::DARK_GREEN, $symbol . "2", $string);
		$string = str_replace(TF::DARK_AQUA, $symbol . "3", $string);
		$string = str_replace(TF::DARK_RED, $symbol . "4", $string);
		$string = str_replace(TF::DARK_PURPLE, $symbol . "5", $string);
		$string = str_replace(TF::GOLD, $symbol . "6", $string);
		$string = str_replace(TF::GRAY, $symbol . "7", $string);
		$string = str_replace(TF::DARK_GRAY, $symbol . "8", $string);
		$string = str_replace(TF::BLUE, $symbol . "9", $string);
		$string = str_replace(TF::GREEN, $symbol . "a", $string);
		$string = str_replace(TF::AQUA, $symbol . "b", $string);
		$string = str_replace(TF::RED, $symbol . "c", $string);
		$string = str_replace(TF::LIGHT_PURPLE, $symbol . "d", $string);
		$string = str_replace(TF::YELLOW, $symbol . "e", $string);
		$string = str_replace(TF::WHITE, $symbol . "f", $string);
		$string = str_replace(TF::OBFUSCATED, $symbol . "k", $string);
		$string = str_replace(TF::BOLD, $symbol . "l", $string);
		$string = str_replace(TF::STRIKETHROUGH, $symbol . "m", $string);
		$string = str_replace(TF::UNDERLINE, $symbol . "n", $string);
		$string = str_replace(TF::ITALIC, $symbol . "o", $string);
		$string = str_replace(TF::RESET, $symbol . "r", $string);
		return $string;
	}

	/**
	 * Removes all coloring and color codes from a string
	 *
	 * @param $string
	 *
	 * @return mixed
	 */
	public static function cleanString($string) {
		$string = self::translateColors($string);
		$string = TF::clean($string);
		return $string;
	}

	public function onEnable() {
		$this->loadConfigs();
		$this->command = new NickCommand($this);
	}

	public function loadConfigs() {
		$this->saveResource(self::DATA_FILE);
		$this->data = new Config($this->getDataFolder() . self::DATA_FILE, Config::YAML);
	}

	/**
	 * Get a players nick
	 *
	 * @param $player
	 *
	 * @return string
	 */
	public function getNick($player) {
		if($player instanceof Player)
			$player = $player->getName();
		$player = strtolower($player);
		if($this->data->exists($player)) {
			return self::translateColors($this->data->get($player, ""));
		} else {
			return "";
		}
	}

	/**
	 * Set a players nick
	 *
	 * @param $player
	 * @param $nick
	 */
	public function setNick($player, $nick) {
		if($player instanceof Player)
			$player = $player->getName();
		$player = strtolower($player);
		$this->data->set($player, self::untranslateColors($nick));
		$this->data->save(true);
	}

	/**
	 * Delete a players nick
	 *
	 * @param $player
	 */
	public function removeNick($player) {
		if($player instanceof Player)
			$player = $player->getName();
		$player = strtolower($player);
		$this->data->remove($player);
		$this->data->save(true);
	}

}