<?php
namespace Gt\Session;

class SessionStoreFactory {
	public static function create(string $namespace, Session $session):SessionStore {
		$namespaceParts = explode(".", $namespace);
		$store = new SessionStore(array_shift($namespaceParts), $session);

		foreach($namespaceParts as $part) {
			$innerStore = $store->createStore($part, $session);
			$store = $innerStore;
		}

		return $store;
	}
}