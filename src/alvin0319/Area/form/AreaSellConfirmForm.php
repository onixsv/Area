<?php

declare(strict_types=1);

namespace alvin0319\Area\form;

use alvin0319\Area\area\Area;
use alvin0319\Area\AreaLoader;
use onebone\economyapi\EconomyAPI;
use OnixUtils\OnixUtils;
use pocketmine\form\Form;
use pocketmine\player\Player;
use function is_bool;

class AreaSellConfirmForm implements Form{
	/** @var Area */
	protected Area $area;

	public function __construct(Area $area){
		$this->area = $area;
	}

	public function jsonSerialize() : array{
		return [
			"type" => "modal",
			"title" => "땅 판매 확인",
			"content" => "정말 {$this->area->getId()}번 땅을 판매하시겠습니까?\n\n판매가: " . AreaLoader::getInstance()->getWorldManager()->get($this->area->getWorld())->getPrice(),
			"button1" => "네",
			"button2" => "아니요"
		];
	}

	public function handleResponse(Player $player, $data) : void{
		if(!is_bool($data)){
			return;
		}
		if($data){
			$this->area->setOwner("");
			EconomyAPI::getInstance()->addMoney($player, AreaLoader::getInstance()->getWorldManager()->get($this->area->getWorld())->getPrice());
			OnixUtils::message($player, "성공적으로 땅을 판매했습니다.");
		}
	}
}