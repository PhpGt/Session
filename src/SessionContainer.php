<?php
namespace Gt\Session;

interface SessionContainer {
	public function get(string $key);
	public function set(string $key, $value);
	public function contains(string $key):bool;
	public function remove(string $key):void;
}