<?php

declare(strict_types=1);

namespace alvin0319\Area\area;

use alvin0319\Area\AreaLoader;
use pocketmine\math\Vector3;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;
use pocketmine\world\World;
use function array_keys;
use function count;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function in_array;
use function is_dir;
use function json_decode;
use function json_encode;
use function max;
use function min;
use function mkdir;
use function strtolower;

class AreaManager{
	/** @var Area[][] */
	protected array $areas = [];
	/** @var AreaLoader */
	protected AreaLoader $plugin;
	/** @var array */
	protected array $data = [];

	public function __construct(AreaLoader $plugin){
		$this->plugin = $plugin;
	}

	public function addArea(Vector3 $start, Vector3 $end, string $owner, $world) : ?Area{
		if($world instanceof World){
			$world = $world->getFolderName();
		}
		if($this->checkOverlap($start, $end, $world)){
			return null;
		}
		$id = $this->getIndex($world);
		$this->increaseIndex($world);
		$area = new Area($id, $start->getX(), $end->getX(), $start->getZ(), $end->getZ(), $world, $owner);
		$this->areas[$world][$id] = $area;
		return $area;
	}

	public function getArea(Vector3 $pos, $world) : ?Area{
		if($world instanceof World){
			$world = $world->getFolderName();
		}
		foreach($this->areas[$world] ?? [] as $id => $area){
			if(($pos->getX() >= $area->getMinX() && $pos->getX() <= $area->getMaxX()) && ($pos->getZ() >= $area->getMinZ() && $pos->getZ() <= $area->getMaxZ()) && $world === $area->getWorld()){
				return $area;
			}
		}
		return $this->loadArea($pos, $world);
	}

	public function getAreaById(int $id, $world) : ?Area{
		if($world instanceof World){
			$world = $world->getFolderName();
		}
		if(isset($this->areas[$world][$id])){
			return $this->areas[$world][$id];
		}
		return $this->loadAreaById($id, $world);
	}

	protected function loadArea(Vector3 $pos, $world) : ?Area{
		if($world instanceof World){
			$world = $world->getFolderName();
		}
		if(!isset($this->data[$world])){
			return null;
		}
		foreach(($this->data[$world] ?? ["id" => 0, "areas" => []])["areas"] as $id => $data){
			if(($pos->getX() >= min($data["x1"], $data["x2"])) && ($pos->getX() <= max($data["x1"], $data["x2"])) && ($pos->getZ() >= min($data["z1"], $data["z2"])) && ($pos->getZ() <= max($data["z1"], $data["z2"]))){
				$area = Area::jsonDeserialize($data);
				if(isset($this->areas[$area->getWorld()][$area->getId()])){
					return $this->areas[$area->getWorld()][$area->getId()];
				}
				return $this->areas[$area->getWorld()][$area->getId()] = $area;
			}
		}
		return null;
	}

	protected function loadAreaById(int $id, $world) : ?Area{
		if($world instanceof World){
			$world = $world->getFolderName();
		}
		if(isset($this->areas[$world][$id])){
			return $this->areas[$world][$id];
		}
		if(!isset($this->data[$world]["areas"][$id])){
			return null;
		}
		return $this->areas[$world][$id] = Area::jsonDeserialize($this->data[$world]["areas"][$id]);
	}

	private function checkOverlap(Vector3 $start, Vector3 $end, $world) : bool{
		if($world instanceof World){
			$world = $world->getFolderName();
		}
		foreach($this->data[$world]["areas"] as $id => $areaData){
			if((min($start->getX(), $end->getX()) >= min($areaData["x1"], $areaData["x2"]) && max($start->getX(), $end->getX()) <= max($areaData["x1"], $areaData["x2"])) && (min($start->getZ(), $end->getZ()) >= min($areaData["z1"], $areaData["z2"]) && max($start->getZ(), $end->getZ()) <= max($areaData["z1"], $areaData["z2"]))){
				return true;
			}
		}
		return false;
	}

	public function loadWorld(World $level) : void{
		if(!is_dir($dir = AreaLoader::getInstance()->getDataFolder() . "area/{$level->getFolderName()}")){
			mkdir($dir);
		}
		if(!file_exists($file = $dir . "/areas.json")){
			file_put_contents($file, json_encode([
				"id" => 0,
				"areas" => []
			]));
		}
		//if(!file_exists($file = AreaLoader::getInstance()->getDataFolder() . "area/{$level->getFolderName()}"))
		$this->data[$level->getFolderName()] = json_decode(file_get_contents($file), true);
	}

	public function unloadWorld(World $level) : void{
		if(!is_dir($dir = AreaLoader::getInstance()->getDataFolder() . "area/{$level->getFolderName()}")){
			mkdir($dir);
		}
		file_put_contents($dir . "/area.json", json_encode($this->data[$level->getFolderName()]));
		unset($this->data[$level->getFolderName()]);
	}

	public function getIndex($world) : int{
		if($world instanceof World){
			$world = $world->getFolderName();
		}
		if(!isset($this->data[$world]))
			return -1;
		return $this->data[$world]["id"];
	}

	public function canBuy(Player $player, $world) : bool{
		if($player->hasPermission(DefaultPermissions::ROOT_OPERATOR))
			return true;
		if($world instanceof World){
			$world = $world->getFolderName();
		}
		$data = AreaLoader::getInstance()->getWorldManager()->get($world);

		if($data->getAreaCount() <= count($this->getOwnAreas($player, $world))){
			return false;
		}
		return true;
	}

	public function increaseIndex($world) : void{
		if($world instanceof World){
			$world = $world->getFolderName();
		}
		$this->data[$world]["id"] = $this->getIndex($world) + 1;
	}

	/**
	 * @param      $player
	 * @param      $world
	 * @param bool $share
	 *
	 * @return Area[]
	 */
	public function getOwnAreas($player, $world, bool $share = false) : array{
		if($world instanceof World){
			$world = $world->getFolderName();
		}
		if($player instanceof Player){
			$player = strtolower($player->getName());
		}
		if(!isset($this->data[$world])){
			return [];
		}
		$res = [];
		foreach($this->areas[$world] ?? [] as $id => $area){
			if($area->getOwner() === $player || ($share && $area->isResident($player))){
				$res[] = $area;
			}
		}
		foreach($this->data[$world]["areas"] as $id => $data){
			if($data["owner"] === $player || ($share && in_array($player, $data["residents"]))){
				$area = $this->loadAreaById($id, $world);
				if($area === null || in_array($area, $res, true)){
					continue;
				}
				$res[] = $area;
			}
		}
		return $res;
	}

	public function save() : void{
		foreach(array_keys($this->areas) as $world){
			if(!is_dir($dir = AreaLoader::getInstance()->getDataFolder() . "area/{$world}")){
				mkdir($dir);
			}
			$data = $this->data[$world];
			foreach($this->areas[$world] as $id => $area){
				$data["areas"][$area->getId()] = $area->jsonSerialize();
			}
			file_put_contents($dir . "/areas.json", json_encode($data));
			$this->data[$world] = $data;
		}
	}
}