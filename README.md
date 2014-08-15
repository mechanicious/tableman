## We're currently in alpha stage. Come back later or help work on awesum features now.

## Tableman

Tables are a great way to represent data. Almost every type of data can be represented as a table. However not every table can represent every type of data. Tableman was created to totally unleash the potential of tables. The powefull API makes it possible for Tableman to easily fit every type of data and any type of need.

## What's included
These are things you might want to exclude from your package if you already have them.

* Laravel 4 Collection API
* Laravel 4 helper methods
* Tests

## Tableman usecases 
* Apply filters based on cell's content
* Capitalize names
* Convert image-links into image-elements
* Escape HTML-entities
* Keep your templates clean
* Remove columns you want to hide
* Translate the column headers
* Wrap certain rows in an HTML-wrapper that are a member of some column
* Sort columns
* Sort rows
* Translate foreign key of your table into human-friendly data
* Add a column actions with links that'll map to your update or delete routes
* Split a table into several pieces you could distribute over the page
* Display different tables using same template
* and all other sorts of data manipulation!

## Tableman usage

What I like to do is to create Tableman in the controller and pass it to the view for optional further processing. Different route-methods in the controller may pass different tables to same template.

## Conversion
Tableman allows you to convert one of the *Tableman Supported Data-Types* into a *Tableman Supported Conversion Type*. After you make the conversion you can then again re-convert the table to the data-type you've started with.

**Tableman Supported Data-Types**
* Array
* JSON

**Tableman Supported Conversion Types**
* Array
* JSON
* HTML
* HTML Bootstrap 3 Table

## Example
```php
$data = array(
    array(
        'id'    => 1,
        'name'  => 'Joe',
        'age'   => 25
    ),
    array(
        'id'    => 2,
        'name'  => 'Tony',
        'age'   => 27
    ),
);

$tableman  = new Tableman($data);
$tableman->getHtml(); // (string) HTML markup for the table

// Custom filters
$tableman->forEveryRow(function($row) {
    if(is_int($row)) $row *= $row; // square it!
})
->getHtml();
```

### API (human friendly)
You'll find a bit of explanation about the methods underneath.

#### mechanicious\Tableman::eachRow($callback)
Allows you to loop through the rows filter things out and apply changes.

```php
		$columnBag = with(new \mechanicious\Columnizer\Columnizer($someData = array(
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
	   	))))
	   	->columnize();
		
		$tableman = new \mechanicious\Tableman\Tableman($columnBag);
		$tableman->eachRow(function(&$ref, &$row, &$rowIndex) {
			// If you actually want to make changes then make sure
			// you **reference** items!
			foreach($row as $columnHeader => &$cell)
			{
				// Append an ellipsis at the very end of every cell.
				$cell .= "...";
			}
		});

		// To JSON
		$tableman->toJSON();
		//{
		//	"id":["1...","2..."],
		//	"name":["Joe...","Tony..."],
		//	"age":["25...", "27..."],
		//	"hobby":["...","sport..."]
		//}

```

**$callback (closure)**
```php
$callback = function(&$hook, &$row, &$rowIndex) {};
```

**$hook (mechanicious\Tableman\Tableman)**
Reference to the main object. Note: all Illuminate\Support\Collection API is in the reach of your hand. Thanks to the `$hook` you don't need to refer to an external variable.
```php
$hook->all();
```

**$row (array)**
Array with cells, each cell carries a column-header(key) of the column to which it belongs and cell-data(value)
```php
// Example
array('id' => 1, 'name' => 'Tony', 'age' => 27);
```

**$rowIndex (int)**
Row number.
```php
if($rowIndex % 2 !== 0) unset($row);
```
