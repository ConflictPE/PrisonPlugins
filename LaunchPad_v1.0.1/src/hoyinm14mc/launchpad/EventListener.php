<?php

/*
 * This file is a part of LaunchPad.
 * Copyright (C) 2015 CyberCube-HK
 *
 * LaunchPad is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * LaunchPad is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with LaunchPad. If not, see <http://www.gnu.org/licenses/>.
 */
namespace hoyinm14mc\launchpad;

use pocketmine\block\Block;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\math\Vector3;
use pocketmine\Player;

class EventListener implements Listener{

	private $plugin;

	public function __construct($plugin){
		$this->plugin = $plugin;
	}

	public function onPlayerMove(PlayerMoveEvent $event){
		$block = $event->getPlayer()->getLevel()->getBlock($event->getPlayer()->floor()->subtract(0, 1));
		$block2 = $event->getPlayer()->getLevel()->getBlock($event->getPlayer()->floor()->subtract(0, 2));
		if($block->getId() !== $this->plugin->getConfig()->get("BLOCK") || $block2->getId() !== $this->plugin->getConfig()->get("BLOCK")){
			return;
		}
		if($event->getPlayer() instanceof Player && $event->getPlayer()->hasPermission("launchpad.use") !== true){
			return;
		}
		if($event->getPlayer() instanceof Player) $event->getPlayer()->sendMessage($this->plugin->colourMessage($this->plugin->getConfig()->get("MESSAGE")));
		$this->plugin->launch($event->getPlayer(), $this->plugin->getConfig()->get("BASE"));
	}

	public function onEntityDamage(EntityDamageEvent $event){
		// Mobs won't be protected
		if($event->getEntity() instanceof Player !== true){
			return;
		}
		if(in_array($event->getEntity()->getName(), $this->plugin->protect)){
			if($event->getCause() == EntityDamageEvent::CAUSE_FALL){
				$event->setCancelled(true);
				unset($this->plugin->protect[$event->getEntity()->getName()]);
			}
		}
	}

	public function onBlockPlace(BlockPlaceEvent $event){
		$block = $event->getBlock();
		$block2 = $event->getPlayer()->getLevel()->getBlock(new Vector3($block->getX(), $block->getY() - 1, $block->getZ()));
		$block3 = $event->getPlayer()->getLevel()->getBlock(new Vector3($block->getX(), $block->getY() + 1, $block->getZ()));
		if($block->getId() !== $this->plugin->getConfig()->get("BLOCK")){
			return;
		}
		if($block2->getId() !== $this->plugin->getConfig()->get("BLOCK") && $block3->getId() !== $this->plugin->getConfig()->get("BLOCK")){
			return;
		}
		// CREATE LAUNCH PAD
		if($event->getPlayer()->hasPermission("launchpad.create") !== true){
			$event->setCancelled(true);
			$event->getPlayer()->sendMessage($this->plugin->colourMessage("&cYou don't have permission for this!"));
		}
		$event->getPlayer()->sendMessage($this->plugin->colourMessage("&eYou created a launch pad!"));
	}

	public function onBlockBreak(BlockBreakEvent $event){
		$block = $event->getBlock();
		$block2 = $event->getPlayer()->getLevel()->getBlock(new Vector3($block->getX(), $block->getY() - 1, $block->getZ()));
		$block3 = $event->getPlayer()->getLevel()->getBlock(new Vector3($block->getX(), $block->getY() + 1, $block->getZ()));
		if($block->getId() !== $this->plugin->getConfig()->get("BLOCK")){
			return;
		}
		if($block2->getId() !== $this->plugin->getConfig()->get("BLOCK") && $block3->getId() !== $this->plugin->getConfig()->get("BLOCK")){
			return;
		}
		// DESTROY LAUNCH PAD
		if($event->getPlayer()->hasPermission("launchpad.destroy") !== true){
			$event->setCancelled(true);
			$event->getPlayer()->sendMessage($this->plugin->colourMessage("&cYou don't have permission for this!"));
		}
		$event->getPlayer()->sendMessage($this->plugin->colourMessage("&eYou destroyed a launch pad!"));
	}

	public function onPlayerInteract(PlayerInteractEvent $event){
		if(in_array($event->getPlayer()->getName(), $this->plugin->create_mode)){
			$event->getPlayer()->getLevel()->setBlock(new Vector3($event->getBlock()->getX(), $event->getBlock()->getY(), $event->getBlock()->getZ()), Block::get($this->plugin->getConfig()->get("BLOCK")));
			$event->getPlayer()->getLevel()->setBlock(new Vector3($event->getBlock()->getX(), $event->getBlock()->getY() - 1, $event->getBlock()->getZ()), Block::get($this->plugin->getConfig()->get("BLOCK")));
			unset($this->plugin->create_mode[$event->getPlayer()->getName()]);
			$event->getPlayer()->sendMessage($this->plugin->colourMessage("&eLaunchPad created!"));
		}
		if(in_array($event->getPlayer()->getName(), $this->plugin->destroy_mode)){
			$block2 = $event->getPlayer()->getLevel()->getBlock(new Vector3($event->getBlock()->getX(), $event->getBlock()->getY() + 1, $event->getBlock()->getZ()));
			$block3 = $event->getPlayer()->getLevel()->getBlock(new Vector3($event->getBlock()->getX(), $event->getBlock()->getY() - 1, $event->getBlock()->getZ()));
			if($event->getBlock()->getId() !== $this->plugin->getConfig()->get("BLOCK")){
				$event->getPlayer()->sendMessage($this->plugin->colourMessage("&cYou are not tapping a LaunchPad!"));
				return;
			}
			if($block2->getId() !== $this->plugin->getConfig()->get("BLOCK") && $block3->getId() !== $this->plugin->getConfig()->get("BLOCK")){
				$event->getPlayer()->sendMessage($this->plugin->colourMessage("&cYou are not tapping a LaunchPad!"));
				return;
			}
			$event->getPlayer()->getLevel()->setBlock(new Vector3($event->getBlock()->getX(), $event->getBlock()->getY(), $event->getBlock()->getZ()), Block::get(0));
			$event->getPlayer()->getLevel()->setBlock(new Vector3($event->getBlock()->getX(), $event->getBlock()->getY() - 1, $event->getBlock()->getZ()), Block::get(0));
			unset($this->plugin->destroy_mode[$event->getPlayer()->getName()]);
			$event->getPlayer()->sendMessage($this->plugin->colourMessage("&eLaunchPad destroyed!"));
		}
	}

	public function onPlayerQuit(PlayerQuitEvent $event){
		if(in_array($event->getPlayer()->getName(), $this->plugin->create_mode)) unset($this->plugin->create_mode[$event->getPlayer()->getName()]);
		if(in_array($event->getPlayer()->getName(), $this->plugin->destroy_mode)) unset($this->plugin->destroy_mode[$event->getPlayer()->getName()]);
	}

}

?>