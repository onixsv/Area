<?php

declare(strict_types=1);

namespace alvin0319\Area\area;

use pocketmine\data\bedrock\BiomeIds;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\format\BiomeArray;
use pocketmine\world\format\Chunk;
use pocketmine\world\Position;
use function array_search;
use function array_values;
use function in_array;
use function max;
use function min;
use function strtolower;

class Area{
	/** @var int */
	protected int $id;
	/** @var int */
	protected int $x1;
	/** @var int */
	protected int $x2;
	/** @var int */
	protected int $z1;
	/** @var int */
	protected int $z2;
	/** @var string */
	protected string $world;
	/** @var string */
	protected string $owner;
	/** @var array */
	protected array $residents = [];
	/** @var AreaProperties */
	protected AreaProperties $areaProperties;

	public function __construct(int $id, int $x1, int $x2, int $z1, int $z2, string $world, string $owner, array $residents = [], ?AreaProperties $properties = null){
		$this->id = $id;
		$this->x1 = $x1;
		$this->x2 = $x2;
		$this->z1 = $z1;
		$this->z2 = $z2;
		$this->world = $world;
		$this->owner = $owner;
		$this->residents = $residents;
		$this->areaProperties = $properties ?? new AreaProperties();
	}

	public function getId() : int{
		return $this->id;
	}

	public function getWorld() : string{
		return $this->world;
	}

	public function getMinX() : int{
		return min($this->x1, $this->x2);
	}

	public function getMaxX() : int{
		return max($this->x1, $this->x2);
	}

	public function getMinZ() : int{
		return min($this->z1, $this->z2);
	}

	public function getMaxZ() : int{
		return max($this->z1, $this->z2);
	}

	public function getOwner() : string{
		return $this->owner;
	}

	public function getResidents() : array{
		return $this->residents;
	}

	public function getAreaProperties() : AreaProperties{
		return $this->areaProperties;
	}

	public function setOwner(string $owner) : void{
		$this->owner = strtolower($owner);
	}

	public function addResident(string $resident) : void{
		if(!in_array($resident = strtolower($resident), $this->residents)){
			$this->residents[] = $resident;
		}
	}

	public function removeResident(string $resident) : void{
		if(in_array($resident = strtolower($resident), $this->residents)){
			unset($this->residents[array_search($resident, $this->residents)]);
			$this->residents = array_values($this->residents);
		}
	}

	public function isResident(string $resident) : bool{
		return in_array(strtolower($resident), $this->residents) || $this->owner === strtolower($resident);
	}

	public function canBuy() : bool{
		return $this->owner === "";
	}

	public function isOwner($player) : bool{
		if($player instanceof Player){
			$player = strtolower($player->getName());
		}
		return $this->owner === $player;
	}

	public function getCenter() : Vector3{
		$xSize = $this->getMaxX() - $this->getMinX();
		$zSize = $this->getMaxZ() - $this->getMinZ();
		$x = $this->getMinX() + ($xSize / 2);
		$z = $this->getMinZ() + ($zSize / 2);
		$server = Server::getInstance();
		$world = $server->getWorldManager()->getWorldByName($this->world);
		$chunk = $world->loadChunk($x >> 4, $z >> 4);
		if($chunk === null){
			$chunk = new Chunk([], BiomeArray::fill(BiomeIds::PLAINS), false);
			$world->setChunk($x >> 4, $z >> 4, $chunk);
		}
		$y = $world->getHighestBlockAt((int) $x, (int) $z);
		return new Vector3($x, $y + 1, $z);
	}

	public function moveTo(Player $player) : void{
		$pos = Position::fromObject($this->getCenter(), $player->getServer()->getWorldManager()->getWorldByName($this->world));
		$player->teleport($pos);
	}

	public function jsonSerialize() : array{
		return [
			"id" => $this->id,
			"x1" => $this->x1,
			"x2" => $this->x2,
			"z1" => $this->z1,
			"z2" => $this->z2,
			"world" => $this->world,
			"owner" => $this->owner,
			"residents" => $this->residents,
			"properties" => $this->areaProperties->jsonSerialize()
		];
	}

	public static function jsonDeserialize(array $data) : Area{
		return new Area($data["id"], $data["x1"], $data["x2"], $data["z1"], $data["z2"], $data["world"], $data["owner"], $data["residents"], new AreaProperties($data["properties"]));
	}

	public function equals(Area $that) : bool{
		return $this->getCenter()->equals($that->getCenter()) && $this->getId() === $that->getId() && $this->getOwner() === $that->getOwner() && $this->getWorld() === $that->getWorld();
	}
}