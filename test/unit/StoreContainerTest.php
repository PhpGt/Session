<?php
namespace Gt\Session\Test;

use Gt\Session\Handler;
use Gt\Session\Session;
use Gt\Session\Test\Helper\FunctionMocker;
use PHPUnit\Framework\TestCase;

class StoreContainerTest extends TestCase {
	public function setUp() {
		FunctionMocker::mock("session_start");
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
	public function testHasNot(array $keyValuePairs):void {
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