<?php

declare(strict_types=1);

namespace alvin0319\Area\command\area;

use alvin0319\Area\AreaLoader;
use OnixUtils\OnixUtils;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use function array_map;
use function implode;

class AreaInfoCommand extends Command{

	public function __construct(){
		parent::__construct("땅 정보", "땅의 정보를 확인합니다.");
		$this->setPermission("area.command.area.info");
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
		$pp = $area->getAreaProperties();
		$pvp = $pp->getAllowPvp() ? "§a허용" : "§c비허용";
		$protect = $pp->isProtect() ? "§a보호" : "§c비보호";
		$enter = $pp->getAllowEnter() ? "§a접근 허용" : "§c접근 비허용";
		$message = $pp->getMessage();
		$sender->sendMessage("§a- - - - - - - - - -");
		$sender->sendMessage("§a땅 번호§f: {$area->getId()}");
		$sender->sendMessage("§a땅 주인§f: {$area->getOwner()}");
		$sender->sendMessage("§a땅 거주자§f: " . implode(", ", array_map(function(string $player) use ($sender) : string{
				return ($sender->getServer()->getPlayerExact($player) !== null ? "§a{$player}" : "§0{$player}") . "§f";
			}, $area->getResidents())));
		$sender->sendMessage("§a전투§f: {$pvp}");
		$sender->sendMessage("§a보호§f: {$protect}");
		$sender->sendMessage("§a접근§f: {$enter}");
		$sender->sendMessage("§a메시지§f: {$message}");
		$sender->sendMessage("§a- - - - - - - - - -");
		return true;
	}
}