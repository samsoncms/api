<?php
/**
 * Created by PhpStorm.
 * User: VITALYIEGOROV
 * Date: 08.12.15
 * Time: 23:11
 */
namespace samsoncms\api\query;

use samson\activerecord\dbQuery;
use samsonframework\orm\Query;

/**
 * Material with additional fields query.
 * @package samsoncms\api
 */
class Generic
{
    /** @var string Entity identifier */
    protected static $identifier;

    /** @var string Entity navigation identifiers */
    protected static $navigationIDs;

    /** @var array Collection of entity field filter */
    protected $fieldFilter;

    /** @var array Collection of matching entity identifiers */
    protected $entityIDs;

    /**
     * Add condition to current query.
     *
     * @param string $fieldName Entity field name
     * @param string $fieldValue Value
     * @param string $relation Relation between field name and its value
     * @return self Chaining
     */
    public function where($fieldName, $fieldValue = null, $relation = '=')
    {
        // Try to find entity additional field
        if (property_exists(static::$identifier, $fieldName)) {
            // Store additional field filter value
            $this->fieldFilter[$fieldName] = $fieldValue;
        }

        return $this;
    }

    /**
     * Get collection of entity identifiers filtered by navigation identifiers.
     *
     * @param array $entityIDs Additional collection of entity identifiers for filtering
     * @return array Collection of material identifiers by navigation identifiers
     */
    protected function findByNavigationIDs($entityIDs)
    {
        return (new MaterialNavigation($entityIDs))->idsByRelationID(static::$navigationIDs);
    }

    /**
     * Get collection of entity identifiers filtered by additional field and its value.
     *
     * @param array $additionalFields Collection of additional field identifiers => values
     * @param array $entityIDs Additional collection of entity identifiers for filtering
     * @return array Collection of material identifiers by navigation identifiers
     */
    protected function findByAdditionalFields($additionalFields, $entityIDs = array())
    {
        // Iterate all additional fields needed for filter entity
        foreach ($additionalFields as $fieldID => $fieldValue) {
            // Get collection of entity identifiers passing already found identifiers
            $entityIDs = (new MaterialField($entityIDs))->idsByRelationID($fieldID, $fieldValue);

            // Stop execution if we have no entities found at this step
            if (!sizeof($entityIDs)) {
                break;
            }
        }

        return $entityIDs;
    }

    /** @return array Collection of material identifiers by navigation identifiers */
    protected function findByAdditionalField()
    {
        $return = (new MaterialField($idsByNavigation))
            ->byRelationID($this->fieldFilter[]);
        return (new MaterialNavigation())->idsByRelationID(static::$navigationIDs);
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
        if (sizeof($idsByNavigation = $this->findByNavigationIDs())) {
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
