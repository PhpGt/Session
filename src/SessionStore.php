<?php
namespace Gt\Session;

class SessionStore {
	use StoreContainer;

	/** @var string */
	protected $name;
	/** @var Session */
	protected $session;
	/** @var SessionStore[] */
	protected $stores;
	/** @var string[] */
	protected $data;

	public function __construct(string $name, Session $session) {
		$this->name = $name;
		$this->session = $session;
	}

	public function createStore(string $name, Session $session):self {
		$newStore = new self($name, $session);
		$this->stores[$name] = $newStore;
		return $newStore;
	}

	public function setData(string $key, $value):void {
		$this->data[$key] = $value;
	}

	public function getData(string $key) {
		return $this->data[$key] ?? null;
	}

	public function containsData(string $key):bool {
		return isset($this->data[$key]);
	}

	public function containsStore(string $key):bool {
		return isset($this->stores[$key]);
	}

	public function removeData(string $key):void {
		unset($this->data[$key]);
	}

	public function removeStore(string $key):void {
		unset($this->stores[$key]);
	}

	public function removeDataOrStore(string $key):void {
		if($this->containsData($key)) {
			$this->removeData($key);
		}
		if($this->containsStore($key)) {
			$this->removeStore($key);
		}
	}

	public function write():void {
		$this->session->write();
	}
}