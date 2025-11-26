<?php

declare(strict_types=1);

namespace kostamax27\VanillaLootTables\pool;

use kostamax27\VanillaLootTables\condition\LootCondition;
use kostamax27\VanillaLootTables\entry\LootEntry;
use kostamax27\VanillaLootTables\LootContext;
use pocketmine\item\Item;
use function count;

class TieredPool extends LootPool{
	/**
	 * @param LootEntry[] $entries
	 * @param LootCondition[] $conditions
	 */
	public function __construct(
		array $entries,
		protected int $initialRange = 1,
		protected int $bonusRolls = 0,
		protected float $bonusChance = 0.0,
		array $conditions = []
	){
		if($initialRange > count($entries)){
			throw new \InvalidArgumentException("initialRange is greater than entries count");
		}
		if($initialRange < 1){
			throw new \InvalidArgumentException("initialRange must be greater than 1");
		}
		if($bonusRolls < 0){
			throw new \InvalidArgumentException("bonusRolls cannot be less than 0");
		}

		parent::__construct($entries, $conditions);
	}

	public function getInitialRange() : int{
		return $this->initialRange;
	}

	public function getBonusRolls() : int{
		return $this->bonusRolls;
	}

	public function getBonusChance() : float{
		return $this->bonusChance;
	}

	/**
	 * @return Item[]
	 */
	public function generate(LootContext $context) : array{
		//tiered pools ignore entry conditions
		$index = $context->getRandom()->nextBoundedInt($this->initialRange);
		if($this->bonusRolls > 0){
			for($i = 0; $i < $this->bonusRolls; $i++){
				if($context->getRandom()->nextFloat() <= $this->bonusChance){
					$index++;
				}
			}
		}

		if(isset($this->entries[$index])){
			return $this->entries[$index]->generate($context);
		}
		return [];
	}
}
