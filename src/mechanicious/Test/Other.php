<?php namespace mechanicious\Test;

require_once __dir__ . '/../../../vendor/autoload.php';

/**
 *  Little testing I run for myself, that isn't necessary 
 * a part of the public API.
 * Best way to run this tests is to use
 * separate files with everything set to public.
 */

class Other extends \PHPUnit_Framework_TestCase
{
  /**
   *  Data to play with
   * @var array
   */
  protected $mockData = array(
        array(
            'id'    => 1,
            'name'  => 'Joe',
            'age'   => 25
        ),
        array(
            'id'    => 2,
            'name'  => 'Tony',
            'age'   => 27,
            'hobby' => 'sport',
        ),
    );

  /**
   *  Useful with formatted string comparison  
   * @param   string $string
   * @return  string
   */
  public function cleanWhiteSpace($string, $replace = "")
  {
    return str_replace(array("\n", "\r", "\t", " "), $replace, $string);
  }

  /**
   *  This method is not in the Columnizer's public API. If you wanna test it
   * make it temporarily public.
   */
  public function testColumnizerIdentifyColumns()
  {
    $columnizer = new \mechanicious\Columnizer\Columnizer($this->mockData);
    // It the hobby 'column' should be included because we assume that 
    // the largest row contains a complete set of columns. Some data may
    // be inconsistent like that but there's not problem handling it.
    $this->assertEquals($columnizer->identifyColumns(), array('id', 'name', 'age', 'hobby'));
  }

  public function testColumnizerColumnizeItems()
  {
    $columnizer = new \mechanicious\Columnizer\Columnizer($this->mockData);
    $this->assertEquals($columnizer->columnizeArrayRow(), array(
      'id'  => array(1, 2),
      'name'  => array('Joe', 'Tony'),
      'age' => array(25, 27),
      // Notice the hobby one below. That's a example when we're dealing with
      // inconsistent data.
      'hobby' => array('sport'),
      ));
  }

  /**
   *  For this test you need to have $items and symmetrize() set to public.
   */
  public function testColumnizeSymmetrize()
  {
    // Here we test if the hobby field for Joe will get pre-filled.
    $columnizer = $columnizer = new \mechanicious\Columnizer\Columnizer($this->mockData);
    $columnizer->symmetrize();
    $this->assertEquals($columnizer->items, array(
      array(
            'id'    => 1,
            'name'  => 'Joe',
            'age'   => 25,
            'hobby' => null,
        ),
        array(
            'id'    => 2,
            'name'  => 'Tony',
            'age'   => 27,
            'hobby' => 'sport',
        )
    ));
  }

  public function testTablemanRenameColumns()
  {
    $columnBag = with(new \mechanicious\Columnizer\Columnizer($this->mockData))->columnize();
    $tableman = new \mechanicious\Tableman\Tableman($columnBag);
    $headers = array(
      'id'    => 'identification',
      'name'  => 'firstname',
      'age'   => 'level',
      'hobby' => 'likes',
      );

    $tableman->renameHeaders($headers);
    $this->assertEquals($this->cleanWhiteSpace($tableman->toJson()), $this->cleanWhiteSpace('
        {
          "identification":[1,2],
          "firstname":["Joe","Tony"],
          "level":[25,27],
          "likes":[null,"sport"]
        }
      '));
  }
}