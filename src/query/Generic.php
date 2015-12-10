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

    /**
     * Add condition to current query.
     *
     * @param string $fieldName Entity field name
     * @param string $fieldValue Value
     * @return self Chaining
     */
    public function where($fieldName, $fieldValue = null)
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

    /**
     * Perform SamsonCMS query and get entities collection.
     *
     * @return mixed[] Collection of found entities
     */
    public function find()
    {
        // TODO: Find and describe approach with maximum generic performance
        $entityIDs = $this->findByNavigationIDs();
        $entityIDs = $this->findByAdditionalFields($this->fieldFilter, $entityIDs);

        return (new Material(static::$identifier))->byIDs($entityIDs, 'exec');
    }
}
