<?php
namespace Gt\Session\Test;

use Gt\Session\Handler;
use Gt\Session\Session;
use Gt\Session\Test\Helper\FunctionMocker;
use Gt\Session\Test\Helper\DataProvider\KeyValuePairProvider;
use PHPUnit\Framework\TestCase;

class SessionStoreTest extends TestCase {
	use KeyValuePairProvider;

	public function setUp() {
		FunctionMocker::mock("session_start");
	}

	/**
	 * @dataProvider data_randomKeyValuePairs
	 */
	public function testGetSetDotNotation(array $keyValuePairs):void {
		$handler = $this->getMockBuilder(Handler::class)
			->getMock();
		$session = new Session($handler);

		$sessionNamespace = "gt.test.session";

		foreach($keyValuePairs as $key => $value) {
			$fullKey = "$sessionNamespace.$key";
			$session->set($fullKey, $value);
		}

		foreach($keyValuePairs as $key => $value) {
			$fullKey = "$sessionNamespace.$key";
			self::assertEquals($value, $session->get($fullKey));
		}
	}
}