<?php
/**
 * Created by PhpStorm.
 * User: VITALYIEGOROV
 * Date: 08.12.15
 * Time: 23:11
 */
namespace samsoncms\api\query;

use samsoncms\api\Material;
use samsonframework\orm\ArgumentInterface;

/**
 * Material with additional fields query.
 * @package samsoncms\api
 */
class Generic
{
    /** @var string Entity identifier */
    protected static $identifier;

    /** @var string Entity navigation identifiers */
    protected static $navigationIDs = array();

    /** @var array Collection of localized additional fields identifiers */
    protected static $localizedFieldIDs = array();

    /** @var array Collection of NOT localized additional fields identifiers */
    protected static $notLocalizedFieldIDs = array();

    /** @var array Collection of all additional fields identifiers */
    protected static $fieldIDs = array();

    /** @var array Collection of all additional fields names */
    protected static $fieldNames = array();

    /** @var  @var array Collection of additional fields value column names */
    protected static $fieldValueColumns = array();


    /** @var array Collection selected additional entity fields */
    protected $selectedFields = array();

    /**
     * Add condition to current query.
     *
     * @param string $fieldName Entity field name
     * @param string $fieldValue Value
     * @return self Chaining
     */
    public function where($fieldName, $fieldValue = null, $fieldRelation = ArgumentInterface::EQUAL)
    {
        // Try to find entity additional field
        $pointer = &static::$fieldNames[$fieldName];
        if (isset($pointer)) {
            // Store additional field filter value
            $this->fieldFilter[$pointer] = $fieldValue;
        }

        return $this;
    }

    /**
     * Add identifier field query condition.
     *
     * @param string $value Field value
     * @return self Chaining
     * @see Generic::where()
     */
    public function identifier($value)
    {
        return $this->where(Material::F_IDENTIFIER, $value);
    }

    /**
     * Add entity published field query condition.
     *
     * @param string $value Field value
     * @return self Chaining
     * @see Generic::where()
     */
    public function published($value)
    {
        return $this->where(Material::F_PUBLISHED, $value);
    }

    /**
     * Add entity creation field query condition.
     *
     * @param string $value Field value
     * @param string $relation @see ArgumentInterface types
     * @return self Chaining
     * @see Generic::where()
     */
    public function created($value, $relation = ArgumentInterface::EQUAL)
    {
        return $this->where(Material::F_CREATED, $value, $relation);
    }

    /**
     * Add entity modification field query condition.
     *
     * @param string $value Field value
     * @param string $relation @see ArgumentInterface types
     * @return self Chaining
     * @see Generic::where()
     */
    public function modified($value, $relation = ArgumentInterface::EQUAL)
    {
        return $this->where(Material::F_MODIFIED, $value, $relation);
    }
}
