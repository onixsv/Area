<?php

declare(strict_types=1);

namespace alvin0319\Area\command\skyblock;

use alvin0319\Area\AreaLoader;
use OnixUtils\OnixUtils;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use function count;

class SkyBlockAddResidentCommand extends Command{

	public function __construct(){
		parent::__construct("스카이블럭 공유", "스카이블럭을 공유합니다.");
		$this->setPermission("area.command.skyblock.share");
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
			OnixUtils::message($sender, "사용법: /스카이블럭 공유 [유저]");
			return false;
		}
		$skyBlock = AreaLoader::getInstance()->getSkyBlockManager()->getSkyBlockByPlayer($sender);
		if($skyBlock->isResident($args[0])){
			OnixUtils::message($sender, "해당 유저는 이미 스카이블럭의 거주자입니다.");
			return false;
		}
		if($sender->getServer()->getPlayerExact($args[0]) === null){
			OnixUtils::message($sender, "해당 유저가 온라인이 아닙니다.");
			return false;
		}
		$skyBlock->addResident($args[0]);
		OnixUtils::message($sender, "{$args[0]}님을 스카이블럭 거주자로 추가했습니다.");
		return true;
	}
}