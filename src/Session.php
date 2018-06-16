<?php
namespace Gt\Session;

use ArrayAccess;
use SessionHandlerInterface;

class Session implements ArrayAccess {
	const DEFAULT_SESSION_NAME = "PHPSESSID";
	const DEFAULT_SESSION_LIFETIME = 0;
	const DEFAULT_SESSION_PATH = "/tmp";
	const DEFAULT_SESSION_DOMAIN = "";
	const DEFAULT_SESSION_SECURE = false;
	const DEFAULT_SESSION_HTTPONLY = true;
	const DEFAULT_COOKIE_PATH = "/";

	protected $data;
	protected $id;
	/** @var SessionHandlerInterface */
	protected $sessionHandler;

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
		$this->data = $this->readSessionData();
	}

	public function get(string $key) {
		return $this->data[$key] ?? null;
	}

	public function set(string $key, $value):void {
		$this->data[$key] = $value;
		$this->writeSessionData();
	}

	public function has(string $key):bool {
		return isset($this->data[$key]);
	}

	public function delete($key):void {
		if($this->has($key)) {
			unset($this->data[$key]);
		}

		$this->writeSessionData();
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

	/**
	 * @link http://php.net/manual/en/arrayaccess.offsetexists.php
	 * @param string $offset
	 */
	public function offsetExists($offset):bool {
		return $this->has($offset);
	}

	/**
	 * @link http://php.net/manual/en/arrayaccess.offsetget.php
	 * @param string $offset <p>
	 */
	public function offsetGet($offset) {
		return $this->get($offset);
	}

	/**
	 * @link http://php.net/manual/en/arrayaccess.offsetset.php
	 * @param string $offset
	 * @param mixed $value
	 */
	public function offsetSet($offset, $value):void {
		$this->set($offset, $value);
	}

	/**
	 * @link http://php.net/manual/en/arrayaccess.offsetunset.php
	 * @param string $offset
	 */
	public function offsetUnset($offset):void {
		$this->delete($offset);
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

	protected function writeSessionData() {
		$this->sessionHandler->write(
			$this->id,
			serialize($this->data)
		);
	}
}