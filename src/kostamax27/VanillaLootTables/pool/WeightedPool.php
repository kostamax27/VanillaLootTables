<?php

declare(strict_types=1);

namespace kostamax27\VanillaLootTables\pool;

use kostamax27\VanillaLootTables\condition\LootCondition;
use kostamax27\VanillaLootTables\entry\LootEntry;
use kostamax27\VanillaLootTables\LootContext;
use pocketmine\item\Item;
use function count;

class WeightedPool extends LootPool{
	/**
	 * @param LootEntry[] $entries
	 * @param LootCondition[] $conditions
	 */
	public function __construct(
		array $entries,
		protected int $minRolls = 1,
		protected int $maxRolls = 1,
		array $conditions = []
	){
		if($minRolls < 0){
			throw new \InvalidArgumentException("minRolls cannot be less than 0");
		}
		if($minRolls > $maxRolls){
			throw new \InvalidArgumentException("minRolls is larger that maxRolls");
		}

		parent::__construct($entries, $conditions);
	}

	public function getMinRolls() : int{
		return $this->minRolls;
	}

	public function getMaxRolls() : int{
		return $this->maxRolls;
	}

	/**
	 * @return Item[]
	 */
	public function generate(LootContext $context) : array{
		$items = [];

		if(count($this->entries) === 0){
			return $items;
		}

		$rolls = $context->getRandom()->nextRange($this->minRolls, $this->maxRolls);
		if($rolls < 1){
			return $items;
		}

		$entries = [];
		$totalWeight = 0;
		foreach($this->entries as $entry){
			if($entry->evaluateConditions($context)){
				$totalWeight += $entry->getWeight();
				$entries[] = $entry;
			}
		}

		for($i = 0; $i < $rolls; $i++){
			$selected = $context->getRandom()->nextRange(1, $totalWeight);
			$currentWeight = 0;
			foreach($entries as $entry){
				$currentWeight += $entry->getWeight();
				if($selected <= $currentWeight){
					foreach($entry->generate($context) as $item){
						$items[] = $item;
					}
					break;
				}
			}
		}

		return $items;
	}
}
