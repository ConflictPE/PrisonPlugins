<?php
namespace minereset;

use pocketmine\level\format\Chunk;
use pocketmine\level\format\FullChunk;
use pocketmine\level\format\LevelProvider;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class MineResetTask extends AsyncTask{

	private $chunks;
	private $a;
	private $b;
	private $mineId;
	private $ratioData;
	private $regionId;
	private $levelId;

	/** @var Chunk */
	private $chunkClass;

	public function __construct(array $chunks, Vector3 $a, Vector3 $b, $mineId, array $data, $levelId, $regionId, $chunkClass){
		$this->chunks = serialize($chunks);
		$this->a = $a;
		$this->b = $b;
		$this->mineId = $mineId;
		$this->ratioData = serialize($data);
		$this->levelId = $levelId;
		$this->regionId = $regionId;
		$this->chunkClass = $chunkClass;
	}

	/**
	 * Actions to execute when run
	 *
	 * @return void
	 */
	public function onRun(){
		$chunkClass = $this->chunkClass;
		/** @var Chunk[] $chunks */
		$chunks = unserialize($this->chunks);
		foreach($chunks as $hash => $binary){
			$chunks[$hash] = $chunkClass::fromFastBinary($binary);
		}
		$sum = [];
		$id = array_keys(unserialize($this->ratioData));
		for($i = 0; $i < count($id); $i++){
			$blockId = explode(":", $id[$i]);
			if(!isset($blockId[1])){
				$blockId[1] = 0;
			}
			$id[$i] = $blockId;
		}
		$m = array_values(unserialize($this->ratioData));
		$sum[0] = $m[0];
		for ($l = 1; $l < count($m); $l++)
			$sum[$l] = $sum[$l - 1] + $m[$l];

		$totalBlocks = ($this->b->x - $this->a->x + 1)*($this->b->y - $this->a->y + 1)*($this->b->z - $this->a->z + 1);
		$interval = $totalBlocks / 8; //TODO determine the interval programmatically
		$lastUpdate = 0;
		$currentBlocks = 0;

		for ($x = $this->a->getX(); $x <= $this->b->getX(); $x++) {
			for ($y = $this->a->getY(); $y <= $this->b->getY(); $y++) {
				for ($z = $this->a->getZ(); $z <= $this->b->getZ(); $z++) {
					$a = rand(0, end($sum));
					for ($l = 0; $l < count($sum); $l++) {
						if ($a <= $sum[$l]) {
							$hash = Level::chunkHash($x >> 4, $z >> 4);
							if(isset($chunks[$hash])){
								$chunks[$hash]->setBlock($x & 0x0f, $y & 0x7f, $z & 0x0f, $id[$l][0] & 0xff, $id[$l][1] & 0xff);
								$currentBlocks++;
							}
							$l = count($sum);
						}
					}
				}
			}
		}
		$this->setResult($chunks);
	}

	public function onCompletion(Server $server){
		$chunks = $this->getResult();
		$plugin = $server->getPluginManager()->getPlugin("MineReset");
		if($plugin instanceof MineReset and $plugin->isEnabled()){
			$level = $server->getLevel($this->levelId);
			if($level instanceof Level){
				/** @var FullChunk $chunk */
				foreach($chunks as $hash => $chunk){
					$plugin->getLogger()->debug($this->mineId . " reset at {$chunk->getX()} {$chunk->getZ()}!");
					Level::getXZ($hash, $x, $z);
					$level->setChunk($x, $z, $chunk, true, true);
					//$h = Level::chunkHash($x, $z);
					//foreach($level->getPlayers() as $p) {
					//	if(isset($p->usedChunks[$h])) {
					//		$level->requestChunk($x, $z, $p);
					//		$plugin->getLogger()->debug("requested chunk for " . $p->getName() . "!");
					//	}
					//}
					//$level->chunkCacheClear($x, $z);
				}
			}
			$plugin->getRegionBlocker()->freeZone($this->regionId, $this->levelId);
			$plugin->getMineById($this->mineId)->setResetting(false);
		}
	}
}
