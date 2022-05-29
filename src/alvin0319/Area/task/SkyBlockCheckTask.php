<?php

declare(strict_types=1);

namespace alvin0319\Area\task;

use alvin0319\Area\AreaLoader;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use function count;

class SkyBlockCheckTask extends Task{

	public function onRun() : void{
		$server = Server::getInstance();
		foreach(AreaLoader::getInstance()->getSkyBlockManager()->getSkyBlocks() as $skyBlock){
			if($server->getPlayerExact($skyBlock->getOwner()) === null){
				$world = $server->getWorldManager()->getWorldByName($skyBlock->getWorld());
				if($world !== null){
					if(count($world->getPlayers()) === 0){
						AreaLoader::getInstance()->getSkyBlockManager()->unloadSkyBlock($skyBlock);
					}
				}
			}
			AreaLoader::getInstance()->getSkyBlockManager()->saveSkyBlock($skyBlock);
		}
	}
}