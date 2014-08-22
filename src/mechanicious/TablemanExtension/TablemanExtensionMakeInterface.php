<?php namespace mechanicious\TablemanExtension;

use mechanicious\Tableman\Tableman;
use mechanicious\TablemanExtension\Config;

interface TablemanExtensionMakeInterface
{
	public function make(Tableman &$reference, Config $config);
}