<?php
namespace Gt\Session;

use Gt\TypeSafeGetter\NullableTypeSafeGetter;
use Gt\TypeSafeGetter\TypeSafeGetter;

class SessionStore implements SessionContainer, TypeSafeGetter {
	use NullableTypeSafeGetter;

	protected string $name;
	protected Session $session;
	/** @var array<SessionStore> */
	protected array $stores;
	/** @var array<string, mixed> */
	protected array $data;
	protected ?SessionStore $parentStore;

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

	public function setData(string $key, mixed $value):void {
		$this->data[$key] = $value;
	}

	public function getData(string $key):mixed {
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

		$store = $this->stores[$topLevelStoreName] ?? null;
		if(is_null($store)) {
			if($createIfNotExists) {
				return $this->createStore($namespace);
			}
			else {
				return null;
			}
		}

		if(empty($namespaceParts)) {
			return $store;
		}

		$namespace = implode(".", $namespaceParts);
		return $store->getStore($namespace, $createIfNotExists);
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

	public function get(string $key):mixed {
		$store = $this->getStoreFromKey($key);
		$key = $this->normaliseKey($key);
		return $store?->getData($key);
	}

	public function set(string $key, mixed $value):void {
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
		$store = $this->getStoreFromKey($key);
		$key = $this->normaliseKey($key);
		return $store?->containsData($key) ?? false;
	}

	private function getStoreFromKey(string $key):?SessionStore {
		$store = $this;
		$lastDotPosition = strrpos($key, ".");

		if ($lastDotPosition !== false) {
			$namespace = $this->getNamespaceFromKey($key);
			$store = $this->getStore($namespace);
		}

		if (is_null($store)) {
			return null;
		}

		return $store;
	}

	private function normaliseKey(string $key):string {
		$lastDotPosition = strrpos($key, ".");
		if($lastDotPosition !== false) {
			$key = substr($key, $lastDotPosition + 1);
		}

		return $key;
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
		$lastDotPosition = strrpos($key, ".");
		if ($lastDotPosition === false) {
			return null;
		}

		return substr($key, 0, $lastDotPosition);
	}
}
