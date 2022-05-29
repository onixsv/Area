<?php

declare(strict_types=1);

namespace alvin0319\Area\command\skyblock;

use alvin0319\Area\AreaLoader;
use OnixUtils\OnixUtils;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use function strtolower;

class SkyBlockIncreaseCommand extends Command{

	public function __construct(){
		parent::__construct("스카이블럭 확장", "스카이블럭을 확장합니다.");
		$this->setPermission("area.command.skyblock.increase");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : bool{
		if(!$this->testPermission($sender)){
			return false;
		}
		if(!$sender instanceof Player){
			return false;
		}
		$skyBlock = AreaLoader::getInstance()->getSkyBlockManager()->getSkyBlock($sender->getWorld()->getFolderName());
		if($skyBlock === null){
			OnixUtils::message($sender, "스카이블럭을 찾을 수 없습니다.");
			return false;
		}
		if(strtolower($skyBlock->getOwner()) !== strtolower($sender->getName())){
			OnixUtils::message($sender, "당신은 이 스카이블럭의 주인이 아닙니다.");
			return false;
		}
		$skyBlock->increaseSize();
		return true;
	}
}