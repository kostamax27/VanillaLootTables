<?php

declare(strict_types=1);

namespace kostamax27\VanillaLootTables\entry;

use kostamax27\VanillaLootTables\entry\function\EntryFunction;
use kostamax27\VanillaLootTables\LootContext;
use pocketmine\data\bedrock\item\ItemTypeDeserializeException;
use pocketmine\item\Item;
use pocketmine\utils\Utils;
use pocketmine\world\format\io\GlobalItemDataHandlers;
use function ceil;
use function min;

final class ItemStackData{
	public function __construct(
		public string $name,
	){}

	/**
	 * @param EntryFunction[] $functions
	 *
	 * @return Item[]
	 */
	public function generate(LootContext $context, array $functions = []) : array{
		Utils::validateArrayValueType($functions, function(EntryFunction $_) : void{});

		$meta = 0;
		$count = 1;

		$items = [];

		foreach($functions as $function){
			$function->onPreCreation($context, $meta, $count);
		}

		if($count > 0){
			try{
				//TODO: This will not deserialize >1.12 blocks
				$item = GlobalItemDataHandlers::getDeserializer()->deserializeStack(GlobalItemDataHandlers::getUpgrader()->upgradeItemTypeDataString(
					$this->name,
					$meta,
					$count,
					null
				));

				foreach($functions as $function){
					$item = $function->onCreation($context, $item);
				}

				//split up stacks
				$maxStackSize = $item->getMaxStackSize();
				$stacks = (int) ceil($count / $maxStackSize);
				if($stacks > 1){
					for($i = 0; $i < $stacks; $i++){
						$items[] = $item->pop(min($maxStackSize, $item->getCount()));
					}
				}else{
					$items[] = $item;
				}

				return $items;
			}catch(ItemTypeDeserializeException $e){
				//probably unknown item
			}
		}

		return $items;
	}
}
