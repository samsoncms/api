<?php
/**
 * Created by PhpStorm.
 * User: VITALYIEGOROV
 * Date: 08.12.15
 * Time: 23:11
 */
namespace samsoncms\api\query;

use samson\activerecord\dbQuery;
use samsoncms\api\Field;
use samsoncms\api\Material;
use samsoncms\api\MaterialField;
use samsonframework\orm\Condition;
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
    protected static $navigationIDs = array();

    /** @var array Collection of localized additional fields identifiers */
    protected static $localizedFieldIDs = array();

    /** @var array Collection of NOT localized additional fields identifiers */
    protected static $notLocalizedFieldIDs = array();

    /** @var array Collection of all additional fields identifiers */
    protected static $fieldIDs = array();

    /** @var  @var array Collection of additional fields value column names */
    protected static $fieldValueColumns = array();


    /** @var array Collection of entity field filter */
    protected $fieldFilter = array();

    /** @var string Query locale */
    protected $locale = '';

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
    protected function findByNavigationIDs($entityIDs = array())
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
     * Get entities additional field values.
     *
     * @param array $entityIDs Collection of entity identifiers
     * @return array Collection of entities additional fields EntityID => [Additional field name => Value]
     */
    protected function findAdditionalFields($entityIDs)
    {
        $return = array();

        // Prepare localized additional field query condition
        $condition = new Condition(Condition::DISJUNCTION);
        foreach (static::$localizedFieldIDs as $fieldID => $fieldName) {
            $condition->addCondition(
                (new Condition())
                ->add(Field::F_PRIMARY, $fieldID)
                ->add(Field::F_LOCALIZED, $this->locale)
            );
        }

        // Prepare not localized fields condition
        foreach (static::$notLocalizedFieldIDs as $fieldID => $fieldName) {
            $condition->add(Field::F_PRIMARY, $fieldID);
        }

        // Get additional fields values for current entity identifiers
        foreach ((new dbQuery())->entity(MaterialField::ENTITY)
                     ->where(Material::F_PRIMARY, $entityIDs)
                     ->whereCondition($condition)
                     ->where(Material::F_DELETION, true)
                     ->exec() as $additionalField
        ) {
            $fieldID = $additionalField[Field::F_PRIMARY];
            $materialID = $additionalField[Material::F_PRIMARY];
            $valueField = static::$fieldValueColumns[$fieldID];
            $fieldName = static::$fieldIDs[$fieldID];
            $fieldValue = $additionalField[$valueField];

            // Gather additional fields values by entity identifiers and field name
            $return[$materialID][$fieldName] = $fieldValue;
        }

        return $return;
    }

    /**
     * Perform SamsonCMS query and get entities collection.
     *
     * @return mixed[] Collection of found entities
     */
    public function find()
    {
        //elapsed('Start SamsonCMS '.static::$identifier.' query');
        // TODO: Find and describe approach with maximum generic performance
        $entityIDs = $this->findByNavigationIDs();
        //elapsed('End navigation filter');
        $entityIDs = $this->findByAdditionalFields($this->fieldFilter, $entityIDs);
        //elapsed('End fields filter');

        $return = array();
        if (sizeof($entityIDs)) {
            $additionalFields = $this->findAdditionalFields($entityIDs);
            //elapsed('End fields values');
            /** @var \samsoncms\api\Entity $item Find entity instances */
            foreach ((new \samsoncms\api\query\Material(static::$identifier))->byIDs($entityIDs, 'exec') as $item) {
                // Iterate all entity additional fields
                foreach (static::$fieldIDs as $fieldID => $variable) {
                    // Set only existing additional fields
                    $pointer = &$additionalFields[$item[Material::F_PRIMARY]][$variable];
                    if (isset($pointer)) {
                        $item->$variable = $pointer;
                    }
                }
                // Store entity by identifier
                $return[$item[Material::F_PRIMARY]] = $item;
            }
        }

        //elapsed('Finish SamsonCMS '.static::$identifier.' query');

        return $return;
    }

    /**
     * Generic constructor.
     * @param string $locale Query localizaation
     */
    public function __construct($locale = '')
    {
        $this->locale = $locale;
    }
}
