<?php

declare(strict_types=1);

namespace kostamax27\VanillaLootTables\entry\function\types;

use pocketmine\item\enchantment\Enchantment;

class LootEnchantmentEntry{
	public readonly int $max;

	public function __construct(public Enchantment $enchantment, public readonly int $min, ?int $max = null){
		$this->max = $max ?? $min;
		if($this->min < 0){
			throw new \InvalidArgumentException("Min level cannot be negative");
		}
		if($this->min > $this->max){
			throw new \InvalidArgumentException("Min level cannot be greater than max level");
		}
	}
}
