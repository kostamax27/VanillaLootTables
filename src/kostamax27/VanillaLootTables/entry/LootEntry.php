<?php

declare(strict_types=1);

namespace kostamax27\VanillaLootTables\entry;

use kostamax27\VanillaLootTables\condition\LootCondition;
use kostamax27\VanillaLootTables\condition\LootConditionHandlingTrait;
use kostamax27\VanillaLootTables\entry\function\EntryFunction;
use kostamax27\VanillaLootTables\LootContext;
use kostamax27\VanillaLootTables\LootTable;
use kostamax27\VanillaLootTables\pool\LootPool;
use pocketmine\item\Item;
use pocketmine\utils\Utils;

class LootEntry{
	use LootConditionHandlingTrait;

	/**
	 * @param EntryFunction[] $functions
	 * @param LootCondition[] $conditions
	 * @param LootPool[] $pools
	 */
	public function __construct(
		protected LootEntryType $type,
		protected ItemStackData|LootTable|string|null $entry,
		protected int $weight = 1,
		protected int $quality = 1,
		protected array $functions = [],
		array $conditions = [],
		protected array $pools = []
	){
		if($weight < 1){
			throw new \InvalidArgumentException("Weight must be at least of 1");
		}
		Utils::validateArrayValueType($functions, function(EntryFunction $_) : void{});
		Utils::validateArrayValueType($conditions, function(LootCondition $_) : void{});
		Utils::validateArrayValueType($pools, function(LootPool $_) : void{});

		$this->conditions = $conditions;
	}

	public function getType() : LootEntryType{
		return $this->type;
	}

	public function getEntry() : ItemStackData|LootTable|string|null{
		return $this->entry;
	}

	public function getWeight() : int{
		return $this->weight;
	}

	public function getQuality() : int{
		return $this->quality;
	}

	/**
	 * @return EntryFunction[]
	 */
	public function getFunctions() : array{
		return $this->functions;
	}

	/**
	 * @return LootPool[]
	 */
	public function getPools() : array{
		return $this->pools;
	}

	/**
	 * @return Item[]
	 */
	public function generate(LootContext $context) : array{
		$items = $this->type->generate($this, $context);

		foreach($this->pools as $pool){
			if($pool->evaluateConditions($context)){
				foreach($pool->generate($context) as $item){
					$items[] = $item;
				}
			}
		}

		return $items;
	}
}
