<?php
namespace Gt\Session;

use SessionHandlerInterface;

abstract class Handler implements SessionHandlerInterface {

	/**
	 * @link http://php.net/manual/en/sessionhandlerinterface.close.php
	 */
	abstract public function close():bool;

	/**
	 * @link http://php.net/manual/en/sessionhandlerinterface.destroy.php
	 * @param string $id
	 */
	abstract public function destroy(string $id = ""):bool;

	/**
	 * @link http://php.net/manual/en/sessionhandlerinterface.gc.php
	 * @param int $max_lifetime
	 */
	abstract public function gc(int $max_lifetime):int|false;

	/**
	 * @link http://php.net/manual/en/sessionhandlerinterface.open.php
	 * @param string $save_path The path where to store/retrieve the session.
	 * @param string $name The session name.
	 */
	abstract public function open(string $save_path, string $name):bool;

	/**
	 * @link http://php.net/manual/en/sessionhandlerinterface.read.php
	 * @param string $session_id
	 */
	abstract public function read(string $session_id):string;

	/**
	 * @link http://php.net/manual/en/sessionhandlerinterface.write.php
	 * @param string $session_id
	 * @param string $session_data
	 */
	abstract public function write(string $session_id, string $session_data):bool;
}
