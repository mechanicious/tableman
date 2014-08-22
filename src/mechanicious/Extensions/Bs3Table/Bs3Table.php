<?php namespace mechanicious\Extensions\Bs3Table;

use mechanicious\TablemanExtension\TablemanExtension;
use mechanicious\TablemanExtension\Config;
use mechanicious\Tableman\Tableman;
use Jacopo\Bootstrap3Table\BootstrapTable;

class Bs3Table extends TablemanExtension
{
	public function make(Tableman &$ref, Config $conf)
	{
    $items = $ref->getColumns();
    $columnNames = $ref->getColumnHeaders();
    $rows = $ref->getRows();

    $table = new BootstrapTable();
    $table->setConfig($conf->get('config'));
    $table->setHeader($conf->get('header'));
    $table->setTableExtraClasses($conf->get('extra_classes'));
    $limit = $conf->get('limit');

    array_walk($rows, function($row, $rowIndex) use(&$table, &$columnNames, $limit) {
      if( ! is_null($limit) && $rowIndex > $limit) return;
      // Flatten I mean from boundary: array('columnName' => 'rowData'), to: array('rowData').
      foreach($columnNames as $columnName) // This guy dictates the order of cells
      {
        $flattenRows = array(); 
        foreach($row as $columnHeader => $cell)
        {
          $mockedRow = array();
          if(isset($row[$columnName])) // If this is false then data got somehow mixed up
            $mockedRow[] = array($row[$columnName]);
        }
        $flattenRows[] = $row;
      }
      $table->addRows(array_flatten($flattenRows));
    });
    // __toString do the work!
    return (string) $table;
	}
}