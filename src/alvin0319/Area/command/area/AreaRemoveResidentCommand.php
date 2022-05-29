<?php

declare(strict_types=1);

namespace alvin0319\Area\command\area;

use alvin0319\Area\AreaLoader;
use OnixUtils\OnixUtils;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use function count;

class AreaRemoveResidentCommand extends Command{

	public function __construct(){
		parent::__construct("땅 추방", "거주자를 추방합니다.");
		$this->setPermission("area.command.area.removeshare");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : bool{
		if(!$this->testPermission($sender)){
			return false;
		}
		if(!$sender instanceof Player){
			return false;
		}

		$area = AreaLoader::getInstance()->getAreaManager()->getArea($sender->getPosition()->asVector3(), $sender->getWorld());
		if($area === null){
			OnixUtils::message($sender, "해당 위치에서 땅을 찾을 수 없습니다.");
			return false;
		}
		if(!$area->isOwner($sender)){
			OnixUtils::message($sender, "당신은 이 땅의 주인이 아닙니다.");
			return false;
		}
		if(count($args) < 1){
			OnixUtils::message($sender, "사용법: /땅 추방 [유저]");
			return false;
		}
		if(!$area->isResident($args[0])){
			OnixUtils::message($sender, "{$args[0]}님은 이 땅의 거주자가 아닙니다.");
			return false;
		}
		$area->removeResident($args[0]);
		OnixUtils::message($sender, "{$args[0]}님을 이 땅의 거주자에서 제거했습니다.");
		return true;
	}
}