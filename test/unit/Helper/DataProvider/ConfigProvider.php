<?php
namespace Gt\Session\Test\Helper\DataProvider;

trait ConfigProvider {
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