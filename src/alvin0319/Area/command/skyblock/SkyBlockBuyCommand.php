<?php

declare(strict_types=1);

namespace alvin0319\Area\command\skyblock;

use alvin0319\Area\AreaLoader;
use onebone\economyapi\EconomyAPI;
use OnixUtils\OnixUtils;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class SkyBlockBuyCommand extends Command{

	public function __construct(){
		parent::__construct("스카이블럭 구매", "스카이블럭 구매 명령어입니다.");
		$this->setPermission("area.command.skyblock.buy");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : bool{
		if(!$this->testPermission($sender)){
			return false;
		}
		if(!$sender instanceof Player){
			return false;
		}
		if(AreaLoader::getInstance()->getSkyBlockManager()->hasSkyBlock($sender)){
			OnixUtils::message($sender, "이미 스카이블럭을 소유중입니다.");
			return false;
		}
		if(!$sender->getServer()->getWorldManager()->isWorldLoaded("skyblock")){
			OnixUtils::message($sender, "스카이블럭 월드가 설정되어있지 않습니다.");
			return false;
		}
		if(EconomyAPI::getInstance()->myMoney($sender) < 500000){
			OnixUtils::message($sender, "스카이블럭을 구매하기 위한 돈이 부족합니다. (50만원 필요)");
			return false;
		}
		EconomyAPI::getInstance()->reduceMoney($sender, 500000);
		AreaLoader::getInstance()->getSkyBlockManager()->createSkyBlock($sender);
		return true;
	}
}