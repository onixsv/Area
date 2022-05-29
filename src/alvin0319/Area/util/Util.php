<?php

declare(strict_types=1);

namespace alvin0319\Area\util;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\math\Vector3;

class Util{

	/**
	 * @param Vector3 $center
	 *
	 * @return Block[]
	 */
	public static function createSphere(Vector3 $center) : array{
		$res = [];
		for($x = $center->getX() - 5; $x < $center->getX() + 5; $x++){
			for($z = $center->getZ() - 5; $z < $center->getZ() + 5; $z++){
				for($y = $center->getY(); $y < 9; $y++){
					if($y > 6){
						$b = BlockFactory::getInstance()->get(BlockLegacyIds::GRASS, 0);
						$b->getPosition()->x = $x;
						$b->getPosition()->y = $y;
						$b->getPosition()->z = $z;
						$res[] = $b;
					}elseif($y >= 0 && $y < 2){
						$b = BlockFactory::getInstance()->get(BlockLegacyIds::BEDROCK, 0);
						$b->getPosition()->x = $x;
						$b->getPosition()->y = $y;
						$b->getPosition()->z = $z;
						$res[] = $b;
					}else{
						$b = BlockFactory::getInstance()->get(BlockLegacyIds::STONE, 0);
						$b->getPosition()->x = $x;
						$b->getPosition()->y = $y;
						$b->getPosition()->z = $z;
						$res[] = $b;
					}
				}
			}
		}
		return $res;
	}
}