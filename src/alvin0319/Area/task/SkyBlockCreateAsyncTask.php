<?php

declare(strict_types=1);

namespace alvin0319\Area\task;

use alvin0319\Area\area\AreaProperties;
use alvin0319\Area\AreaLoader;
use alvin0319\Area\skyblock\SkyBlock;
use OnixUtils\OnixUtils;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use RecursiveDirectoryIterator;
use SplFileInfo;
use function copy;
use function is_dir;
use function mkdir;
use function strtolower;
use function substr;
use const DIRECTORY_SEPARATOR;

class SkyBlockCreateAsyncTask extends AsyncTask{
	/** @var string */
	protected string $owner;
	/** @var string */
	protected string $worldPath;

	public function __construct(string $owner, string $worldPath){
		$this->owner = $owner;
		$this->worldPath = $worldPath;
	}

	public function onRun() : void{
		$this->recursiveCopy($this->worldPath . "skyblock", $this->worldPath . "skyblock." . strtolower($this->owner));
	}

	public function onCompletion() : void{
		$server = Server::getInstance();
		$owner = $server->getPlayerExact($this->owner);
		$server->getWorldManager()->loadWorld("skyblock." . strtolower($this->owner));
		$level = $server->getWorldManager()->getWorldByName("skyblock." . strtolower($this->owner));
		$spawn = $level->getSafeSpawn();
		$skyBlock = new SkyBlock($spawn->getX() - 10, $spawn->getX() + 10, $spawn->getZ() - 10, $spawn->getZ() + 10, $level->getFolderName(), strtolower($this->owner), [], 1, new AreaProperties());

		AreaLoader::getInstance()->getSkyBlockManager()->registerSkyBlock($skyBlock);

		if($owner !== null){
			OnixUtils::message($owner, "스카이블럭 생성에 성공했습니다.");
			OnixUtils::message($owner, "\"/스카이블럭 이동\"으로 내 스카이블럭에 이동할 수 있습니다.");
		}
	}

	public function onError() : void{
		$server = Server::getInstance();
		$owner = $server->getPlayerExact($this->owner);
		if($owner !== null){
			OnixUtils::message($owner, "스카이블럭 생성에 실패했습니다. 이 메시지를 찍어 관리자에게 문의해주세요.");
		}
	}

	private function recursiveCopy(string $origin, string $destination) : void{
		if(substr($origin, -1) !== DIRECTORY_SEPARATOR){
			$origin .= DIRECTORY_SEPARATOR;
		}
		if(substr($destination, -1) !== DIRECTORY_SEPARATOR){
			$destination .= DIRECTORY_SEPARATOR;
		}

		$recursiveDirectoryIteratorIterator = new RecursiveDirectoryIterator($origin, RecursiveDirectoryIterator::SKIP_DOTS);

		if(!is_dir($destination))
			mkdir($destination);

		/**
		 * @var SplFileInfo $fileInfo
		 */
		foreach($recursiveDirectoryIteratorIterator as $fileInfo){
			if($fileInfo->getFilename() !== "." && $fileInfo->getFilename() !== ".."){
				if($fileInfo->isDir()){
					$this->recursiveCopy($origin . DIRECTORY_SEPARATOR . $fileInfo->getFilename(), $destination . DIRECTORY_SEPARATOR . $fileInfo->getFilename());
				}else{
					copy($origin . DIRECTORY_SEPARATOR . $fileInfo->getFilename(), $destination . DIRECTORY_SEPARATOR . $fileInfo->getFilename());
				}
			}
		}
	}
}