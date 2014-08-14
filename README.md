## Tableman

Tables are a great way to represent data. Almost every type of data can be represented as a table. However not every table can represent every type of data. Tableman was created to totally unleash the potential of tables. The powefull API makes it possible for Tableman to easily fit every type of data.


## Conversion
Tableman allows you to convert one of the *Tableman Supported Data-Types* into a *Tableman Supported Conversion Type*. After you make the conversion you can then again re-convert the table to the data-type you've started with.

**Tableman Supported Data-Types**
* Array

**Tableman Supported Conversion Types**
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
