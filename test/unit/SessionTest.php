<?php
namespace Gt\Session\Test;

use Gt\Session\Handler;
use Gt\Session\Session;
use Gt\Session\Test\Helper\FunctionMocker;
use PHPUnit\Framework\TestCase;

class SessionTest extends TestCase {
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