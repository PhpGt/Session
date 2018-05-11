<?php
namespace Gt\Session\Test;

use Gt\Session\Handler;
use Gt\Session\Session;
use PHPUnit\Framework\TestCase;

class SessionTest extends TestCase {
	/**
	 * @dataProvider data_randomString
	 */
	public function testGetReturnsNull(string $randomString):void {
		$handler = $this->getMockBuilder(Handler::class)
			->getMock();
		$session = new Session($handler);
		self::assertNull($session->get($randomString));
	}

	public function data_randomString():array {
		$data = [];

		for($i = 0; $i < 10; $i++) {
			$row = [];
			$row []= uniqid("random");
			$data []= $row;
		}

		return $data;
	}
}

namespace Gt\Session;
$sessionStartCalls = [];
function session_start() {
	$sessionStartCalls []= func_get_args();
}