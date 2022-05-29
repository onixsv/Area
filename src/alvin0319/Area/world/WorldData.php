<?php

declare(strict_types=1);

namespace alvin0319\Area\world;

class WorldData{
	public const PROTECT = "보호";
	public const PRICE = "가격";
	public const CREATE = "생성";
	public const AUTOCREATE = "자동생성";
	public const AREA_COUNT = "보유수";
	public const PVP = "pvp";

	public const DEFAULTS = [
		self::PROTECT => true,
		self::PRICE => 5000,
		self::CREATE => false,
		self::AUTOCREATE => false,
		self::AREA_COUNT => 2,
		self::PVP => false
	];

	protected array $data = self::DEFAULTS;

	public function __construct(array $data = self::DEFAULTS){
		$this->data = $data;
		$this->fix();
	}

	private function fix() : void{
		foreach(self::DEFAULTS as $name => $value){
			if(!isset($this->data[$name]))
				$this->data[$name] = $value;
		}
	}

	public function get(string $option){
		return $this->data[$option] ?? null;
	}

	public function set(string $option, $value){
		$this->data[$option] = $value;
	}

	public function isProtect() : bool{
		return $this->get(self::PROTECT);
	}

	public function getAllowCreate() : bool{
		return $this->get(self::CREATE);
	}

	public function getAllowAutoCreate() : bool{
		return $this->get(self::AUTOCREATE);
	}

	public function getPrice() : int{
		return $this->get(self::PRICE);
	}

	public function getAreaCount() : int{
		return $this->get(self::AREA_COUNT);
	}

	public function getAllowPvP() : bool{
		return $this->get(self::PVP);
	}

	public function setProtect(bool $protect = true) : void{
		$this->set(self::PROTECT, $protect);
	}

	public function setAllowCreate(bool $create = false) : void{
		$this->set(self::CREATE, $create);
	}

	public function setAllowAutoCreate(bool $autoCreate = false) : void{
		$this->set(self::AUTOCREATE, $autoCreate);
	}

	public function setPrice(int $price) : void{
		$this->set(self::PRICE, $price);
	}

	public function setAreaCount(int $count) : void{
		$this->set(self::AREA_COUNT, $count);
	}

	public function setAllowPvP(bool $pvp) : void{
		$this->set(self::PVP, $pvp);
	}

	public function jsonSerialize() : array{
		return $this->data;
	}
}