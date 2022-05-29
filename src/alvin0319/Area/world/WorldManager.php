<?php

declare(strict_types=1);

namespace alvin0319\Area\world;

use alvin0319\Area\AreaLoader;
use pocketmine\world\World;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function is_dir;
use function json_decode;
use function json_encode;
use function mkdir;

class WorldManager{
	/** @var WorldData[] */
	protected array $worlds = [];
	/** @var AreaLoader */
	protected AreaLoader $plugin;

	public function __construct(AreaLoader $plugin){
		$this->plugin = $plugin;
	}

	public function get($world) : WorldData{
		$world = $world instanceof World ? $world->getFolderName() : $world;
		return $this->worlds[$world] ?? $this->worlds[$world] = new WorldData();
	}

	public function loadWorld(World $world) : void{
		if(isset($this->worlds[$world->getFolderName()]))
			return;
		if(!is_dir($dir = AreaLoader::getInstance()->getDataFolder() . "area/{$world->getFolderName()}")){
			mkdir($dir);
		}
		if(file_exists($file = $dir . "/world.json")){
			$data = json_decode(file_get_contents($file), true);
		}else{
			$data = null;
		}
		$data = new WorldData($data ?? WorldData::DEFAULTS);
		$this->worlds[$world->getFolderName()] = $data;
	}

	public function unloadWorld(World $world) : void{
		if(!isset($this->worlds[$world->getFolderName()]))
			return;
		if(!is_dir($dir = AreaLoader::getInstance()->getDataFolder() . "area/{$world->getFolderName()}")){
			mkdir($dir);
		}
		file_put_contents($dir . "/world.json", json_encode($this->worlds[$world->getFolderName()]->jsonSerialize()));
		unset($this->worlds[$world->getFolderName()]);
	}

	public function save() : void{
		foreach($this->worlds as $name => $world){
			if(!is_dir($dir = AreaLoader::getInstance()->getDataFolder() . "area/{$name}")){
				mkdir($dir);
			}
			file_put_contents($dir . "/world.json", json_encode($this->worlds[$name]->jsonSerialize()));
		}
	}
}