<?php

declare(strict_types=1);

namespace kostamax27\VanillaLootTables\entry\function\types;

use kostamax27\VanillaLootTables\entry\function\EntryFunction;
use kostamax27\VanillaLootTables\LootContext;
use pocketmine\crafting\FurnaceRecipe;
use pocketmine\crafting\FurnaceType;
use pocketmine\item\Item;

class FurnaceSmeltFunction extends EntryFunction{
	public function onCreation(LootContext $context, Item $item) : Item{
		$smelt = $context->getWorld()->getServer()->getCraftingManager()->getFurnaceRecipeManager(FurnaceType::FURNACE())->match($item);
		if($smelt instanceof FurnaceRecipe){
			$item = $smelt->getResult()->setCount($item->getCount());
		}
		return parent::onCreation($context, $item);
	}
}
