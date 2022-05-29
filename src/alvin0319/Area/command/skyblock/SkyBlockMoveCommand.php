<?php

declare(strict_types=1);

namespace alvin0319\Area\command\skyblock;

use alvin0319\Area\AreaLoader;
use OnixUtils\OnixUtils;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use function strtolower;
use function trim;

class SkyBlockMoveCommand extends Command{

	public function __construct(){
		parent::__construct("스카이블럭 이동", "스카이블럭으로 이동합니다.");
		$this->setPermission("area.command.skyblock.move");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : bool{
		if(!$this->testPermission($sender)){
			return false;
		}
		if(!$sender instanceof Player){
			return false;
		}
		if(trim($args[0] ?? "") === ""){
			if(!AreaLoader::getInstance()->getSkyBlockManager()->hasSkyBlock($sender)){
				OnixUtils::message($sender, "스카이블럭을 소유중이지 않습니다.");
				return false;
			}
			$skyBlock = AreaLoader::getInstance()->getSkyBlockManager()->getSkyBlockByPlayer($sender);
			$skyBlock->moveTo($sender);
			OnixUtils::message($sender, "스카이블럭으로 이동했습니다.");
		}else{
			if(!AreaLoader::getInstance()->getSkyBlockManager()->hasSkyBlock($args[0])){
				OnixUtils::message($sender, "{$args[0]}님의 스카이블럭이 존재하지 않습니다.");
				return false;
			}
			$skyBlock = AreaLoader::getInstance()->getSkyBlockManager()->loadSkyBlock(strtolower($args[0]));
			$skyBlock->moveTo($sender);
			OnixUtils::message($sender, "{$args[0]}님의 스카이블럭으로 이동했습니다.");
		}
		return true;
	}
}