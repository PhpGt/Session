<?php
namespace Gt\Session;

class SessionStore {
	/** @var string */
	protected $name;
	/** @var Session */
	protected $session;
	/** @var SessionStore[] */
	protected $stores;
	/** @var array */
	protected $data;
	/** @var SessionStore */
	protected $parentStore;

	public function __construct(
		string $name,
		Session $session,
		self $parentStore = null
	) {
		$this->name = $name;
		$this->session = $session;
		$this->parentStore = $parentStore;
		$this->stores = [];
		$this->data = [];
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

	public function getStore(
		string $namespace,
		bool $createIfNotExists = false
	):?SessionStore {
		$namespaceParts = explode(".", $namespace);
		$topLevelStoreName = array_shift($namespaceParts);

		/** @var SessionStore $store */
		$store = $this->stores[$topLevelStoreName] ?? null;
		if(is_null($store)) {
			if($createIfNotExists) {
				$store = $this->createStore($namespace);
				return $store;
			}
			else {
				return null;
			}
		}

		if(empty($namespaceParts)) {
			return $store;
		}

		$namespace = implode(".", $namespaceParts);
		return $store->getStore($namespace);
	}

	public function setStore(
		string $namespace
	):void {
		$namespaceParts = explode(".", $namespace);
		$store = $this;
		$nextStore = $store;

		while (!empty($namespaceParts)) {
			$storeName = array_shift($namespaceParts);
			$store = $nextStore;
			$nextStore = $store->getStore($storeName);

			if (is_null($nextStore)) {
				$nextStore = new SessionStore(
					$storeName,
					$this->session,
					$store
				);
				$store->stores[$storeName] = $nextStore;
			}
		}
	}

	public function createStore(string $namespace):SessionStore {
		$this->setStore($namespace);
		return $this->getStore($namespace);
	}

	public function get(string $key) {
		$store = $this;
		$lastDotPosition = strrpos($key, ".");

		if ($lastDotPosition !== false) {
			$namespace = $this->getNamespaceFromKey($key);
			$store = $this->getStore($namespace);
		}

		if (is_null($store)) {
			return null;
		}

		if ($lastDotPosition !== false) {
			$key = substr($key, $lastDotPosition + 1);
		}

		return $store->getData($key);
	}

	public function set(string $key, $value):void {
		$store = $this;
		$lastDotPosition = strrpos($key, ".");

		if ($lastDotPosition !== false) {
			$namespace = $this->getNamespaceFromKey($key);
			$store = $this->getStore($namespace);

			if (is_null($store)) {
				$store = $this->createStore($namespace);
			}
		}

		if ($lastDotPosition !== false) {
			$key = substr($key, $lastDotPosition + 1);
		}

		$store->setData($key, $value);
		$store->write();
	}

	public function contains(string $key):bool {
		$store = $this;
		$lastDotPosition = strrpos($key, ".");

		if ($lastDotPosition !== false) {
			$namespace = $this->getNamespaceFromKey($key);
			$store = $this->getStore($namespace);
		}

		if (is_null($store)) {
			return false;
		}

		if ($lastDotPosition !== false) {
			$key = substr($key, $lastDotPosition + 1);
		}

		return $store->containsData($key);
	}

	public function remove(string $key = null):void {
		if(is_null($key)) {
			foreach($this->stores as $i => $childStore) {
				unset($this->stores[$i]);
			}

			$this->parentStore->remove($this->name);
			return;
		}

		$store = $this;
		$lastDotPosition = strrpos($key, ".");

		if ($lastDotPosition !== false) {
			$namespace = $this->getNamespaceFromKey($key);
			$store = $this->getStore($namespace);
		}

		if (is_null($store)) {
			return;
		}

		if ($lastDotPosition !== false) {
			$key = substr($key, $lastDotPosition + 1);
		}

		$store->removeDataOrStore($key);
		$store->write();
	}

	protected function getSession():Session {
		return $this->session;
	}

	protected function getNamespaceFromKey(string $key):?string {
		$lastDotPostition = strrpos($key, ".");
		if ($lastDotPostition === false) {
			return null;
		}

		return substr($key, 0, $lastDotPostition);
	}
}