<?php

declare(strict_types=1);

namespace kostamax27\VanillaLootTables\pool;

use kostamax27\VanillaLootTables\condition\LootCondition;
use kostamax27\VanillaLootTables\condition\LootConditionHandlingTrait;
use kostamax27\VanillaLootTables\entry\LootEntry;
use kostamax27\VanillaLootTables\LootContext;
use pocketmine\item\Item;
use pocketmine\utils\Utils;

abstract class LootPool{
	use LootConditionHandlingTrait;

	/**
	 * @param LootEntry[] $entries
	 * @param LootCondition[] $conditions
	 *
	 * @phpstan-param list<LootEntry> $entries
	 */
	public function __construct(
		protected array $entries,
		array $conditions = []
	){
		Utils::validateArrayValueType($entries, function(LootEntry $_) : void{});
		Utils::validateArrayValueType($conditions, function(LootCondition $_) : void{});

		$this->conditions = $conditions;
	}

	/**
	 * @return LootEntry[]
	 */
	public function getEntries() : array{
		return $this->entries;
	}

	/**
	 * @return Item[]
	 */
	abstract public function generate(LootContext $context) : array;
}
