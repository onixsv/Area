<?php

declare(strict_types=1);

namespace alvin0319\Area\area;

class AreaProperties{
	public const PVP = "pvp";
	public const PROTECT = "보호";
	public const ENTER = "접근";
	public const MESSAGE = "메시지";
	public const PRICE = "가격";

	public const DEFAULTS = [
		self::PVP => true,
		self::PROTECT => true,
		self::ENTER => true,
		self::MESSAGE => "땅에 오신 것을 환영합니다.",
	];
	/** @var Area */
	protected $area;
	/** @var array */
	protected $data = self::DEFAULTS;

	public function __construct(array $data = self::DEFAULTS){
		$this->data = $data;

		$this->fix();
	}

	private function fix() : void{
		if(!isset($this->data[self::PRICE])){
			$this->data[self::PRICE] = 5000;
		}
		foreach(self::DEFAULTS as $key => $value){
			if(!isset($this->data[$key])){
				$this->data[$key] = $value;
			}
		}
	}

	public function get(string $option){
		return $this->data[$option] ?? null;
	}

	public function set(string $option, $value){
		$this->data[$option] = $value;
	}

	public function setAllowPvp(bool $allow = false){
		$this->set(self::PVP, $allow);
	}

	public function setProtect(bool $protect = true){
		$this->set(self::PROTECT, $protect);
	}

	public function setAllowEnter(bool $enter = true){
		$this->set(self::ENTER, $enter);
	}

	public function setMessage(string $message = ""){
		$this->set(self::MESSAGE, $message);
	}

	public function getAllowPvp() : bool{
		return $this->get(self::PVP);
	}

	public function isProtect() : bool{
		return $this->get(self::PROTECT);
	}

	public function getAllowEnter() : bool{
		return $this->get(self::ENTER);
	}

	public function getMessage() : string{
		return $this->get(self::MESSAGE);
	}

	public function jsonSerialize() : array{
		return $this->data;
	}
}