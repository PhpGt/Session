<?php
namespace Gt\Session;

use Gt\Session\Test\Helper\FunctionMocker;

function session_destroy() {
	if(FunctionMocker::$callState["session_start__fail"]) {
		unset(FunctionMocker::$callState["session_start__fail"]);
	}

	FunctionMocker::$mockCalls["session_destroy"] []= func_get_args();
	return true;
}