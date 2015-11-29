#SamsonCMS API module  

[![Latest Stable Version](https://poser.pugx.org/samsoncms/api/v/stable.svg)](https://packagist.org/packages/samsoncms/material)
[![Build Status](https://scrutinizer-ci.com/g/samsoncms/api/badges/build.png?b=master)](https://scrutinizer-ci.com/g/samsoncms/api/build-status/master)
[![Code Coverage](https://scrutinizer-ci.com/g/samsoncms/api/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/samsoncms/api/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/samsoncms/api/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/samsoncms/api/?branch=master) 
[![Total Downloads](https://poser.pugx.org/samsoncms/api/downloads.svg)](https://packagist.org/packages/samsoncms/material)
[![Stories in Ready](https://badge.waffle.io/samsoncms/material.png?label=ready&title=Ready)](https://waffle.io/samsoncms/material)

> SamsonCMS API for intercation with materials, fields and structures.

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
