<?php
namespace Gt\Session;

use SessionHandlerInterface;

class Session implements SessionContainer {
	const DEFAULT_SESSION_NAME = "PHPSESSID";
	const DEFAULT_SESSION_LIFETIME = 0;
	const DEFAULT_SESSION_PATH = "/tmp";
	const DEFAULT_SESSION_DOMAIN = "";
	const DEFAULT_SESSION_SECURE = false;
	const DEFAULT_SESSION_HTTPONLY = true;
	const DEFAULT_COOKIE_PATH = "/";

	/** @var string */
	protected $id;
	/** @var SessionHandlerInterface */
	protected $sessionHandler;
	/** @var SessionStore */
	protected $store;

	public function __construct(
		SessionHandlerInterface $sessionHandler,
		iterable $config = [],
		string $id = null
	) {
		$this->sessionHandler = $sessionHandler;

		if(is_null($id)) {
			$id = $this->getId();
		}

		$this->id = $id;

		$sessionPath = $this->getAbsolutePath(
			$config["save_path"] ?? self::DEFAULT_SESSION_PATH
		);
		$sessionName = $config["name"] ?? self::DEFAULT_SESSION_NAME;
		$sessionStartAttempts = 0;

		do {
			$success = @session_start([
				"save_path" => $sessionPath,
				"name" => $sessionName,
				"cookie_lifetime" => $config["cookie_lifetime"] ?? self::DEFAULT_SESSION_LIFETIME,
				"cookie_path" => $config["cookie_path"] ?? self::DEFAULT_COOKIE_PATH,
				"cookie_domain" => $config["cookie_domain"] ?? self::DEFAULT_SESSION_DOMAIN,
				"cookie_secure" => $config["cookie_secure"] ?? self::DEFAULT_SESSION_SECURE,
				"cookie_httponly" => $config["cookie_httponly"] ?? self::DEFAULT_SESSION_HTTPONLY,
			]);

			if(!$success) {
				$sessionStartAttempts++;
				@session_destroy();
			}
		}
		while(!$success && $sessionStartAttempts <= 1);

		$this->sessionHandler->open($sessionPath, $sessionName);
		$this->store = $this->readSessionData() ?: null;
		if(is_null($this->store)) {
			$this->store = new SessionStore(__NAMESPACE__, $this);
		}
	}

	public function kill():void {
		$this->sessionHandler->destroy($this->getId());
		$params = session_get_cookie_params();
		setcookie(
			session_name(),
			"",
			-1,
			$params["path"],
			$params["domain"],
			$params["secure"],
			$params["httponly"]
		);
	}

	public function getStore(
		string $namespace,
		bool $createIfNotExists = false
	):?SessionStore {
		return $this->store->getStore(
			$namespace,
			$createIfNotExists
		);
	}

	public function get(string $key) {
		return $this->store->get($key);
	}

	public function set(string $key, $value):void {
		$this->store->set($key, $value);
	}

	public function contains(string $key):bool {
		return $this->store->contains($key);
	}

	public function remove(string $key):void {
		$this->store->remove($key);
	}

	public function getId():string {
		$id = session_id();
		if(empty($id)) {
			session_id($this->createNewId());
		}

		return session_id();
	}

	protected function getAbsolutePath(string $path):string {
		$path = str_replace(
			["/", "\\"],
			DIRECTORY_SEPARATOR,
			$path
		);

		if($path[0] !== DIRECTORY_SEPARATOR) {
			$path = implode(DIRECTORY_SEPARATOR, [
				sys_get_temp_dir(),
				$path,
			]);
		}

		return $path;
	}

	protected function createNewId():string {
		return session_create_id();
	}

	protected function readSessionData() {
		return unserialize($this->sessionHandler->read($this->id));
	}

	public function write() {
		$this->sessionHandler->write(
			$this->id,
			serialize($this->store)
		);
	}
}