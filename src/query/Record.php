<?php
/**
 * Created by PhpStorm.
 * User: nazarenko
 * Date: 29.03.2016
 * Time: 10:52
 */
namespace samsoncms\api\query;

use samsonframework\orm\ArgumentInterface;
use samsonframework\orm\ConditionInterface;
use samsonframework\orm\QueryInterface;

class Record
{
    /** @var string Table class name */
    protected static $identifier;

    /** @var string Table primary field name */
    protected static $primaryFieldName;

    /** @var array Collection of all entity fields */
    protected static $fields = array();

    /** @var QueryInterface Database query instance */
    protected $query;

    /** @var array Collection of entity fields to retrieved from database */
    protected $selectedFields;

    /** @var ConditionInterface Query conditions */
    protected $conditions;

    /** @var array Collection of ordering parameters */
    protected $orderBy = array();

    /** @var array Collection of limit parameters */
    protected $limit = array();

    /** @var array Collection of entity identifiers */
    protected $entityIDs = array();

    /**
     * Convert date value to database format.
     * TODO: Must implement at database layer
     *
     * @param string $date Date value for conversion
     * @return string Converted date to correct format
     */
    protected function convertToDateTime($date)
    {
        return date('Y-m-d H:i:s', strtotime($date));
    }

    /**
     * Add sorting to entity identifiers.
     *
     * @param array $entityIDs
     * @param string $fieldName Additional field name for sorting
     * @param string $order Sorting order(ASC|DESC)
     * @return array Collection of entity identifiers ordered by additional field value
     */
    protected function applySorting(array $entityIDs, $fieldName, $order = 'ASC')
    {
        if (array_key_exists($fieldName, static::$fields)) {
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

    /**
     * Add condition to current query.
     *
     * @param string $fieldName Entity field name
     * @param string $fieldValue Value
     * @param string $fieldRelation
     * @return $this Chaining
     */
    public function where($fieldName, $fieldValue = null, $fieldRelation = ArgumentInterface::EQUAL)
    {
        $this->conditions->add($fieldName, $fieldValue, $fieldRelation);

        return $this;
    }

    /**
     * Set field for sorting.
     *
     * @param string $fieldName Additional field name
     * @param string $order Sorting order
     * @return $this Chaining
     */
    public function orderBy($fieldName, $order = 'ASC')
    {
        if (array_key_exists($fieldName, static::$fields)) {
            $this->orderBy = array($fieldName, $order);
        }

        return $this;
    }

    /**
     * Add primary field query condition.
     *
     * @param string $value Field value
     * @return $this Chaining
     * @see Material::where()
     */
    public function primary($value)
    {
        return $this->where(static::$primaryFieldName, $value);
    }

    /**
     * Reorder elements in one array according to keys of another.
     *
     * @param array $array Source array
     * @param array $orderArray Ideal array
     * @return array Ordered array
     */
    protected function sortArrayByArray(array $array, array $orderArray) {
        $ordered = array();
        foreach($orderArray as $key) {
            if(array_key_exists($key,$array)) {
                $ordered[$key] = $array[$key];
                unset($array[$key]);
            }
        }
        return array_merge($ordered, $array);
    }

    /**
     * Perform SamsonCMS query and get entities collection.
     *
     * @return \samsoncms\api\Entity[] Collection of found entities
     */
    public function find()
    {
        $this->query->entity(static::$identifier);

        // Set entity primary keys if predefined
        if (count($this->entityIDs)) {
            $this->primary($this->entityIDs);
        }

        // Add query sorter for showed page
        if (count($this->orderBy) === 2) {
            $this->query->orderBy($this->orderBy[0], $this->orderBy[1]);
        }

        // Proxy to regular database query
        $return = $this->query
            ->whereCondition($this->conditions)
            ->exec();

        // Reorder if entity identifiers collection was defined
        return $this->sortArrayByArray($return, $this->entityIDs);
    }
}