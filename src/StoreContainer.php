<?php
namespace Gt\Session;

trait StoreContainer {
	public function getStore(string $namespace):?SessionStore {
		$namespaceParts = explode(".", $namespace);
		$topLevelStoreName = array_shift($namespaceParts);

		/** @var SessionStore $store */
		$store = $this->stores[$topLevelStoreName] ?? null;
		if(is_null($store)) {
			return null;
		}

		if(empty($namespaceParts)) {
			return $store;
		}

		$namespace = implode(".", $namespaceParts);
		return $store->getStore($namespace);
	}

	public function get(string $key) {
		$store = $this;

		$lastDotPosition = strrpos($key, ".");

		if($this instanceof Session
		|| $lastDotPosition !== false) {
			$namespace = $this->getNamespaceFromKey($key)
				?? Session::DEFAULT_STORE;

			$store = $this->getStore($namespace);
		}

		if(is_null($store)) {
			return null;
		}

		if($lastDotPosition !== false) {
			$key = substr($key, $lastDotPosition + 1);
		}

		return $store->getData($key);
	}

	public function set(string $key, $value):void {
		$store = $this;

		$lastDotPosition = strrpos($key, ".");

		if($this instanceof Session
		|| $lastDotPosition !== false) {
			$namespace = $this->getNamespaceFromKey($key)
				?? Session::DEFAULT_STORE;

			$store = $this->getStore($namespace);

			if(is_null($store)) {
				$store = SessionStoreFactory::create(
					$namespace,
					$this->getSession()
				);
				$this->stores[$namespace] = $store;
			}
		}

		if($lastDotPosition !== false) {
			$key = substr($key, $lastDotPosition + 1);
		}

		$store->setData($key, $value);
		$store->write();
	}

	public function contains(string $key):bool {
		$store = $this;

		$lastDotPosition = strrpos($key, ".");

		if($this instanceof Session
		|| $lastDotPosition !== false) {
			$namespace = $this->getNamespaceFromKey($key)
				?? Session::DEFAULT_STORE;

			$store = $this->getStore($namespace);
		}

		if(is_null($store)) {
			return null;
		}

		if($lastDotPosition !== false) {
			$key = substr($key, $lastDotPosition + 1);
		}

		return $store->containsData($key);
	}

	public function remove(string $key):void {
		$store = $this;

		$lastDotPosition = strrpos($key, ".");

		if($this instanceof Session
		|| $lastDotPosition !== false) {
			$namespace = $this->getNamespaceFromKey($key)
				?? Session::DEFAULT_STORE;

			$store = $this->getStore($namespace);
		}

		if(is_null($store)) {
			return;
		}

		if($lastDotPosition !== false) {
			$key = substr($key, $lastDotPosition + 1);
		}

		$store->removeDataOrStore($key);
		$store->write();
	}

	protected function getSession():Session {
		if($this instanceof Session) {
			return $this;
		}

		return $this->session;
	}

	protected function getNamespaceFromKey(string $key):?string {
		$lastDotPostition = strrpos($key, ".");
		if($lastDotPostition === false) {
			return null;
		}

		return substr($key, 0, $lastDotPostition);
	}
}