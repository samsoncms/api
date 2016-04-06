<?php
/**
 * Created by PhpStorm.
 * User: VITALYIEGOROV
 * Date: 11.12.15
 * Time: 17:35
 */
namespace samsoncms\api\query;

use samson\activerecord\dbQuery;
use samsoncms\api\CMS;
use samsoncms\api\exception\EntityFieldNotFound;
use samsoncms\api\Field;
use samsoncms\api\Material;
use samsonframework\orm\Argument;
use samsonframework\orm\ArgumentInterface;
use samsonframework\orm\Condition;
use samsonframework\orm\ConditionInterface;
use samsonframework\orm\QueryInterface;

/**
 * Generic SamsonCMS Entity query.
 * @package samsoncms\api\query
 */
class Entity extends Generic
{
    /** @var array Collection of all additional fields names */
    public static $fieldNames = array();

    /** @var array Collection of localized additional fields identifiers */
    protected static $localizedFieldIDs = array();

    /** @var array Collection of NOT localized additional fields identifiers */
    protected static $notLocalizedFieldIDs = array();

    /** @var array Collection of all additional fields identifiers */
    protected static $fieldIDs = array();

    /** @var  @var array Collection of additional fields value column names */
    protected static $fieldValueColumns = array();

    /** @var Condition Collection of entity field filter */
    protected $fieldFilter = array();

    /** @var string Query locale */
    protected $locale = '';

    /** @var array Collection of additional fields for ordering */
    protected $entityOrderBy = array();

    /** @var array Collection of search fields for query */
    protected $searchFilter = array();

    /**
     * Generic constructor.
     *
     * @param QueryInterface $query  Database query instance
     * @param string         $locale Query localization
     */
    public function __construct(QueryInterface $query = null, $locale = null)
    {
        $this->locale = $locale;

        parent::__construct(null === $query ? new dbQuery() : $query);

        // Work only with active entities
        $this->active(true);
    }

    /**
     * Select specified entity fields.
     * If this method is called then only selected entity fields
     * would be filled in entity instances.
     *
     * @param mixed $fieldNames Entity field name or collection of names
     *
*@return $this Chaining
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
     * Set additional field for sorting.
     *
     * @param string $fieldName Additional field name
     * @param string $order Sorting order
     * @return $this Chaining
     */
    public function orderBy($fieldName, $order = 'ASC')
    {
        if (array_key_exists($fieldName, static::$fieldNames)) {
            $this->entityOrderBy = array($fieldName, $order);
        } else {
            parent::orderBy($fieldName, $order);
        }

        return $this;
    }

    /**
     * Search entity fields by text.
     *
     * @param string $text Searching text
     * @return $this
     */
    public function search($text)
    {
        $this->searchFilter[] = $text;

        return $this;
    }

    /**
     * Set resulting query limits.
     *
     * @param integer $offset Starting index
     * @param integer|null $count Entities count
     * @return $this Chaining
     */
    public function limit($offset, $count = null)
    {
        $this->limit = array($offset, $count);

        return $this;
    }

    /**
     * Perform SamsonCMS query and get collection of entities.
     *
     * @param int $page Page number
     * @param int $size Page size
     *
     * @return \samsoncms\api\Entity[] Collection of entity fields
     */
    public function find($page = null, $size = null)
    {
        $return = array();
        if (count($this->entityIDs = $this->findEntityIDs())) {
            // Apply search filter
            if (count($this->searchFilter)) {
                $this->entityIDs = $this->applySearch($this->entityIDs);

                // Return result if not ids
                if (count($this->entityIDs) === 0) {
                    return $return;
                }
            }

            // Slice identifier array to match pagination
            if (null !== $page && null !== $size) {
                $this->entityIDs = array_slice($this->entityIDs, ($page - 1) * $size, $size);
            }

            // Perform parent find() only if we have entity identifiers
            if (count($this->entityIDs)) {
                // Get entity additional field records
                $additionalFields = $this->findAdditionalFields($this->entityIDs);

                /** @var \samsoncms\api\Entity $item Find entity instances */
                foreach (parent::find() as $item) {
                    // Fill entity with additional fields
                    $item = $this->fillEntityFields($item, $additionalFields);

                    // Store entity by identifier
                    $return[$item[Material::F_PRIMARY]] = $item;
                }
            }
        }

        //elapsed('Finish SamsonCMS '.static::$identifier.' query');

        return $return;
    }

    /**
     * Prepare entity identifiers.
     *
     * @param array $entityIDs Collection of identifier for filtering
     * @return array Collection of entity identifiers
     */
    protected function findEntityIDs(array $entityIDs = array())
    {
        if ($this->conditions) {
            $entityIDs = $this->query
                ->entity(Material::ENTITY)
                ->whereCondition($this->conditions)
                ->fields(Material::F_PRIMARY);
        }

        // TODO: Find and describe approach with maximum generic performance
        $entityIDs = $this->findByAdditionalFields(
            $this->fieldFilter,
            $this->findByNavigationIDs($entityIDs)
        );

        // Perform sorting if necessary
        if (count($this->entityOrderBy) === 2) {
            $entityIDs = $this->applySorting($entityIDs, $this->entityOrderBy[0], $this->entityOrderBy[1]);
        }

        // Perform sorting in parent fields if necessary
        if (count($this->orderBy) === 2) {
            $entityIDs = $this->applySorting($entityIDs, $this->orderBy[0], $this->orderBy[1]);
        }

        // Perform limits if necessary
        if (count($this->limit)) {
            $entityIDs = array_slice($entityIDs, $this->limit[0], $this->limit[1]);
        }

        return $entityIDs;
    }

    /**
     * Get collection of entity identifiers filtered by additional field and its value.
     *
     * @param Condition[] $additionalFields Collection of additional field identifiers => values
     * @param array $entityIDs Additional collection of entity identifiers for filtering
     * @return array Collection of material identifiers by navigation identifiers
     */
    protected function findByAdditionalFields($additionalFields, $entityIDs = array())
    {
        /**
         * TODO: We have separate request to materialfield for each field, maybe faster to
         * make one single query with all fields conditions. Performance tests are needed.
         */

        /** @var Condition $fieldCondition Iterate all additional fields needed for filter condition */
        foreach ($additionalFields as $fieldID => $fieldCondition) {
            // Get collection of entity identifiers passing already found identifiers
            $entityIDs = (new MaterialField($entityIDs))->idsByRelationID($fieldID, $fieldCondition, array(), $this->locale);

            // Stop execution if we have no entities found at this step
            if (!count($entityIDs)) {
                break;
            }
        }

        return $entityIDs;
    }

    /**
     * Get collection of entity identifiers filtered by navigation identifiers.
     *
     * @param array $entityIDs Additional collection of entity identifiers for filtering
     *
     * @return array Collection of material identifiers by navigation identifiers
     */
    protected function findByNavigationIDs($entityIDs = array())
    {
        return (new MaterialNavigation($entityIDs))->idsByRelationID(static::$navigationIDs);
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
        // Get additional field metadata
        $fieldID = &static::$fieldNames[$fieldName];
        $valueColumn = &static::$fieldValueColumns[$fieldID];

        // If this is additional field
        if (null !== $fieldID && null !== $valueColumn) {
            return $this->query
                ->entity(CMS::MATERIAL_FIELD_RELATION_ENTITY)
                ->where(Field::F_PRIMARY, $fieldID)
                ->where(Material::F_PRIMARY, $entityIDs)
                ->orderBy($valueColumn, $order)
                ->fields(Material::F_PRIMARY);
        } else { // Nothing is changed
            return parent::applySorting($entityIDs, $fieldName, $order);
        }
    }

    /**
     * Get entities additional field values.
     *
     * @param array $entityIDs Collection of entity identifiers
     * @return array Collection of entities additional fields EntityID => [Additional field name => Value]
     * @throws EntityFieldNotFound
     */
    protected function findAdditionalFields($entityIDs)
    {
        $return = array();

        // Copy fields arrays
        $localized = static::$localizedFieldIDs;
        $notLocalized = static::$notLocalizedFieldIDs;

        // If we filter additional fields that we need to receive
        if (count($this->selectedFields)) {
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
                    ->add(\samsoncms\api\MaterialField::F_LOCALE, $this->locale)
            );
        }

        // Prepare not localized fields condition
        foreach ($notLocalized as $fieldID => $fieldName) {
            $condition->add(Field::F_PRIMARY, $fieldID);
        }

        // Get additional fields values for current entity identifiers
        foreach ($this->query->entity(CMS::MATERIAL_FIELD_RELATION_ENTITY)
                     ->where(Material::F_PRIMARY, $entityIDs)
                     ->whereCondition($condition)
                     ->where(Material::F_DELETION, true)
                     ->exec() as $additionalField
        ) {
            // Get needed metadata
            $fieldID = $additionalField[Field::F_PRIMARY];
            $materialID = $additionalField[Material::F_PRIMARY];
            $valueField = &static::$fieldValueColumns[$fieldID];
            $fieldName = &static::$fieldIDs[$fieldID];

            // Check if we have this additional field in this entity query
            if (null === $valueField || null === $fieldName) {
                throw new EntityFieldNotFound($fieldID);
            } else { // Add field value to result
                $fieldValue = $additionalField[$valueField];
                // Gather additional fields values by entity identifiers and field name
                $return[$materialID][$fieldName] = $fieldValue;
            }
        }

        return $return;
    }

    /**
     * @param array $entityIDs
     *
     * @return array
     */
    protected function applySearch(array $entityIDs)
    {
        $condition = new Condition(ConditionInterface::DISJUNCTION);

        foreach ($this->searchFilter as $searchText) {
            foreach (static::$fieldValueColumns as $fieldId => $fieldColumn) {
                $condition->addCondition((new Condition())
                    ->addArgument(new Argument($fieldColumn, '%' . $searchText . '%', ArgumentInterface::LIKE))
                    ->addArgument(new Argument(\samsoncms\api\MaterialField::F_FIELDID, $fieldId)));
            }
        }

        return $this->query
            ->entity(\samsoncms\api\MaterialField::class)
            ->whereCondition($condition)
            ->where(Material::F_PRIMARY, $entityIDs)
            ->fields(Material::F_PRIMARY);
    }

    /**
     * Fill entity additional fields.
     *
     * @param \samsoncms\api\Entity $entity Entity instance for filling
     * @param array $additionalFields Collection of additional field values
     * @return Entity With filled additional field values
     */
    protected function fillEntityFields(\samsoncms\api\Entity $entity, array $additionalFields)
    {
        // If we have list of additional fields that we need
        $fieldIDs = count($this->selectedFields) ? $this->selectedFields : static::$fieldIDs;

        // Iterate all entity additional fields
        foreach ($fieldIDs as $variable) {
            // Set only existing additional fields
            $pointer = &$additionalFields[$entity->id][$variable];
            if (null !== $pointer) {
                $entity->$variable = $pointer;
            }
        }

        return $entity;
    }

    /**
     * Perform SamsonCMS query and get first matching entity.
     *
     * @return \samsoncms\api\Entity Firt matching entity
     */
    public function first()
    {
        $return = null;
        if (count($entityIDs = $this->findEntityIDs())) {
            $this->primary($entityIDs);
            $additionalFields = $this->findAdditionalFields($entityIDs);

            if (null !== ($foundEntity = parent::first())) {
                $return = $this->fillEntityFields($foundEntity, $additionalFields);
            }
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
        if (count($entityIDs = $this->findEntityIDs())) {
            // Check if our entity has this field
            $fieldID = &static::$fieldNames[$fieldName];
            if (isset($fieldID)) {
                $return = $this->query
                    ->entity(\samsoncms\api\MaterialField::ENTITY)
                    ->where(Material::F_PRIMARY, $entityIDs)
                    ->where(Field::F_PRIMARY, $fieldID)
                    ->where(\samsoncms\api\MaterialField::F_DELETION, true)
                    ->fields(static::$fieldValueColumns[$fieldID]);
            } elseif (property_exists(static::$identifier, $fieldName)) {
                // TODO: Generalize real and virtual entity fields and manipulations with them
                // Set filtered entity identifiers
                $this->where(Material::F_PRIMARY, $entityIDs);
                // If this is parent field
                return parent::fields($fieldName);
            } else {
                throw new EntityFieldNotFound($fieldName);
            }
        }

        //elapsed('Finish SamsonCMS '.static::$identifier.' query');

        return $return;
    }

    /**
     * Add condition to current query.
     *
     * @param string $fieldName     Entity field name
     * @param string $fieldValue    Value
     * @param string $fieldRelation Entity field to value relation
     *
     * @return $this Chaining
     */
    public function where($fieldName, $fieldValue = null, $fieldRelation = ArgumentInterface::EQUAL)
    {
        // Try to find entity additional field
        if (array_key_exists($fieldName, static::$fieldNames)) {
            $pointer = static::$fieldNames[$fieldName];
            // Store additional field filter value
            $this->fieldFilter[$pointer] = (new Condition())->add(static::$fieldValueColumns[$pointer], $fieldValue, $fieldRelation);
        } else {
            parent::where($fieldName, $fieldValue, $fieldRelation);
        }

        return $this;
    }

    /**
     * Perform SamsonCMS query and get amount resulting entities.
     *
     * @return int Amount of resulting entities
     */
    public function count()
    {
        $return = 0;
        if (count($entityIDs = $this->findEntityIDs())) {

            if (count($this->searchFilter)) {
                $entityIDs = $this->applySearch($entityIDs);

                // Return result if not ids
                if (count($entityIDs) === 0) {
                    return 0;
                }
            }

            $this->primary($entityIDs);
            $return = parent::count();
        }

        return $return;
    }
}
