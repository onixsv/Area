<?php

declare(strict_types=1);

namespace alvin0319\Area\generator;

use alvin0319\Area\AreaLoader;
use OnixUtils\OnixUtils;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\data\bedrock\BiomeIds;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\BiomeArray;
use pocketmine\world\format\Chunk;
use pocketmine\world\generator\Generator;
use pocketmine\world\World;
use function round;
use function strtolower;
use function var_dump;

class SkyIslandGenerator extends Generator{

	public function getName() : string{
		return "SkyIsland";
	}

	public function generateChunk(ChunkManager $world, int $chunkX, int $chunkZ) : void{
		// TODO: Implement generateChunk() method.
	}

	public function populateChunk(ChunkManager $world, int $chunkX, int $chunkZ) : void{
		// TODO: Implement populateChunk() method.
	}

	public static function generate(Player $owner, $world) : void{
		if($world instanceof World){
			$world = $world->getFolderName();
		}
		$index = AreaLoader::getInstance()->getAreaManager()->getIndex($world);
		$defaultAreaSize = 200;
		$defaultFarmSize = 16;
		$farmX = ($defaultAreaSize + 2) * (int) ($index / 1000);
		$farmZ = ($defaultAreaSize + 2) * ($index % 1000);

		$startX = (int) round($farmX - ($defaultAreaSize / 2));
		$endX = (int) round($farmX + ($defaultAreaSize / 2));
		$startZ = (int) round($farmZ - ($defaultAreaSize / 2));
		$endZ = (int) round($farmZ + ($defaultAreaSize / 2));

		$area = AreaLoader::getInstance()->getAreaManager()->addArea(new Vector3($startX, 0, $startZ), new Vector3($endX, 0, $endZ), strtolower($owner->getName()), $world);

		if($area === null){
			var_dump($area);
			OnixUtils::message($owner, "섬 생성에 실패했습니다.");
			return;
		}

		$world = Server::getInstance()->getWorldManager()->getWorldByName($world);

		$chunk = $world->loadChunk($area->getCenter()->getFloorX() >> 4, $area->getCenter()->getFloorZ() >> 4);
		if($chunk === null){
			$chunk = new Chunk([], BiomeArray::fill(BiomeIds::PLAINS), false);
			$world->setChunk($area->getCenter()->getFloorX() >> 4, $area->getCenter()->getFloorZ() >> 4, $chunk);
		}

		$center = $area->getCenter();

		AreaLoader::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use ($center, $world, $defaultFarmSize) : void{
			$startX = (int) round($center->x - ($defaultFarmSize / 2));
			$endX = (int) round($center->x + ($defaultFarmSize / 2));
			$startZ = (int) round($center->z - ($defaultFarmSize / 2));
			$endZ = (int) round($center->z + ($defaultFarmSize / 2));

			for($x = $startX; $x <= $endX; $x++){
				for($z = $startZ; $z <= $endZ; $z++){
					$chunk = $world->loadChunk($x >> 4, $z >> 4);
					if($chunk === null){
						$chunk = new Chunk([], BiomeArray::fill(BiomeIds::PLAINS), false);
						$world->setChunk($x >> 4, $z >> 4, $chunk);
					}
					if($world->isChunkLocked($x >> 4, $z >> 4)){
						$world->unlockChunk($x >> 4, $z >> 4, null);
					}
					if(!$chunk->isPopulated())
						$chunk->setPopulated(true);
					$center = new Vector3($x, 0, $z);
					$world->setBlock($center, BlockFactory::getInstance()->get(BlockLegacyIds::BEDROCK, 0));
					$center->y = 1;
					$world->setBlock($center, BlockFactory::getInstance()->get(BlockLegacyIds::BEDROCK, 0));
					$center->y = 2;
					$world->setBlock($center, BlockFactory::getInstance()->get(BlockLegacyIds::DIRT, 0));
					$center->y = 3;
					$world->setBlock($center, BlockFactory::getInstance()->get(BlockLegacyIds::GRASS, 0));
				}
			}
		}), 20);

		OnixUtils::message($owner, "{$area->getId()}번 섬을 구매했습니다.");
	}
}