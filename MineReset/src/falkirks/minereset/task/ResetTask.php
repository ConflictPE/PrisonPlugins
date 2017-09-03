<?php
namespace falkirks\minereset\task;

use falkirks\minereset\MineReset;
use pocketmine\level\format\Chunk;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\level\format\FullChunk;

class ResetTask extends AsyncTask{
    /** @var  string */
    private $name;
    /** @var string $chunks */
    private $chunks;
    /** @var Vector3 $a */
    private $a;
    /** @var Vector3 $b */
    private $b;
    /** @var string $ratioData */
    private $ratioData;
    /** @var int $levelId */
    private $levelId;
    /** @var Chunk $chunkClass */
    private $chunkClass;

    public function __construct(string $name, array $chunks, Vector3 $a, Vector3 $b, array $data, $levelId, $chunkClass){
        $this->name = $name;
        $this->chunks = serialize($chunks);
        $this->a = $a;
        $this->b = $b;
        $this->ratioData = serialize($data);
        $this->levelId = $levelId;
        $this->chunkClass = $chunkClass;
    }
    /**
     * Actions to execute when run
     *
     * @return void
     */
    public function onRun(){
        $chunkClass = $this->chunkClass;
        /** @var  Chunk[] $chunks */
        $chunks = unserialize($this->chunks);
        $changed = [];
        foreach($chunks as $hash => $binary){
            $chunks[$hash] = $chunkClass::fromFastBinary($binary, null, false);
        }
        // var_dump(count($chunks) . " chunks to reset. mine: {$this->name}");
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

        for ($x = $this->a->getX(); $x <= $this->b->getX(); $x++) {
            for ($y = $this->a->getY(); $y <= $this->b->getY(); $y++) {
                for ($z = $this->a->getZ(); $z <= $this->b->getZ(); $z++) {
                    $a = rand(0, end($sum));
                    for ($l = 0; $l < count($sum); $l++) {
                        if ($a <= $sum[$l]) {
                            $hash = Level::chunkHash($x >> 4, $z >> 4);
                            if(array_key_exists($hash, $chunks)){
                                if($chunks[$hash] === null) {
                                    continue;
                                }
                                $chunks[$hash]->setBlock($x & 0x0f, $y & 0x7f, $z & 0x0f, $id[$l][0] & 0xff, $id[$l][1] & 0xff);
                                $changed[$hash] = clone $chunks[$hash];
                            }
                            $l = count($sum);
                        }
                    }
                }
            }
        }
        // var_dump(count($changed) . " chunks reset. mine: {$this->name}");
        $this->setResult($changed);
    }
    /**
     * @param Server $server
     */
    public function onCompletion(Server $server){
        $plugin = $server->getPluginManager()->getPlugin("MineReset");
        if($plugin instanceof MineReset and $plugin->isEnabled()) {
            $level = $server->getLevel($this->levelId);
            if($level instanceof Level) {
                $chunks = $this->getResult();
                $chunkClass = $this->chunkClass;
                foreach($chunks as $hash => $chunk) {
                    Level::getXZ($hash, $x, $z);
                    $level->setChunk($x, $z, $chunk, false);
                }
            }
            $plugin->getRegionBlockerListener()->clearMine($this->name);
            $plugin->getResetProgressManager()->notifyComplete($this->name);
        }
    }
}