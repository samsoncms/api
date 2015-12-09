<?php
/**
 * Created by PhpStorm.
 * User: VITALYIEGOROV
 * Date: 08.12.15
 * Time: 23:11
 */
namespace samsoncms\api\query;

use samson\activerecord\dbQuery;

/**
 * Material with additional fields query.
 * @package samsoncms\api
 */
class Entity
{
    /** @var string Entity identifier */
    protected static $identifier;

    /** @var string Entity navigation identifiers */
    protected static $navigationIDs;

    /** @var array Collection of entity field filter */
    protected $fieldFilter;

    /**
     * Set additional entity field condition.
     *
     * @param string $fieldName Field identifier
     * @param string $fieldValue Field value
     * @return self Chaining
     */
    public function where($fieldName, $fieldValue)
    {
        if (property_exists(static::$identifier, $fieldName)) {
            $this->fieldFilter[$fieldName] = $fieldValue;
        }
    }

    /**
     * Perform SamsonCMS query and get entities collection.
     *
     * @return mixed[] Collection of found entities
     */
    public function find()
    {
        $return = array();
        /** @var array $idsByNavigation First step - filter by navigation */
        if (sizeof($idsByNavigation = (new MaterialNavigation())->idsByRelationID(static::$navigationIDs))) {
            // Second step filter by additional field value
            if (sizeof($this->fieldFilter)) {
                $return = (new MaterialField($idsByNavigation))
                    ->byRelationID($this->fieldFilter[]);
            } else { // Just return entities filtered by navigation
                return (new Material($idsByNavigation, static::$identifier))->byIDs($idsByNavigation, 'exec');
            }
        }

        return $return;
    }
}
