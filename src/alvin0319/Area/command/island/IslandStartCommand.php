<?php

declare(strict_types=1);

namespace alvin0319\Area\command\island;

use OnixUtils\OnixUtils;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\world\generator\GeneratorManager;

class IslandStartCommand extends Command{

	public function __construct(){
		parent::__construct("섬 시작", "섬을 시작합니다.");
		$this->setPermission("area.command.island.start");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return false;
		}
		if($sender->getServer()->getWorldManager()->isWorldLoaded("island")){
			OnixUtils::message($sender, "섬이 이미 시작되있습니다.");
			return false;
		}
		$sender->getServer()->getWorldManager()->generateWorld("island", 404, GeneratorManager::getInstance()->getGenerator("island"));
		OnixUtils::message($sender, "섬 월드 생성에 성공했습니다.");
		return true;
	}
}