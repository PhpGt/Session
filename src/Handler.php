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
	 * @param string $session_id
	 */
	abstract public function destroy($session_id);

	/**
	 * @link http://php.net/manual/en/sessionhandlerinterface.gc.php
	 * @param int $maxlifetime
	 */
	abstract public function gc($maxlifetime):bool;

	/**
	 * @link http://php.net/manual/en/sessionhandlerinterface.open.php
	 * @param string $save_path The path where to store/retrieve the session.
	 * @param string $name The session name.
	 */
	abstract public function open($save_path, $name):bool;

	/**
	 * @link http://php.net/manual/en/sessionhandlerinterface.read.php
	 * @param string $session_id
	 */
	abstract public function read($session_id):string;

	/**
	 * @link http://php.net/manual/en/sessionhandlerinterface.write.php
	 * @param string $session_id
	 * @param string $session_data
	 */
	abstract public function write($session_id, $session_data):bool;
}