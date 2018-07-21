<?php
namespace Gt\Session;

use ArrayAccess;
use SessionHandlerInterface;

class Session {
	use StoreContainer;

	const DEFAULT_SESSION_NAME = "PHPSESSID";
	const DEFAULT_SESSION_LIFETIME = 0;
	const DEFAULT_SESSION_PATH = "/tmp";
	const DEFAULT_SESSION_DOMAIN = "";
	const DEFAULT_SESSION_SECURE = false;
	const DEFAULT_SESSION_HTTPONLY = true;
	const DEFAULT_COOKIE_PATH = "/";

	const DEFAULT_STORE = "_";

	/** @var string */
	protected $id;
	/** @var SessionHandlerInterface */
	protected $sessionHandler;
	/** @var SessionStore[] */
	protected $stores;

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
		session_start([
			"save_path" => $sessionPath,
			"name" => $sessionName,
			"cookie_lifetime" => $config["cookie_lifetime"] ?? self::DEFAULT_SESSION_LIFETIME,
			"cookie_path" => $config["cookie_path"] ?? self::DEFAULT_COOKIE_PATH,
			"cookie_domain" => $config["cookie_domain"] ?? self::DEFAULT_SESSION_DOMAIN,
			"cookie_secure" => $config["cookie_secure"] ?? self::DEFAULT_SESSION_SECURE,
			"cookie_httponly" => $config["cookie_httponly"] ?? self::DEFAULT_SESSION_HTTPONLY,
		]);

		$this->sessionHandler->open($sessionPath, $sessionName);
		$this->stores = $this->readSessionData();
	}

//	public function get(string $key) {
//		$store = $this->getStore($key, true);
//
//		if(is_null($store)) {
//			return null;
//		}
//
//		$dotPosition = strrpos($key, ".");
//		if($dotPosition > 0) {
//			$key = substr($key, $dotPosition + 1);
//		}
//
//		return $store->get($key);
//	}
//
//	public function set(string $key, $value):void {
//		$store = $this->getStore($key, true);
//		$dotPosition = strrpos($key, ".");
//		if($dotPosition > 0) {
//			$key = substr($key, $dotPosition + 1);
//		}
//
//		$store->set($key, $value);
//
//		$this->write();
//	}
//
//	public function contains(string $key):bool {
//		$store = $this->getStore($key, true);
//		$dotPosition = strrpos($key, ".");
//		if($dotPosition > 0) {
//			$key = substr($key, $dotPosition + 1);
//		}
//
//		return $store->contains($key);
//	}
//
//	public function remove(string $key = null):void {
//// TODO: I think this will behave slightly differently, as you can remove a key OR an entire store.
//		$this->write();
//	}

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
			serialize($this->stores)
		);
	}
}