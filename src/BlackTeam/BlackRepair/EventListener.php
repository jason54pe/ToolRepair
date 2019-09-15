<?php

declare(strict_types = 1);

namespace BlackTeam\BlackRepair;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\math\Vector3;
use pocketmine\tile\Sign;
use pocketmine\utils\TextFormat;

class EventListener implements Listener{
	
	private $plugin;
	
	public function __construct(Main $plugin){
		$this->plugin = $plugin;
	}

	public function onSignTap(PlayerInteractEvent $event): void{
		$tile = $event->getBlock()->getLevel()->getTile(new Vector3($event->getBlock()->getFloorX(), $event->getBlock()->getFloorY(), $event->getBlock()->getFloorZ()));
		if($tile instanceof Sign){
			if(TextFormat::clean($tile->getText()[0], true) === "[Repair]"){
				if($this->plugin->getConfig()->get("enableRepairSigns") == true){
					$event->setCancelled(true);
					if(!$event->getPlayer()->hasPermission("repair.sign.use")){
						$event->getPlayer()->sendMessage(TextFormat::RED . "[Erreur]" . TextFormat::DARK_RED . " Vous n'êtes pas autorisé à utiliser ce signe.");
						return;
					}
					if(($tile->getText()[1]) === "Hand"){
						if(!$event->getPlayer()->hasPermission("repair.sign.use.hand")){
							$event->getPlayer()->sendMessage(TextFormat::RED . "[Erreur]" . TextFormat::DARK_RED . " Vous n'avez pas la permission d'utiliser ce signe.");
							return;
						}
						$index = $event->getPlayer()->getInventory()->getHeldItemIndex();
						$item = $event->getPlayer()->getInventory()->getItem($index);
						if($this->plugin->isRepairable($item)){
							if($item->getDamage() > 0){
								$event->getPlayer()->getInventory()->setItem($index, $item->setDamage(0));
								$event->getPlayer()->sendMessage(TextFormat::GREEN . "Item successfully repaired.");
							}else{
								$event->getPlayer()->sendMessage(TextFormat::RED . "[Errer]" . TextFormat::DARK_RED . " L'article n'a aucun dommage.");
							}
						}else{
							$event->getPlayer()->sendMessage(TextFormat::RED . "[Erreur]" . TextFormat::DARK_RED . " Cet article ne peut pas être réparé.");
						}
					}
					if(($tile->getText()[1]) === "All"){
						if(!$event->getPlayer()->hasPermission("repair.sign.use.all")){
							$event->getPlayer()->sendMessage(TextFormat::RED . "[Erreur]" . TextFormat::DARK_RED . " Vous n'avez pas la permission d'utiliser ce signe.");
							return;
						}
						foreach($event->getPlayer()->getInventory()->getContents() as $index => $item){
							if($this->plugin->isRepairable($item)){
								if($item->getDamage() > 0){
									$event->getPlayer()->getInventory()->setItem($index, $item->setDamage(0));
								}
							}
						}
						foreach($event->getPlayer()->getArmorInventory()->getContents() as $index => $item){
							if($this->plugin->isRepairable($item)){
								if($item->getDamage() > 0){
									$event->getPlayer()->getArmorInventory()->setItem($index, $item->setDamage(0));
								}
							}
						}
						$event->getPlayer()->sendMessage(TextFormat::GREEN . "Tous les outils de votre inventaire ont été réparés (y compris l'armure équipée)");
					}
				}else{
					$event->getPlayer()->sendMessage(TextFormat::RED . "[Erreur]" . TextFormat::DARK_RED . " Les panneaux de réparation sont actuellement désactivés.");
				}
			}
		}
	}
	
	/**
	 * @param BlockPlaceEvent $event
	 * @priority HIGH
	 */
	public function onBlockPlace(BlockPlaceEvent $event): void{
		$tile = $event->getBlock()->getLevel()->getTile(new Vector3($event->getBlock()->getFloorX(), $event->getBlock()->getFloorY(), $event->getBlock()->getFloorZ()));
		if($tile instanceof Sign){
			if(TextFormat::clean($tile->getText()[0], true) === "[Repair]"){
				if(!$event->getPlayer()->hasPermission("repair.sign.place")){
					$event->getPlayer()->sendMessage(TextFormat::RED . "[Erreur]" . TextFormat::DARK_RED . " Vous n'êtes pas autorisé à placer des panneaux de réparation.");
					return;
				}
				if($this->plugin->getConfig()->get("enableRepairSigns") == true){
					$event->setCancelled(true);
					$event->getPlayer()->sendMessage(TextFormat::RED . "Vous n'avez pas la permission de briser ce signe.");
				}else{
					$event->getPlayer()->sendMessage(TextFormat::RED . "[Erreur]" . TextFormat::DARK_RED . " Les panneaux de réparation sont actuellement désactivés.");
				}
			}
		}
	}
	
	/**
	 * @param BlockBreakEvent $event
	 * @priority HIGH
	 */
	public function onBlockBreak(BlockBreakEvent $event): void{
		$tile = $event->getBlock()->getLevel()->getTile(new Vector3($event->getBlock()->getFloorX(), $event->getBlock()->getFloorY(), $event->getBlock()->getFloorZ()));
		if($tile instanceof Sign){
			if(TextFormat::clean($tile->getText()[0], true) === "[Repair]"){
				if(!$event->getPlayer()->hasPermission("repair.sign.break")){
					$event->getPlayer()->sendMessage(TextFormat::RED . "[Erreur]" . TextFormat::DARK_RED . " Vous n'êtes pas autorisé à casser les panneaux de réparation.");
					return;
				}
				if($this->plugin->getConfig()->get("enableRepairSigns") == true){
					$event->getPlayer()->sendMessage(TextFormat::GREEN . "Signe de réparation brisé avec succès.");
				}else{
					$event->setCancelled(true);
					$event->getPlayer()->sendMessage(TextFormat::RED . "[Erreur]" . TextFormat::DARK_RED . " Les panneaux de réparation sont actuellement désactivés.");
				}
			}
		}
	}
	
	/**
	 * @param SignChangeEvent $event
	 */
	public function onSignChange(SignChangeEvent $event): void{
		if(strtolower(TextFormat::clean($event->getLine(0), true)) == "[repair]"){
			if(!$event->getPlayer()->hasPermission("repair.sign.create")){
				$event->getPlayer()->sendMessage(TextFormat::RED . "[Error]" . TextFormat::DARK_RED . " You don't have permission to create repair signs.");
				return;
			}
			if($this->plugin->getConfig()->get("enableRepairSigns") == true){
				switch(strtolower($event->getLine(1))){
					case "hand":
						$event->setLine(1, "Hand");
						break;
					case "all":
						$event->setLine(1, "All");
						break;
					default:
						$event->setCancelled(true);
				}
				$event->getPlayer()->sendMessage(TextFormat::GREEN . "Repair sign successfully created!");
				$event->setLine(0, TextFormat::AQUA . "[Repair]");
			}else{
				$event->getPlayer()->sendMessage(TextFormat::RED . "[Error]" . TextFormat::DARK_RED . " Repair signs are currently disabled.");
			}
		}
	}
}
