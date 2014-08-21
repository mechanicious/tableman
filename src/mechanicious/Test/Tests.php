<?php namespace mechanicious\Test;

require_once __dir__ . '/../../../vendor/autoload.php';

class Tests extends \PHPUnit_Framework_TestCase
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
      // Unfortunately got a little messy!
      $this->cleanWhiteSpace($tableman->getBS3Table()), 
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
}