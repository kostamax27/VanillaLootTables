<?php

declare(strict_types=1);

namespace kostamax27\VanillaLootTables\entry\function\types;

use kostamax27\VanillaLootTables\condition\LootCondition;
use kostamax27\VanillaLootTables\entry\function\EntryFunction;
use kostamax27\VanillaLootTables\LootContext;
use pocketmine\item\Item;
use function array_values;
use function count;

class SetLoreFunction extends EntryFunction{
	/** @var string[] */
	private array $lore;

	/**
	 * @param string[] $lines
	 * @param LootCondition[] $conditions
	 */
	public function __construct(array $lines, array $conditions = []){
		$this->lore = array_values($lines);
		parent::__construct($conditions);
	}

	public function onCreation(LootContext $context, Item $item) : Item{
		$item->setLore($this->lore);
		return parent::onCreation($context, $item);
	}

	/**
	 * Returns an array of properties that can be serialized to json.
	 *
	 * @phpstan-return array{
	 *    function: string,
	 *    lore: string|string[],
	 *    conditions?: array<array{condition: string, ...}>
	 * }
	 */
	public function jsonSerialize() : array{
		$data = parent::jsonSerialize();

		$data["lore"] = count($this->lore) === 1 ? $this->lore[0] : $this->lore;

		return $data;
	}
}
