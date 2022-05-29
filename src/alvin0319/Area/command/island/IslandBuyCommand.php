<?php

declare(strict_types=1);

namespace alvin0319\Area\command\island;

use alvin0319\Area\AreaLoader;
use alvin0319\Area\generator\IslandGenerator;
use onebone\economyapi\EconomyAPI;
use OnixUtils\OnixUtils;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class IslandBuyCommand extends Command{

	public function __construct(){
		parent::__construct("섬 구매", "섬을 구매합니다.");
		$this->setPermission("area.command.island.buy");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : bool{
		if(!$this->testPermission($sender)){
			return false;
		}
		if(!$sender instanceof Player){
			return false;
		}
		if(!$sender->getServer()->getWorldManager()->isWorldGenerated("island")){
			OnixUtils::message($sender, "섬 월드가 생성되지 않았습니다.");
			return false;
		}
		if(!$sender->getServer()->getWorldManager()->isWorldLoaded("island")){
			$sender->getServer()->getWorldManager()->loadWorld("island");
		}
		$world = AreaLoader::getInstance()->getWorldManager()->get("island");
		$price = $world->getPrice();
		if(EconomyAPI::getInstance()->myMoney($sender) < $price){
			OnixUtils::message($sender, "섬을 구매하기 위한 돈이 부족합니다. ({$price}원 필요)");
			return false;
		}
		if(!AreaLoader::getInstance()->getAreaManager()->canBuy($sender, "island")){
			OnixUtils::message($sender, "이미 섬을 최대치만큼 보유하고 있습니다.");
			return false;
		}
		EconomyAPI::getInstance()->reduceMoney($sender, $price);
		IslandGenerator::generate($sender, "island");
		return true;
	}
}