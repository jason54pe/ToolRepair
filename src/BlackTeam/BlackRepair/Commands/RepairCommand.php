<?php

declare(strict_types = 1);

namespace BlackTeam\BlackRepair\Commands;

use BlackTeam\BlackRepair\Main;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class RepairCommand extends PluginCommand{
	
	private $plugin;

	public function __construct(string $name, Main $plugin){
		parent::__construct($name, $plugin);
		$this->setDescription("Accès aux commandes de réparation.");
		$this->setUsage("/repair [all:hand]");
		$this->setAliases(["fix"]);
		$this->setPermission("repair.command.use");
		$this->plugin = $plugin;
	}

	public function execute(CommandSender $sender, string $alias, array $args): bool{
		if(!$this->testPermission($sender)){
			return true;
		}
		if(!$sender instanceof Player){
			$sender->sendMessage(TextFormat::RED . "[Erreur]" . TextFormat::DARK_RED . " Cette commande ne fonctionne qu'en jeu..");
			return true;
		}
		$a = "hand";
		if(isset($args[0])){
			$a = strtolower($args[0]);
		}
		if(!($a === "hand" || $a === "all")){
			$sender->sendMessage(TextFormat::RED . "Usage:" . TextFormat::DARK_RED . "/repair [all:hand]");
			return true;
		}
		if($a === "all"){
			if(!$sender->hasPermission("repair.command.use.all")){
				$sender->sendMessage(TextFormat::RED . "[Erreur]" . TextFormat::DARK_RED . " Vous n'êtes pas autorisé à utiliser cette commande.");
				return true;
			}
			foreach($sender->getInventory()->getContents() as $index => $item){
				if($this->plugin->isRepairable($item)){
					if($item->getDamage() > 0){
						$sender->getInventory()->setItem($index, $item->setDamage(0));
					}
				}
			}
			$m = TextFormat::GREEN . "Tous les outils de votre inventaire ont été réparés!";
			if($sender->hasPermission("essentials.repair.armor")){
				foreach($sender->getArmorInventory()->getContents() as $index => $item){
					if($this->plugin->isRepairable($item)){
						if($item->getDamage() > 0){
							$sender->getArmorInventory()->setItem($index, $item->setDamage(0));
						}
					}
				}
				$m .= TextFormat::AQUA . " (Y compris l'armure équipée)";
			}
		}else{
			if(!$sender->hasPermission("repair.command.use.hand")){
				$sender->sendMessage(TextFormat::RED . "[Erreur]" . TextFormat::DARK_RED . " Vous n'êtes pas autorisé à utiliser cette commande.");
				return true;
			}
			$index = $sender->getInventory()->getHeldItemIndex();
			$item = $sender->getInventory()->getItem($index);
			if(!$this->plugin->isRepairable($item)){
				$sender->sendMessage(TextFormat::RED . "[Erreur] Cet article ne peut pas être réparé!");
				return true;
			}
			if($item->getDamage() > 0){
				$sender->getInventory()->setItem($index, $item->setDamage(0));
			}else{
				$sender->sendMessage(TextFormat::RED . "[Erreur] L'article n'a aucun dommage");
			}
			$m = TextFormat::GREEN . "Article réparé avec succès!";
		}
		$sender->sendMessage($m);
		return true;
	}
}
