<?php
namespace Gt\Session;
function session_id() {
	\Gt\Session\Test\Helper\FunctionMocker::$mockCalls["session_id"] []= func_get_args();
	return "TEST";
}