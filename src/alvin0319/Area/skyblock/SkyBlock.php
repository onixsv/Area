<?php

declare(strict_types=1);

namespace alvin0319\Area\skyblock;

use alvin0319\Area\area\AreaProperties;
use onebone\economyapi\EconomyAPI;
use OnixUtils\OnixUtils;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\Position;
use function array_search;
use function array_values;
use function in_array;
use function max;
use function min;
use function strtolower;

class SkyBlock{

	public const MONEY_PER_INCREASE = 10000;

	public const SIZE_PER_INCREASE = 10;

	protected $x1;

	protected $x2;

	protected $z1;

	protected $z2;

	protected $world;

	protected $owner;
	/** @var string[] */
	protected $residents = [];
	/** @var int */
	protected $increaseLevel = 1;
	/** @var AreaProperties */
	protected $properties;

	public function __construct(int $x1, int $x2, int $z1, int $z2, string $world, string $owner, array $residents, int $increaseLevel, AreaProperties $properties){
		$this->x1 = $x1;
		$this->x2 = $x2;
		$this->z1 = $z1;
		$this->z2 = $z2;
		$this->world = $world;
		$this->owner = $owner;
		$this->residents = $residents;
		$this->increaseLevel = $increaseLevel;
		$this->properties = $properties;
	}

	public function getOwner() : string{
		return $this->owner;
	}

	public function getWorld() : string{
		return $this->world;
	}

	public function getResidents() : array{
		return $this->residents;
	}

	public function getIncreaseLevel() : int{
		return $this->increaseLevel;
	}

	public function getProperties() : AreaProperties{
		return $this->properties;
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

	public function getCenter() : Vector3{
		$xSize = $this->getMaxX() - $this->getMinX();
		$zSize = $this->getMaxZ() - $this->getMinZ();
		$x = $this->getMinX() + ($xSize / 2);
		$z = $this->getMinZ() + ($zSize / 2);
		$server = Server::getInstance();
		$world = $server->getWorldManager()->getWorldByName($this->world);
		if(!$world->isChunkLoaded($x >> 4, $z >> 4)){
			$world->loadChunk($x >> 4, $z >> 4);
		}
		$y = $world->getHighestBlockAt($x, $z);
		return new Vector3($x, $y, $z);
	}

	public function jsonSerialize() : array{
		return [
			"x1" => $this->x1,
			"x2" => $this->x2,
			"z1" => $this->z1,
			"z2" => $this->z2,
			"world" => $this->world,
			"owner" => $this->owner,
			"residents" => $this->residents,
			"properties" => $this->properties->jsonSerialize(),
			"increaseLevel" => $this->increaseLevel
		];
	}

	public static function jsonDeserialize(array $data) : SkyBlock{
		return new SkyBlock($data["x1"], $data["x2"], $data["z1"], $data["z2"], $data["world"], $data["owner"], $data["residents"], $data["increaseLevel"], new AreaProperties($data["properties"]));
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

	public function increaseSize() : void{
		$needMoney = $this->increaseLevel * (self::SIZE_PER_INCREASE * self::MONEY_PER_INCREASE);
		$owner = Server::getInstance()->getPlayerExact($this->owner);
		if($owner === null){
			return;
		}
		if(EconomyAPI::getInstance()->myMoney($this->owner) < $needMoney){
			OnixUtils::message($owner, "스카이블럭을 확장할 돈이 부족합니다.");
			return;
		}
		EconomyAPI::getInstance()->reduceMoney($owner, $needMoney);
		$this->increaseLevel += 1;

		$minX = $this->getMinX();
		$maxX = $this->getMaxX();
		$minZ = $this->getMinZ();
		$maxZ = $this->getMaxZ();

		$this->x1 = $minX - 10;
		$this->x2 = $maxX + 10;
		$this->z1 = $minZ - 10;
		$this->z2 = $maxZ + 10;
		OnixUtils::message($owner, "스카이블럭을 확장했습니다.");
	}

	public function moveTo(Player $player) : void{
		$pos = Position::fromObject($this->getCenter(), $player->getServer()->getWorldManager()->getWorldByName($this->world));
		$player->teleport($pos);
	}

	public function isInArea(Position $pos) : bool{
		return ($this->getMinX() <= $pos->getX() && $this->getMaxX() >= $pos->getX()) && ($this->getMinZ() <= $pos->getZ() && $this->getMaxZ() >= $pos->getZ()) && $pos->getWorld()->getFolderName() === $this->world;
	}
}