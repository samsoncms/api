#SamsonCMS API module  

[![Latest Stable Version](https://poser.pugx.org/samsoncms/api/v/stable.svg)](https://packagist.org/packages/samsoncms/material)
[![Build Status](https://scrutinizer-ci.com/g/samsoncms/api/badges/build.png?b=master)](https://scrutinizer-ci.com/g/samsoncms/api/build-status/master)
[![Code Coverage](https://scrutinizer-ci.com/g/samsoncms/api/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/samsoncms/api/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/samsoncms/api/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/samsoncms/api/?branch=master) 
[![Total Downloads](https://poser.pugx.org/samsoncms/api/downloads.svg)](https://packagist.org/packages/samsoncms/material)
[![Stories in Ready](https://badge.waffle.io/samsoncms/material.png?label=ready&title=Ready)](https://waffle.io/samsoncms/material)

> SamsonCMS API for interaction with materials, fields and structures.

##Additional fields table
After creating corresponding [Table structure]() and setting its [Additional fields]() you can
get additional fields table object(```\samsoncms\api\field\Table```) ancestor for a specific ```\samsoncms\api\Material``` ancestor you need to create class
that extends generated additional fields table:
```php
class MyTable extends \samsoncms\api\MyGeneratedTable
{
    protected $indexView = 'specify a path to index view file';
    protected $rowView = 'specify a path to row view file';
}
```
This class will contain generated generic methods for retrieving collection of table column values with according field name,
for example if you have additional field with name ```age``` method ```age()``` would be generated to get collection of ```age```
values in all table rows.

###Rendering custom additional field tables
[SamsonCMS]() will generate all created [Table structure]() for automatically to simplify your code creation, the only thing
that needs to be done is extending  its classes and creating a views for outputting.

>Remember ```\samsoncms\api\field\Table``` is dependent on ```\samsonframework\core\ViewInterface``` instance and uses it for
 rendering its views, so the path to views and views themselves should be located within this instance.
 
###Default index view file
By default index view renders all rendered rows into view variable with name stored in ```\samsoncms\api\field\Table::ROWS_VIEW_VAR``` - ```rows```:
```php
<div class='my-table'>
    <h2>Table title<h2>
    <div class="my-table-rows">
        <?php echo $rows ?>
    </div>
</div>
```

###Default row view file
By default ```\samsoncms\api\field\Row``` ancestor view object is stored in ```\samsoncms\api\field\Table::ROW_VIEW_VAR``` - ```row```:
```php
<?php /** @var \myapplication\MyTableRow $row */?>
<div class="my-row">
    <div class="name"><?php echo $row->field1 ?></div>
    <div class="name"><?php echo $row->field2 ?></div>
    <div class="name"><?php echo $row->field3 ?></div>
</div>
```
> Give a type hint to a generated ```\samsoncms\api\field\Row``` ancestor and IDE will help outputting needed row data.


#Navigation

#Material

Added method for creating/updating material additional fields 
```public function setFieldByID($fieldID, $value, $locale = DEFAULT_LOCALE)```
Method find Field record in database by Field identifier and the receives its type for
correct storing of additional field value.

#Field
You can find additional field ```samsonframework\orm\RecordInterface``` object by using one of provided methods:
* By its identifier ```\samsoncms\api\Field::byID($query, $identifier, &$return = null)```
* By its name ```\samsoncms\api\Field::byName($query, $name, &$return = null)```
* By its name or identifier ```\samsoncms\api\Field::byNameOrID($query, $identifier, &$return = null)```

All this methods requires first argument ```samsonframework\orm\QueryInterface``` instance for performing
database queries. 

Regular usage example:
```php
/** @var \samsoncms\api\Field $fieldRecord Try to find additional field record */
$fieldRecord = \samsoncms\api\Field::byNameOrID('image')
if (isset($fieldRecord)) {
    // Additional field has been found
}
```

Last argument is optional and should be used for simple and beautiful condition creation:
```php
/** @var \samsoncms\api\Field $fieldRecord Try to find additional field record */
if (\samsoncms\api\Field::byNameOrID('image', $fieldRecord)) {
    // Additional field has been found
}
```

#Field value
