<?php
namespace Gt\Session;
function session_start() {
	\Gt\Session\Test\Helper\FunctionMocker::$mockCalls["session_start"] []= func_get_args();
}