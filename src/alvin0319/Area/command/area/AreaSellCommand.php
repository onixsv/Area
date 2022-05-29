<?php

declare(strict_types=1);

namespace alvin0319\Area\command\area;

use alvin0319\Area\AreaLoader;
use alvin0319\Area\form\AreaSellConfirmForm;
use OnixUtils\OnixUtils;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use function strtolower;

class AreaSellCommand extends Command{

	public function __construct(){
		parent::__construct("땅 판매", "보유중인 땅을 판매합니다.");
		$this->setPermission("area.command.area.sell");
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
		if($area->getOwner() !== strtolower($sender->getName())){
			OnixUtils::message($sender, "땅 주인만 실행 가능한 명령어입니다.");
			return false;
		}

		$sender->sendForm(new AreaSellConfirmForm($area));
		return true;
	}
}