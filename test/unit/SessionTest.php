<?php
namespace Gt\Session\Test;

use Gt\Session\Handler;
use Gt\Session\Session;
use PHPUnit\Framework\TestCase;

class SessionTest extends TestCase {
	public static $sessionStartCalls = [];

	public function setUp() {
		self::$sessionStartCalls = [];
	}

	/**
	 * @dataProvider data_randomString
	 */
	public function testGetReturnsNull(string $randomString):void {
		$handler = $this->getMockBuilder(Handler::class)
			->getMock();
		$session = new Session($handler);
		self::assertNull($session->get($randomString));
	}

	public function testSessionStarts():void {
		$handler = $this->getMockBuilder(Handler::class)
			->getMock();

		self::assertEmpty(self::$sessionStartCalls);
		$session = new Session($handler);
		self::assertCount(1, self::$sessionStartCalls);
	}

	/**
	 * @dataProvider data_randomConfig
	 */
	public function testSessionStartsWithConfig(array $config):void {
		$handler = $this->getMockBuilder(Handler::class)
			->getMock();

		$session = new Session($handler ,$config);
		$sessionStartParameter = self::$sessionStartCalls[0][0];

		foreach($config as $key => $value) {
// For Windows compatibility, save_path is handled differently (see Session::getAbsolutePath)
			if($key === "save_path") {
				continue;
			}
			self::assertEquals($value, $sessionStartParameter[$key]);
		}
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

	public function data_randomConfig():array {
		$data = [];
		$configKeyList = [
			"save_path","name","cookie_lifetime","cookie_path",
			"cookie_domain","cookie_secure","cookie_httponly",
		];

		for($i = 0; $i < 10; $i++) {
			$row = [];
			$configItem = [];

			foreach($configKeyList as $configKey) {
				$configItem[$configKey] = uniqid($configKey);
			}

			$row []= $configItem;
			$data []= $row;
		}

		return $data;
	}
}

namespace Gt\Session;
function session_start() {
	\Gt\Session\Test\SessionTest::$sessionStartCalls []= func_get_args();
}