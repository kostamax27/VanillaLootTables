<?php

declare(strict_types=1);

namespace kostamax27\VanillaLootTables\json;

use kostamax27\VanillaLootTables\condition\LootCondition;
use kostamax27\VanillaLootTables\entry\function\EntryFunction;
use kostamax27\VanillaLootTables\entry\ItemStackData;
use kostamax27\VanillaLootTables\entry\LootEntry;
use kostamax27\VanillaLootTables\entry\LootEntryType;
use kostamax27\VanillaLootTables\LootTable;
use kostamax27\VanillaLootTables\pool\LootPool;
use kostamax27\VanillaLootTables\pool\TieredPool;
use kostamax27\VanillaLootTables\pool\WeightedPool;
use pocketmine\data\SavedDataLoadingException;
use function is_numeric;

final class LootTableDeserializerHelper{
	/**
	 * @param mixed[] $data
	 *
	 * @phpstan-param array{
	 *    pools?: array<array{
	 *        rolls: int|float|array{min: int|float, max: int|float},
	 *        entries?: array<array{
	 *            type: string,
	 *            name?: string,
	 *            weight?: int|float,
	 *            quality?: int|float,
	 *            functions?: array<array{function: string, ...}>,
	 *            conditions?: array<array{condition: string, ...}>,
	 *            ...
	 *        }>,
	 *        tiers?: array{
	 *            initial_range?: int|float,
	 *            bonus_rolls?: int|float,
	 *            bonus_chance?: float
	 *        },
	 *        conditions?: array<array{condition: string, ...}>
	 *    }>
	 * } $data
	 */
	public static function deserializeLootTable(array $data) : LootTable{
		$pools = [];

		if(isset($data["pools"])){
			foreach($data["pools"] as $poolData){
				$pools[] = self::deserializeLootPool($poolData);
			}
		}

		return new LootTable($pools);
	}

	/**
	 * @param mixed[] $data
	 *
	 * @phpstan-param array{
	 *    rolls?: int|float|array{min: int|float, max: int|float},
	 *    entries?: array<array{
	 *        type: string,
	 *        name?: string,
	 *        weight?: int|float,
	 *        quality?: int|float,
	 *        functions?: array<array{function: string, ...}>,
	 *        conditions?: array<array{condition: string, ...}>,
	 *        ...
	 *    }>,
	 *    tiers?: array{
	 *        initial_range?: int|float,
	 *        bonus_rolls?: int|float,
	 *        bonus_chance?: float
	 *    },
	 *    conditions?: array<array{condition: string, ...}>
	 * } $data
	 */
	public static function deserializeLootPool(array $data) : LootPool{
		if(isset($data["tiers"])){
			return self::deserializeTieredPool($data);
		}
		return self::deserializeWeightedPool($data);
	}

	/**
	 * @param mixed[] $data
	 *
	 * @phpstan-param array{
	 *    tiers: array{
	 *        initial_range?: int|float,
	 *        bonus_rolls?: int|float,
	 *        bonus_chance?: float
	 *    },
	 *    entries?: array<array{
	 *        type: string,
	 *        name?: string,
	 *        weight?: int|float,
	 *        quality?: int|float,
	 *        functions?: array<array{function: string, ...}>,
	 *        conditions?: array<array{condition: string, ...}>,
	 *        ...
	 *    }>,
	 *    conditions?: array<array{condition: string, ...}>
	 * } $data
	 */
	public static function deserializeTieredPool(array $data) : TieredPool{
		$entries = [];
		if(isset($data["entries"])){
			foreach($data["entries"] as $entryData){
				$entries[] = self::deserializeLootEntry($entryData);
			}
		}

		$conditions = [];
		if(isset($data["conditions"])){
			foreach($data["conditions"] as $conditionData){
				$conditions[] = LootCondition::jsonDeserialize($conditionData);
			}
		}

		$initialRange = (int) ($data["tiers"]["initial_range"] ?? 1);
		$bonusRolls = (int) ($data["tiers"]["bonus_rolls"] ?? 0);
		$bonusChance = (float) ($data["tiers"]["bonus_chance"] ?? 0);

		return new TieredPool($entries, $initialRange, $bonusRolls, $bonusChance, $conditions);
	}

	/**
	 * @param mixed[] $data
	 *
	 * @phpstan-param array{
	 *    rolls?: int|float|array{min: int|float, max: int|float},
	 *    entries?: array<array{
	 *        type: string,
	 *        name?: string,
	 *        weight?: int|float,
	 *        quality?: int|float,
	 *        functions?: array<array{function: string, ...}>,
	 *        conditions?: array<array{condition: string, ...}>,
	 *        ...
	 *    }>,
	 *    conditions?: array<array{condition: string, ...}>
	 * } $data
	 */
	public static function deserializeWeightedPool(array $data) : WeightedPool{
		$rolls = $data["rolls"] ?? 1;
		if(is_numeric($rolls)){
			$minRolls = $maxRolls = (int) $rolls;
		}else{
			$minRolls = (int) $rolls["min"];
			$maxRolls = (int) $rolls["max"];
		}

		$entries = [];
		if(isset($data["entries"])){
			foreach($data["entries"] as $entryData){
				$entries[] = self::deserializeLootEntry($entryData);
			}
		}

		$conditions = [];
		if(isset($data["conditions"])){
			foreach($data["conditions"] as $conditionData){
				$conditions[] = LootCondition::jsonDeserialize($conditionData);
			}
		}

		return new WeightedPool($entries, $minRolls, $maxRolls, $conditions);
	}

	/**
	 * @param mixed[] $data
	 *
	 * @phpstan-param array{
	 *    type: string,
	 *    name?: string,
	 *    weight?: int|float,
	 *    quality?: int|float,
	 *    functions?: array<array{function: string, ...}>,
	 *    conditions?: array<array{condition: string, ...}>,
	 *    ...
	 * } $data
	 */
	public static function deserializeLootEntry(array $data) : LootEntry{
		$entry = null;
		switch($data["type"]){
			case "item":
				$type = LootEntryType::ITEM();

				if(!isset($data["name"])){
					throw new SavedDataLoadingException("Expected key \"name\"");
				}
				$entry = new ItemStackData($data["name"]);
				break;
			case "loot_table":
				$type = LootEntryType::LOOT_TABLE();

				if(!isset($data["name"])){
					throw new SavedDataLoadingException("Expected key \"name\"");
				}
				//Store table name instead of loading immediately to support circular references
				$entry = $data["name"];
				break;
			case "empty":
				$type = LootEntryType::EMPTY();
				break;
			default:
				throw new SavedDataLoadingException("Type \"" . $data["type"] . "\" doesn't exists");
		}

		$weight = (int) ($data["weight"] ?? 1);
		$quality = (int) ($data["quality"] ?? 1);

		$functions = [];
		if(isset($data["functions"])){
			foreach($data["functions"] as $functionData){
				$functions[] = EntryFunction::jsonDeserialize($functionData);
			}
		}

		$conditions = [];
		if(isset($data["conditions"])){
			foreach($data["conditions"] as $conditionData){
				$conditions[] = LootCondition::jsonDeserialize($conditionData);
			}
		}

		$pools = [];
		if(isset($data["pools"])){
			foreach($data["pools"] as $poolsData){
				$pools[] = self::deserializeLootPool($poolsData);
			}
		}

		return new LootEntry($type, $entry, $weight, $quality, $functions, $conditions, $pools);
	}
}
