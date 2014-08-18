<?php namespace mechanicious\Columnizer;

use Illuminate\Support\Collection;

class ColumnBag extends Collection
{
  /**
   *  All collection features just like that!
   */
  public function getColumnHeaders()
  {
    return array_keys($this->items);
  }
}