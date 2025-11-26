<?php

declare(strict_types=1);

namespace kostamax27\VanillaLootTables\condition\types;

use kostamax27\VanillaLootTables\condition\LootCondition;
use kostamax27\VanillaLootTables\LootContext;

class RandomChanceCondition extends LootCondition{
	public function __construct(
		protected float $chance,
	){}

	public function evaluate(LootContext $context) : bool{
		return $context->getRandom()->nextFloat() <= $this->chance;
	}

	/**
	 * Returns an array of properties that can be serialized to json.
	 *
	 * @phpstan-return array{
	 *    condition: string,
	 *    chance: float
	 * }
	 */
	public function jsonSerialize() : array{
		$data = parent::jsonSerialize();

		$data["chance"] = $this->chance;

		return $data;
	}
}
