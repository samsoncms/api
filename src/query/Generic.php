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

    /**
     * Add condition to current query.
     *
     * @param string $fieldName Entity field name
     * @param string $fieldValue Value
     * @return self Chaining
     */
    public function where($fieldName, $fieldValue = null, $fieldRelation = ArgumentInterface::EQUAL)
    {
        // Proxy call
        $this->query->where($fieldName, $fieldValue, $fieldRelation);

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

    /**
     * Perform SamsonCMS query and get entities collection.
     *
     * @return Material[] Collection of found entities
     */
    public function find()
    {
        // Proxy to regular database query
        return $this->query->exec();
    }

    /**
     * Generic constructor.
     *
     * @param QueryInterface $query Database query instance
     */
    public function __construct(QueryInterface $query)
    {
        $this->query = $query;
    }
}
