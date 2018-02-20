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

	protected $data;
	protected $id;
	/** @var SessionHandlerInterface */
	protected $sessionHandler;

	// TODO: Default session path passed in from default config...........................

	public function __construct(
		SessionHandlerInterface $sessionHandler,
		string $id = null,
		iterable $config
	) {
		$this->sessionHandler = $sessionHandler;

		if(is_null($id)) {
			$id = $this->getId();
		}

		$this->id = $id;

		$sessionPath = $this->getAbsolutePath(
			$config["path"] ?? self::DEFAULT_SESSION_PATH
		);
		$sessionName = $config["name"] ?? self::DEFAULT_SESSION_NAME;
		session_start([
			"save_path" => $sessionPath,
			"name" => $sessionName,
			"cookie_lifetime" => $config["lifetime"] ?? self::DEFAULT_SESSION_LIFETIME,
			"cookie_path" => $config["path"] ?? self::DEFAULT_SESSION_PATH,
			"cookie_domain" => $config["domain"] ?? self::DEFAULT_SESSION_DOMAIN,
			"cookie_secure" => $config["secure"] ?? self::DEFAULT_SESSION_SECURE,
			"cookie_httponly" => $config["httponly"] ?? self::DEFAULT_SESSION_HTTPONLY,
		]);


		$this->sessionHandler->open($sessionPath, $sessionName);
		$this->data = $this->readSessionData();
	}

	public function get(string $key):?string {
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

	protected function getId():string {
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
		$success = $this->sessionHandler->write(
			$this->id,
			serialize($this->data)
		);
	}
}