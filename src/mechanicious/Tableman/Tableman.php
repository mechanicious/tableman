<?php namespace mechanicious\Tableman;

use mechanicious\Columnizer\ColumnBag;
use mechanicious\Columnizer\Column;
use mechanicious\Columnizer\Columnizer;
use Illuminate\Support\Collection;
use Jacopo\Bootstrap3Table\BootstrapTable;

/**
* Tableman
* Provides a convenient API for manipulation and 
* creation of tables.
* 
* TODO: Write missing tests.
*/
class Tableman extends Collection
{
  public function __construct(ColumnBag $cols)
  {
    $this->items = $cols->all();
  }

  /**
   *  Get JSON representation of the data
   * @return string
   */
  public function getJson($format = 'column')
  {
    switch ($format) {
      case 'column':
        return $json = $this->toJson();
      break;

      default:
        $collection = new Collection($this->getRows());
        return $json = $collection->toJson();
      break;
    }
  }
  

  private function getHTML(){// TODO
  }
  
  private function getXML(){// TODO
  }

  /**
   * Sort columns with a callback function
   * @param  closure $callback
   * @return mechanicious\Tableman\Tableman;
   */
  public function sortColumns(\closure $callback)
  {
    $this->sort($callback);
    return $this;
  }

  /**
   * Put columns into a desired order
   * 
   * @return mechanicious\Tableman\Tableman
   */
  public function orderColumns(array $order = array())
  {
    array_walk($order, function() use(&$order) {
      $this->sort(function($previous, $current) use($order) {
        // Find the $current and $previous column headers in the $order array
        // provided by the user. Get the offsets from the $order provided by the user.
        $prevOffset    = array_search($previous->getHeader(), $order);
        $currentOffset = array_search($current->getHeader(), $order);
        // ... And then just simply compare those together.  
        if($prevOffset == $currentOffset) return 0;
        return ($prevOffset < $currentOffset) ? -1 : 1;
      });
    });
  }

  /**
   * Get order of columns
   * 
   * @return array
   */
  public function getColumnOrder()
  {
    return array_keys($this->items);
  }

  /**
   * Get current headers of columns
   * @return array
   */
  public function getColumnHeaders()
  {
    return array_keys($this->items);
  }

  /**
   * Check if a column exists
   *  
   * @return bool
   */
  public function columnExists()
  {
    return in_array($header, $this->getColumnHeaders());
  }

  /**
   * Check if a certain column has a certain value
   * 
   * @param  string $header
   * @param  mixed $needle
   * @return bool
   */
  public function columnHas($header, $needle)
  {
    return in_array($needle, $this->items[$header]);
  }

  /**
   * Add column at a particular offset
   * 
   * @param Column $col
   * @param int $offset
   */
  public function addColumn(Column $col, $offset)
  {
    $this->splice($offset, 0, $col);
    return $this;
  }

  /**
   * Prepend column
   * @param  Column $col
   * @return mechanicious\Tableman\Tableman;
   */
  public function prependColumn(Column $col)
  {
    $this->prepend($col);
    return $this;
  }

  /**
   * Append column
   * @param  Column $col
   * @return mechanicious\Tableman\Tableman;
   */
  public function appendColumn(Column $col)
  {
    $this->push($col);
    return $this;
  }

  /**
   * Remove the last column
   *   
   * @param  string $header
   * @return mechanicious\Tableman\Tableman
   */
  public function popColumn($header)
  {
    $this->pop();
    return $this;
  }

  /**
   * Remove first column
   * @param  string $header
   * 
   * @return mechanicious\Tableman\Tableman
   */
  public function shiftColumn($header)
  {
    $this->shift();
    return $this;
  }

  /**
   * Remove a column the header
   * 
   * @param  string $header
   * @return mechanicious\Tableman\Tableman
   */
  public function removeColumn($header)
  {
    $this->forget($header);
    return $this;
  }

  /**
   * Check if tho columns carry same content
   * 
   * @param  string $a
   * @param  string $b
   * @return boolean
   */
  public function compareColumns($colA, $colB)
  {
    return (bool) ($colA->toJson() === $colB->toJson());
  }

  /**
   * Replace column by another column
   * 
   * @param  Column $col
   * @param  string $header
   * @return mechanicious\Tableman\Tableman;
   */
  public function replaceColumn(Column $col, $header)
  {
    if( ! in_array($header, $this->items)) throw new Exception("column {$header} don't exist");
    
    $this->items[$header] = $col;
    return $this;
  }

  /**
   * Apply a callback on each row belonging to a specified column
   * 
   * @param  closure $callback
   * @return mechanicious\Tableman\Tableman
   */
  public function eachRowOf(\closure $callback, $header)
  {
    $rows = $this->items[$header]->getRows();
    array_walk($rows, function(&$row, &$rowIndex) use(&$rows, $callback) {
      $callback($this, $row, $rowIndex);
    });
    $this->swapColumn($rows);
    return $this;
  }

  /**
   * Apply a callback on each row
   * 
   * @param  closure $callback
   * @return mechanicious\Tableman\Tableman
   */
  public function eachRow(\closure $callback) 
  {
    $rows = $this->getRows();
    array_walk($rows, function(&$row, &$rowIndex) use(&$rows, $callback) {
      $callback($this, $row, $rowIndex);
    });
    $this->swap($rows);
    return $this;
  }

  /**
   * Apply a callback on each cell
   * 
   * @param  closure $callback
   * @return mechanicious\Tableman\Tableman
   */
  public function eachCell(closure $callback) 
  {
     $rows = $this->getRows();
    array_walk($rows, function(&$row, &$rowIndex) use(&$rows, $callback) {
      foreach($row as $cellColumn => &$cell)
      {
        $callback($this, $cell, $cellColumn, $row, $rowIndex);
      }
    });
    $this->swap($rows);
    return $this;
  }

  /**
   * Apply a callback to each column
   * 
   * @param  closure $callback
   * @return mechanicious\Tableman\Tableman
   */
  public function eachColumn(closure $callback) 
  {
    $columns = $this->items;
    array_walk($columns, function(&$column, &$columnHeader) use($callback) {
      $callback($this, $column, $columnHeader);
    });
    $this->swap($columns);
    return $this;
  }

  /**
   * Apply a callback to each column header
   * 
   * @param  closure $callback
   * @return mechanicious\Tableman\Tableman
   */
  public function eachHeader(closure $callback) 
  {
    $columns = $this->items;
    array_walk($columns, function(&$column, &$columnHeader) use($callback) {
      $callback($this, $columnHeader);
    });
    $this->swap($columns);
    return $this;
  }

  /**
   * Create an Bootstrap 3 Table HTML markup.
   * 
   * @param   int $limit
   * @param   array $header
   * @param   array $extraClasses
   * @param   array  $config
   * @return  string
   */
  public function getBS3Table($limit = null, $header = array(), $extraClasses = array(), $config = array())
  {
    $items = &$this->items;
    $columnNames = array_keys($this->items);
    $rows = $this->getRows();

    $table = new BootstrapTable();
    $table->setConfig($config);
    $table->setHeader($header);
    $table->setTableExtraClasses($extraClasses);

    array_walk($rows, function($row, $rowIndex) use(&$table, &$columnNames, $limit) {
      if( ! is_null($limit) && $rowIndex > $limit) return;
      // Flatten I mean from boundary: array('columnName' => 'rowData'), to: rowData only.
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

  /**
   * Get a column by it's header
   *   
   * @param  string $header
   * @return mechanicious\Columnizer\Column;
   */
  public function getColumn($header)
  {
    return $this[$header];
  }

  /**
   * Get columns
   * 
   * @return array
   */
  public function getAllColumns()
  {
    return $this->items;
  }

  /**
   * Get the rows as an array
   * 
   * @return  array
   */
  public function getRows()
  {
    $items  = &$this->items;
    $mergee = array();
    $columNames = array_keys($this->items);
    // A short explanation. 
    // For each column we'll loop though the rows.
    // If the row index doesn't exist in the mergee then we'll push the row
    // into the mergee. Otherwise we won't. See assembleRow() for how a row
    // is being assembled.
    // We could just loop through one of the columns, but we want to deal with
    // asymmetric arrays as well.
    foreach($items as $columnName => &$column)
    {
      for($i = 0; $i < count($column); $i++)
      {
        if( ! isset($mergee[$i]))
          $mergee[] = $this->assembleRow($columNames, $i);
      }
    }
    return $mergee;
  }

  /**
   * Assemble a row from columns
   * 
   * @param   array $columnNames
   * @param   int $index
   * @return  array
   */
  protected function assembleRow(&$columnNames, $index)
  {
    // Notice we deal with asymmetric arrays. If the index doesn't
    // exist in the co-columns then we'll push for those column-index
    // combination a null.
    $row = array();
    array_walk($columnNames, function($column) use($index, &$row) {
      // Note we'll bind the column name to cells as well, although it's not really
      // necessary. To make sure we'll not mix the data in some unwanted way.
      if(isset($this[$column][$index])) return $row[$column] = $this[$column][$index];
      // This is how a row "looks like."
      // array(
      // array('id' => 'Tony', 'name' => 'Tony', 'age' => '27')
      // );
      return $row[$column] = null; 
    });
    return $row;
  }

  /**
   * Replace the current set of items with new items
   * 
   * @param  array $items
   * @return void
   */
  protected function swap($items)
  {
    $this->__construct(with(new Columnizer($items))->columnize());
  }

  /**
   * Unlike getColumn headers this function doesn't get the keys
   * of the current set, but the header of the columns obtained
   * with getHeader()
   * 
   * @return array
   */
  protected function getGeunineColumnHeaders()
  {
    return array_map($this->items, function($column) {
      return $column->getHeader();
    });
  }

  /**
   * Rename Headers
   * 
   * @return void
   */
  public function renameColumns(array $headers = array())
  {
    $items = $this->items;
    array_walk($items, function($column, $header) use(&$headers) {
      $oldKey = $header;
      $newKey = $headers[$header];
      // We don't want to change the key in items array only,
      // but we want to change the key of the column as well.
      // So we can simply replace the keys.
      $column = new Column($column->all(), $newKey);
      $this->put($newKey, $column);
      $this->forget($oldKey);
    });
    return $this;
  }
}