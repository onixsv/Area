<?php

declare(strict_types=1);

namespace alvin0319\Area\command\area;

use alvin0319\Area\area\AreaProperties;
use alvin0319\Area\AreaLoader;
use OnixUtils\OnixUtils;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class AreaSettingCommand extends Command{

	public function __construct(){
		parent::__construct("땅 설정", "땅을 설정합니다.");
		$this->setPermission("area.command.area.setting");
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
		switch($args[0] ?? "x"){
			case "pvp":
			case "유저간전투허용":
				$v = $area->getAreaProperties()->getAllowPvp();
				$area->getAreaProperties()->set(AreaProperties::PVP, !$v);
				OnixUtils::message($sender, "땅의 유저간전투허용을 " . ($v ? "비" : "") . "허용으로 바꿨습니다.");
				break;
			case "보호":
				$v = $area->getAreaProperties()->isProtect();
				$area->getAreaProperties()->set(AreaProperties::PROTECT, !$v);
				OnixUtils::message($sender, "땅의 보호를 " . ($v ? "비" : "") . "보호로 바꿨습니다.");
				break;
			case "접근":
				$v = $area->getAreaProperties()->getAllowEnter();
				$area->getAreaProperties()->set(AreaProperties::ENTER, !$v);
				OnixUtils::message($sender, "땅의 접근을 " . ($v ? "비" : "") . "허용으로 바꿨습니다.");
				break;
			case "메시지":
				$area->getAreaProperties()->set(AreaProperties::MESSAGE, $res = $args[1] ?? "땅에 오신것을 환영합니다.");
				OnixUtils::message($sender, "땅의 메시지를 {$res}(으)로 바꿨습니다.");
				break;
			default:
				foreach([
					["유저간전투허용", "유저간 전투 허용을 관리합니다."],
					["보호", "땅의 보호를 관리합니다."],
					["접근", "땅의 접근을 관리합니다."],
					["메시지 [메시지(옵션)]", "땅의 메시지를 설정합니다."]
				] as $usage){
					OnixUtils::message($sender, "/땅 설정 " . $usage[0] . " - " . $usage[1]);
				}
		}
		return true;
	}
}