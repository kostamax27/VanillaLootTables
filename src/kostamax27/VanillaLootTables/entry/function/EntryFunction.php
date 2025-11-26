<?php

declare(strict_types=1);

namespace kostamax27\VanillaLootTables\entry\function;

use kostamax27\VanillaLootTables\condition\LootCondition;
use kostamax27\VanillaLootTables\condition\LootConditionHandlingTrait;
use kostamax27\VanillaLootTables\LootContext;
use pocketmine\item\Item;
use pocketmine\utils\Utils;

abstract class EntryFunction implements \JsonSerializable{
	use LootConditionHandlingTrait;

	/**
	 * @param LootCondition[] $conditions
	 */
	public function __construct(array $conditions){
		Utils::validateArrayValueType($conditions, function(LootCondition $_) : void{});
		$this->conditions = $conditions;
	}

	public function onPreCreation(LootContext $context, int &$meta, int &$count) : void{}

	public function onCreation(LootContext $context, Item $item) : Item{
		return $item;
	}

	/**
	 * Returns an array of an entry function properties that can be serialized to json.
	 *
	 * @phpstan-return array{
	 *    function: string,
	 *    conditions?: array<array{condition: string, ...}>
	 * }
	 */
	public function jsonSerialize() : array{
		$data = [];

		$data["function"] = EntryFunctionFactory::getInstance()->getSaveId($this::class);
		foreach($this->conditions as $condition){
			$data["conditions"][] = $condition->jsonSerialize();
		}

		return $data;
	}

	/**
	 * Returns a EntryFunction from properties created in an array by {@link EntryFunction#jsonSerialize}
	 *
	 * @phpstan-param array{
	 *    function: string,
	 *    conditions?: array<array{condition: string, ...}>
	 * } $data
	 */
	public static function jsonDeserialize(array $data) : EntryFunction{
		return EntryFunctionFactory::getInstance()->createFromData($data) ?? throw new \InvalidArgumentException("EntryFunction \"" . $data["function"] . "\" is not registered");
	}
}
