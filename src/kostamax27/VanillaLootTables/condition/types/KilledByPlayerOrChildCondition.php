<?php

declare(strict_types=1);

namespace kostamax27\VanillaLootTables\condition\types;

use kostamax27\VanillaLootTables\condition\LootCondition;
use kostamax27\VanillaLootTables\LootContext;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\player\Player;

class KilledByPlayerOrChildCondition extends LootCondition{
	public function evaluate(LootContext $context) : bool{
		$origin = $context->getOrigin();
		if($origin instanceof Entity && ($lastDamage = $origin->getLastDamageCause()) instanceof EntityDamageByEntityEvent){
			$damager = $lastDamage->getDamager();
			if($damager !== null){
				return $damager instanceof Player || $damager->getOwningEntity() instanceof Player;
			}
		}
		return false;
	}
}
