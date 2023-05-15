<?php
namespace Gt\Session;
use Gt\Session\Test\Helper\FunctionMocker;

function session_id(string $newId = null) {
	$existing = end(FunctionMocker::$mockCalls["session_id"]);
	if(is_array($existing)) {
		$existing = $existing[0];
	}
	FunctionMocker::$mockCalls["session_id"] []= func_get_args();
	return $newId ?? $existing ?? "TEST";
}
