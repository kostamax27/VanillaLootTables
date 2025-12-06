<?php

declare(strict_types=1);

namespace kostamax27\VanillaLootTables\entry\function\types;

use kostamax27\VanillaLootTables\condition\LootCondition;
use kostamax27\VanillaLootTables\entry\function\EntryFunction;
use kostamax27\VanillaLootTables\LootContext;
use pocketmine\item\enchantment\EnchantingHelper;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\StringToEnchantmentParser;
use pocketmine\item\Item;
use pocketmine\utils\Utils;
use function min;

class SpecificEnchantsFunction extends EntryFunction{
	/**
	 * @param LootEnchantmentEntry[] $entries
	 * @param LootCondition[] $conditions
	 *
	 * @phpstan-param non-empty-list<LootEnchantmentEntry> $entries
	 */
	public function __construct(private array $entries, array $conditions = []){
		Utils::validateArrayValueType($entries, function(LootEnchantmentEntry $_) : void{});
		parent::__construct($conditions);
	}

	public function onCreation(LootContext $context, Item $item) : Item{
		foreach($this->entries as $entry){
			$level = min($context->getRandom()->nextRange($entry->min, $entry->max), $entry->enchantment->getMaxLevel());

			if($level > 0){
				$enchant = new EnchantmentInstance($entry->enchantment, $level);
				$item = EnchantingHelper::enchantItem($item, [$enchant]);
			}
		}
		return parent::onCreation($context, $item);
	}

	/**
	 * Returns an array of properties that can be serialized to json.
	 *
	 * @phpstan-return array{
	 *    function: string,
	 *    enchants: array<array{id: string, level: int|array{min: int, max: int}}>,
	 *    conditions?: array<array{condition: string, ...}>
	 * }
	 */
	public function jsonSerialize() : array{
		$data = parent::jsonSerialize();

		$enchants = [];
		foreach($this->entries as $entry){
			//TODO: ...
			$id = null;
			$factory = StringToEnchantmentParser::getInstance();
			foreach($factory->getKnownAliases() as $alias){
				if($factory->parse($alias) === $entry->enchantment){
					$id = $alias;
					break;
				}
			}
			if($id === null){
				continue;
			}

			$enchantEntry = [
				"id" => $id,
			];
			if($entry->min === $entry->max){
				$enchantEntry["level"] = $entry->min;
			}else{
				$enchantEntry["level"] = [
					"min" => $entry->min,
					"max" => $entry->max,
				];
			}
			$enchants[] = $enchantEntry;
		}

		$data["enchants"] = $enchants;

		return $data;
	}
}
