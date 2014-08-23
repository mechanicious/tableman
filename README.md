## We're currently in alpha stage. Come back later or help work on awesum features now.

## Contents
  * [Latest API](http://mechanicious.github.io/tableman/)
  * [Tableman](https://github.com/mechanicious/tableman#tableman)
  * [Tableman Modularity](https://github.com/mechanicious/tableman#tableman-modularity)
  * [What's included](https://github.com/mechanicious/tableman#whats-included)
  * [Tableman usecases](https://github.com/mechanicious/tableman#tableman-usecases)

## Tableman

Tables are a great way to represent data. Almost every type of data can be represented as a table. However not every table can represent every type of data. Tableman was created to totally unleash the potential of tables. The powefull API makes it possible for Tableman to easily fit every type of data and any type of need.

## Tableman Modularity
Tableman exists out of four main modules, wich are: Tableman, Columnizer, Collection and TablemanExtension. The Tableman module is reponsible for the API to manipulate data. Columnizer is responsible to translate many types and formats of data into something that Tableman will be able to work with. Collection (borrwed from Laravel 4) is reponsible for enrichement of the API, many generic methods are included with Collection that work very well. TablemanExtension is a module reposonsible of providing an API to Tableman Extension Developers which makes it easier to extend Tableman with own features.

## What's included
These are things you might want to exclude from your package if you already have them.
> Note: For foreign libraries default mappings are used so for example Laravel 4 Collection would be mapped to Illuminate\Support\Collection

* Laravel 4 Collection
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
* Add a column "actions" with links that'll map to your update or delete route
* Split a table into several pieces you could distribute over the page
* Display different tables using same template
* and all other sorts of data manipulation!


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
$tableman->eachRow(function(&$rowIndex, &$row) {
    if($row['id'] === 1) unset($row); // remove user with id eq to 1 from the table!
})
->getHtml();
```

## Tableman API (human friendly)
You'll find a bit of explanation about the methods underneath.

#### mechanicious\Tableman::eachRow($callback)
Allows you to loop through the rows filter things out and apply changes.

#####$callback (closure)
```php
$callback = function(&$hook, &$row, &$rowIndex) {};
```
* **$hook (mechanicious\Tableman\Tableman)**
Reference to the main object. Note: `Illuminate\Support\Collection` API is in the reach of your hand. Thanks to the `$hook` variable you don't need an external variable outside the filter function.
```php
$hook->all();
```
* **$row (array)**
Array with cells, each cell carries a column-header(key) of the column to which it belongs and cell-data(value)
```php
// Example
array('id' => 1, 'name' => 'Tony', 'age' => 27);
```
* **$rowIndex (int)**
Row number.
```php
if($rowIndex % 2 !== 0) unset($row);
```

####Example mechanicious\Tableman::eachRow($callback)
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
$tableman->eachRow(function(&$ref, &$row, $rowIndex) {
	// If you actually want to make changes then make sure
	// you **reference** items.
	foreach($row as $columnHeader => &$cell)
	{
		// Append an ellipsis at the very end of every cell.
		$cell .= "...";
	}
});

// To JSON 
// Two formats are available column-format, and row-format. Row-format is the one you get from a DB-Query.
$tableman->toJson($format = "column");
//{
//	"id":["1...","2..."],
//	"name":["Joe...","Tony..."],
//	"age":["25...", "27..."],
//	"hobby":["...","sport..."]
//}

```

#### mechanicious\Tableman::addColumn($col, $position = 3)
Allows you to loop through the rows filter things out and apply changes.

#####$col (mechanicious\Columnizer\Column)
Column to add.

#####$position (int)
Offset to add the column at.


####Example mechanicious\Tableman::eachRow($callback)
```php
$tableman->addColumn(new Column($anyData = array(true, false, true), 'registered'), 3);

```

#### mechanicious\Columnizer\Column::chop($amount)
Remove a certain amount of items from the end of a column.

#####amount (int)
Offset to add the column at.

####Example mechanicious\Columnizer\Column::chop($amount)
```php
$column->chop($amount = 3);

```

