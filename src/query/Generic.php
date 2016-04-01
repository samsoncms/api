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
 *
 * @package samsoncms\api
 */
class Generic extends Record
{
    /** @var string Table class name */
    protected static $identifier = Material::class;

    /** @var string Table primary field name */
    protected static $primaryFieldName = Material::F_PRIMARY;

    /** @var array Collection of all supported entity fields */
    protected static $fieldIDs = array(
        Material::F_PRIMARY=> Material::F_PRIMARY,
        Material::F_PRIORITY => Material::F_PRIORITY,
        Material::F_IDENTIFIER => Material::F_IDENTIFIER,
        Material::F_DELETION => Material::F_DELETION,
        Material::F_PUBLISHED => Material::F_PUBLISHED,
        Material::F_PARENT => Material::F_PARENT,
        Material::F_CREATED => Material::F_CREATED,
    );

    /** @var array Collection of all supported entity fields */
    protected static $fieldNames = array(
        Material::F_PRIMARY => Material::F_PRIMARY,
        Material::F_PRIORITY => Material::F_PRIORITY,
        Material::F_IDENTIFIER => Material::F_IDENTIFIER,
        Material::F_DELETION => Material::F_DELETION,
        Material::F_PUBLISHED => Material::F_PUBLISHED,
        Material::F_PARENT => Material::F_PARENT,
        Material::F_CREATED => Material::F_CREATED,
    );

    /** @var string Entity navigation identifiers */
    protected static $navigationIDs = array();

    /**
     * Add primary field query condition.
     *
     * @param string $value Field value
     * @return $this Chaining
     * @see Material::where()
     */
    public function primary($value)
    {
        return $this->where(Material::F_PRIMARY, $value);
    }

    /**
     * Add identifier field query condition.
     *
     * @param string $value Field value
     * @return $this Chaining
     * @see Material::where()
     */
    public function identifier($value)
    {
        return $this->where(Material::F_IDENTIFIER, $value);
    }

    /**
     * Add active flag condition.
     *
     * @param bool $value Field value
     * @return $this Chaining
     * @see Material::where()
     */
    public function active($value)
    {
        return $this->where(Material::F_DELETION, $value);
    }

    /**
     * Add entity published field query condition.
     *
     * @param string $value Field value
     * @return $this Chaining
     * @see Material::where()
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
     * @return $this Chaining
     * @see Material::where()
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
     * @return $this Chaining
     * @see Material::where()
     */
    public function modified($value, $relation = ArgumentInterface::EQUAL)
    {
        return $this->where(Material::F_MODIFIED, $this->convertToDateTime($value), $relation);
    }
}
