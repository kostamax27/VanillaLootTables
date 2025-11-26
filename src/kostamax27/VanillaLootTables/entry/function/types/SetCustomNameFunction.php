<?php

declare(strict_types=1);

namespace kostamax27\VanillaLootTables\entry\function\types;

use kostamax27\VanillaLootTables\condition\LootCondition;
use kostamax27\VanillaLootTables\entry\function\EntryFunction;
use kostamax27\VanillaLootTables\LootContext;
use pocketmine\item\Item;

class SetCustomNameFunction extends EntryFunction{
	/**
	 * @param LootCondition[] $conditions
	 */
	public function __construct(private string $name, array $conditions = []){
		parent::__construct($conditions);
	}

	public function onCreation(LootContext $context, Item $item) : Item{
		$item->setCustomName($this->name);
		return parent::onCreation($context, $item);
	}

	/**
	 * Returns an array of properties that can be serialized to json.
	 *
	 * @phpstan-return array{
	 *    function: string,
	 *    name: string,
	 *    conditions?: array<array{condition: string, ...}>
	 * }
	 */
	public function jsonSerialize() : array{
		$data = parent::jsonSerialize();

		$data["name"] = $this->name;

		return $data;
	}
}
