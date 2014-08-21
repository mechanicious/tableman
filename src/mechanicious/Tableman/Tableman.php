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
  

  private function getHtml(){// TODO
  }
  
  private function getXml(){// TODO
  }

  /**
   * Returns whatever the callback function returns
   *  
   * @param  closure $callback
   * @return mixed
   */
  public function withdraw(\closure $callback)
  {
    return $callback($this);
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
   * Reverse the order of columns
   *  
   * @return mechanicious\Tableman\Tableman;
   */
  public function reverseColumnOrder()
  {
    $this->reverse();
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
   * Get the columns
   * 
   * @return array
   */
  public function getColumns()
  {
    return $this->items;
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
  public function columnExists($header)
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
    $header = $col->getHeader();
    // If user is adding an already existing index, then
    // we'll assume the client intends to replace the columns.
    if($this->columnExists($header)) $this->forget($header);
    // Create expected order
    $columnOrder = $this->getColumnHeaders();
    array_splice($columnOrder, $offset, 0, $header);
    // Add column into the items
    $this->items[$header] = $this->padData($col);
    // Apply our order
    $this->orderColumns($columnOrder);
    return $this;
  }

  /**
   * Symmetrize data if the given column is asymmetric  
   * 
   * @param  mechanicious\Columnizer\Column $col
   * @return mechanicious\Columnizer\Column
   */
  public function padData(Column $col, $padValue = null)
  {
    if( ( $colSize = count($col) ) > ( $dataSize = count($this->first()) ) )
    {
      $this->eachColumn(function(&$ref, &$column, $header) use($colSize, $padValue) {
        $column->items = array_pad($column->all(), $colSize, $padValue);
      });
    }
    else
    {
     $col->items = array_pad($col->all(), $dataSize, $padValue);
    }
    // Either way return the column back.
    return $col;
  }

  /**
   * Prepend column
   * @param  Column $col
   * @return mechanicious\Tableman\Tableman;
   */
  public function prependColumn(Column $col)
  {
    $this->prepend($this->padData($col));
    return $this;
  }

  /**
   * Append column
   * @param  Column $col
   * @return mechanicious\Tableman\Tableman;
   */
  public function appendColumn(Column $col)
  {
    $this->push($this->padData($col));
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
  public function compareColumnContent($colA, $colB)
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
    if( ! in_array($header, array_keys($this->items))) throw new \Exception("column {$header} don't exist in " . implode(', ', array_keys($this->items)));
    
    $this->items[$header] = $col;
    if($header !== $col->getHeader())
    {
      $this->renameColumns(array($header => $col->getHeader()));
    } 
      
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
    array_walk($this->items[$header]->items, function(&$row, $rowIndex) use($callback)
    {
      $callback($this, $row, $rowIndex);
    });
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
    array_walk($this->getRows(), function(&$row, $rowIndex) use($callback)
    {
      $callback($this, $row, $rowIndex);
    });
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
    array_walk($this->getRows(), function(&$row, &$rowIndex) use(&$rows, $callback) {
      foreach($row as $cellColumn => &$cell)
      {
        $callback($this, $cell, $cellColumn, $row, $rowIndex);
      }
    });
    return $this;
  }

  /**
   * Apply a callback to each column
   * 
   * @param  closure $callback
   * @return mechanicious\Tableman\Tableman
   */
  public function eachColumn(\closure $callback) 
  {
    array_walk($this->items, function(&$column, &$columnHeader) use($callback, &$columns) {
      $callback($this, $column, $columnHeader);      
    });
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
  public function getBs3Table($limit = null, $header = array(), $extraClasses = array(), $config = array())
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
   * Get referenced-rows as an array
   * 
   * @return  array
   */
  public function getRows()
  {
    $items  = &$this->items;
    $mergee = array();
    $columnNames = array_keys($this->items);
    // We assume the present data is symmetric
    for($i = 0; $i < count($this->first()); $i++)
    {
      $row = array();
      foreach($items as $header => &$column)
      {
        $row[$header] = &$column->items[$i];
      }
      $mergee[] = $row;
    }
    return $mergee;
  }

  /**
   * Rename Headers
   * 
   * @return void
   */
  public function renameColumns(array $headers = array())
  {
    $items = $this->items;
    // We'll need this to keep the order later on if user will decide
    // to for example rename only one column instead of all. That's because
    // we use put(), and put appends. (We're supposed to only rename the columns after all)
    $headersCopy = $this->getColumnHeaders();
    array_walk($headers, function($newKey, $oldKey) use(&$items, &$headersCopy) {
      // We don't want to change the key in items array only,
      // but we want to change the key of the column as well.
      // So we can simply replace the keys.
      
      // If you would do this when headers are equal, then you
      // would clean the array out of items because you would
      // not create any new entries and still remove old ones.
      if($newKey !== $oldKey)
      {
        $column = new Column($items[$oldKey]->all(), $newKey);
        $this->put($newKey, $column);
        $this->forget($oldKey);
        $headersCopy[array_search($oldKey, $headersCopy)] = $newKey;
      }
    });
    // Order columns like they where before.
    $this->orderColumns($headersCopy);
    return $this;
  }
}