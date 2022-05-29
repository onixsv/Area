<?php

declare(strict_types=1);

namespace alvin0319\Area\generator;

use alvin0319\Area\AreaLoader;
use OnixUtils\OnixUtils;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\data\bedrock\BiomeIds;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\timings\TimingsHandler;
use pocketmine\world\biome\BiomeRegistry;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\BiomeArray;
use pocketmine\world\format\Chunk;
use pocketmine\world\generator\Generator;
use pocketmine\world\generator\GeneratorManager;
use pocketmine\world\World;
use function round;
use function strtolower;
use function var_dump;

class IslandGenerator extends Generator{
	/** @var TimingsHandler */
	protected static TimingsHandler $islandCreateTimings;

	public function getName() : string{
		return "Island";
	}

	public function generateChunk(ChunkManager $world, int $chunkX, int $chunkZ) : void{
		$chunk = $world->getChunk($chunkX, $chunkZ);
		$created = false;
		if($chunk === null){
			$chunk = new Chunk([], BiomeArray::fill(BiomeIds::PLAINS), false);
			$created = true;
		}
		for($x = 0; $x < 16; $x++){
			for($z = 0; $z < 16; $z++){
				$chunk->setFullBlock($x, 0, $z, VanillaBlocks::BEDROCK()->getFullId());
				$chunk->setFullBlock($x, 1, $z, VanillaBlocks::DIRT()->getFullId());
				$chunk->setFullBlock($x, 2, $z, VanillaBlocks::DIRT()->getFullId());
				for($i = 3; $i < 6; $i++){
					$chunk->setFullBlock($x, $i, $z, VanillaBlocks::WATER()->getFullId());
				}
			}
		}
		if($created){
			$world->setChunk($chunkX, $chunkZ, $chunk);
		}
	}

	public function populateChunk(ChunkManager $world, int $chunkX, int $chunkZ) : void{
		$chunk = $world->getChunk($chunkX, $chunkZ);
		if($chunk === null)
			return;
		$biome = BiomeRegistry::getInstance()->getBiome(BiomeIds::OCEAN);
		$biome->populateChunk($world, $chunkX, $chunkZ, $this->random);
	}

	public static function generate(Player $owner, $world) : void{
		if(empty(self::$islandCreateTimings)){
			self::$islandCreateTimings = new TimingsHandler("Island creation");
		}
		self::$islandCreateTimings->startTiming();
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
		$world = Server::getInstance()->getWorldManager()->getWorldByName($world);

		$area = AreaLoader::getInstance()->getAreaManager()->addArea(new Vector3($startX, 0, $startZ), new Vector3($endX, 0, $endZ), strtolower($owner->getName()), $world);

		if($area === null){
			var_dump($area);
			OnixUtils::message($owner, "섬 생성에 실패했습니다.");
			return;
		}
		$center = $area->getCenter();

		$chunk = $world->loadChunk($area->getCenter()->getFloorX() >> 4, $area->getCenter()->getFloorZ() >> 4);
		if($chunk === null){
			$chunk = new Chunk([], BiomeArray::fill(BiomeIds::PLAINS), false);
			$world->setChunk($area->getCenter()->getFloorX() >> 4, $area->getCenter()->getFloorZ() >> 4, $chunk);
		}

		AreaLoader::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use ($center, $world, $defaultFarmSize) : void{
			$startX = (int) round($center->x - ($defaultFarmSize / 2));
			$endX = (int) round($center->x + ($defaultFarmSize / 2));
			$startZ = (int) round($center->z - ($defaultFarmSize / 2));
			$endZ = (int) round($center->z + ($defaultFarmSize / 2));

			$class = GeneratorManager::getInstance()->getGenerator("island");
			/** @var IslandGenerator $generator */
			$generator = new $class(404, []);

			$generator->generateChunk($world, $startX >> 4, $startZ >> 4);
			$generator->generateChunk($world, $endX >> 4, $endZ >> 4);

			for($x = $startX; $x <= $endX; $x++){
				for($z = $startZ; $z <= $endZ; $z++){
					if($x % 6 === 0 && $z % 6 === 0){
						$generator->generateChunk($world, $x >> 4, $z >> 4);
					}
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
					$center->y = 6;
					$world->setBlock($center, BlockFactory::getInstance()->get(BlockLegacyIds::GRASS, 0));
				}
			}
			self::$islandCreateTimings->stopTiming();
		}), 20);

		OnixUtils::message($owner, "{$area->getId()}번 섬을 구매했습니다.");
	}
}