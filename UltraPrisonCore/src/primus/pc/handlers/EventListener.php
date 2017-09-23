<?php

namespace primus\pc\handlers;

use _64FF00\PurePerms\PPGroup;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\plugin\Plugin;
use primus\pc\utils\HUD;

class EventListener implements Listener {

	private $owner;

	public function __construct(Plugin $plugin) {
		$this->owner = $plugin;
	}

	public function onSignCreate(SignChangeEvent $event) {
		$b = $event->getBlock();
		$p = $event->getPlayer();
		if(strtolower($event->getLine(0)) === 'prison') {
			if($event->getPlayer()->hasPermission('pc.sign.create')) {
				$inf = $event->getLine(1);
				$hash = $this->blockHash($b);
				switch($inf) {
					case '__rankup':
						$format = $this->getFormat('rankup');
						$event->setLine(0, $format[0]);
						$event->setLine(1, $format[1]);
						$event->setLine(2, $format[2]);
						$event->setLine(3, $format[3]);
						$this->owner->signs->setNested($hash, ['type' => 'rankup']);
						$this->owner->signs->save();
						$p->sendMessage($this->owner->prefix . $this->owner->messages->getMessage('pc.sign.create.success'));
						return;
						break;
					default:
						$group = $this->owner->getGroupManager()->getGroup($inf);
						if($group instanceof PPGroup) {
							$cost = $this->owner->getEconomy()->formatMoney($this->owner->getGroupManager()->getPrice($group));
							$format = $this->getFormat('rankshop', $group->getName(), $cost);
							$event->setLine(0, $format[0]);
							$event->setLine(1, $format[1]);
							$event->setLine(2, $format[2]);
							$event->setLine(3, $format[3]);
							$this->owner->signs->setNested($hash, ['group' => $group->getName(), 'type' => 'shop']);
							$this->owner->signs->save();
							$p->sendMessage($this->owner->prefix . $this->owner->messages->getMessage('pc.sign.create.success'));
						} else {
							$p->sendMessage($this->owner->prefix . $this->owner->messages->getMessage('pc.sign.create.groupNotExist', $inf));
							return;
						}
				}
			} else {
				$p->sendMessage($this->owner->prefix . $this->owner->messages->getMessage('pc.sign.create.noPermission'));
				return;
			}
		}
	}

	public function blockHash($b) {
		return $b->getFloorX() . ":" . $b->getFloorY() . ":" . $b->getFloorZ() . ":" . $b->getLevel()->getName();
	}

	public function getFormat($node, $group = 'Undefined', $price = 0) {
		$format = $this->owner->signFormat->get($node);
		if(!is_array($format))
			return ['ERROR', 'ERROR', 'ERROR', 'ERROR'];
		$c = 0;
		$result = [];
		foreach($format as $line) {
			$e = str_replace(['%price%', '%group%', '%prefix%',], [$price, $group, $this->owner->prefix], $line);
			$result[$c] = $e;
			$c++;
		}
		return $result;
	}

	public function onBlockBreak(BlockBreakEvent $event) {
		$block = $event->getBlock();
		if(!$block->getId() === 63 or !$block->getId() === 68)
			return;
		if($this->owner->signs->exists($this->blockHash($block))) {
			if($event->getPlayer()->hasPermission('pc.sign.destroy')) {
				$this->owner->signs->remove($this->blockHash($block));
				$event->getPlayer()->sendMessage($this->owner->prefix . $this->owner->messages->getMessage('pc.sign.destroy.success'));
				return;
			} else {
				$event->setCancelled(true);
				$event->getPlayer()->sendMessage($this->owner->prefix . $this->owner->messages->getMessage('pc.sign.destroy.noPermission'));
				return;
			}
		}
	}

	public function onPlayerInteractEvent(PlayerInteractEvent $event) {
		$p = $event->getPlayer();
		$b = $event->getBlock();
		if(!$b->getId() === 63 or !$b->getId() === 68)
			return;
		$hash = $this->blockHash($b);
		if($this->owner->signs->exists($hash)) {
			if($p->hasPermission('pc.sign.use')) {
				$sign = $this->owner->signs->get($hash);
				$type = $sign['type'];
				$playerGroup = $this->owner->getGroup($p);
				if($type === 'rankup') {
					$rank = $this->owner->getGroupManager()->getNextGroup($playerGroup);
					if($rank) {
						$price = $this->owner->getGroupManager()->getPrice($rank);
						if($this->owner->getEconomy()->getMoney($p) >= $price) {
							$nextRank = $this->owner->rankUp($p);
							$p->sendMessage($this->owner->prefix . $this->owner->messages->getMessage('pc.sign.use.rankedUp', $nextRank->getName()));
							if($this->owner->getConfig()->get('broadcastMessageOnRankUp'))
								$this->owner->getServer()->broadcastMessage($this->owner->messages->getMessage('pc.command.rankup.rankedUpBroadcast', $p->getName(), $nextRank->getName()));
							return;
						} else {
							$p->sendMessage($this->owner->prefix . $this->owner->messages->getMessage('pc.sign.use.rankup.notEnoughMoney', $this->owner->getEconomy()->formatMoney($price)));
							return;
						}
					} else {
						$p->sendMessage($this->owner->prefix . $this->owner->messages->getMessage('pc.sign.use.rankup.topRank'));
						return;
					}
				}
				if($type === 'shop') {
					$groupInf = (isset($sign['group'])) ? $sign['group'] : '';
					$group = $this->owner->getGroupManager()->getGroup($groupInf);
					if($group == \null) {
						$p->sendMessage($this->owner->prefix . $this->owner->messages->getMessage('pc.sign.use.shop.groupNotExist', $groupInf));
						return;
					}
					$price = $this->owner->getGroupManager()->getPrice($group);
					if($this->owner->getEconomy()->getMoney($p) >= $price) {
						if($this->owner->getGroup($p)->getName() == $group->getName()) {
							$p->sendMessage($this->owner->prefix . $this->owner->messages->getMessage('pc.sign.use.shop.sameGroup'));
							return;
						}
						if($this->owner->setGroup($p, $group, \null)) {
							$this->owner->getEconomy()->takeMoney($p, $price, \false);
							$p->sendMessage($this->owner->prefix . $this->owner->messages->getMessage('pc.sign.use.shop.boughtRank', $group->getName(), ($price > 0 ? $this->owner->getEconomy()->formatMoney($price) : 'free')));
						} else {
							$p->sendMessage($this->owner->prefix . $this->owner->messages->getMessage('pc.sign.use.shop.failed', $group->getName()));
							return;
						}
					} else {
						$p->sendMessage($this->owner->prefix . $this->owner->messages->getMessage('pc.sign.use.shop.notEnoughMoney'));
					}
				}
			} else {
				$p->sendMessage($this->owner->prefix . $this->owner->messages->getMessage('pc.sign.use.noPermission'));
			}
		}
	}

	public function onJoin(PlayerJoinEvent $event) {
		HUD::get()->addViewer($event->getPlayer());
	}
}