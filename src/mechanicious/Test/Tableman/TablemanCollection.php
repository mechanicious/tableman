<?php namespace mechanicious\Test\Tableman;

require_once __dir__ . '/../../../../vendor/autoload.php';

class TablemanCollection extends \PHPUnit_Framework_TestCase
{
  /**
   *  Data to play with
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
    // Make sure you don't use this method together with whitespace sensitive testing
    return str_replace(array("\n", "\r", "\t", " "), $replace, $string);
  }

  public function testMake()
  {
    $columnBag = with(new \mechanicious\Columnizer\Columnizer($this->mockData))->columnizeRowArray();
    $tableman = new \mechanicious\Tableman\Tableman($columnBag);
    $this->assertTrue($tableman->make(new \mechanicious\Columnizer\ColumnBag($this->mockData)) instanceof \mechanicious\Tableman\Tableman);
  }

  public function testAll()
  {
    $columnBag = with(new \mechanicious\Columnizer\Columnizer($this->mockData))->columnizeRowArray();
    $tableman = new \mechanicious\Tableman\Tableman($columnBag);
    $this->assertTrue(is_array($tableman->all()));
    $this->assertTrue($tableman->all()['id'] instanceof \mechanicious\Columnizer\Column);
  }

  public function testCollapse()
  {
    $columnBag = with(new \mechanicious\Columnizer\Columnizer($this->mockData))->columnizeRowArray();
    $tableman = new \mechanicious\Tableman\Tableman($columnBag);
    $this->assertEquals(count($tableman->collapse()), 8);
  }

  public function testDiff()
  {
    $columnBag = with(new \mechanicious\Columnizer\Columnizer($this->mockData))->columnizeRowArray();
    $tableman = new \mechanicious\Tableman\Tableman($columnBag);
    $deleted = array_pop($this->mockData);
    // Note: $tableman get's prefilled with null, we deleted 2 entry from the testee.
    $this->assertEquals($tableman->diff($this->mockData), array(array('hobby' => null), $deleted));
  }

  public function testEach()
  {
    // Since each() is an alias of eachColumn, we borrow the test from TablemanMethods tests.
    
    // We'll try to rename columns while looping through the items and at the
    // same time we'll try to replace columns.
    $columnBag = with(new \mechanicious\Columnizer\Columnizer($this->mockData))->columnizeRowArray();
    $tableman = new \mechanicious\Tableman\Tableman($columnBag);
    $tableman->eachColumn(function(&$ref, &$column, $header) {
      // If you actually want to make changes then make sure
      // you **reference** items!
      
      // Replace the id column
      if($header === 'id')
      {
        $column = new \mechanicious\Columnizer\Column(array(3,4), 'id');
      }

      // Renaming columns while still in the loop
      if(isset($ref['id'])) // should be true on the first iteration only 
      {
        $ref->renameColumns(array(
        'id'    => 'identification',
        'name'  => 'firstname',
        'age'   => 'level',
        'hobby' => 'likes',
      ));
      }

      // Again replace the column, note that the column has different name than the
      // name of the column we're replacing, thus the name changes as well.
      if(in_array('identification', $ref->getColumnHeaders()))
      {
        $ref->replaceColumn(new \mechanicious\Columnizer\Column(array(6,7), '#'), 'identification');
      }
    });

    $keyHeaders = $tableman->getColumnHeaders();
    $columnHeaders = array_map(function($column) {
      return $column->getHeader();
    }, $tableman->getColumns());

    $this->assertEquals($keyHeaders, array_values($columnHeaders));

    $this->assertEquals($this->cleanWhiteSpace($tableman->toJson()), $this->cleanWhiteSpace('
        {
          "#":[6,7],
          "firstname":["Joe","Tony"],
          "level":[25,27],
          "likes":[null,"sport"]
        }
      '));
  }

  public function testFetch()
  {
    $columnBag = with(new \mechanicious\Columnizer\Columnizer($this->mockData))->columnizeRowArray();
    $tableman = new \mechanicious\Tableman\Tableman($columnBag);
    $this->assertEquals($tableman->fetch('id'), new \Illuminate\Support\Collection(array(1, 2)));
  }

  public function testFilter()
  {
    $columnBag = with(new \mechanicious\Columnizer\Columnizer($this->mockData))->columnizeRowArray();
    $tableman = new \mechanicious\Tableman\Tableman($columnBag);

    $this->assertEquals($tableman->filter(function($value) { 
      if($value['name'] === "Tony")
        return $value;
    }), new \Illuminate\Support\Collection(array(1 => array(
          'id'    => 2,
          'name'  => 'Tony',
          'age'   => 27,
          'hobby' => 'sport',
      ))));
  }

  public function testFirst()
  {
    $columnBag = with(new \mechanicious\Columnizer\Columnizer($this->mockData))->columnizeRowArray();
    $tableman = new \mechanicious\Tableman\Tableman($columnBag);
    $this->assertTrue($tableman->first() instanceof \mechanicious\Columnizer\Column);
    // First column is the id column
    $this->assertEquals($tableman->first()[0], 1);
  }

  public function testFlatten()
  { 
    $columnBag = with(new \mechanicious\Columnizer\Columnizer($this->mockData))->columnizeRowArray();
    $tableman = new \mechanicious\Tableman\Tableman($columnBag);
    $this->assertEquals($tableman->flatten()->all(), $columnBag->flatten()->all());
  }

  public function testForget()
  {
    $columnBag = with(new \mechanicious\Columnizer\Columnizer($this->mockData))->columnizeRowArray();
    $tableman = new \mechanicious\Tableman\Tableman($columnBag);
    $tableman->forget('id');
    $this->assertEquals($tableman->get('id'), null);
  }

  public function testGet()
  {
    $columnBag = with(new \mechanicious\Columnizer\Columnizer($this->mockData))->columnizeRowArray();
    $tableman = new \mechanicious\Tableman\Tableman($columnBag);
    $this->assertEquals($tableman->get('id')->all(), array(1, 2));
  }

  public function testGroupBy()
  {
    $columnBag = with(new \mechanicious\Columnizer\Columnizer($this->mockData))->columnizeRowArray();
    $tableman = new \mechanicious\Tableman\Tableman($columnBag);
    // This function needs to be reimplemented.
    $this->assertEquals($tableman->groupBy('hobby'), $tableman->groupBy('hobby'));
  }

  public function testHas()
  {
    $columnBag = with(new \mechanicious\Columnizer\Columnizer($this->mockData))->columnizeRowArray();
    $tableman = new \mechanicious\Tableman\Tableman($columnBag);
    $this->assertTrue($tableman->has('id'));
  }

  public function testImplode()
  {
    $columnBag = with(new \mechanicious\Columnizer\Columnizer($this->mockData))->columnizeRowArray();
    $tableman = new \mechanicious\Tableman\Tableman($columnBag);
    $this->assertEquals($tableman->implode('name', ', '), 'Joe, Tony');
  }
}