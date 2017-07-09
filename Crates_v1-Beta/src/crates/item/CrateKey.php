<?php
/**
 * CrateKey class
 *
 * Created on Mar 20, 2016 at 3:53:45 PM
 *
 * @author Jack
 */

namespace crates\item;

use pocketmine\block\TripwireHook;

class CrateKey extends TripwireHook {

	protected $id = self::TRIPWIRE_HOOK;

	public function __construct($meta = 0) {
		$this->meta = $meta;
	}

	public function getName() : string {
		return "Crate Key";
	}

}
