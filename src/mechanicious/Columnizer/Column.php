<?php namespace mechanicious\Columnizer;

use Illuminate\Support\Collection;

class Column extends Collection
{
  /**
   *  Yup, column is a collection as well!
   */
  protected $header = "";

  public function __construct(array $items = array(), $header)
  {
    parent::__construct($items);
    $this->header = $header;
  }

  /**
   * Get the column header
   * 
   * @return string
   */
  public function getHeader()
  {
    return $this->header;
  }

  /**
   * Remove last elements of an array
   *  
   * @param  int $amount
   * @return mechanicious\Columnizer\Column
   */
  public function chop($amount = 1)
  {
    $endOffset = count($this->items) - $amount;
    $slice = array_slice($this->items, 0, $endOffset, $preserveKeys = true);
    $this->items = $slice;
    return $this;
  }
}