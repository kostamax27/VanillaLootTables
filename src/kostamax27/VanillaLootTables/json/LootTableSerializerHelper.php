<?php

declare(strict_types=1);

namespace kostamax27\VanillaLootTables\json;

use kostamax27\VanillaLootTables\entry\ItemStackData;
use kostamax27\VanillaLootTables\entry\LootEntry;
use kostamax27\VanillaLootTables\LootTable;
use kostamax27\VanillaLootTables\LootTableFactory;
use kostamax27\VanillaLootTables\pool\LootPool;
use kostamax27\VanillaLootTables\pool\TieredPool;
use kostamax27\VanillaLootTables\pool\WeightedPool;
use function is_string;

/**
 * Bunch of functions to convert loot tables into properties that can be serialized to json.
 */
final class LootTableSerializerHelper{
	/**
	 * @return mixed[]
	 * @phpstan-return array{
	 *    pools?: array<array{
	 *        rolls?: int|array{min: int, max: int},
	 *        entries?: array<array{
	 *            type: string,
	 *            name?: string,
	 *            weight?: int,
	 *            quality?: int,
	 *            functions?: array<array{function: string, ...}>,
	 *            conditions?: array<array{condition: string, ...}>,
	 *            ...
	 *        }>,
	 *        tiers?: array{
	 *            initial_range?: int,
	 *            bonus_rolls?: int,
	 *            bonus_chance?: float
	 *        },
	 *        conditions?: array<array{condition: string, ...}>
	 *    }>
	 * }
	 */
	public static function serializeLootTable(LootTable $table) : array{
		$data = [];

		$pools = $table->getPools();
		foreach($pools as $pool){
			$data["pools"][] = self::serializeLootPool($pool);
		}

		return $data;
	}

	/**
	 * @return mixed[]
	 * @phpstan-return array{
	 *    rolls?: int|array{min: int, max: int},
	 *    entries?: array<array{
	 *        type: string,
	 *        name?: string,
	 *        weight?: int,
	 *        quality?: int,
	 *        functions?: array<array{function: string, ...}>,
	 *        conditions?: array<array{condition: string, ...}>,
	 *        ...
	 *    }>,
	 *    tiers?: array{
	 *        initial_range?: int,
	 *        bonus_rolls?: int,
	 *        bonus_chance?: float
	 *    },
	 *    conditions?: array<array{condition: string, ...}>
	 * }
	 */
	public static function serializeLootPool(LootPool $pool) : array{
		if($pool instanceof TieredPool){
			return self::serializeTieredPool($pool);
		}
		if($pool instanceof WeightedPool){
			return self::serializeWeightedPool($pool);
		}
		throw new \InvalidArgumentException("Unknown LootPool type: " . $pool::class);
	}

	/**
	 * @return mixed[]
	 * @phpstan-return array{
	 *    tiers: array{
	 *        initial_range: int,
	 *        bonus_rolls: int,
	 *        bonus_chance: float
	 *    },
	 *    entries?: array<array{
	 *        type: string,
	 *        name?: string,
	 *        weight?: int,
	 *        quality?: int,
	 *        functions?: array<array{function: string, ...}>,
	 *        conditions?: array<array{condition: string, ...}>,
	 *        ...
	 *    }>,
	 *    conditions?: array<array{condition: string, ...}>
	 * }
	 */
	public static function serializeTieredPool(TieredPool $pool) : array{
		$data = [];

		$data["tiers"]["initial_range"] = $pool->getInitialRange();
		$data["tiers"]["bonus_rolls"] = $pool->getBonusRolls();
		$data["tiers"]["bonus_chance"] = $pool->getBonusChance();

		foreach($pool->getEntries() as $entry){
			$data["entries"][] = self::serializeLootEntry($entry);
		}

		foreach($pool->getConditions() as $condition){
			$data["conditions"][] = $condition->jsonSerialize();
		}

		return $data;
	}

	/**
	 * @return mixed[]
	 * @phpstan-return array{
	 *    rolls?: int|array{min: int, max: int},
	 *    entries?: array<array{
	 *        type: string,
	 *        name?: string,
	 *        weight?: int,
	 *        quality?: int,
	 *        functions?: array<array{function: string, ...}>,
	 *        conditions?: array<array{condition: string, ...}>,
	 *        ...
	 *    }>,
	 *    conditions?: array<array{condition: string, ...}>
	 * }
	 */
	public static function serializeWeightedPool(WeightedPool $pool) : array{
		$data = [];

		$minRolls = $pool->getMinRolls();
		$maxRolls = $pool->getMaxRolls();
		if($minRolls === 1 && $maxRolls === 1){
			//Don't write it
		}elseif($minRolls === $maxRolls){
			$data["rolls"] = $minRolls;
		}else{
			$data["rolls"] = [
				"min" => $minRolls,
				"max" => $maxRolls,
			];
		}

		foreach($pool->getEntries() as $entry){
			$data["entries"][] = self::serializeLootEntry($entry);
		}

		foreach($pool->getConditions() as $condition){
			$data["conditions"][] = $condition->jsonSerialize();
		}

		return $data;
	}

	/**
	 * @return mixed[]
	 * @phpstan-return array{
	 *    type: string,
	 *    name?: string,
	 *    weight?: int,
	 *    quality?: int,
	 *    functions?: array<array{function: string, ...}>,
	 *    conditions?: array<array{condition: string, ...}>,
	 *    ...
	 * }
	 */
	public static function serializeLootEntry(LootEntry $entry) : array{
		$data = [];

		$data["type"] = $entry->getType()->name();

		$e = $entry->getEntry();
		if($e instanceof ItemStackData){
			$data["name"] = $e->name;
		}elseif($e instanceof LootTable){
			$data["name"] = LootTableFactory::getInstance()->getSaveName($e);
		}elseif(is_string($e)){
			//String reference to a loot table (for circular references)
			$data["name"] = $e;
		}

		$weight = $entry->getWeight();
		if($weight !== 1){
			$data["weight"] = $weight;
		}

		$quality = $entry->getQuality();
		if($quality !== 1){
			$data["quality"] = $quality;
		}

		$functions = $entry->getFunctions();
		foreach($functions as $function){
			$data["functions"][] = $function->jsonSerialize();
		}

		$conditions = $entry->getConditions();
		foreach($conditions as $condition){
			$data["conditions"][] = $condition->jsonSerialize();
		}

		$pools = $entry->getPools();
		foreach($pools as $pool){
			$data["pools"][] = self::serializeLootPool($pool);
		}

		return $data;
	}
}
