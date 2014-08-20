<?php namespace mechanicious\Columnizer;

use Illuminate\Support\Collection;

class Columnizer
{
  /**
   *  Holds the items
   * @var array
   */
  public $items = array();

  /**
   *  Initialize the columnizer by providing an array with data
   * @param array $data
   */
  public function __construct(array $data = array())
  {
    $this->items = $data; 
  }

  /**
   *  Go from rows to columns
   * 
   * @return  mechanicious\Columnizer\ColumnBag
   */
  public function columnize()
  {
    if($this->items instanceof ColumnBag) return $this->items;
    // Somtimes the data has already a columnized structure, then you
    // just want to wrap it in the bag.

    $columnized = $this->columnizeItems();
    $assemble   = array();
    array_walk($columnized, function($column, $columnName) use(&$assemble) {
      $assemble[$columnName] = new Column($column, $columnName);
    });
    return new ColumnBag($assemble);
  }

  /**
   * Line up duplicate haeader
   *  
   * @param  int $counter
   * @return string
   */
  protected function safeHeader($counter)
  {

  }

  /**
   *  Pre-fill missing collumns to line the rows up 
   * @return  void
   */
  protected function symmetrize()
  {
    $columnNames = $this->identifyColumns();
    $columnCount = count($this->identifyColumns());
    $items = &$this->items;
    array_walk($items, function(&$row, $rowIndex) use($columnCount, $columnNames) {
      if(count($row) < $columnCount)
      {
        // We have to pre-fill the missing columns in other rows
        // to avoid complications later on.
        foreach($row as $cellIndex => $cell) {
          foreach($columnNames as $columnName) {
            if( ! isset($row[$columnName]))
              $row[$columnName] = null;
          }
        }
      }
    });
  }

  /**
   *  Columnize data
   * 
   * @return  array
   */
  protected function columnizeItems()
  {
    $this->symmetrize();
    $items = &$this->items;
    $columnNames = $this->identifyColumns();
    $columnized = array();

    array_walk($columnNames, function($columnName, $index) use ($items, &$columnized) {
      $columnized[$columnName] = array_column($items, $columnName);
    });
    return $columnized;
  }

  /**
   *  Identify what the columns are 
   * @return array
   */
  public function identifyColumns()
  {
    $largest = array();
    $items = &$this->items;
    // We can deal with a situation when the data is
    // inconsistent. One row may have one extra column
    // somehow. So we'll assume that the largest row
    // contains a complete set of columns.
    array_walk($items, function($item, $key) use($items, &$largest) {
      if(count($items[$key]) > count($largest)) $largest = $items[$key];
    });

    return $columns = array_keys($largest);
  }
}