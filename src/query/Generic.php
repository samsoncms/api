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
use samsonframework\orm\Condition;
use samsonframework\orm\QueryInterface;

/**
 * Material with additional fields query.
 * @package samsoncms\api
 */
class Generic
{
    /** @var array Collection of all supported entity fields */
    protected static $parentFields = array(
        Material::F_PRIORITY => Material::F_PRIORITY,
        Material::F_IDENTIFIER => Material::F_IDENTIFIER,
        Material::F_DELETION => Material::F_DELETION,
        Material::F_PUBLISHED => Material::F_PUBLISHED,
        Material::F_PARENT => Material::F_PARENT,
        Material::F_CREATED => Material::F_CREATED,
    );

    /** @var string Entity identifier */
    protected static $identifier;

    /** @var string Entity navigation identifiers */
    protected static $navigationIDs = array();

    /** @var QueryInterface Database query instance */
    protected $query;

    /** @var array Collection of entity fields to retrieved from database */
    protected $selectedFields;

    /** @var Condition Query conditions */
    protected $conditions;

    /**
     * Convert date value to database format.
     * TODO: Must implement at database layer
     *
     * @param string $date Date value for conversion
     * @return string Converted date to correct format
     */
    protected function convertToDateTime($date)
    {
        return date("Y-m-d H:i:s", strtotime($date));
    }

    /**
     * Add condition to current query.
     *
     * @param string $fieldName Entity field name
     * @param string $fieldValue Value
     * @return self Chaining
     */
    public function where($fieldName, $fieldValue = null, $fieldRelation = ArgumentInterface::EQUAL)
    {
        $this->conditions->add($fieldName, $fieldValue, $fieldRelation);

        return $this;
    }

    /**
     * Add primary field query condition.
     *
     * @param string $value Field value
     * @return self Chaining
     * @see Generic::where()
     */
    public function primary($value)
    {
        return $this->where(Material::F_PRIMARY, $value);
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
        return $this->where(Material::F_CREATED, $this->convertToDateTime($value), $relation);
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
        return $this->where(Material::F_MODIFIED, $this->convertToDateTime($value), $relation);
    }

    /**
     * Perform SamsonCMS query and get entities collection.
     *
     * @return Entity[] Collection of found entities
     */
    public function find()
    {
        // Proxy to regular database query
        return $this->query->entity(static::$identifier)->whereCondition($this->conditions)->exec();
    }

    /**
     * Generic constructor.
     *
     * @param QueryInterface $query Database query instance
     */
    public function __construct(QueryInterface $query)
    {
        $this->query = $query;
        $this->conditions = new Condition();
    }
}
