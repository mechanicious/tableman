<?php namespace mechanicious\Tableman;

use mechanicious\Columnizer\ColumnBag;
use mechanicious\Columnizer\Columnizer;
use Illuminate\Support\Collection;
use Jacopo\Bootstrap3Table\BootstrapTable;

/**
* Tableman
*/
class Tableman extends Collection
{
	public function __construct(ColumnBag $cols)
	{
		$this->items = $cols->all();
	}

	/**
	 * 	Get JSON representation of the data
	 * @return [type]
	 */
	public function getJSON()
	{
		return with(new static(new ColumnBag($this->items)))->toJSON();
	}
	

	private function getHTML(){// TODO
	}
	
	private function getXML(){// TODO
	}

  /**
   * Execute a callback to each row
   * @param  closure $callback
   * @return void
   */
  public function eachRow(\closure $callback) 
  {
    $rows = $this->getRows();
    array_walk($rows, function(&$row, &$rowIndex) use(&$rows, $callback) {
      $callback($this, $row, $rowIndex);
    });
    $this->swap($rows);
  }

  public function eachCell(closure $callback) 
  {
     $rows = $this->getRows();
    array_walk($rows, function(&$row, &$rowIndex) use(&$rows, $callback) {
      foreach($row as $cellColumn => &$cell)
      {
        $callback($this, $cell, $cellColumn, $row, $rowIndex);
      }
    });
  }

  public function eachColumn(closure $callback) 
  {
    $columns = $this->items;
    array_walk($columns, function(&$column, &$columnHeader) use($callback) {
      $callback($this, $column, $columnHeader);
    });
  }

  public function eachHeader(closure $callback) {}

	/**
	 * 	Create an Bootstrap 3 Table HTML markup.
	 * @param  	int $limit
	 * @param  	array $header
	 * @param  	array $extraClasses
	 * @param  	array  $config
	 * @return 	string
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
	 * 	Get the rows as an array
	 * @return 	array
	 */
	public function getRows()
	{
		$items 	= &$this->items;
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
	 * 	Assemble a row from columns
	 * @param  	array $columnNames
	 * @param  	int $index
	 * @return 	array
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
   * @param  array $items
   * @return void
   */
  protected function swap($items)
  {
    $this->__construct(with(new Columnizer($items))->columnize());
  }
}