<?php
/**
 * Created by PhpStorm.
 * User: nazarenko
 * Date: 29.03.2016
 * Time: 10:52
 */
namespace samsoncms\api\query;

use samson\activerecord\dbQuery;
use samsoncms\api\exception\WrongQueryConditionArgument;
use samsonframework\orm\ArgumentInterface;
use samsonframework\orm\Condition;
use samsonframework\orm\ConditionInterface;
use samsonframework\orm\QueryInterface;

/**
 * Generic real database entity query class.
 *
 * @package samsoncms\api\query
 */
class Record
{
    /** @var string Table class name */
    protected static $identifier;

    /** @var array Collection of all supported entity fields ids => names */
    protected static $fieldIDs = array();

    /** @var array Collection of all supported entity fields names => ids */
    protected static $fieldNames = array();

    /** @var string Table primary field name */
    protected static $primaryFieldName;

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
     * Generic constructor.
     *
     * @param QueryInterface $query Database query instance
     */
    public function __construct(QueryInterface $query = null)
    {
        $this->query = null === $query ? new dbQuery() : $query;
        $this->conditions = new Condition();
    }

    /**
     * Select specified entity fields.
     * If this method is called then only selected entity fields
     * would be filled in entity instances.
     *
     * @param mixed $fieldNames Entity field name or collection of names
     *
     * @return $this Chaining
     */
    public function select($fieldNames)
    {
        // Convert argument to array and iterate
        foreach ((!is_array($fieldNames) ? array($fieldNames) : $fieldNames) as $fieldName) {
            // Try to find entity additional field
            $pointer = &static::$fieldNames[$fieldName];
            if (null !== $pointer) {
                // Store selected additional field buy FieldID and Field name
                $this->selectedFields[$pointer] = $fieldName;
            }
        }

        return $this;
    }

    /**
     * Set field for sorting.
     *
     * @param string $fieldName Additional field name
     * @param string $order     Sorting order
     *
     * @return $this Chaining
     */
    public function orderBy($fieldName, $order = 'ASC')
    {
        if (array_key_exists($fieldName, static::$fieldIDs)) {
            $this->orderBy = array($fieldName, $order);
        }

        return $this;
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

    /**
     * Add primary field query condition.
     *
     * @param string $value Field value
     * @param string $fieldRelation
     *
     * @return $this Chaining
     * @see Material::where()
     */
    public function primary($value, $fieldRelation = ArgumentInterface::EQUAL)
    {
        return $this->where(static::$primaryFieldName, $value, $fieldRelation);
    }

    /**
     * Add condition to current query.
     *
     * @param string $fieldName Entity field name
     * @param mixed $fieldValue Value
     * @param string $fieldRelation
     *
     * @return $this Chaining
     *
     * @throws WrongQueryConditionArgument
     */
    public function where($fieldName, $fieldValue = null, $fieldRelation = ArgumentInterface::EQUAL)
    {
        // Ignore objects
        if (!is_object($fieldValue)) {
            // Get real table field name
            if (array_key_exists($fieldName, static::$fieldNames)) {
                $fieldName = static::$fieldNames[$fieldName];
            }
            $this->conditions->add($fieldName, $fieldValue, $fieldRelation);
        } else {
            throw new WrongQueryConditionArgument('Object is passed to condition');
        }

        return $this;
    }

    /**
     * Reorder elements in one array according to keys of another.
     *
     * @param array $array Source array
     * @param array $orderArray Ideal array
     * @return array Ordered array
     */
    protected function sortArrayByArray(array $array, array $orderArray)
    {
        $ordered = array();
        foreach ($orderArray as $key) {
            if (array_key_exists($key, $array)) {
                $ordered[$key] = $array[$key];
                unset($array[$key]);
            }
        }
        return array_merge($ordered, $array);
    }

    /**
     * Perform SamsonCMS query and get collection of entities fields.
     *
     * @param string $fieldName Entity field name
     *
     * @return array Collection of entity fields
     */
    public function fields($fieldName)
    {
        // Proxy to regular database query
        return $this->query
            ->entity(static::$identifier)
            ->whereCondition($this->conditions)
            ->fields($fieldName);
    }

    /**
     * Perform SamsonCMS query and get first matching entity.
     *
     * @return \samsoncms\api\Entity First matching entity
     */
    public function first()
    {
        // Proxy to regular database query
        $return = $this->query
            ->entity(static::$identifier)
            ->limit(1)
            ->whereCondition($this->conditions)
            ->exec();

        return array_shift($return);
    }

    /**
     * Perform SamsonCMS query and get amount resulting entities.
     *
     * @return int Amount of resulting entities
     */
    public function count()
    {
        // Proxy to regular database query
        return $this->query
            ->entity(static::$identifier)
            ->whereCondition($this->conditions)
            ->count();
    }

    /**
     * Convert date value to database format.
     * TODO: Must implement at database layer
     *
     * @param string $date Date value for conversion
     *
     * @return string Converted date to correct format
     */
    protected function convertToDateTime($date)
    {
        return date('Y-m-d H:i:s', strtotime($date));
    }

    /**
     * Add sorting to entity identifiers.
     *
     * @param array  $entityIDs
     * @param string $fieldName Additional field name for sorting
     * @param string $order     Sorting order(ASC|DESC)
     *
     * @return array Collection of entity identifiers ordered by additional field value
     */
    protected function applySorting(array $entityIDs, $fieldName, $order = 'ASC')
    {
        if (array_key_exists($fieldName, static::$fieldIDs)) {
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
