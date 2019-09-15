<?php

declare(strict_types = 1);

namespace BlackTeam\BlackRepair;

use BlackTeam\BlackRepair\Commands\RepairCommand;
use pocketmine\item\Armor;
use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase{
	
	public function onEnable(): void{
		if (!is_dir($this->getDataFolder())){
			mkdir($this->getDataFolder());
		}
		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
		$this->getServer()->getCommandMap()->register("repair", new RepairCommand("repair", $this));
		$this->saveDefaultConfig();
		$this->getLogger()->info("Plugin Enabled.");
	}

	public function isRepairable(Item $item): bool{
		return $item instanceof Tool || $item instanceof Armor;
	}
}
