<?php

declare(strict_types=1);

namespace alvin0319\Area\command\area;

use alvin0319\Area\AreaLoader;
use onebone\economyapi\EconomyAPI;
use OnixUtils\OnixUtils;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class AreaBuyCommand extends Command{

	public function __construct(){
		parent::__construct("땅 구매", "땅을 구매합니다.");
		$this->setPermission("area.command.area.buy");
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
		if($area->getOwner() !== ""){
			OnixUtils::message($sender, "이 땅은 현재 판매중이 아닙니다.");
			return false;
		}
		$world = AreaLoader::getInstance()->getWorldManager()->get($sender->getWorld());
		$price = $world->getPrice();
		if(EconomyAPI::getInstance()->myMoney($sender) < $price){
			OnixUtils::message($sender, "땅을 구매하기 위한 돈이 부족합니다. ({$price}원 필요)");
			return false;
		}
		if(!AreaLoader::getInstance()->getAreaManager()->canBuy($sender, $sender->getWorld())){
			OnixUtils::message($sender, "이미 땅을 최대치만큼 보유하고 있습니다.");
			return false;
		}
		EconomyAPI::getInstance()->reduceMoney($sender, $price);
		$area->setOwner($sender->getName());
		OnixUtils::message($sender, "{$area->getId()}번 땅을 구매했습니다.");
		return true;
	}
}