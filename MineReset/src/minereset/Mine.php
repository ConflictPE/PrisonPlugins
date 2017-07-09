<?php
namespace minereset;

use pocketmine\level\Level;
use pocketmine\math\Vector3;

class Mine{

	public $a, $b, $lev, $data;

	/** @var MineReset */
	private $base;

	/* Unique identifier for the mine */
	private $id = null;

	/** @var bool */
	private $resetting = false;

	public function __construct(MineReset $base, Vector3 $a, Vector3 $b, Level $level, $id, array $data = []){
		$this->id = $id;
		$this->a = $a;
		$this->b = $b;
		$this->base = $base;
		$this->data = $data;
		$this->level = $level;
	}

	public function isMineSet(){
		return (count($this->data) != 0);
	}

	public function getId(){
		return $this->id;
	}

	public function setData(array $arr){
		$this->data = $arr;
	}

	public function getA(){
		return $this->a;
	}

	public function getB(){
		return $this->b;
	}

	public function getLevel(){
		return $this->level;
	}

	public function getData(){
		return $this->data;
	}

	public function setResetting($value = true){
		$this->resetting = $value;
	}

	public function isResetting(){
		return $this->resetting;
	}

	public function resetMine(){
		if(!$this->isResetting()){
			$chunks = [];
			for($x = $this->getA()->getX(); $x - 16 <= $this->getB()->getX(); $x += 16){
				for($z = $this->getA()->getZ(); $z - 16 <= $this->getB()->getZ(); $z += 16){
					$chunk = $this->level->getChunk($x >> 4, $z >> 4, true);
					$chunkClass = get_class($chunk);
					$chunks[Level::chunkHash($x >> 4, $z >> 4)] = $chunk->toFastBinary();
				}
			}

			$resetTask = new MineResetTask($chunks, $this->a, $this->b, $this->id, $this->data, $this->getLevel()->getId(), $this->base->getRegionBlocker()->blockZone($this->a, $this->b, $this->level), $chunkClass);
			$this->base->scheduleReset($this, $resetTask);
		}
	}
}
