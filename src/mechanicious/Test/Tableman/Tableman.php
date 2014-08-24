<?php namespace mechanicious\Test\Tableman;
use mechanicious\Columnizer\Column;

require_once __dir__ . '/../../../../vendor/autoload.php';

class Tableman extends \PHPUnit_Framework_TestCase
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

  public function testIlluminateCollectionInstatiation()
  {
    $collection = new \Illuminate\Support\Collection(array());
    $this->assertEquals(get_class($collection), 'Illuminate\Support\Collection');
  }

  public function testColumnizerColumnizeWithoutData()
  {
    $columnizer = new \mechanicious\Columnizer\Columnizer();
    $this->assertEquals(get_class($columnizer->columnizeRowArray()), 'mechanicious\Columnizer\ColumnBag');
  }

  public function testArrayAccessColumnizerColumnizeWithData()
  {
    $columnizer = new \mechanicious\Columnizer\Columnizer($this->mockData);
    // ColumnBag
    //      |--- Column (id)
    //          |--- 1, 2
    //    |--- Column (name)
    //    |--- Column (age)
    //    |--- Column (hobby)
    $this->assertEquals(get_class($columnizer->columnizeRowArray()), 'mechanicious\Columnizer\ColumnBag');
    $this->assertEquals(get_class($columnizer->columnizeRowArray()->get('name')), 'mechanicious\Columnizer\Column');

    // ArrayAccess in action
    $this->assertEquals($columnizer->columnizeRowArray()['name'][1], 'Tony');
  }

  public function testGetBS3Table()
  {
    $columnBag = with(new \mechanicious\Columnizer\Columnizer($this->mockData))->columnizeRowArray();
    $tableman = new \mechanicious\Tableman\Tableman($columnBag);
     $this->assertEquals(
      $this->cleanWhiteSpace($tableman->Bs3Table(array(
          'config' => array(),
          'header' => array(),
          'extra_classes' => array(),
          'limit' => 10))),
        $this->cleanWhiteSpace('
        <table  class="table ">
              <thead>
                      <tr>
                              <th></th>
                              <th></th>
                              <th></th>
                              <th></th>
                      </tr>
              </thead>
              <tbody>
                      <tr>
                              <td>1</td>
                              <td>Joe</td>
                              <td>25</td>
                              <td></td>
                      </tr>
                      <tr>
                              <td>2</td>
                              <td>Tony</td>
                              <td>27</td>
                              <td>sport</td>
                      </tr>
              </tbody>
        </table>'));
  }

  public function testTablemanToJSON()
  {
    $columnBag = with(new \mechanicious\Columnizer\Columnizer($this->mockData))->columnizeRowArray();
    $tableman = new \mechanicious\Tableman\Tableman($columnBag);
    $this->assertEquals($this->cleanWhiteSpace($tableman->getJson()), 
      $this->cleanWhiteSpace('
      {
        "id":[1,2],
        "name":["Joe","Tony"],
        "age":[25,27],
        "hobby":[null,"sport"]
      }'
    ));
  }

  public function testEachRow()
  {
    $columnBag = with(new \mechanicious\Columnizer\Columnizer($this->mockData))->columnizeRowArray();
    $tableman = new \mechanicious\Tableman\Tableman($columnBag);
    $tableman->eachRow(function(&$ref, &$row, &$rowIndex) {
      // If you actually want to make changes then make sure
      // you **reference** items!
      foreach($row as $columnHeader => &$cell)
      {
        // Append an ellipsis at the very end of every cell.
        $cell .= "...";
      }
      if(in_array('id', $ref->getColumnHeaders()))
      {
        $ref->replaceColumn(new \mechanicious\Columnizer\Column(array(6,7), '#'), 'id');
      }

      $ref->referenceTest = 'ok';
    });
    
    // Test if the data changes get preserved.
    $this->assertEquals($this->cleanWhiteSpace($tableman->getJson('column')), 
      $this->cleanWhiteSpace('
      {
        "#":[6, 7],
        "name":["Joe...","Tony..."],
        "age":["25...", "27..."],
        "hobby":["...","sport..."]
      }'
    ));

    // Test if the main object get's affected. Wether the reference points to the right object.
    $this->assertEquals($tableman->referenceTest, 'ok');
  }

  public function testTablemanOrderColumns()
  {
    $columnBag = with(new \mechanicious\Columnizer\Columnizer($this->mockData))->columnizeRowArray();
    $tableman = new \mechanicious\Tableman\Tableman($columnBag);
    $tableman->orderColumns(array('hobby', 'age', 'id', 'name'));
    $this->assertEquals($this->cleanWhiteSpace($tableman->getJSON()), 
      $this->cleanWhiteSpace('
      {
        "hobby":[null,"sport"],
        "age":[25, 27],
        "id":[1,2],
        "name":["Joe","Tony"]
      }'
    ));
  }

  public function testTablemanRenameColumns()
  {
    $columnBag = with(new \mechanicious\Columnizer\Columnizer($this->mockData))->columnizeRowArray();
    $tableman = new \mechanicious\Tableman\Tableman($columnBag);
    $headers = array(
      'id'    => 'identification',
      'name'  => 'firstname',
      'age'   => 'level',
      'hobby' => 'likes',
      );

    $tableman->renameColumns($headers);
    $this->assertEquals($this->cleanWhiteSpace($tableman->toJson()), $this->cleanWhiteSpace('
        {
          "identification":[1,2],
          "firstname":["Joe","Tony"],
          "level":[25,27],
          "likes":[null,"sport"]
        }
      '));
  }

  public function testTablemanRenameColumnsEachRowCompatibility()
  {
    // We'll try to rename columns while looping through the items.
    $columnBag = with(new \mechanicious\Columnizer\Columnizer($this->mockData))->columnizeRowArray();
    $tableman = new \mechanicious\Tableman\Tableman($columnBag);
    $tableman->eachRow(function(&$ref, &$row, &$rowIndex) {
      // If you actually want to make changes then make sure
      // you **reference** items!
      foreach($row as $columnHeader => &$cell)
      {
        // Append an ellipsis at the very end of every cell.
        $cell .= "...";
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

      if(in_array('id', $ref->getColumnHeaders()))
      {
        $ref->replaceColumn(new \mechanicious\Columnizer\Column(array(6,7), '#'), 'identification');
      }
    });

    $keyHeaders = $tableman->getColumnHeaders();
    $columnHeaders = array_map(function($column) {
      return $column->getHeader();
    }, $tableman->getColumns());

    $this->assertEquals($keyHeaders, array_values($columnHeaders));
  }

  public function testEachColumn()
  {
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

  public function testColumnAdd()
  {
    // We'll try here:
    // 1. If data stays symmetric when adding an assymetric column (larger and smaller column in relation to the existent data)
    // 2. If the column gets the right offset
    // 3. If when adding an existing column the column gets replaced
    $columnBag = with(new \mechanicious\Columnizer\Columnizer($this->mockData))->columnizeRowArray();
    $tableman = new \mechanicious\Tableman\Tableman($columnBag);
    $tableman->addColumn(new \mechanicious\Columnizer\Column(array(true, false, true), 'registered'), 3);
    $this->assertEquals($this->cleanWhiteSpace($tableman->getJson()), 
      $this->cleanWhiteSpace('
      {
        "id":         [1, 2, null],
        "name":       ["Joe","Tony", null],
        "age":        [25, 27, null],
        "registered": [true, false, true],
        "hobby":      [null, "sport", null]
      }'
    ));

    $tableman->addColumn(new \mechanicious\Columnizer\Column(array(true), 'registered'), 2);
    $this->assertEquals($this->cleanWhiteSpace($tableman->getJson()), 
      $this->cleanWhiteSpace('
      {
        "id":         [1, 2, null],
        "name":       ["Joe","Tony", null],
        "registered": [true, null, null],
        "age":        [25, 27, null],
        "hobby":      [null, "sport", null]
      }'
    ));
  }

  public function testColumnChop()
  {
    $columnBag = with(new \mechanicious\Columnizer\Columnizer($this->mockData))->columnizeRowArray();
    $tableman = new \mechanicious\Tableman\Tableman($columnBag);
    $tableman->eachColumn(function(&$ref, &$column, $header) {
      $column->chop(1);
    });
    
    $this->assertEquals($this->cleanWhiteSpace($tableman->getJson()), 
      $this->cleanWhiteSpace('
      {
        "id":         [1],
        "name":       ["Joe"],
        "age":        [25],
        "hobby":      [null]
      }'
    ));
  }

  public function testTablemanBs3TableExtension()
  {
    $columnBag = with(new \mechanicious\Columnizer\Columnizer($this->mockData))->columnizeRowArray();
    $tableman = new \mechanicious\Tableman\Tableman($columnBag);
    $this->assertEquals(
      $this->cleanWhiteSpace($tableman->Bs3Table(array(
          'config' => array(),
          'header' => array(),
          'extra_classes' => array(),
          'limit' => 10))),
        $this->cleanWhiteSpace('
        <table  class="table ">
              <thead>
                      <tr>
                              <th></th>
                              <th></th>
                              <th></th>
                              <th></th>
                      </tr>
              </thead>
              <tbody>
                      <tr>
                              <td>1</td>
                              <td>Joe</td>
                              <td>25</td>
                              <td></td>
                      </tr>
                      <tr>
                              <td>2</td>
                              <td>Tony</td>
                              <td>27</td>
                              <td>sport</td>
                      </tr>
              </tbody>
        </table>'));
  } 

  public function testTablemanWithdraw()
  {
    $columnBag = with(new \mechanicious\Columnizer\Columnizer($this->mockData))->columnizeRowArray();
    $tableman = new \mechanicious\Tableman\Tableman($columnBag);
    $this->assertEquals($tableman->withdraw(function() {
      return 'withdraw string';
    }), 'withdraw string');
  }

  public function testSortColumns()
  {
    $columnBag = with(new \mechanicious\Columnizer\Columnizer($this->mockData))->columnizeRowArray();
    $tableman = new \mechanicious\Tableman\Tableman($columnBag);
    $tableman->sortColumns(function($current, $previous) { 
      if(strpos($current, 'a') !== false && strpos($previous, 'a') !== false) return 0;
      // Such that a is inferior
      return strpos($current, 'a') !== false && strpos($previous, 'a') === false ? 1 : -1; 
    });

    $this->assertEquals($tableman->getColumnHeaders(), array('hobby', 'age', 'name', 'id'));
  }

  public function testReverseColumnOrder()
  {
    $columnBag = with(new \mechanicious\Columnizer\Columnizer($this->mockData))->columnizeRowArray();
    $tableman = new \mechanicious\Tableman\Tableman($columnBag);
    $tableman->reverse();
    $this->assertEquals($tableman->getColumnHeaders(), array('hobby', 'age', 'name', 'id'));
  }

  public function testGetColumnHeaders()
  {
    $columnBag = with(new \mechanicious\Columnizer\Columnizer($this->mockData))->columnizeRowArray();
    $tableman = new \mechanicious\Tableman\Tableman($columnBag);
    $this->assertEquals($tableman->getColumnHeaders(), array('id', 'name', 'age', 'hobby'));
  }

  public function testColumnExists()
  {
    $columnBag = with(new \mechanicious\Columnizer\Columnizer($this->mockData))->columnizeRowArray();
    $tableman = new \mechanicious\Tableman\Tableman($columnBag);
    $this->assertEquals($tableman->columnExists('test'), false);
    $this->assertEquals($tableman->columnExists('id'), true);
  }  

  public function testColumnHas()
  {
    $columnBag = with(new \mechanicious\Columnizer\Columnizer($this->mockData))->columnizeRowArray();
    $tableman = new \mechanicious\Tableman\Tableman($columnBag);
    $this->assertEquals($tableman->columnHas('name', 'Tony'), true);
    $this->assertEquals($tableman->columnHas('age', '99'), false);
  }

  public function testPadData()
  {
    $columnBag = with(new \mechanicious\Columnizer\Columnizer($this->mockData))->columnizeRowArray();
    $tableman = new \mechanicious\Tableman\Tableman($columnBag);
    $tableman->padData(new Column(array(true, true, false), 'is_human'));
    // Since the mockData['names'] was only 2 row large and we aligned the columns with 3 row large column
    // now or name column should be prefilled to 3 rows with null
    $this->assertEquals($tableman->get('name')->toArray(), array('Joe', 'Tony', null));
  }

  public function testPrependColumn()
  {
    $columnBag = with(new \mechanicious\Columnizer\Columnizer($this->mockData))->columnizeRowArray();
    $tableman = new \mechanicious\Tableman\Tableman($columnBag);
    $tableman->prependColumn(new Column(array(true, true, false), 'is_human'));
    $this->assertEquals($tableman->getColumnHeaders()[0], 'is_human');
  }

  public function testAppendColumn()
  {
    $columnBag = with(new \mechanicious\Columnizer\Columnizer($this->mockData))->columnizeRowArray();
    $tableman = new \mechanicious\Tableman\Tableman($columnBag);
    $tableman->appendColumn(new Column(array(true, true, false), 'is_human'));
    $this->assertEquals(array_reverse($tableman->getColumnHeaders())[0], 'is_human');
  }

  public function testPopColumn()
  {
    $columnBag = with(new \mechanicious\Columnizer\Columnizer($this->mockData))->columnizeRowArray();
    $tableman = new \mechanicious\Tableman\Tableman($columnBag);
    $tableman->popColumn();
    $this->assertFalse(in_array('hobby', $tableman->getColumnHeaders()));
  }

  public function testShiftColumn()
  {
    $columnBag = with(new \mechanicious\Columnizer\Columnizer($this->mockData))->columnizeRowArray();
    $tableman = new \mechanicious\Tableman\Tableman($columnBag);
    $tableman->shiftColumn();
    $this->assertFalse(in_array('id', $tableman->getColumnHeaders()));
  }

  public function testRemoveColumn()
  {
    $columnBag = with(new \mechanicious\Columnizer\Columnizer($this->mockData))->columnizeRowArray();
    $tableman = new \mechanicious\Tableman\Tableman($columnBag);
    $tableman->removeColumn('name');
    $this->assertFalse(in_array('name', $tableman->getColumnHeaders()));
  }

  public function testCompareColumnContent()
  {
    $columnBag = with(new \mechanicious\Columnizer\Columnizer($this->mockData))->columnizeRowArray();
    $tableman = new \mechanicious\Tableman\Tableman($columnBag);
    $this->assertFalse($tableman->compareColumnContent($tableman->get('name'), $tableman->get('id')));
    // Although the column headers change the content should stay the same
    $this->assertTrue($tableman->compareColumnContent($tableman->get('name'), new Column(array('Joe', 'Tony'), 'coolnames')));
  }

  public function testReplaceColumn()
  {
    $columnBag = with(new \mechanicious\Columnizer\Columnizer($this->mockData))->columnizeRowArray();
    $tableman = new \mechanicious\Tableman\Tableman($columnBag);
    $tableman->replaceColumn(new Column(array('old', 'young'), 'approx_age'), 'age');
    $this->assertEquals($tableman->get('age'), null);
    $this->assertEquals($tableman->get('approx_age')->first(), 'old');
  }

  public function testEachRowOf()
  {
    $columnBag = with(new \mechanicious\Columnizer\Columnizer($this->mockData))->columnizeRowArray();
    $tableman = new \mechanicious\Tableman\Tableman($columnBag);
    $tableman->eachRowOf('age', function(&$ref, &$age, $rowIndex) {
      $age = 'oops?';
    });
    $this->assertEquals($tableman->get('age')->first(), 'oops?');
    $this->assertFalse($tableman->get('name')->first() === 'oops?');
  }

  public function testEachCell()
  {
    $columnBag = with(new \mechanicious\Columnizer\Columnizer($this->mockData))->columnizeRowArray();
    $tableman = new \mechanicious\Tableman\Tableman($columnBag);
    $tableman->eachCell(function(&$ref, &$cell, &$row, $rowIndex) {
      if($cell === $row['age']) return $cell = 100;
      $cell = 1;
    });
    $this->assertTrue($tableman->get('age')->first() === 100);
    $this->assertTrue($tableman->get('name')->first() === 1);
  }

  public function testGetColumn()
  {
    $columnBag = with(new \mechanicious\Columnizer\Columnizer($this->mockData))->columnizeRowArray();
    $tableman = new \mechanicious\Tableman\Tableman($columnBag);
    $this->assertEquals($tableman->getColumn('name')->first(), 'Joe');
  }

  public function testGetAllColumns()
  {
    $columnBag = with(new \mechanicious\Columnizer\Columnizer($this->mockData))->columnizeRowArray();
    $tableman = new \mechanicious\Tableman\Tableman($columnBag);
    $this->assertEquals($tableman->getAllColumns()['id']->last(), 2);
    $this->assertEquals(count($tableman->getAllColumns()), 4);
    $this->assertTrue($tableman->getAllColumns()['id'] instanceof \mechanicious\Columnizer\Column);
  }

  public function testGetRows()
  {
    $columnBag = with(new \mechanicious\Columnizer\Columnizer($this->mockData))->columnizeRowArray();
    $tableman = new \mechanicious\Tableman\Tableman($columnBag);
    $this->assertEquals($tableman->getRows()[1]['name'], 'Tony');
  }
}