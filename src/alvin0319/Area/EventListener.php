<?php

declare(strict_types=1);

namespace alvin0319\Area;

use alvin0319\Area\area\Area;
use alvin0319\Area\generator\SuperFlatGenerator;
use alvin0319\Area\world\WorldData;
use ojy\warn\SWarn;
use OnixUtils\OnixUtils;
use pocketmine\block\Bedrock;
use pocketmine\block\Chest;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\world\ChunkPopulateEvent;
use pocketmine\event\world\WorldLoadEvent;
use pocketmine\event\world\WorldUnloadEvent;
use pocketmine\math\Vector3;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;
use pocketmine\world\generator\GeneratorManager;
use function count;
use function mb_strpos;

class EventListener implements Listener{

	/**
	 * @param ChunkPopulateEvent $event
	 *
	 * @priority HIGHEST
	 */
	public function onChunkPopulate(ChunkPopulateEvent $event){
		if(GeneratorManager::getInstance()->getGenerator($event->getWorld()->getProvider()->getWorldData()->getGenerator()) === SuperFlatGenerator::class){
			$chunk = $event->getChunk();
			if($event->getChunkX() % 2 == 0 && $event->getChunkZ() % 2 == 0){
				AreaLoader::getInstance()->getAreaManager()->addArea(new Vector3(($event->getChunkX() * 16) + 3, 0, ($event->getChunkZ() * 16) + 3), new Vector3((($event->getChunkX() + 2) * 16) - 4, 0, (($event->getChunkZ() + 2) * 16) - 4), "", $event->getWorld());
			}
		}
	}

	/**
	 * @param BlockBreakEvent $event
	 *
	 * @priority NORMAL
	 *
	 * @handleCancelled false
	 */
	public function onBlockBreak(BlockBreakEvent $event) : void{
		$block = $event->getBlock();
		$player = $event->getPlayer();

		if(($area = AreaLoader::getInstance()->getAreaManager()->getArea($block->getPosition(), $block->getPosition()->getWorld())) !== null){
			if(!$player->hasPermission(DefaultPermissions::ROOT_OPERATOR) && !$area->isResident($player->getName()) && $area->getAreaProperties()->isProtect()){
				$event->cancel();
				return;
			}
			if($block instanceof Chest){
				return;
			}
			if($block instanceof Bedrock && !$player->hasPermission(DefaultPermissions::ROOT_OPERATOR)){
				SWarn::addWarn($player->getName(), 5, "핵 (기반암 파괴)");
				return;
			}
			$items = $player->getInventory()->addItem(...$event->getDrops());
			$event->setDrops([]);
			if(count($items) > 0){
				$player->sendTitle("§c[ §f! §c]", "인벤토리가 꽉찼습니다!");
			}
		}elseif(($skyBlock = AreaLoader::getInstance()->getSkyBlockManager()->getSkyBlock($block->getPosition()->getWorld())) !== null){
			if(!$player->hasPermission(DefaultPermissions::ROOT_OPERATOR) && !$skyBlock->isResident($player->getName()) && $skyBlock->getProperties()->isProtect()){
				$event->cancel();
			}
			if(!$player->hasPermission(DefaultPermissions::ROOT_OPERATOR) && $skyBlock->isResident($player->getName()) && !$skyBlock->isInArea($block->getPosition())){
				$event->cancel();
				OnixUtils::message($player, "이 구역은 이용할 수 없습니다. 스카이블럭을 확장해주세요.");
			}
		}else{
			$world = AreaLoader::getInstance()->getWorldManager()->get($block->getPosition()->getWorld());
			if(!$player->hasPermission(DefaultPermissions::ROOT_OPERATOR) && $world->isProtect()){
				$event->cancel();
			}
		}
	}

	/**
	 * @param BlockPlaceEvent $event
	 *
	 * @priority LOWEST
	 */
	public function onBlockPlace(BlockPlaceEvent $event) : void{
		$block = $event->getBlock();
		$player = $event->getPlayer();

		if(($area = AreaLoader::getInstance()->getAreaManager()->getArea($block->getPosition(), $block->getPosition()->getWorld())) !== null){
			if(!$player->hasPermission(DefaultPermissions::ROOT_OPERATOR) && !$area->isResident($player->getName()) && $area->getAreaProperties()->isProtect()){
				$event->cancel();
			}
		}elseif(($skyBlock = AreaLoader::getInstance()->getSkyBlockManager()->getSkyBlock($block->getPosition()->getWorld())) !== null){
			if(!$player->hasPermission(DefaultPermissions::ROOT_OPERATOR) && !$skyBlock->isResident($player->getName()) && $skyBlock->getProperties()->isProtect()){
				$event->cancel();
			}
			if(!$player->hasPermission(DefaultPermissions::ROOT_OPERATOR) && $skyBlock->isResident($player->getName()) && !$skyBlock->isInArea($block->getPosition())){
				$event->cancel();
				OnixUtils::message($player, "이 구역은 이용할 수 없습니다. 스카이블럭을 확장해주세요.");
			}
		}else{
			$world = AreaLoader::getInstance()->getWorldManager()->get($block->getPosition()->getWorld());
			if(!$player->hasPermission(DefaultPermissions::ROOT_OPERATOR) && $world->isProtect()){
				$event->cancel();
			}
		}
	}

	/**
	 * @param PlayerInteractEvent $event
	 *
	 * @handleCancelled true
	 *
	 * @priority LOWEST
	 */
	public function onPlayerInteract(PlayerInteractEvent $event) : void{
		$block = $event->getBlock();
		$player = $event->getPlayer();

		if(($area = AreaLoader::getInstance()->getAreaManager()->getArea($block->getPosition(), $block->getPosition()->getWorld())) !== null){
			if(!$player->hasPermission(DefaultPermissions::ROOT_OPERATOR) && !$area->isResident($player->getName()) && $area->getAreaProperties()->isProtect()){
				$event->cancel();
			}
		}elseif(($skyBlock = AreaLoader::getInstance()->getSkyBlockManager()->getSkyBlock($block->getPosition()->getWorld())) !== null){
			if(!$player->hasPermission(DefaultPermissions::ROOT_OPERATOR) && !$skyBlock->isResident($player->getName()) && $skyBlock->getProperties()->isProtect()){
				$event->cancel();
			}
			if(!$player->hasPermission(DefaultPermissions::ROOT_OPERATOR) && $skyBlock->isResident($player->getName()) && !$skyBlock->isInArea($block->getPosition())){
				$event->cancel();
				OnixUtils::message($player, "이 구역은 이용할 수 없습니다. 스카이블럭을 확장해주세요.");
			}
		}else{
			$world = AreaLoader::getInstance()->getWorldManager()->get($block->getPosition()->getWorld());
			if(!$player->hasPermission(DefaultPermissions::ROOT_OPERATOR) && $world->isProtect()){
				$event->cancel();
			}
		}
	}

	/**
	 * @param SignChangeEvent $event
	 *
	 * @priority LOWEST
	 */
	public function onSignChange(SignChangeEvent $event) : void{
		$block = $event->getBlock();
		$player = $event->getPlayer();

		if(($area = AreaLoader::getInstance()->getAreaManager()->getArea($block->getPosition(), $block->getPosition()->getWorld())) !== null){
			if(!$player->hasPermission(DefaultPermissions::ROOT_OPERATOR) && !$area->isResident($player->getName()) && $area->getAreaProperties()->isProtect()){
				$event->cancel();
			}
		}elseif(($skyBlock = AreaLoader::getInstance()->getSkyBlockManager()->getSkyBlock($block->getPosition()->getWorld())) !== null){
			if(!$player->hasPermission(DefaultPermissions::ROOT_OPERATOR) && !$skyBlock->isResident($player->getName()) && $skyBlock->getProperties()->isProtect()){
				$event->cancel();
			}
			if(!$player->hasPermission(DefaultPermissions::ROOT_OPERATOR) && $skyBlock->isResident($player->getName()) && !$skyBlock->isInArea($block->getPosition())){
				$event->cancel();
				OnixUtils::message($player, "이 구역은 이용할 수 없습니다. 스카이블럭을 확장해주세요.");
			}
		}else{
			$world = AreaLoader::getInstance()->getWorldManager()->get($block->getPosition()->getWorld());
			if(!$player->hasPermission(DefaultPermissions::ROOT_OPERATOR) && $world->isProtect()){
				$event->cancel();
			}
		}
	}

	public function onWorldLoad(WorldLoadEvent $event) : void{
		$world = $event->getWorld();
		if(mb_strpos($world->getFolderName(), "skyblock.") === false){
			AreaLoader::getInstance()->getWorldManager()->loadWorld($world);
			AreaLoader::getInstance()->getAreaManager()->loadWorld($world);
		}
	}

	public function onWorldUnload(WorldUnloadEvent $event) : void{
		$world = $event->getWorld();
		if(mb_strpos($world->getFolderName(), "skyblock.") === false){
			AreaLoader::getInstance()->getWorldManager()->unloadWorld($world);
			AreaLoader::getInstance()->getAreaManager()->unloadWorld($world);
		}
	}

	public function onPlayerJoin(PlayerJoinEvent $event) : void{
		$player = $event->getPlayer();
		if(AreaLoader::getInstance()->getSkyBlockManager()->hasSkyBlock($player)){
			AreaLoader::getInstance()->getSkyBlockManager()->loadSkyBlock($player);
		}
	}

	public function onPlayerQuit(PlayerQuitEvent $event) : void{
		$player = $event->getPlayer();
		if(AreaLoader::getInstance()->getSkyBlockManager()->hasSkyBlock($player)){
			AreaLoader::getInstance()->getSkyBlockManager()->unloadSkyBlock(AreaLoader::getInstance()->getSkyBlockManager()->getSkyBlockByPlayer($player));
		}
	}

	/**
	 * @param EntityDamageByEntityEvent $event
	 *
	 * @handleCancelled true
	 * @priority        LOWEST
	 */
	public function onEntityDamageByEntity(EntityDamageByEntityEvent $event) : void{
		$damager = $event->getDamager();
		$entity = $event->getEntity();
		if(!$damager instanceof Player || !$entity instanceof Player){
			return;
		}

		$area = AreaLoader::getInstance()->getAreaManager()->getArea($entity->getPosition(), $entity->getWorld());

		if($area instanceof Area){
			if($area->getAreaProperties()->getAllowPvp())
				return;
		}else{
			if(mb_strpos($entity->getWorld()->getFolderName(), "skyblock.") === false){
				$whiteWorld = AreaLoader::getInstance()->getWorldManager()->get($entity->getWorld());
				if($whiteWorld instanceof WorldData)
					if($whiteWorld->getAllowPvP())
						return;
			}
		}
		if(mb_strpos($entity->getWorld()->getFolderName(), "skyblock.") !== false){
			$skyBlock = AreaLoader::getInstance()->getSkyBlockManager()->getSkyBlock($entity->getWorld());
			if($skyBlock !== null){
				if($skyBlock->getProperties()->getAllowPvp())
					return;
			}
		}
		$event->cancel();
	}

	public function onPlayerDeath(PlayerDeathEvent $event) : void{
		$event->setKeepInventory(true);
	}
}