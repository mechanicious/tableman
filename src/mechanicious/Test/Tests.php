<?php namespace mechanicious\Test;

require_once __dir__ . '/../../../vendor/autoload.php';

class Tests extends \PHPUnit_Framework_TestCase
{
	/**
	 * 	Data to play with
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
	 * 	Useful with formatted string comparison  
	 * @param  	string $string
	 * @return 	string
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
		$this->assertEquals(get_class($columnizer->columnize()), 'mechanicious\Columnizer\ColumnBag');
	}

	public function testArrayAccessColumnizerColumnizeWithData()
	{
		$columnizer = new \mechanicious\Columnizer\Columnizer($this->mockData);
		// ColumnBag
		//    	|--- Column (id)
		//    			|--- 1, 2
		//		|--- Column (name)
		//		|--- Column (age)
		//		|--- Column (hobby)
		$this->assertEquals(get_class($columnizer->columnize()), 'mechanicious\Columnizer\ColumnBag');
		$this->assertEquals(get_class($columnizer->columnize()->get('name')), 'mechanicious\Columnizer\Column');

		// ArrayAccess in action
		$this->assertEquals($columnizer->columnize()['name'][1], 'Tony');
	}

	public function testGetBS3Table()
	{
		$columnBag = with(new \mechanicious\Columnizer\Columnizer($this->mockData))->columnize();
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
		$columnBag = with(new \mechanicious\Columnizer\Columnizer($this->mockData))->columnize();
		$tableman = new \mechanicious\Tableman\Tableman($columnBag);
		$this->assertEquals($this->cleanWhiteSpace($tableman->toJSON()), 
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
		$columnBag = with(new \mechanicious\Columnizer\Columnizer($this->mockData))->columnize();
		$tableman = new \mechanicious\Tableman\Tableman($columnBag);
		$tableman->eachRow(function(&$ref, &$row, &$rowIndex) {
			// If you actually want to make changes then make sure
			// you **reference** items!
			dd($ref->all());
			foreach($row as $columnHeader => &$cell)
			{
				// Append an ellipsis at the very end of everything.
				$cell .= "...";
			}
		});

		$this->assertEquals($this->cleanWhiteSpace($tableman->toJSON()), 
			$this->cleanWhiteSpace('
			{
				"id":["1...","2..."],
				"name":["Joe...","Tony..."],
				"age":["25...", "27..."],
				"hobby":["...","sport..."]
			}'
		));

	}
}