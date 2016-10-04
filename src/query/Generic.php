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
 * TODO: We have create a Record class that lays behind Entity-Generic and we store entity fields in fieldIDs static variable
 * which cannot be accessed in parent function calls like orderBy, applySorting, to get intermediary static class variables
 * from Generic class. This affect code duplication of method in Record to Generic to give access to static class fields
 * described in fieldIDs.
 */

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
        Material::F_MODIFIED => Material::F_MODIFIED,
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
        Material::F_MODIFIED => Material::F_MODIFIED
    );

    /** @var string Entity navigation identifiers */
    protected static $navigationIDs = array();

    /**
     * Add primary field query condition.
     *
     * @param string $value Field value
     * @param string $relation @see ArgumentInterface types
     *
     * @return $this Chaining
     * @see Material::where()
     */
    public function primary($value, $relation = ArgumentInterface::EQUAL)
    {
        return $this->where(Material::F_PRIMARY, $value, $relation);
    }

    /**
     * Add identifier field query condition.
     *
     * @param string $value Field value
     * @param string $relation @see ArgumentInterface types
     *
     * @return $this Chaining
     * @see Material::where()
     */
    public function identifier($value, $relation = ArgumentInterface::EQUAL)
    {
        return $this->where(Material::F_IDENTIFIER, $value, $relation);
    }

    /**
     * Add active flag condition.
     *
     * @param bool $value Field value
     * @param string $relation @see ArgumentInterface types
     *
     * @return $this Chaining
     * @see Material::where()
     */
    public function active($value, $relation = ArgumentInterface::EQUAL)
    {
        return $this->where(Material::F_DELETION, $value, $relation);
    }

    /**
     * Add entity published field query condition.
     *
     * @param string $value Field value
     * @param string $relation @see ArgumentInterface types
     *
     * @return $this Chaining
     * @see Material::where()
     */
    public function published($value, $relation = ArgumentInterface::EQUAL)
    {
        return $this->where(Material::F_PUBLISHED, $value, $relation);
    }

    /**
     * Add entity creation field query condition.
     *
     * @param string $value Field value
     * @param string $relation @see ArgumentInterface types
     *
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

    /**
     * Set field for sorting.
     * TODO: We have code duplication in Record::orderBy() due to late static binding
     * @param string $fieldName Additional field name
     * @param string $order     Sorting order
     *
     * @return $this Chaining
     */
    public function orderBy($fieldName, $order = 'ASC')
    {
        if (in_array($fieldName, self::$fieldIDs)) {
            $this->orderBy = array($fieldName, $order);
        }

        return $this;
    }

    /**
     * Add sorting to entity identifiers.
     * TODO: We have code duplication in Record::orderBy() due to late static binding
     * @param array  $entityIDs
     * @param string $fieldName Additional field name for sorting
     * @param string $order     Sorting order(ASC|DESC)
     *
     * @return array Collection of entity identifiers ordered by additional field value
     */
    protected function applySorting(array $entityIDs, $fieldName, $order = 'ASC')
    {
        if (array_key_exists($fieldName, self::$fieldIDs)) {
            // Order by parent fields
            return $this->query
                ->entity(static::$identifier)
                ->where(static::$primaryFieldName, $entityIDs)
                ->orderBy($fieldName, $order)
                ->fields(static::$primaryFieldName);
        } else { // Nothing is changed
            return $entityIDs;
        }
    }
}
