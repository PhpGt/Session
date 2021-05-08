<?php
namespace Gt\Session\Test;

use Gt\Session\Handler;
use Gt\Session\Session;
use Gt\Session\SessionStore;
use Gt\Session\Test\Helper\FunctionMocker;
use Gt\Session\Test\Helper\DataProvider\KeyValuePairProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SessionHandler;

class SessionStoreTest extends TestCase {
	use KeyValuePairProvider;

	protected function setUp():void {
		FunctionMocker::mock("session_start");
		FunctionMocker::mock("session_id");
	}

	/**
	 * @dataProvider data_randomKeyValuePairs
	 */
	public function testGetSetDotNotation(array $keyValuePairs):void {
		/** @var MockObject|SessionHandler $handler */
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

	public function testRemoveSelf():void {
		/** @var MockObject|SessionHandler $handler */
		$handler = $this->getMockBuilder(Handler::class)
			->getMock();
		$session = new Session($handler);

		$leafStore = $session->getStore(
			"gt.test.session.trunk.leaf",
			true
		);
		$trunkStore = $session->getStore(
			"gt.test.session.trunk"
		);

		$trunkStore->remove();

		self::assertNull($session->getStore("gt.test.session.trunk"));
		self::assertNull($session->getStore("gt.test.session.trunk.leaf"));
		self::assertInstanceOf(
			SessionStore::class,
			$session->getStore("gt.test.session")
		);
	}

	public function testGetCreatesNonExistantStore() {
		/** @var MockObject|SessionHandler $handler */
		$handler = $this->getMockBuilder(Handler::class)
			->getMock();
		$session = new Session($handler);

		$trunkStore = $session->getStore(
			"gt.test.session",
			true
		);
		$leafStore = $session->getStore(
			"gt.test.session.leaf",
			true
		);

		self::assertNotNull($leafStore);
	}
}