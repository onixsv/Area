<?php

declare(strict_types=1);

namespace alvin0319\Area\command\skyisland;

use OnixUtils\OnixUtils;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\world\generator\GeneratorManager;

class SkyIslandStartCommand extends Command{

	public function __construct(){
		parent::__construct("하늘섬 시작", "하늘섬을 시작합니다.");
		$this->setPermission("area.command.skyisland.start");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return false;
		}
		if($sender->getServer()->getWorldManager()->isWorldLoaded("skyisland")){
			OnixUtils::message($sender, "하늘섬이 이미 시작되있습니다.");
			return false;
		}
		$sender->getServer()->getWorldManager()->generateWorld("skyisland", 404, GeneratorManager::getInstance()->getGenerator("skyisland"));
		OnixUtils::message($sender, "하늘섬 월드 생성에 성공했습니다.");
		return true;
	}
}