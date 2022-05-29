<?php

declare(strict_types=1);

namespace alvin0319\Area;

use alvin0319\Area\area\AreaManager;
use alvin0319\Area\command\area\AreaAddResidentCommand;
use alvin0319\Area\command\area\AreaBuyCommand;
use alvin0319\Area\command\area\AreaInfoCommand;
use alvin0319\Area\command\area\AreaMoveCommand;
use alvin0319\Area\command\area\AreaRemoveResidentCommand;
use alvin0319\Area\command\area\AreaSellCommand;
use alvin0319\Area\command\area\AreaSettingCommand;
use alvin0319\Area\command\area\AreaTransferCommand;
use alvin0319\Area\command\island\IslandBuyCommand;
use alvin0319\Area\command\island\IslandMoveCommand;
use alvin0319\Area\command\island\IslandStartCommand;
use alvin0319\Area\command\skyblock\SkyBlockAddResidentCommand;
use alvin0319\Area\command\skyblock\SkyBlockBuyCommand;
use alvin0319\Area\command\skyblock\SkyBlockIncreaseCommand;
use alvin0319\Area\command\skyblock\SkyBlockInfoCommand;
use alvin0319\Area\command\skyblock\SkyBlockMoveCommand;
use alvin0319\Area\command\skyblock\SkyBlockRemoveResidentCommand;
use alvin0319\Area\command\skyblock\SkyBlockSettingCommand;
use alvin0319\Area\command\skyisland\SkyIslandBuyCommand;
use alvin0319\Area\command\skyisland\SkyIslandMoveCommand;
use alvin0319\Area\command\skyisland\SkyIslandStartCommand;
use alvin0319\Area\command\world\WorldManageCommand;
use alvin0319\Area\generator\IslandGenerator;
use alvin0319\Area\generator\SkyIslandGenerator;
use alvin0319\Area\generator\SuperFlatGenerator;
use alvin0319\Area\skyblock\SkyBlockManager;
use alvin0319\Area\task\SkyBlockCheckTask;
use alvin0319\Area\world\WorldManager;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\generator\GeneratorManager;
use function is_dir;
use function mkdir;

class AreaLoader extends PluginBase{
	use SingletonTrait;

	/** @var WorldManager */
	protected WorldManager $worldManager;
	/** @var AreaManager */
	protected AreaManager $areaManager;
	/** @var SkyBlockManager */
	protected SkyBlockManager $skyBlockManager;

	protected function onLoad() : void{
		self::$instance = $this;

		$this->worldManager = new WorldManager($this);
		$this->areaManager = new AreaManager($this);
		$this->skyBlockManager = new SkyBlockManager();
	}

	protected function onEnable() : void{
		if(!is_dir($dir = $this->getDataFolder() . "area")){
			mkdir($dir);
		}
		GeneratorManager::getInstance()->addGenerator(IslandGenerator::class, "island", static fn() => null, true);
		GeneratorManager::getInstance()->addGenerator(SkyIslandGenerator::class, "skyisland", static fn() => null, true);
		GeneratorManager::getInstance()->addGenerator(SuperFlatGenerator::class, "superflat", static fn() => null, true);

		$this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);

		$this->getServer()->getCommandMap()->registerAll("area", [
			new AreaAddResidentCommand(),
			new AreaBuyCommand(),
			new AreaInfoCommand(),
			new AreaMoveCommand(),
			new AreaRemoveResidentCommand(),
			new AreaSellCommand(),
			new AreaSettingCommand(),
			new AreaTransferCommand(),

			new IslandBuyCommand(),
			new IslandMoveCommand(),
			new IslandStartCommand(),

			new SkyIslandBuyCommand(),
			new SkyIslandMoveCommand(),
			new SkyIslandStartCommand(),

			new SkyBlockAddResidentCommand(),
			new SkyBlockBuyCommand(),
			new SkyBlockIncreaseCommand(),
			new SkyBlockInfoCommand(),
			new SkyBlockMoveCommand(),
			new SkyBlockRemoveResidentCommand(),
			new SkyBlockSettingCommand(),

			new WorldManageCommand()
		]);

		$this->getScheduler()->scheduleRepeatingTask(new ClosureTask(function() : void{
			$this->save();
		}), 1200 * 5);

		$this->getScheduler()->scheduleRepeatingTask(new SkyBlockCheckTask(), 1200 * 5);
		//$this->getScheduler()->scheduleRepeatingTask(new PlayerCheckTask(), 20);
	}

	public function getWorldManager() : WorldManager{
		return $this->worldManager;
	}

	public function getAreaManager() : AreaManager{
		return $this->areaManager;
	}

	public function getSkyBlockManager() : SkyBlockManager{
		return $this->skyBlockManager;
	}

	public function save() : void{
		$this->worldManager->save();
		$this->areaManager->save();
		$this->skyBlockManager->save();
	}

	public function onDisable() : void{
		$this->save();
	}
}