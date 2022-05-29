<?php

declare(strict_types=1);

namespace alvin0319\Area\command\area;

use alvin0319\Area\AreaLoader;
use OnixUtils\OnixUtils;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use function count;
use function strtolower;

class AreaTransferCommand extends Command{

	public function __construct(){
		parent::__construct("땅 양도", "땅을 양도합니다.");
		$this->setPermission("area.command.area.transfer");
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
			OnixUtils::message($sender, "사용법: /땅 양도 <닉네임>");
			return false;
		}
		if(($target = $sender->getServer()->getPlayerExact($args[0])) === null){
			OnixUtils::message($sender, "{$args[0]}님은 현재 온라인이 아닙니다.");
			return false;
		}
		if(strtolower($sender->getName()) === $args[0]){
			OnixUtils::message($sender, "자기 자신에게 땅 양도를 할 수 없습니다.");
			return false;
		}
		$area->setOwner($args[0]);
		OnixUtils::message($sender, "땅을 {$args[0]}님께 양도했습니다.");
		if($target !== null){
			OnixUtils::message($target, $sender->getName() . "님으로부터 {$area->getId()}번 땅을 양도받았습니다.");
		}
		return true;
	}
}