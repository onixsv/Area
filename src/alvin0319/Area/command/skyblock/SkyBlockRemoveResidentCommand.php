<?php

declare(strict_types=1);

namespace alvin0319\Area\command\skyblock;

use alvin0319\Area\AreaLoader;
use OnixUtils\OnixUtils;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use function count;

class SkyBlockRemoveResidentCommand extends Command{

	public function __construct(){
		parent::__construct("스카이블럭 추방", "스카이블럭에 공유된 유저를 제거합니다.");
		$this->setPermission("area.command.skyblock.removeshare");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : bool{
		if(!$this->testPermission($sender)){
			return false;
		}
		if(!$sender instanceof Player){
			return false;
		}
		if(!AreaLoader::getInstance()->getSkyBlockManager()->hasSkyBlock($sender)){
			OnixUtils::message($sender, "스카이블럭을 소유중이지 않습니다.");
			return false;
		}
		if(count($args) < 1){
			OnixUtils::message($sender, "사용법: /스카이블럭 추방 [유저]");
			return false;
		}
		$skyBlock = AreaLoader::getInstance()->getSkyBlockManager()->getSkyBlockByPlayer($sender);
		if(!$skyBlock->isResident($args[0])){
			OnixUtils::message($sender, "해당 유저는 스카이블럭 거주자가 아닙니다.");
			return false;
		}
		$skyBlock->removeResident($args[0]);
		OnixUtils::message($sender, "{$args[0]}님을 스카이블럭 거주자에서 제거했습니다.");
		return true;
	}
}