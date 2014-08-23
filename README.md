## We're currently in alpha stage. Come back later or help work on awesum features now.

## Contents
  * [Latest API](http://mechanicious.github.io/tableman/)
  * [Tableman](https://github.com/mechanicious/tableman#tableman)
  * [Tableman Modularity](https://github.com/mechanicious/tableman#tableman-modularity)
  * [What's included](https://github.com/mechanicious/tableman#whats-included)
  * [Tableman usecases](https://github.com/mechanicious/tableman#tableman-usecases)
  * Wiki
    * [Data Modeling Inside of Tableman](https://github.com/mechanicious/tableman/wiki/Data-Modeling-Inside-Tableman) 

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
$columnizer = new Columnizer($data); // Data converversion
$tableman   = new Tableman($columnizer->columnizeRowArray()); // Data modeling
// Custom filters
$tableman->eachRow(function(&$_this, &row, $index) { 
    if($row['id'] === 1) unset($row);
})
echo $tableman->Bs3Table(new Config(array('config'=>array(), 'header'=> array(), 'extra_classes'=>array())); // Custom extensions
```

