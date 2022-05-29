<?php

namespace alvin0319\Area\generator;

use pocketmine\world\ChunkManager;
use pocketmine\world\generator\Generator;
use function array_pad;
use function array_reverse;

class SuperFlatGenerator extends Generator{

	public const BASE_LAYER = [
		[7, 0],
		[7, 0],
		[1, 0],
		[1, 0],
		[1, 0],
		[1, 0],
		[1, 0],
		[1, 0],
		[1, 0],
		[1, 0],
		[1, 0],
		[1, 0],
		[3, 0],
		[3, 0],
		[3, 0],
		[3, 0]
	];
	public const ROAD_BLOCK = [1, 4];
	public const LAND_EDGE_BLOCK = [43, 0];
	public const LAND_BLOCK = [2, 0];

	public const ROAD_FLAG = 1;
	public const LAND_EDGE_FLAG = 2;
	public const LAND_FLAG = 3;

	public function getName() : string{
		return "SuperFlatGenerator";
	}

	public function generateChunk(ChunkManager $world, int $chunkX, int $chunkZ) : void{
		$xOrder = array_pad([
			self::ROAD_FLAG,
			self::ROAD_FLAG,
			self::ROAD_FLAG,
			self::LAND_EDGE_FLAG
		], 16, self::LAND_FLAG);
		$zOrder = array_pad([
			self::ROAD_FLAG,
			self::ROAD_FLAG,
			self::ROAD_FLAG,
			self::LAND_EDGE_FLAG
		], 16, self::LAND_FLAG);

		if($chunkX % 2 != 0){
			$xOrder = array_reverse($xOrder);
		}
		if($chunkZ % 2 != 0){
			$zOrder = array_reverse($zOrder);
		}

		$chunk = $world->getChunk($chunkX, $chunkZ);

		// Create Chunk
		for($x = 0; $x < 16; $x++){
			for($z = 0; $z < 16; $z++){
				// Create base layer
				$y = 0;
				foreach(self::BASE_LAYER as $block){
					$fullId = ($block[0] << 4) | $block[1];
					$chunk->setFullBlock($x, $y, $z, $fullId);
					$y++;
				}

				if($xOrder[$x] == self::ROAD_FLAG || $zOrder[$z] == self::ROAD_FLAG){
					$chunk->setFullBlock($x, $y, $z, (self::ROAD_BLOCK[0] << 4) | self::ROAD_BLOCK[1]);
				}elseif($xOrder[$x] == self::LAND_EDGE_FLAG || $zOrder[$z] == self::LAND_EDGE_FLAG){
					$chunk->setFullBlock($x, $y, $z, (self::LAND_EDGE_BLOCK[0] << 4) | self::LAND_EDGE_BLOCK[1]);
				}else{
					$chunk->setFullBlock($x, $y, $z, (self::LAND_BLOCK[0] << 4) | self::LAND_BLOCK[1]);
				}
			}
		}
	}

	public function populateChunk(ChunkManager $world, int $chunkX, int $chunkZ) : void{
	}
}