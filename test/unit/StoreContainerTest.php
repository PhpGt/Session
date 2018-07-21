<?php
namespace Gt\Session\Test;

use Gt\Session\Handler;
use Gt\Session\Session;
use PHPUnit\Framework\TestCase;

class StoreContainerTest extends TestCase {
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

	/**
	 * @@dataProvider data_randomKeyValuePairs
	 */
	public function testSetGet(array $keyValuePairs):void {
		$handler = $this->getMockBuilder(Handler::class)
			->getMock();
		$session = new Session($handler);

		foreach($keyValuePairs as $key => $value) {
			$session->set($key, $value);
		}

		foreach($keyValuePairs as $key => $value) {
			self::assertEquals($value, $session->get($key));
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

	public function data_randomKeyValuePairs():array {
		$data = [];

		for($i = 0; $i < 10; $i++) {
			$row = [];

			$numberKeys = rand(2, 10);
			$config = [];
			for($j = 0; $j < $numberKeys; $j++) {
				$key = uniqid("key");
				$value = uniqid("value");

				$config[$key] = $value;
			}

			$row []= $config;
			$data []= $row;
		}

		return $data;
	}
}