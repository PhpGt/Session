<?php
namespace Gt\Session;

class SessionArrayWrapper implements SessionContainer {
	private $sourceArray;

	public function __construct(array &$sourceArray) {
		$this->sourceArray = &$sourceArray;
	}

	public function get(string $key) {
		return $this->sourceArray[$key] ?? null;
	}

	public function set(string $key, $value) {
		$this->sourceArray[$key] = $value;
	}

	public function contains(string $key):bool {
		return isset($this->sourceArray[$key]);
	}

	public function remove(string $key):void {
		if(!$this->contains($key)) {
			return;
		}

		unset($this->sourceArray[$key]);
	}
}