<?php

declare(strict_types=1);

namespace alvin0319\Area\command\world;

use alvin0319\Area\AreaLoader;
use OnixUtils\OnixUtils;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use function is_numeric;
use function mb_strpos;
use function trim;

class WorldManageCommand extends Command{

	public function __construct(){
		parent::__construct("월드관리", "월드를 관리합니다.");
		$this->setPermission("area.command.worldmanage");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : bool{
		if(!$this->testPermission($sender)){
			return false;
		}
		if(!$sender instanceof Player){
			return false;
		}
		if(mb_strpos($sender->getWorld()->getFolderName(), "skyblock.") !== false){
			OnixUtils::message($sender, "스카이블럭 월드에서는 월드 관리를 할 수 없습니다.");
			return false;
		}
		$data = AreaLoader::getInstance()->getWorldManager()->get($sender->getWorld());
		switch($args[0] ?? "x"){
			case "보호":
				$v = $data->isProtect();
				$data->setProtect(!$v);
				OnixUtils::message($sender, "{$sender->getWorld()->getFolderName()} 월드의 보호를 " . ($v ? "비" : "") . "보호로 설정했습니다.");
				break;
			case "pvp":
			case "유저간전투허용":
				$v = $data->getAllowPvP();
				$data->setAllowPvP(!$v);
				OnixUtils::message($sender, "{$sender->getWorld()->getFolderName()} 월드의 유저간 전투를 " . ($v ? "비" : "") . "허용으로 설정했습니다.");
				break;
			case "가격":
				if(trim($args[1] ?? "") === ""){
					OnixUtils::message($sender, "사용법: /월드관리 가격 [가격]");
					break;
				}
				if(!is_numeric($args[1]) || (int) $args[1] < 0){
					OnixUtils::message($sender, "가격은 정수여야 합니다.");
					break;
				}
				$data->setPrice((int) $args[1]);
				OnixUtils::message($sender, "{$sender->getWorld()->getFolderName()} 월드의 땅 가격을 {$args[1]}원으로 설정했습니다.");
				break;
			case "생성":
				$v = $data->getAllowAutoCreate();
				$data->setAllowAutoCreate(!$v);
				OnixUtils::message($sender, "{$sender->getWorld()->getFolderName()} 월드의 땅 생성을 " . ($v ? "비" : "") . "허용으로 설정했습니다.");
				break;
			case "수동생성":
				$v = $data->getAllowCreate();
				$data->setAllowCreate(!$v);
				OnixUtils::message($sender, "{$sender->getWorld()->getFolderName()} 월드의 땅 수동생성을 " . ($v ? "비" : "") . "허용으로 설정했습니다.");
				break;
			default:
				foreach([
					["보호", "월드의 보호를 설정합니다."],
					["유저간전투허용", "월드의 유저간 전투를 설정합니다."],
					["가격", "월드의 땅 가격을 설정합니다."],
					["생성", "월드의 땅 생성을 설정합니다."],
					["수동생성", "월드의 땅 수동생성을 설정합니다."]
				] as $usage){
					OnixUtils::message($sender, "/월드관리 " . $usage[0] . " - " . $usage[1]);
				}
		}
		return true;
	}
}