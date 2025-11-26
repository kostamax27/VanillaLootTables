<?php

declare(strict_types=1);

namespace kostamax27\VanillaLootTables\condition\types;

use InvalidArgumentException;
use kostamax27\VanillaLootTables\condition\LootCondition;
use kostamax27\VanillaLootTables\LootContext;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Utils;
use pocketmine\world\World;

class RandomDifficultyChanceCondition extends LootCondition{
	/**
	 * @param array<int, float> $difficultiesChance difficulty => chance
	 */
	public function __construct(protected array $difficultiesChance, protected float $defaultChance){
		Utils::validateArrayValueType($difficultiesChance, function(float $_) : void{});
		foreach($difficultiesChance as $difficulty => $chance){
			if($difficulty < World::DIFFICULTY_PEACEFUL || $difficulty > World::DIFFICULTY_HARD){
				throw new InvalidArgumentException("Invalid difficulty level $difficulty");
			}
		}
	}

	public function evaluate(LootContext $context) : bool{
		return $context->getRandom()->nextFloat() <= ($this->difficultiesChance[$context->getWorld()->getDifficulty()] ?? $this->defaultChance);
	}

	/**
	 * Returns an array of properties that can be serialized to json.
	 *
	 * @phpstan-return array{
	 *    condition: string,
	 *    default_chance: float,
	 *    peaceful?: float,
	 *    easy?: float,
	 *    normal?: float,
	 *    hard?: float
	 * }
	 */
	public function jsonSerialize() : array{
		$data = parent::jsonSerialize();

		$data["default_chance"] = $this->defaultChance;
		foreach($this->difficultiesChance as $difficulty => $chance){
			$dName = match ($difficulty) {
				World::DIFFICULTY_PEACEFUL => "peaceful",
				World::DIFFICULTY_EASY => "easy",
				World::DIFFICULTY_NORMAL => "normal",
				World::DIFFICULTY_HARD => "hard",
				default => throw new AssumptionFailedError("Unknown difficulty $difficulty")
			};
			$data[$dName] = $chance;
		}

		return $data;
	}
}
