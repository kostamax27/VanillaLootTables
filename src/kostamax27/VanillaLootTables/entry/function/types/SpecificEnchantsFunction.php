<?php

declare(strict_types=1);

namespace kostamax27\VanillaLootTables\entry\function\types;

use kostamax27\VanillaLootTables\condition\LootCondition;
use kostamax27\VanillaLootTables\entry\function\EntryFunction;
use kostamax27\VanillaLootTables\LootContext;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\StringToEnchantmentParser;
use pocketmine\item\Item;
use function min;

class SpecificEnchantsFunction extends EntryFunction{
	/**
	 * @param LootEnchantmentEntry[] $entries
	 * @param LootCondition[] $conditions
	 */
	public function __construct(private array $entries, array $conditions = []){
		parent::__construct($conditions);
	}

	public function onCreation(LootContext $context, Item $item) : Item{
		foreach($this->entries as $entry){
			$level = $entry->min === $entry->max
				? $entry->min
				: $context->getRandom()->nextBoundedInt($entry->max - $entry->min + 1) + $entry->min;

			$level = min($level, $entry->enchantment->getMaxLevel());

			if($level > 0){
				$item->addEnchantment(new EnchantmentInstance($entry->enchantment, $level));
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
