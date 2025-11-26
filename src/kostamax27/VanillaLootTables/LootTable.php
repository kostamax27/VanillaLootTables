<?php

declare(strict_types=1);

namespace kostamax27\VanillaLootTables;

use kostamax27\VanillaLootTables\pool\LootPool;
use pocketmine\item\Item;
use pocketmine\utils\Utils;

class LootTable{
	/**
	 * @param LootPool[] $pools
	 */
	public function __construct(private array $pools){
		Utils::validateArrayValueType($pools, function(LootPool $_) : void{});
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
		$items = [];
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
