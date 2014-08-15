<?php namespace Illuminate\Test;

require_once __DIR__ . '/../vendor/autoload.php';

class InstantiationTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test if we're able to get an instance of Collection
	 * @return void
	 */
	public function testInstantiation()
	{
		$collection = new \Illuminate\Support\Collection(array());
		$this->assertEquals(get_class($collection), 'Illuminate\Support\Collection');
	}
}