<?php

declare(strict_types=1);

namespace alvin0319\Area\command\island;

use alvin0319\Area\area\Area;
use alvin0319\Area\AreaLoader;
use OnixUtils\OnixUtils;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use function array_map;
use function count;
use function implode;
use function is_numeric;

class IslandMoveCommand extends Command{

	public function __construct(){
		parent::__construct("섬 이동", "땅으로 이동합니다.");
		$this->setPermission("area.command.island.move");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return false;
		}
		if(!$sender instanceof Player){
			return false;
		}

		if(count($args) < 1){
			OnixUtils::message($sender, "사용법: /섬 이동 [번호|유저]");
			return false;
		}
		if(is_numeric($args[0])){
			$area = AreaLoader::getInstance()->getAreaManager()->getAreaById((int) $args[0], "island");
			if($area === null){
				OnixUtils::message($sender, "{$args[0]}번 섬이 존재하지 않습니다.");
				return false;
			}
			$area->moveTo($sender);
			OnixUtils::message($sender, "{$area->getId()}번 섬으로 이동했습니다.");
		}else{
			$areas = AreaLoader::getInstance()->getAreaManager()->getOwnAreas($args[0], "island");
			OnixUtils::message($sender, "{$args[0]}님이 소유한 섬 목록입니다.");
			$sender->sendMessage(implode(", ", array_map(function(Area $area) : string{
				return "§d<§f{$area->getId()}§d>§f";
			}, $areas)));
		}
		return true;
	}
}