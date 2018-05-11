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

	/**
	 * @@dataProvider data_randomKeyValuePairs
	 */
	public function testSetGetNotExistsOtherKey(array $keyValuePairs):void {
		$handler = $this->getMockBuilder(Handler::class)
			->getMock();
		$session = new Session($handler);

		foreach($keyValuePairs as $key => $value) {
			$session->set($key, $value);
		}

// Test that other keys' values do not match.
		foreach($keyValuePairs as $key => $value) {
			do {
				$otherKey = array_rand($keyValuePairs);
			}while($otherKey === $key);

			self::assertNotEquals($value, $session->get($otherKey));
		}

// Test that unknown keys' values do not match.
		foreach($keyValuePairs as $key => $value) {
			self::assertNotEquals(
				$value,
				$session->get("$key-oh-no")
			);
		}
	}

	/**
	 * @dataProvider data_randomKeyValuePairs
	 */
	public function testHas(array $keyValuePairs):void {
		$handler = $this->getMockBuilder(Handler::class)
			->getMock();
		$session = new Session($handler);

		foreach($keyValuePairs as $key => $value) {
			$session->set($key, $value);
		}

		foreach($keyValuePairs as $key => $value) {
			self::assertTrue($session->has($key));
		}
	}

	/**
	 * @dataProvider data_randomKeyValuePairs
	 */
	public function testOffsetExists(array $keyValuePairs):void {
		$handler = $this->getMockBuilder(Handler::class)
			->getMock();
		$session = new Session($handler);

		foreach($keyValuePairs as $key => $value) {
			$session->set($key, $value);
		}

		foreach($keyValuePairs as $key => $value) {
			self::assertTrue(isset($session[$key]));
		}
	}

	/**
	 * @dataProvider data_randomKeyValuePairs
	 */
	public function testHasNot(array $keyValuePairs):void {
		$handler = $this->getMockBuilder(Handler::class)
			->getMock();
		$session = new Session($handler);

		foreach($keyValuePairs as $key => $value) {
			$session->set($key, $value);
		}

		foreach($keyValuePairs as $key => $value) {
			self::assertFalse($session->has("$key-oh-no"));
		}
	}

	/**
	 * @dataProvider data_randomKeyValuePairs
	 */
	public function testOffsetNotExists(array $keyValuePairs):void {
		$handler = $this->getMockBuilder(Handler::class)
			->getMock();
		$session = new Session($handler);

		foreach($keyValuePairs as $key => $value) {
			$session->set($key, $value);
		}

		foreach($keyValuePairs as $key => $value) {
			self::assertFalse(isset($session["$key-oh-no"]));
		}
	}

	/**
	 * @dataProvider data_randomKeyValuePairs
	 */
	public function testDelete(array $keyValuePairs):void {
		$handler = $this->getMockBuilder(Handler::class)
			->getMock();
		$session = new Session($handler);

		foreach($keyValuePairs as $key => $value) {
			$session->set($key, $value);
		}

		uasort($keyValuePairs, function () {
			return rand(-1, 1);
		});

		foreach($keyValuePairs as $key => $value) {
			self::assertTrue($session->has($key));
			$session->delete($key);
			self::assertFalse($session->has($key));
		}
	}

	public function testWriteSessionDataCalled() {
		$handler = $this->getMockBuilder(Handler::class)
			->getMock();
		$handler->expects($this->exactly(2))
			->method("write");
		$session = new Session($handler);

		$session->set("test-key", "test-value");
		$session->delete("test-key");
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

namespace Gt\Session;
function session_start() {
	\Gt\Session\Test\SessionTest::$sessionStartCalls []= func_get_args();
}