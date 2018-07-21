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

	public function setData(string $key, $value) {
		$this->data[$key] = $value;
	}

	public function getData(string $key) {
		return $this->data[$key] ?? null;
	}

	public function write():void {
		$this->session->write();
	}
}