<?php

declare(strict_types=1);

namespace kostamax27\VanillaLootTables\entry\function\types;

use kostamax27\VanillaLootTables\condition\LootCondition;
use kostamax27\VanillaLootTables\entry\function\EntryFunction;
use kostamax27\VanillaLootTables\LootContext;
use pocketmine\item\Item;
use pocketmine\item\Potion;
use pocketmine\item\PotionType;
use pocketmine\item\SplashPotion;
use function strtolower;

class SetPotionTypeFunction extends EntryFunction{
	/**
	 * @param LootCondition[] $conditions
	 */
	public function __construct(protected PotionType $potionType, array $conditions = []){
		parent::__construct($conditions);
	}

	public function onCreation(LootContext $context, Item $item) : Item{
		if($item instanceof Potion || $item instanceof SplashPotion){
			$item->setType($this->potionType);
		}
		return parent::onCreation($context, $item);
	}

	/**
	 * Returns an array of properties that can be serialized to json.
	 *
	 * @phpstan-return array{
	 *    function: string,
	 *    id: string,
	 *    conditions?: array<array{condition: string, ...}>
	 * }
	 */
	public function jsonSerialize() : array{
		$data = parent::jsonSerialize();

		$data["id"] = strtolower($this->potionType->name);

		return $data;
	}
}
