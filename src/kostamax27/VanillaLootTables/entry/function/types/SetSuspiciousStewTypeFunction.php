<?php

declare(strict_types=1);

namespace kostamax27\VanillaLootTables\entry\function\types;

use kostamax27\VanillaLootTables\condition\LootCondition;
use kostamax27\VanillaLootTables\entry\function\EntryFunction;
use kostamax27\VanillaLootTables\LootContext;
use pocketmine\data\bedrock\SuspiciousStewTypeIdMap;
use pocketmine\item\Item;
use pocketmine\item\SuspiciousStew;
use pocketmine\item\SuspiciousStewType;
use pocketmine\utils\Utils;
use function count;

class SetSuspiciousStewTypeFunction extends EntryFunction{
	/**
	 * @param SuspiciousStewType[] $types
	 * @param LootCondition[] $conditions
	 *
	 * @phpstan-param non-empty-list<SuspiciousStewType> $types
	 */
	public function __construct(protected array $types, array $conditions = []){
		Utils::validateArrayValueType($types, function(SuspiciousStewType $_) : void{});
		parent::__construct($conditions);
	}

	public function onCreation(LootContext $context, Item $item) : Item{
		if($item instanceof SuspiciousStew){
			$item->setType($this->types[$context->getRandom()->nextBoundedInt(count($this->types))]);
		}
		return parent::onCreation($context, $item);
	}

	/**
	 * Returns an array of properties that can be serialized to json.
	 *
	 * @phpstan-return array{
	 *    function: string,
	 *    effects: array<array{id: int}>,
	 *    conditions?: array<array{condition: string, ...}>
	 * }
	 */
	public function jsonSerialize() : array{
		$data = parent::jsonSerialize();

		foreach($this->types as $type){
			$data["effects"][] = ["id" => SuspiciousStewTypeIdMap::getInstance()->toId($type)];
		}

		return $data;
	}
}
