<?php
namespace Gt\Session;

use DirectoryIterator;

class FileHandler extends Handler {
	const EMPTY_PHP_ARRAY = "a:0:{}";
	protected $path;
	protected $cache;

	/**
	 * @link http://php.net/manual/en/sessionhandlerinterface.open.php
	 * @param string $save_path The path where to store/retrieve the session.
	 * @param string $name The session name.
	 */
	public function open(string $save_path, string $name):bool {
		$success = true;

		$save_path = str_replace(
			["/", "\\"],
			DIRECTORY_SEPARATOR,
			$save_path
		);

		$this->path = implode(DIRECTORY_SEPARATOR, [
			$save_path,
			$name,
		]);

		if(!is_dir($this->path)) {
			$success = mkdir($this->path, 0775, true);
		}

		return $success;
	}

	/**
	 * @link http://php.net/manual/en/sessionhandlerinterface.close.php
	 */
	public function close():bool {
		return true;
	}

	/**
	 * @link http://php.net/manual/en/sessionhandlerinterface.read.php
	 * @param string $session_id
	 */
	public function read(string $session_id):string {
		if(isset($this->cache[$session_id])) {
			return $this->cache[$session_id];
		}

		$filePath = $this->getFilePath($session_id);

		if(!file_exists($filePath)) {
			return "";
		}

		$this->cache[$session_id] = file_get_contents($filePath);
		return $this->cache[$session_id];
	}

	/**
	 * @link http://php.net/manual/en/sessionhandlerinterface.write.php
	 * @param string $session_id
	 * @param string $session_data
	 */
	public function write(string $session_id, string $session_data):bool {
		if($session_data === self::EMPTY_PHP_ARRAY) {
			return true;
		}
		$filePath = $this->getFilePath($session_id);
		$bytesWritten = file_put_contents($filePath, $session_data);
		return $bytesWritten !== false;
	}

	/**
	 * @link http://php.net/manual/en/sessionhandlerinterface.destroy.php
	 * @param string $id
	 */
	public function destroy($id):bool {
		$filePath = $this->getFilePath($id);

		if(file_exists($filePath)) {
			return unlink($filePath);
		}

		return true;
	}

	/**
	 * @link http://php.net/manual/en/sessionhandlerinterface.gc.php
	 * @param int $maxlifetime
	 */
	public function gc($maxlifetime):bool {
		$now = time();
		$expired = $now - $maxlifetime;

		foreach(new DirectoryIterator($this->path) as $fileInfo) {
			if(!$fileInfo->isFile()) {
				continue;
			}

			$lastModified = $fileInfo->getMTime();
			if($lastModified < $expired) {
				if(!unlink($fileInfo->getPathname())) {
					return false;
				}
			}
		}

		return true;
	}

	protected function getFilePath(string $id):string {
		return implode(DIRECTORY_SEPARATOR, [
			$this->path,
			$id,
		]);
	}
}
