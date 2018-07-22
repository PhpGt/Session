<?php
namespace Gt\Session\Test;

use Gt\Session\Handler;
use Gt\Session\Session;
use Gt\Session\Test\Helper\DataProvider\ConfigProvider;
use Gt\Session\Test\Helper\DataProvider\StringProvider;
use Gt\Session\Test\Helper\FunctionMocker;
use Gt\Session\Test\Helper\DataProvider\KeyValuePairProvider;
use PHPUnit\Framework\TestCase;

class SessionTest extends TestCase {
	use KeyValuePairProvider;
	use StringProvider;
	use ConfigProvider;

	public function setUp() {
		FunctionMocker::mock("session_start");
	}

	public function testSessionStarts():void {
		$handler = $this->getMockBuilder(Handler::class)
			->getMock();

		self::assertEmpty(FunctionMocker::$mockCalls["session_start"]);
		$session = new Session($handler);
		self::assertCount(
			1,
			FunctionMocker::$mockCalls["session_start"]
		);
	}

	/**
	 * @dataProvider data_randomConfig
	 */
	public function testSessionStartsWithConfig(array $config):void {
		$handler = $this->getMockBuilder(Handler::class)
			->getMock();

		$session = new Session($handler ,$config);
		$sessionStartParameter = FunctionMocker::$mockCalls["session_start"][0][0];

		foreach($config as $key => $value) {
// For Windows compatibility, save_path is handled differently (see Session::getAbsolutePath)
			if($key === "save_path") {
				continue;
			}
			self::assertEquals($value, $sessionStartParameter[$key]);
		}
	}

	public function testWriteSessionDataCalled() {
		$handler = $this->getMockBuilder(Handler::class)
			->getMock();
		$handler->expects($this->exactly(2))
			->method("write");
		$session = new Session($handler);

		$session->set("test-key", "test-value");
		$session->remove("test-key");
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
	public function testContains(array $keyValuePairs):void {
		$handler = $this->getMockBuilder(Handler::class)
			->getMock();
		$session = new Session($handler);

		foreach($keyValuePairs as $key => $value) {
			$session->set($key, $value);
		}

		foreach($keyValuePairs as $key => $value) {
			self::assertTrue($session->contains($key));
		}
	}

	/**
	 * @dataProvider data_randomKeyValuePairs
	 */
	public function testNotContains(array $keyValuePairs):void {
		$handler = $this->getMockBuilder(Handler::class)
			->getMock();
		$session = new Session($handler);

		foreach($keyValuePairs as $key => $value) {
			$session->set($key, $value);
		}

		foreach($keyValuePairs as $key => $value) {
			self::assertFalse($session->contains("$key-oh-no"));
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
			self::assertTrue($session->contains($key));
			$session->remove($key);
			self::assertFalse($session->contains($key));
		}
	}
}