<?php
namespace Gt\Session;

use SessionHandlerInterface;

abstract class Handler implements SessionHandlerInterface {

	/** @link http://php.net/manual/en/sessionhandlerinterface.close.php */
	abstract public function close():bool;

	/** @link http://php.net/manual/en/sessionhandlerinterface.destroy.php */
	abstract public function destroy(string $id = ""):bool;

	/** @link http://php.net/manual/en/sessionhandlerinterface.gc.php */
	abstract public function gc(int $maxLifeTime):int|false;

	/** @link http://php.net/manual/en/sessionhandlerinterface.open.php */
	abstract public function open(string $save_path, string $name):bool;

	/** @link http://php.net/manual/en/sessionhandlerinterface.read.php */
	abstract public function read(string $session_id):string;

	/** @link http://php.net/manual/en/sessionhandlerinterface.write.php */
	abstract public function write(string $session_id, string $session_data):bool;
}
