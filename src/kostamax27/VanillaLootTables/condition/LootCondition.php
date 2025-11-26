<?php

declare(strict_types=1);

namespace kostamax27\VanillaLootTables\condition;

use InvalidArgumentException;
use JsonSerializable;
use kostamax27\VanillaLootTables\LootContext;

abstract class LootCondition implements JsonSerializable{
	abstract public function evaluate(LootContext $context) : bool;

	/**
	 * Returns an array of loot condition properties that can be serialized to json.
	 *
	 * @phpstan-return array{
	 *    condition: string
	 * }
	 */
	public function jsonSerialize() : array{
		return ["condition" => LootConditionFactory::getInstance()->getSaveId($this::class)];
	}

	/**
	 * Returns a LootCondition from properties created in an array by {@link LootCondition#jsonSerialize}
	 *
	 * @phpstan-param array{
	 *    condition: string
	 * } $data
	 */
	public static function jsonDeserialize(array $data) : LootCondition{
		return LootConditionFactory::getInstance()->createFromData($data) ?? throw new InvalidArgumentException("LootCondition \"" . $data["condition"] . "\" is not registered");
	}
}
