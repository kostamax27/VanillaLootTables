<?php

declare(strict_types=1);

namespace kostamax27\VanillaLootTables;

use pocketmine\player\Player;
use pocketmine\utils\Random;
use pocketmine\world\World;

class LootContext{
	private World $world;

	private mixed $origin;

	private ?Player $player;

	private Random $random;

	public function __construct(World $world, mixed $origin, ?Player $player = null, ?Random $random = null){
		$this->world = $world;
		$this->origin = $origin;
		$this->player = $player;
		$this->random = $random ?? new Random();
	}

	public function getWorld() : World{
		return $this->world;
	}

	/**
	 * Returns what caused the table to be generated.
	 * It could be:
	 * - a fishing rod
	 * - an entity at death
	 * - a chest from a structure
	 */
	public function getOrigin() : mixed{
		return $this->origin;
	}

	public function getPlayer() : ?Player{
		return $this->player;
	}

	public function getRandom() : Random{
		return $this->random;
	}
}
