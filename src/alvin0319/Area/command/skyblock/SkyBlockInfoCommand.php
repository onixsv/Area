<?php

declare(strict_types=1);

namespace alvin0319\Area\command\skyblock;

use alvin0319\Area\AreaLoader;
use OnixUtils\OnixUtils;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use function array_map;
use function implode;
use function trim;

class SkyBlockInfoCommand extends Command{

	public function __construct(){
		parent::__construct("스카이블럭 정보", "스카이블럭의 정보를 봅니다.");
		$this->setPermission("area.command.skyblock.info");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : bool{
		if(!$this->testPermission($sender)){
			return false;
		}
		if(!$sender instanceof Player){
			return false;
		}
		if(trim($args[0] ?? "") === ""){
			if(!AreaLoader::getInstance()->getSkyBlockManager()->hasSkyBlock($sender)){
				OnixUtils::message($sender, "스카이블럭을 소유중이지 않습니다.");
				return false;
			}
			$skyBlock = AreaLoader::getInstance()->getSkyBlockManager()->getSkyBlockByPlayer($sender);
		}else{
			if(!AreaLoader::getInstance()->getSkyBlockManager()->hasSkyBlock($args[0])){
				OnixUtils::message($sender, "해당 유저는 스카이블럭을 소유중이지 않습니다.");
				return false;
			}
			$skyBlock = AreaLoader::getInstance()->getSkyBlockManager()->loadSkyBlock($args[0]);
		}
		$pp = $skyBlock->getProperties();
		$pvp = $pp->getAllowPvp() ? "§a허용" : "§c비허용";
		$protect = $pp->isProtect() ? "§a보호" : "§c비보호";
		$enter = $pp->getAllowEnter() ? "§a접근 허용" : "§c접근 비허용";
		$message = $pp->getMessage();
		$sender->sendMessage("§a- - - - - - - - - -");
		$sender->sendMessage("§a스카이블럭 주인§f: {$skyBlock->getOwner()}");
		$sender->sendMessage("§a스카이블럭 거주자§f: " . implode(", ", array_map(function(string $player) use ($sender) : string{
				return ($sender->getServer()->getPlayerExact($player) !== null ? "§a{$player}" : "§0{$player}") . "§f";
			}, $skyBlock->getResidents())));
		$sender->sendMessage("§a전투§f: {$pvp}");
		$sender->sendMessage("§a보호§f: {$protect}");
		$sender->sendMessage("§a접근§f: {$enter}");
		$sender->sendMessage("§a메시지§f: {$message}");
		$sender->sendMessage("§a- - - - - - - - - -");
		return true;
	}
}