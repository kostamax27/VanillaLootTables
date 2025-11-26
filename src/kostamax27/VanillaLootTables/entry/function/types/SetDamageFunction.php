<?php

declare(strict_types=1);

namespace kostamax27\VanillaLootTables\entry\function\types;

use kostamax27\VanillaLootTables\condition\LootCondition;
use kostamax27\VanillaLootTables\entry\function\EntryFunction;
use kostamax27\VanillaLootTables\LootContext;
use pocketmine\item\Durable;
use pocketmine\item\Item;
use function ceil;

class SetDamageFunction extends EntryFunction{
	/**
	 * @param LootCondition[] $conditions
	 */
	public function __construct(private float $min, private float $max, array $conditions = []){
		if($min < 0 || $min > 1){
			throw new \InvalidArgumentException("Min must be between 0.0 and 1.0");
		}
		if($max < 0 || $max > 1){
			throw new \InvalidArgumentException("Max must be between 0.0 and 1.0");
		}
		if($min > $max){
			throw new \InvalidArgumentException("Min is larger that max");
		}
		parent::__construct($conditions);
	}

	public function onCreation(LootContext $context, Item $item) : Item{
		if($item instanceof Durable){
			$durability = $item->getMaxDurability() - $item->getDamage();
			$item->setDamage($durability - (int) ceil(($context->getRandom()->nextFloat() * ($this->max - $this->min) + $this->min) * $durability));
		}
		return parent::onCreation($context, $item);
	}

	/**
	 * Returns an array of properties that can be serialized to json.
	 *
	 * @phpstan-return array{
	 *    function: string,
	 *    damage: float|array<string, float>,
	 *    conditions?: array<array{condition: string, ...}>
	 * }
	 */
	public function jsonSerialize() : array{
		$data = parent::jsonSerialize();

		if($this->min === $this->max){
			$data["damage"] = $this->min;
		}else{
			$data["damage"] = [
				"min" => $this->min,
				"max" => $this->max,
			];
		}

		return $data;
	}
}
