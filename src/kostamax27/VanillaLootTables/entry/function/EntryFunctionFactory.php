<?php

declare(strict_types=1);

namespace kostamax27\VanillaLootTables\entry\function;

use DaveRandom\CallbackValidator\BuiltInTypes;
use DaveRandom\CallbackValidator\CallbackType;
use DaveRandom\CallbackValidator\ParameterType;
use DaveRandom\CallbackValidator\ReturnType;
use kostamax27\VanillaLootTables\condition\LootCondition;
use kostamax27\VanillaLootTables\entry\function\types\EnchantRandomlyFunction;
use kostamax27\VanillaLootTables\entry\function\types\EnchantWithLevelsFunction;
use kostamax27\VanillaLootTables\entry\function\types\FurnaceSmeltFunction;
use kostamax27\VanillaLootTables\entry\function\types\LootEnchantmentEntry;
use kostamax27\VanillaLootTables\entry\function\types\RandomDyeFunction;
use kostamax27\VanillaLootTables\entry\function\types\SetCountFunction;
use kostamax27\VanillaLootTables\entry\function\types\SetCustomNameFunction;
use kostamax27\VanillaLootTables\entry\function\types\SetDamageFunction;
use kostamax27\VanillaLootTables\entry\function\types\SetLoreFunction;
use kostamax27\VanillaLootTables\entry\function\types\SetMetaFunction;
use kostamax27\VanillaLootTables\entry\function\types\SetPotionTypeFunction;
use kostamax27\VanillaLootTables\entry\function\types\SetSuspiciousStewTypeFunction;
use kostamax27\VanillaLootTables\entry\function\types\SpecificEnchantsFunction;
use pocketmine\data\bedrock\SuspiciousStewTypeIdMap;
use pocketmine\data\SavedDataLoadingException;
use pocketmine\item\enchantment\StringToEnchantmentParser;
use pocketmine\item\PotionType;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\Utils;
use function count;
use function is_array;
use function is_numeric;
use function is_string;
use function reset;
use function str_replace;
use function strtolower;
use function strtoupper;
use function trim;

final class EntryFunctionFactory{
	use SingletonTrait;

	/**
	 * @var \Closure[] save ID => creator function
	 * @phpstan-var array<string, \Closure(array<string, mixed>, LootCondition[]) : EntryFunction>
	 */
	private array $creationFuncs = [];

	/**
	 * @var string[]
	 * @phpstan-var array<class-string<EntryFunction>, string>
	 */
	private array $saveNames = [];

	public function __construct(){
		$this->register(EnchantRandomlyFunction::class, function(array $data, array $conditions) : EnchantRandomlyFunction{
			$treasure = isset($data["treasure"]) && $data["treasure"];
			return new EnchantRandomlyFunction($treasure, $conditions);
		}, ["enchant_randomly"]);

		$this->register(EnchantWithLevelsFunction::class, function(array $data, array $conditions) : EnchantWithLevelsFunction{
			if(!isset($data["levels"])){
				throw new SavedDataLoadingException("Key \"levels\" doesn't exists");
			}
			[$min, $max] = $this->parseIntMinMax($data["levels"], "levels");

			$treasure = isset($data["treasure"]) && $data["treasure"];
			return new EnchantWithLevelsFunction($min, $max, $treasure, $conditions);
		}, ["enchant_with_levels"]);

		$this->register(SpecificEnchantsFunction::class, function(array $data, array $conditions) : SpecificEnchantsFunction{
			if(!isset($data["enchants"]) || !is_array($data["enchants"])){
				throw new SavedDataLoadingException("\"enchants\" isn't an array or doesn't exists");
			}

			$enchantments = [];
			foreach($data["enchants"] as $enchantData){
				if(!isset($enchantData["id"]) || !is_string($enchantData["id"])){
					throw new SavedDataLoadingException("\"id\" isn't a string or doesn't exists");
				}

				$enchantmentId = $enchantData["id"];
				$enchantment = StringToEnchantmentParser::getInstance()->parse($enchantmentId);
				if($enchantment === null){
					throw new SavedDataLoadingException("Unknown enchantment ID: $enchantmentId");
				}

				if(!isset($enchantData["level"])){
					throw new SavedDataLoadingException("\"level\" doesn't exists for enchantment $enchantmentId");
				}

				[$min, $max] = $this->parseIntMinMax($enchantData["level"], "level", allowIndexedArray: true);

				$enchantments[] = new LootEnchantmentEntry($enchantment, $min, $max);
			}

			if(count($enchantments) === 0){
				throw new SavedDataLoadingException("No enchantments found");
			}
			return new SpecificEnchantsFunction($enchantments, $conditions);
		}, ["specific_enchants"]);

		$this->register(FurnaceSmeltFunction::class, function(array $data, array $conditions) : FurnaceSmeltFunction{
			return new FurnaceSmeltFunction($conditions);
		}, ["furnace_smelt"]);

		$this->register(RandomDyeFunction::class, function(array $data, array $conditions) : RandomDyeFunction{
			return new RandomDyeFunction($conditions);
		}, ["random_dye"]);

		$this->register(SetCountFunction::class, function(array $data, array $conditions) : SetCountFunction{
			if(!isset($data["count"])){
				throw new SavedDataLoadingException("Key \"count\" doesn't exists");
			}
			[$min, $max] = $this->parseIntMinMax($data["count"], "count");
			return new SetCountFunction($min, $max, $conditions);
		}, ["set_count"]);

		$this->register(SetCustomNameFunction::class, function(array $data, array $conditions) : SetCustomNameFunction{
			$name = $data["name"] ?? null;
			if(!is_string($name)){
				throw new SavedDataLoadingException("Name is not a string or key doesn't exists");
			}
			Utils::checkUTF8($name);
			return new SetCustomNameFunction($name, $conditions);
		}, ["set_name"]);

		$this->register(SetLoreFunction::class, function(array $data, array $conditions) : SetLoreFunction{
			if(!isset($data["lore"])){
				throw new SavedDataLoadingException("Key \"lore\" doesn't exists");
			}
			$lore = is_array($data["lore"]) ? $data["lore"] : [$data["lore"]];
			foreach($lore as $line){
				if(!is_string($line)){
					throw new SavedDataLoadingException("Lore line is not a string");
				}
				Utils::checkUTF8($line);
			}
			return new SetLoreFunction($lore, $conditions);
		}, ["set_lore"]);

		$this->register(SetDamageFunction::class, function(array $data, array $conditions) : SetDamageFunction{
			if(!isset($data["damage"])){
				throw new SavedDataLoadingException("Key \"damage\" doesn't exists");
			}
			[$min, $max] = $this->parseFloatMinMax($data["damage"], "damage");
			if($max < 0 || $max > 1){
				throw new SavedDataLoadingException("Max damage must be between 0.0 and 1.0");
			}
			return new SetDamageFunction($min, $max, $conditions);
		}, ["set_damage"]);

		$this->register(SetMetaFunction::class, function(array $data, array $conditions) : SetMetaFunction{
			$meta = $data["data"] ?? $data["values"] ?? throw new SavedDataLoadingException("Expected keys \"data\" or \"values\"");
			[$min, $max] = $this->parseIntMinMax($meta, "data/values");
			return new SetMetaFunction($min, $max, $conditions);
		}, ["set_data", "random_aux_value"]);

		$this->register(SetSuspiciousStewTypeFunction::class, function(array $data, array $conditions) : SetSuspiciousStewTypeFunction{
			if(!isset($data["effects"]) || !is_array($data["effects"])){
				throw new SavedDataLoadingException("\"effects\" isn't an array or doesn't exists");
			}

			$types = [];
			foreach($data["effects"] as $typeData){
				if(!isset($typeData["id"]) || !is_numeric($typeData["id"])){
					throw new SavedDataLoadingException("\"id\" isn't numeric or doesn't exists");
				}
				$id = (int) $typeData["id"];
				$types[] = SuspiciousStewTypeIdMap::getInstance()->fromId($id) ?? throw new SavedDataLoadingException("Unknown suspicious stew type ID $id");
			}
			if(count($types) === 0){
				throw new SavedDataLoadingException("No suspicious stew types found");
			}
			return new SetSuspiciousStewTypeFunction($types, $conditions);
		}, ["set_stew_effect"]);

		$this->register(SetPotionTypeFunction::class, function(array $data, array $conditions) : SetPotionTypeFunction{
			$potionId = $data["id"] ?? null;

			if(!is_string($potionId)){
				throw new SavedDataLoadingException("PotionId is not a string or key doesn't exists");
			}
			$upperId = strtoupper($potionId);

			$potionType = null;
			foreach(PotionType::cases() as $case){
				if($case->name === $upperId){
					$potionType = $case;
					break;
				}
			}

			if($potionType === null){
				throw new SavedDataLoadingException("Unknown potion type ID $potionId");
			}
			return new SetPotionTypeFunction($potionType, $conditions);
		}, ["set_potion"]);
	}

	/**
	 * Registers an entry function type into the index.
	 *
	 * @param string $className Class that extends EntryFunction
	 * @param string[] $saveNames An array of save names which this entity might be saved under.
	 *
	 * @phpstan-param class-string<EntryFunction> $className
	 * @phpstan-param list<string> $saveNames
	 * @phpstan-param \Closure(array<string, mixed> $arguments, LootCondition[] $conditions) : EntryFunction $creationFunc
	 *
	 * NOTE: The first save name in the $saveNames array will be used when saving the function.
	 *
	 * @throws \InvalidArgumentException
	 */
	public function register(string $className, \Closure $creationFunc, array $saveNames) : void{
		if(count($saveNames) === 0){
			throw new \InvalidArgumentException("At least one save name must be provided");
		}
		Utils::testValidInstance($className, EntryFunction::class);
		Utils::validateCallableSignature(new CallbackType(
			new ReturnType(EntryFunction::class),
			new ParameterType("arguments", BuiltInTypes::ARRAY),
			new ParameterType("conditions", BuiltInTypes::ARRAY)
		), $creationFunc);

		foreach($saveNames as $name){
			$this->creationFuncs[$name] = $creationFunc;
		}
		$this->saveNames[$className] = reset($saveNames);
	}

	/**
	 * Creates an entry function from data stored on a chunk.
	 *
	 * @param mixed[] $data
	 *
	 * @phpstan-param array{
	 *    function: string,
	 *    conditions?: array<array{condition: string, ...}>,
	 *    ...
	 * } $data
	 *
	 * @throws SavedDataLoadingException
	 * @internal
	 */
	public function createFromData(array $data) : ?EntryFunction{
		$func = $this->creationFuncs[$this->reprocess($data["function"])] ?? null;
		if($func === null){
			return null;
		}
		unset($data["function"]);

		$conditions = [];
		if(isset($data["conditions"])){
			foreach($data["conditions"] as $conditionData){
				$conditions[] = LootCondition::jsonDeserialize($conditionData);
			}
		}
		unset($data["conditions"]);

		/** @var EntryFunction $function */
		$function = $func($data, $conditions);

		return $function;
	}

	/**
	 * @phpstan-param class-string<EntryFunction> $class
	 */
	public function getSaveId(string $class) : string{
		if(isset($this->saveNames[$class])){
			return $this->saveNames[$class];
		}
		throw new \InvalidArgumentException("EntryFunction $class is not registered");
	}

	protected function reprocess(string $input) : string{
		return strtolower(str_replace([" ", "minecraft:"], ["_", ""], trim($input)));
	}

	/**
	 * Helper to extract min/max from mixed input.
	 *
	 * @param mixed $value The value to parse (numeric, associative array, or indexed array)
	 * @param string $fieldName The name of the field for error messages
	 * @param bool $allowIndexedArray Whether to allow the format [min, max] (vs. {"min": ..., "max": ...})
	 *
	 * @return array{int|float, int|float}
	 * @throws SavedDataLoadingException
	 */
	private function parseRawMinMax(mixed $value, string $fieldName, bool $allowIndexedArray) : array{
		if(is_numeric($value)){
			return [$value, $value];
		}
		if(is_array($value)){
			if($allowIndexedArray && isset($value[0], $value[1])){
				[$min, $max] = $value;
			}elseif(isset($value["min"], $value["max"])){
				$min = $value["min"];
				$max = $value["max"];
			}else{
				$formats = $allowIndexedArray ? '["min", "max"] or {"min": ..., "max": ...}' : '{"min": ..., "max": ...}';
				throw new SavedDataLoadingException("Invalid array format for \"$fieldName\". Expected number or array in one of these formats: $formats.");
			}

			if(!is_numeric($min) || !is_numeric($max)){
				throw new SavedDataLoadingException("Min or max value in \"$fieldName\" isn't numeric.");
			}
			if($min > $max){
				throw new SavedDataLoadingException("Min is larger than max in \"$fieldName\"");
			}
			return [$min, $max];
		}
		throw new SavedDataLoadingException("Invalid format for \"$fieldName\". Expected number or array.");
	}

	/**
	 * Parses a min/max range as integers.
	 *
	 * @return array{int, int}
	 */
	private function parseIntMinMax(mixed $value, string $fieldName, bool $allowNegative = false, bool $allowIndexedArray = false) : array{
		[$min, $max] = $this->parseRawMinMax($value, $fieldName, $allowIndexedArray);

		$min = (int) $min;
		$max = (int) $max;

		if(!$allowNegative && $min < 0){
			throw new SavedDataLoadingException("Min cannot be less than 0 in \"$fieldName\"");
		}
		return [$min, $max];
	}

	/**
	 * Parses a min/max range as floats.
	 *
	 * @return array{float, float}
	 */
	private function parseFloatMinMax(mixed $value, string $fieldName, bool $allowIndexedArray = false) : array{
		[$min, $max] = $this->parseRawMinMax($value, $fieldName, $allowIndexedArray);
		return [(float) $min, (float) $max];
	}
}
