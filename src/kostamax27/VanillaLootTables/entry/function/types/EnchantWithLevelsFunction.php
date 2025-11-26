<?php

declare(strict_types=1);

namespace kostamax27\VanillaLootTables\entry\function\types;

use kostamax27\VanillaLootTables\condition\LootCondition;
use kostamax27\VanillaLootTables\entry\function\EntryFunction;
use kostamax27\VanillaLootTables\LootContext;
use pocketmine\item\Item;

class EnchantWithLevelsFunction extends EntryFunction{
	/**
	 * @param LootCondition[] $conditions
	 */
	public function __construct(private int $min, private int $max, private bool $treasureEnchants = false, array $conditions = []){
		if($min < 0){
			throw new \InvalidArgumentException("Min cannot be less than 0");
		}
		if($min > $max){
			throw new \InvalidArgumentException("Min is larger that max");
		}
		parent::__construct($conditions);
	}

	public function onCreation(LootContext $context, Item $item) : Item{
		//TODO: EnchantingHelper...
		$enchantments = [];
		foreach($enchantments as $enchantment){
			$item->addEnchantment($enchantment);
		}
		return parent::onCreation($context, $item);
	}

	/**
	 * Returns an array of properties that can be serialized to json.
	 *
	 * @phpstan-return array{
	 *    function: string,
	 *    levels: int|array{min: int, max: int},
	 *    treasure?: bool,
	 *    conditions?: array<array{condition: string, ...}>
	 * }
	 */
	public function jsonSerialize() : array{
		$data = parent::jsonSerialize();

		if($this->min === $this->max){
			$data["levels"] = $this->min;
		}else{
			$data["levels"] = [
				"min" => $this->min,
				"max" => $this->max,
			];
		}

		if($this->treasureEnchants){
			$data["treasure"] = true;
		}

		return $data;
	}
}
