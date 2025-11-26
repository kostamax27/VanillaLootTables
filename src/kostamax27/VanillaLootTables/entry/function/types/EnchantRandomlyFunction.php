<?php

declare(strict_types=1);

namespace kostamax27\VanillaLootTables\entry\function\types;

use kostamax27\VanillaLootTables\condition\LootCondition;
use kostamax27\VanillaLootTables\entry\function\EntryFunction;
use kostamax27\VanillaLootTables\LootContext;
use pocketmine\item\enchantment\AvailableEnchantmentRegistry;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use function count;

class EnchantRandomlyFunction extends EntryFunction{
	/**
	 * @param LootCondition[] $conditions
	 */
	public function __construct(private bool $treasureEnchants = false, array $conditions = []){
		parent::__construct($conditions);
	}

	public function onCreation(LootContext $context, Item $item) : Item{
		$registry = AvailableEnchantmentRegistry::getInstance();
		if($this->treasureEnchants){
			$enchants = $registry->getAllEnchantmentsForItem($item);
		}else{
			$enchants = $registry->getPrimaryEnchantmentsForItem($item);
		}
		if(count($enchants) !== 0){
			$item->addEnchantment(new EnchantmentInstance($enchants[$context->getRandom()->nextBoundedInt(count($enchants))]));
		}
		return parent::onCreation($context, $item);
	}

	/**
	 * Returns an array of properties that can be serialized to json.
	 *
	 * @phpstan-return array{
	 *    function: string,
	 *    treasure?: bool,
	 *    conditions?: array<array{condition: string, ...}>
	 * }
	 */
	public function jsonSerialize() : array{
		$data = parent::jsonSerialize();

		if($this->treasureEnchants){
			$data["treasure"] = true;
		}

		return $data;
	}
}
