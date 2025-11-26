<?php

declare(strict_types=1);

namespace kostamax27\VanillaLootTables\condition;

use kostamax27\VanillaLootTables\LootContext;
use function count;

/**
 * This trait encapsulates all condition handling needed for loot.
 * The primary purpose of this trait is providing scope isolation for the methods it contains.
 */
trait LootConditionHandlingTrait{

	/** @var LootCondition[] */
	protected array $conditions = [];

	public function hasConditions() : bool{
		return count($this->conditions) !== 0;
	}

	/**
	 * @return LootCondition[]
	 */
	public function getConditions() : array{
		return $this->conditions;
	}

	public function evaluateConditions(LootContext $context) : bool{
		foreach($this->conditions as $condition){
			if(!$condition->evaluate($context)){
				return false;
			}
		}
		return true;
	}
}
