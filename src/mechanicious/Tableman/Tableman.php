<?php namespace mechanicious\Tableman;

use mechanicious\Columnizer\ColumnBag;
use mechanicious\Columnizer\Column;
use mechanicious\Columnizer\Columnizer;
use mechanicious\TablemanExtension\TablemanExtension;
use mechanicious\TablemanExtension\Config;
use Illuminate\Support\Collection;

/**
* Tableman
* Provides a convenient API for manipulation and 
* creation of tables.
*/
class Tableman extends Collection
{
  /**
   * Holds the extensions
   * @var array
   */
  protected $exts = array();

  public function __construct(ColumnBag $cols)
  {
    $this->items = $cols->all();
  }

  /**
   *  Get JSON representation of the data
   * 
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
   * 
   * @param  closure $callback
   * @callback function($previous, $current) {// -1, 0, 1}
   * @usage http://php.net/manual/en/function.uasort.php#refsect1-function.uasort-parameters
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
  public function reverse()
  {
    $this->items = array_reverse($this->items);
    return $this;
  }

  /**
   * Reverse the order of columns, alias of Tableman::reverse
   *  
   * @return mechanicious\Tableman\Tableman;
   */
  public function reverseColumns()
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
   * 
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
    return in_array($needle, $this->items[$header]->toArray());
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
   * 
   * @param  Column $col
   * @return mechanicious\Tableman\Tableman;
   */
  public function prependColumn(Column $col)
  {
    $this->items[$col->getHeader()] = $this->padData($col);
    $order = $this->getColumnHeaders();
    array_unshift($order, $col->getHeader());
    $this->orderColumns($order);
    return $this;
  }

  /**
   * Append column
   * 
   * @param  Column $col
   * @return mechanicious\Tableman\Tableman;
   */
  public function appendColumn(Column $col)
  {
    $this->items[$col->getHeader()] = $this->padData($col);
    return $this;
  }

  /**
   * Remove the last column
   *   
   * @param  string $header
   * @return mechanicious\Tableman\Tableman
   */
  public function popColumn()
  {
    $this->pop();
    return $this;
  }

  /**
   * Remove first column
   * 
   * @param  string $header
   * @return mechanicious\Tableman\Tableman
   */
  public function shiftColumn()
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
   * @param     closure $callback
   * @callback  function(&$ref, &$row, $rowIndex) {}
   * @return    mechanicious\Tableman\Tableman
   */
  public function eachRowOf($header, \closure $callback)
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
   * @param     closure $callback
   * @callback  function(&$ref, &$row, $rowIndex) {}
   * @return    mechanicious\Tableman\Tableman
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
   * @param     closure $callback
   * @callback  function(&$ref, &$cell, &$row, $rowIndex) {}
   * @return    mechanicious\Tableman\Tableman
   */
  public function eachCell(\closure $callback) 
  {
    array_walk($this->getRows(), function(&$row, $rowIndex) use(&$rows, $callback) {
      foreach($row as $index => &$cell)
      {
        $callback($this, $cell, $row, $rowIndex);
      }
    });
    return $this;
  }

  /**
   * Apply a callback on each column
   * 
   * @param     closure $callback
   * @callback  function(&$ref, &$column, $header) {}
   * @return    mechanicious\Tableman\Tableman
   */
  public function eachColumn(\closure $callback) 
  {
    array_walk($this->items, function(&$column, &$columnHeader) use($callback, &$columns) {
      $callback($this, $column, $columnHeader);      
    });
    return $this;
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
    // We need a copy because the order will be overridden by the replace routine
    // because in a certain situation some replacements will be skipped while still
    // appending the other columns.
    $headersCopy = $this->getColumnHeaders(); 
    array_walk($headers, function($newKey, $oldKey) use(&$items, &$headersCopy) {
      if($newKey !== $oldKey) // No need to bother when headers are same
      {
        $column = new Column($items[$oldKey]->all(), $newKey);
        $this->forget($oldKey);
        $this->put($newKey, $column);
        // Replace the old header from $headersCopy with the new header
        $headersCopy[array_search($oldKey, $headersCopy)] = $newKey;
      }
    });
    // Order columns like they where before.
    $this->orderColumns($headersCopy);
    return $this;
  }

  public function __call($name, $args)
  {
    $autloadedClasses = get_declared_classes();

    // Don't bother, it seems we did what's needed before already before.
    if(in_array($name, array_keys($this->exts))) return $this->exts[$name]->make($this, new Config($args[0]));

    /**
     * Use existing class mechanism
     */
    $found = false;
    // Let's see if the class is loaded first.
    array_walk($autloadedClasses, function($class, $index) use($name, &$found) {
      if(is_string($found)) return;
      $segments = explode('\\', $class);
      // We assume the last segment of a fully-qualified-classname is equal to the
      // entry in the array.
      if(last($segments) === $name) return $found = $class;
    });
    // Make sure it's a TablemanExtension
    if($found && ($instance = new $found) instanceof TablemanExtension)
    {
      $this->exts[$name] = $instance;
      return $this->exts[$name]->make($this, new Config($args[0]));
    } 

    /*
     * Load non-existent class mechanism
     */
    $ext_reg = require "/../Config/ExtensionRegister.php";
    if( ! in_array($name, array_keys($ext_reg))) throw new \Exception("method {$name} is not a part of " . __CLASS__);
    require_once $ext_reg[$name]['relative_path'];
    $classname = $ext_reg[$name]['fully_qualified_classname'];
    $instance = new $classname;
    $this->exts[$name] = $instance;
    if( ! isset($args[0])) throw new \Exception("you must provide a config for {$name}");
    if( ! is_array($args[0])) throw new \Exception("config for {$name} must be an array");
    return $this->exts[$name]->make($this, new Config($args[0]));
  }
}