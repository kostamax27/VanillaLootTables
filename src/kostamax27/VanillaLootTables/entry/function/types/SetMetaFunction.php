<?php

declare(strict_types=1);

namespace kostamax27\VanillaLootTables\entry\function\types;

use kostamax27\VanillaLootTables\condition\LootCondition;
use kostamax27\VanillaLootTables\entry\function\EntryFunction;
use kostamax27\VanillaLootTables\LootContext;

class SetMetaFunction extends EntryFunction{
	/**
	 * @param LootCondition[] $conditions
	 */
	public function __construct(private int $min, private int $max, array $conditions = []){
		if($min < 0){
			throw new \InvalidArgumentException("Min cannot be less than 0");
		}
		if($min > $max){
			throw new \InvalidArgumentException("Min is larger that max");
		}
		parent::__construct($conditions);
	}

	public function onPreCreation(LootContext $context, int &$meta, int &$count) : void{
		$meta = $context->getRandom()->nextRange($this->min, $this->max);
	}

	/**
	 * Returns an array of properties that can be serialized to json.
	 *
	 * @phpstan-return array{
	 *    function: string,
	 *    data: int|array<string, int>,
	 *    conditions?: array<array{condition: string, ...}>
	 * }
	 */
	public function jsonSerialize() : array{
		$data = parent::jsonSerialize();

		if($this->min === $this->max){
			$data["data"] = $this->min;
		}else{
			$data["data"] = [
				"min" => $this->min,
				"max" => $this->max,
			];
		}

		return $data;
	}
}
