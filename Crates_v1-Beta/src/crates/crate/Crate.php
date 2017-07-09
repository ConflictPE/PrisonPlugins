<?php
/**
 * Crate.php class
 *
 * Created on 26/05/2016 at 10:10 PM
 *
 * @author Jack
 */

namespace crates\crate;

use pocketmine\item\Item;
use pocketmine\math\Vector3;

class Crate {

	/** @var string */
	private $name = "";

	/** @var string */
	private $description = "";

	/** @var Vector3[] */
	private $locations;

	/** @var int */
	private $id = 0;

	/** @var Item[] */
	private $items = [];

	/** @var int[] */
	private $money = [];

	/**
	 * Crate constructor
	 *
	 * @param string $name
	 * @param string $description
	 * @param Vector3[] $locations
	 * @param Item[] $items
	 * @param int[] $money
	 */
	public function __construct($name, $description, array $locations, $id, array $items, array $money) {
		$this->name = $name;
		$this->description = $description;
		$this->locations = $locations;
		$this->id = $id;
		$this->items = $items;
		$this->money = $money;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * @return Vector3[]
	 */
	public function getLocations() {
		return $this->locations;
	}

	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return Item[]
	 */
	public function getItems() {
		return $this->items;
	}

	/**
	 * @return Item
	 */
	public function getRandomItem() {
		return $this->items[array_rand($this->items)];
	}

	/**
	 * @return \int[]
	 */
	public function getMoney() {
		return $this->money;
	}

	/**
	 * @return int
	 */
	public function getRandomMoney() {
		return $this->money[array_rand($this->money)];
	}

}