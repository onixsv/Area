<?php

declare(strict_types=1);

namespace alvin0319\Area\command\skyblock;

use alvin0319\Area\area\AreaProperties;
use alvin0319\Area\AreaLoader;
use OnixUtils\OnixUtils;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class SkyBlockSettingCommand extends Command{

	public function __construct(){
		parent::__construct("스카이블럭 설정", "스카이블럭 설정을 관리합니다.");
		$this->setPermission("area.command.skyblock.setting");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : bool{
		if(!$this->testPermission($sender)){
			return false;
		}
		if(!$sender instanceof Player){
			return false;
		}
		if(!AreaLoader::getInstance()->getSkyBlockManager()->hasSkyBlock($sender)){
			OnixUtils::message($sender, "스카이블럭을 소유중이지 않습니다.");
			return false;
		}
		$skyBlock = AreaLoader::getInstance()->getSkyBlockManager()->getSkyBlockByPlayer($sender);
		switch($args[0] ?? "x"){
			case "pvp":
			case "유저간전투허용":
				$v = $skyBlock->getProperties()->getAllowPvp();
				$skyBlock->getProperties()->set(AreaProperties::PVP, !$v);
				OnixUtils::message($sender, "스카이블럭의 유저간전투허용을 " . ($v ? "비" : "") . "허용으로 바꿨습니다.");
				break;
			case "보호":
				$v = $skyBlock->getProperties()->isProtect();
				$skyBlock->getProperties()->set(AreaProperties::PROTECT, !$v);
				OnixUtils::message($sender, "스카이블럭의 보호를 " . ($v ? "비" : "") . "보호로 바꿨습니다.");
				break;
			case "접근":
				$v = $skyBlock->getProperties()->getAllowEnter();
				$skyBlock->getProperties()->set(AreaProperties::ENTER, !$v);
				OnixUtils::message($sender, "스카이블럭의 접근을 " . ($v ? "비" : "") . "허용으로 바꿨습니다.");
				break;
			case "메시지":
				$skyBlock->getProperties()->set(AreaProperties::MESSAGE, $res = $args[1] ?? "스카이블럭에 오신것을 환영합니다.");
				OnixUtils::message($sender, "스카이블럭의 메시지를 {$res}(으)로 바꿨습니다.");
				break;
			default:
				foreach([
					["유저간전투허용", "유저간 전투 허용을 관리합니다."],
					["보호", "스카이블럭의 보호를 관리합니다."],
					["접근", "스카이블럭의 접근을 관리합니다."],
					["메시지 [메시지(옵션)]", "스카이블럭의 메시지를 설정합니다."]
				] as $usage){
					OnixUtils::message($sender, "/스카이블럭 설정" . $usage[0] . " - " . $usage[1]);
				}
		}
		return true;
	}
}