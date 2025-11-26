<?php

declare(strict_types=1);

namespace kostamax27\VanillaLootTables\entry;

use kostamax27\VanillaLootTables\entry\function\EntryFunction;
use kostamax27\VanillaLootTables\LootContext;
use kostamax27\VanillaLootTables\LootTable;
use kostamax27\VanillaLootTables\LootTableFactory;
use pocketmine\item\Item;
use pocketmine\utils\EnumTrait;
use function array_filter;
use function is_string;

/**
 * This doc-block is generated automatically, do not modify it manually.
 * This must be regenerated whenever registry members are added, removed or changed.
 *
 * @see build/generate-registry-annotations.php
 * @generate-registry-docblock
 *
 * @method static LootEntryType EMPTY()
 * @method static LootEntryType ITEM()
 * @method static LootEntryType LOOT_TABLE()
 */
final class LootEntryType{
	use EnumTrait {
		__construct as Enum___construct;
	}

	protected static function setup() : void{
		self::registerAll(
			new self("item", function(LootEntry $entry, LootContext $context) : array{
				$stack = $entry->getEntry();
				if(!$stack instanceof ItemStackData){
					throw new \InvalidArgumentException("Entry should be ItemStackData type");
				}
				return $stack->generate($context, array_filter($entry->getFunctions(), function(EntryFunction $function) use ($context){
					return $function->evaluateConditions($context);
				}));
			}),
			new self("loot_table", function(LootEntry $entry, LootContext $context) : array{
				$table = $entry->getEntry();
				if(is_string($table)){
					$table = LootTableFactory::getInstance()->get($table);
					if($table === null){
						throw new \InvalidArgumentException("LootTable \"" . $entry->getEntry() . "\" is not registered");
					}
				}
				if(!$table instanceof LootTable){
					throw new \InvalidArgumentException("Entry should be LootTable type or string reference");
				}
				return $table->generate($context);
			}),
			new self("empty", fn() => [])
		);
	}

	/**
	 * @phpstan-param Closure(LootEntry, LootContext) : Item[] $resultGetter
	 */
	private function __construct(
		string $enumName,
		private \Closure $resultGetter
	){
		$this->Enum___construct($enumName);
	}

	/**
	 * @return Item[]
	 */
	public function generate(LootEntry $entry, LootContext $context) : array{
		return ($this->resultGetter)($entry, $context);
	}
}
