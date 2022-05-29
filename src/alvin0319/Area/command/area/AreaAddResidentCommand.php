<?php

declare(strict_types=1);

namespace alvin0319\Area\command\area;

use alvin0319\Area\AreaLoader;
use OnixUtils\OnixUtils;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use function count;

class AreaAddResidentCommand extends Command{

	public function __construct(){
		parent::__construct("땅 공유", "땅을 공유합니다.");
		$this->setPermission("area.command.area.share");
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
			OnixUtils::message($sender, "사용법: /땅 공유 [유저]");
			return false;
		}
		if($sender->getServer()->getPlayerExact($args[0]) === null){
			OnixUtils::message($sender, "{$args[0]}님은 현재 온라인이 아닙니다.");
			return false;
		}
		if($area->isResident($args[0])){
			OnixUtils::message($sender, "{$args[0]}님은 이미 이 땅의 거주자입니다.");
			return false;
		}
		$area->addResident($args[0]);
		OnixUtils::message($sender, "{$args[0]}님을 이 땅의 거주자로 추가했습니다.");

		if(($player = $sender->getServer()->getPlayerExact($args[0])) !== null){
			OnixUtils::message($player, "{$sender->getName()}님으로부터 {$area->getId()}번 땅을 공유받았습니다.");
		}
		return true;
	}
}