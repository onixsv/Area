<?php

declare(strict_types=1);

namespace alvin0319\Area\skyblock;

use alvin0319\Area\AreaLoader;
use alvin0319\Area\task\SkyBlockCreateAsyncTask;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\World;
use function array_values;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function is_dir;
use function json_decode;
use function json_encode;
use function mkdir;
use function strtolower;

class SkyBlockManager{
	/** @var SkyBlock[] */
	protected array $skyBlocks = [];

	public function __construct(){
		if(!is_dir($dir = AreaLoader::getInstance()->getDataFolder() . "skyblock/")){
			mkdir($dir);
		}
	}

	public function createSkyBlock(Player $owner) : void{
		if(!$owner->getServer()->getWorldManager()->isWorldLoaded("skyblock")){
			return;
		}
		if($owner->getServer()->getWorldManager()->isWorldGenerated("skyblock." . strtolower($owner->getName()))){
			if(!$owner->getServer()->getWorldManager()->isWorldLoaded("skyblock." . strtolower($owner->getName()))){
				$owner->getServer()->getWorldManager()->loadWorld("skyblock." . strtolower($owner->getName()));
			}
			$this->loadSkyBlock($owner);
			return;
		}
		$owner->getServer()->getAsyncPool()->submitTask(new SkyBlockCreateAsyncTask($owner->getName(), $owner->getServer()->getDataPath() . "worlds/"));
	}

	public function loadSkyBlock($player) : ?SkyBlock{
		if($player instanceof Player){
			$player = strtolower($player->getName());
		}
		$server = Server::getInstance();
		if(!$server->getWorldManager()->isWorldGenerated("skyblock.{$player}")){
			return null;
		}
		if($server->getWorldManager()->isWorldGenerated("skyblock.{$player}")){
			if(!$server->getWorldManager()->isWorldLoaded("skyblock.{$player}")){
				$server->getWorldManager()->loadWorld("skyblock.{$player}");
			}
			if(!file_exists($file = AreaLoader::getInstance()->getDataFolder() . "skyblock/{$player}.json")){
				return null;
			}
			$skyBlock = SkyBlock::jsonDeserialize(json_decode(file_get_contents($file), true));
			return $this->skyBlocks[$skyBlock->getWorld()] = $skyBlock;
		}
		return null;
	}

	public function hasSkyBlock($player) : bool{
		if($player instanceof Player){
			$player = strtolower($player->getName());
		}
		return file_exists(AreaLoader::getInstance()->getDataFolder() . "skyblock/{$player}.json") || isset($this->skyBlocks["skyblock." . $player]);
	}

	public function getSkyBlock($world) : ?SkyBlock{
		if($world instanceof World){
			$world = $world->getFolderName();
		}
		return $this->skyBlocks[$world] ?? null;
	}

	public function getSkyBlockByPlayer(Player $player) : ?SkyBlock{
		return $this->getSkyBlock("skyblock." . strtolower($player->getName()));
	}

	public function registerSkyBlock(SkyBlock $skyBlock) : void{
		$this->skyBlocks[$skyBlock->getWorld()] = $skyBlock;
	}

	public function saveSkyBlock(SkyBlock $skyBlock) : void{
		//if(!is_dir($dir = AreaLoader::getInstance()->getDataFolder() . "skyblock/{$skyBlock->getOwner()}"))
		file_put_contents(AreaLoader::getInstance()->getDataFolder() . "skyblock/{$skyBlock->getOwner()}.json", json_encode($skyBlock->jsonSerialize()));
	}

	public function unloadSkyBlock(SkyBlock $skyBlock) : void{
		if(isset($this->skyBlocks[$skyBlock->getWorld()])){
			$this->saveSkyBlock($skyBlock);
			unset($this->skyBlocks[$skyBlock->getWorld()]);
			$world = Server::getInstance()->getWorldManager()->getWorldByName($skyBlock->getWorld());
			if($world !== null){
				Server::getInstance()->getWorldManager()->unloadWorld($world);
			}
		}
	}

	/**
	 * @return SkyBlock[]
	 */
	public function getSkyBlocks() : array{
		return array_values($this->skyBlocks);
	}

	public function save() : void{
		foreach($this->getSkyBlocks() as $skyBlock){
			$this->saveSkyBlock($skyBlock);
		}
	}
}