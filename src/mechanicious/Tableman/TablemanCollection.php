<?php namespace mechanicious\Tableman;

use mechanicious\Columnizer\ColumnBag;
use mechanicious\Columnizer\Column;
use mechanicious\Columnizer\Columnizer;
use mechanicious\TablemanExtension\TablemanExtension;
use mechanicious\TablemanExtension\Config;
use Illuminate\Support\Collection;

/**
 * Responsiblity to make Collection support Tableman. 
 * Adds a Collection compatibility(kind of) layer for Tableman.
 */
class TablemanCollection extends Collection
{
  /**
   * Create a whole new Tableman instance. Extensions will be purged.
   * 
   * @return mechanicious\Tableman\Tableman
   */
  static function make(ColumnBag $columns)
  {
    if (is_null($columns)) return new static($columns);

    if ($columns instanceof Tableman) return $columns;

    return new static($columns);
  }

 /**
   * Collapse the collection items into a single array.
   *
   * @return \Illuminate\Support\Collection
   */
  public function collapse()
  {
    $results = array();
    foreach ($this->items as $column)
    {
      $results = array_merge($results, $column->toArray());
    }

    return new Collection($results);
  } 

  /**
   * Returns what the object running this method has but what $items doesn't have. Using row-set comparison.
   *
   * @param  \Illuminate\Support\Collection|\Illuminate\Support\Contracts\ArrayableInterface|array  $items
   * @return array
   */
  public function diff($items)
  {
    $items = $this->getArrayableItems($items);
    // What $has has, but $leaks leaks.
    return $this->array_diff_recursive($has = $this->getRows(), $leaks = $items);
  }

  /**
   * Execute a callback over each item. Alias of Tableman::eachColumn
   * 
   * @param  closure $callback
   * @return mechanicious\Tableman\Tableman
   */
  public function each(\closure $callback)
  {
    $this->eachColumn($cllback);
  }

  /**
   * Fetch a nested element of the collection.
   *
   * @param  string  $key
   * @return \Illuminate\Support\Collection
   */
  public function fetch($key)
  {
    return new Collection(array_fetch(with(new Collection($this->getRows()))->items, $key));
  }

  /**
   * Run a filter over each of the items.
   *
   * @param  Closure  $callback
   * @return \Illuminate\Support\Collection
   */
  public function filter(\Closure $callback)
  {
    return new Collection(array_filter(with(new Collection($this->getRows()))->items, $callback));
  }

  /**
   * Get a flattened array of the items in the collection.
   *
   * @return array
   */
  public function flatten()
  {
    return new Collection(array_flatten($this->items));
  }

    /**
   * Group an associative array by a field or Closure value.
   *
   * @param  callable|string  $groupBy
   * @return \Illuminate\Support\Collection
   */
  public function groupBy($groupBy)
  {
    // I'll just leave it like that for now.
    $results = array();

    foreach ($this->toArray() as $key => $value)
    {
      $key = is_callable($groupBy) ? $groupBy($value, $key) : data_get($value, $groupBy);

      $results[$key][] = $value;
    }

    return new Collection($results);
  }

  /**
   * Concatenate values of a given header as a string.
   * 
   * @param  string $header
   * @param  string $glue
   * @return string
   */
  public function implode($header, $glue = ", ")
  {
    $items = $this->items[$header];
    return implode($glue, $items->toArray());
  }

  /**
   * Results array of items from Collection or ArrayableInterface.
   *
   * @param  \Illuminate\Support\Collection|\Illuminate\Support\Contracts\ArrayableInterface|array  $items
   * @return array
   */
  protected function getArrayableItems($items)
  {
    if(is_array($items)) return $items;
    if ($items instanceof ColumnBag || $items instanceof ColumnBag || $items instanceof ArrayableInterface)
    {
      return $items->toArray();
    }
    return new \Exception(__METHOD__ . ' could not convert to array');
  }
}