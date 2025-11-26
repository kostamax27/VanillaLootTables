<?php

declare(strict_types=1);

namespace kostamax27\VanillaLootTables\entry\function\types;

use kostamax27\VanillaLootTables\entry\function\EntryFunction;
use kostamax27\VanillaLootTables\LootContext;
use pocketmine\block\utils\DyeColor;
use pocketmine\item\Armor;
use pocketmine\item\Item;
use pocketmine\item\VanillaArmorMaterials;
use function array_values;
use function count;

class RandomDyeFunction extends EntryFunction{
	public function onCreation(LootContext $context, Item $item) : Item{
		if($item instanceof Armor && $item->getMaterial() === VanillaArmorMaterials::LEATHER()){
			$colors = array_values(DyeColor::getAll());
			$item->setCustomColor(($colors[$context->getRandom()->nextBoundedInt(count($colors))])->getRgbValue());
		}
		return parent::onCreation($context, $item);
	}
}
