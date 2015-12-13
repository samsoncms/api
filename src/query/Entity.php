<?php
/**
 * Created by PhpStorm.
 * User: VITALYIEGOROV
 * Date: 11.12.15
 * Time: 17:35
 */
namespace samsoncms\api\query;

use samsoncms\api\exception\EntityFieldNotFound;
use samsoncms\api\Field;
use samsoncms\api\Material;
use samsonframework\orm\ArgumentInterface;
use samsonframework\orm\Condition;
use samsonframework\orm\QueryInterface;

/**
 * Generic SamsonCMS Entity query.
 * @package samsoncms\api\query
 */
class Entity extends Material
{
    /** @var array Collection of all additional fields names */
    protected static $fieldNames = array();

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
     * Select specified entity fields.
     * If this method is called then only selected entity fields
     * would be return in entity instances.
     *
     * @param mixed $fieldNames Entity field name or collection of names
     * @return self Chaining
     */
    public function select($fieldNames)
    {
        // Convert argument to array and iterate
        foreach ((!is_array($fieldNames) ? array($fieldNames) : $fieldNames) as $fieldName) {
            // Try to find entity additional field
            $pointer = &static::$fieldNames[$fieldName];
            if (isset($pointer)) {
                // Store selected additional field buy FieldID and Field name
                $this->selectedFields[$pointer] = $fieldName;
            }
        }

        return $this;
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
        // Try to find entity additional field
        $pointer = &static::$fieldNames[$fieldName];
        if (isset($pointer)) {
            // Store additional field filter value
            $this->fieldFilter[$pointer] = $fieldValue;
        } else {
            parent::where($fieldName, $fieldValue, $fieldRelation);
        }

        return $this;
    }

    /** @return array Collection of entity identifiers */
    protected function findEntityIDs()
    {
        // TODO: Find and describe approach with maximum generic performance
        return $this->findByAdditionalFields(
            $this->fieldFilter,
            $this->findByNavigationIDs()
        );
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
        /**
         * TODO: We have separate request to materialfield for each field, maybe faster to
         * make one single query with all fields conditions. Performance tests are needed.
         */

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

        // Copy fields arrays
        $localized = static::$localizedFieldIDs;
        $notLocalized = static::$notLocalizedFieldIDs;

        // If we filter additional fields that we need to receive
        if (sizeof($this->selectedFields)) {
            foreach ($this->selectedFields as $fieldID => $fieldName) {
                // Filter localized and not fields by selected fields
                if (!isset(static::$localizedFieldIDs[$fieldID])) {
                    unset($localized[$fieldID]);
                }

                if (!isset(static::$notLocalizedFieldIDs[$fieldID])) {
                    unset($notLocalized[$fieldID]);
                }
            }
        }

        // Prepare localized additional field query condition
        $condition = new Condition(Condition::DISJUNCTION);
        foreach ($localized as $fieldID => $fieldName) {
            $condition->addCondition(
                (new Condition())
                    ->add(Field::F_PRIMARY, $fieldID)
                    ->add(Field::F_LOCALIZED, $this->locale)
            );
        }

        // Prepare not localized fields condition
        foreach ($notLocalized as $fieldID => $fieldName) {
            $condition->add(Field::F_PRIMARY, $fieldID);
        }

        // Get additional fields values for current entity identifiers
        foreach ($this->query->entity(\samsoncms\api\MaterialField::ENTITY)
                     ->where(Material::F_PRIMARY, $entityIDs)
                     ->whereCondition($condition)
                     ->where(Material::F_DELETION, true)
                     ->exec() as $additionalField
        ) {
            // Get needed metadata
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
     * Perform SamsonCMS query and get collection of entities.
     *
     * @return \samsoncms\api\Entity[] Collection of entity fields
     */
    public function find()
    {
        $return = array();
        if (sizeof($entityIDs = $this->findEntityIDs())) {
            $additionalFields = $this->findAdditionalFields($entityIDs);

            // Set entity primary keys
            $this->primary($entityIDs);

            //elapsed('End fields values');
            /** @var \samsoncms\api\Entity $item Find entity instances */
            foreach (parent::find() as $item) {
                // If we have list of additional fields that we need
                $fieldIDs = sizeof($this->selectedFields) ? $this->selectedFields : static::$fieldIDs;

                // Iterate all entity additional fields
                foreach ($fieldIDs as $variable) {
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
     * Perform SamsonCMS query and get first matching entity.
     *
     * @return \samsoncms\api\Entity Firt matching entity
     */
    public function first()
    {
        $return = array();
        if (sizeof($entityIDs = $this->findEntityIDs())) {
            $this->primary($entityIDs);

            $return = parent::first();
        }

        return $return;
    }

    /**
     * Perform SamsonCMS query and get collection of entities fields.
     *
     * @param string $fieldName Entity field name
     * @return array Collection of entity fields
     * @throws EntityFieldNotFound
     */
    public function fields($fieldName)
    {
        $return = array();
        if (sizeof($entityIDs = $this->findEntityIDs())) {
            // Check if our entity has this field
            $fieldID = &static::$fieldNames[$fieldName];
            if (isset($fieldID)) {
                $return = $this->query
                    ->entity(\samsoncms\api\MaterialField::ENTITY)
                    ->where(Material::F_PRIMARY, $entityIDs)
                    ->where(Field::F_PRIMARY, $fieldID)
                    ->where(\samsoncms\api\MaterialField::F_DELETION, true)
                    ->fields(static::$fieldValueColumns[$fieldID]);
            } else {
                throw new EntityFieldNotFound($fieldName);
            }
        }

        //elapsed('Finish SamsonCMS '.static::$identifier.' query');

        return $return;
    }

    /**
     * Generic constructor.
     *
     * @param QueryInterface $query Database query instance
     * @param string $locale Query localizaation
     */
    public function __construct(QueryInterface $query, $locale = '')
    {
        $this->locale = $locale;

        parent::__construct($query);
    }
}
