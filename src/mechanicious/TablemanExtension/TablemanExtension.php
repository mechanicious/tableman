<?php namespace mechanicious\TablemanExtension;

use mechanicious\Tableman\Tableman;

abstract class TablemanExtension implements TablemanExtensionMakeInterface
{
  abstract public function make(Tableman &$ref, Config $conf);
}
