<?php
/**
 * Created by PhpStorm.
 * User: VITALYIEGOROV
 * Date: 09.12.15
 * Time: 09:57
 */
namespace samsoncms\api\field;

use samsoncms\api\CMS;
use samsoncms\api\Material;
use samsoncms\api\MaterialField;
use samsoncms\api\query\FieldNavigation;
use samsoncms\api\query\MaterialNavigation;
use samsonframework\orm\Condition;
use samsonframework\orm\ConditionInterface;
use samsonframework\orm\QueryInterface;
use samsoncms\api\Field;

/**
 * Material additional fields table.
 * @package samsoncms\api
 */
class Table
{
    /** @var array Collection of real row field names  */
    protected static $fieldsRealNames = array();

    /** @var integer Navigation identifier for table structure */
    protected $navigationID;

    /** @var integer Table parent material identifier */
    protected $materialID;

    /** @var Field[] Collection field instances that correspond table columns */
    protected $fields;

    /** @var QueryInterface Database query interface */
    protected $query;

    /** @var string Locale identifier */
    protected $locale;

    /** @var Row[] Fields table rows collection */
    protected $collection = array();

    /** @var string Row class name */
    protected $rowInstance = '\samsoncms\api\field\Row';

    /** @return array Get field table column names collection */
    public function columns()
    {
        return array_column($this->fields, Field::F_IDENTIFIER);
    }

    /**
     * Get collection of table column values as array.
     *
     * @param string $fieldID Additional field identifier
     *
     * @return array Collection of table column values as array
     */
    public function values($fieldID)
    {
        return (null !== $this->fields[$fieldID]) ? array_column($this->collection, $fieldID) : array();
    }

    /**
     * Get field table as multidimensional array.
     *
     * @return Row[] Field table represented as array
     */
    public function toArray()
    {
        return $this->collection;
    }

    /** @return array Collection of table rows(materials) identifiers */
    protected function rowIDs()
    {
        // Get collection of nested materials
        return $this->query
            ->entity(Material::class)
            ->where(Material::F_DELETION, 1)
            ->where(Material::F_PRIMARY, (new MaterialNavigation())->idsByRelationID($this->navigationID))
            ->where(Material::F_PARENT, $this->materialID)
            ->orderBy(Material::F_PRIORITY)
            ->fields(Material::F_PRIMARY);
    }

    /**
     * Build correct localized field request for retrieving additional fields records.
     *
     * @param Field[] $fields Collection of additional fields
     *
     * @return Condition Built condition for query
     */
    protected function fieldsCondition($fields)
    {
        // Group fields by localization
        $localizedColumns = array();
        $notLocalizedColumns = array();
        /** @var Field $field Iterate table columns(fields) */
        foreach ($fields as $field) {
            if ($field->localized()) {
                $localizedColumns[] = $field->id;
            } else {
                $notLocalizedColumns[] = $field->id;
            }
        }

        // Create field condition
        $fieldsCondition = new Condition(ConditionInterface::DISJUNCTION);
        // Create localized condition
        if (count($localizedColumns)) {
            $localizedCondition = new Condition(ConditionInterface::CONJUNCTION);
            $localizedCondition->add(Field::F_PRIMARY, $localizedColumns)
                ->add(MaterialField::F_LOCALE, $this->locale);

            // Add this condition to condition group
            $fieldsCondition->addCondition($localizedCondition);
        }

        // Create not localized condition
        if (count($notLocalizedColumns)) {
            $fieldsCondition->add(Field::F_PRIMARY, $notLocalizedColumns);
        }

        return $fieldsCondition;
    }

    /**
     * Fill table with data from database.
     */
    protected function load()
    {
        // Get table Fields instances
        $this->fields = (new FieldNavigation())->byRelationID($this->navigationID);

        $collection = array();
        if (count($rowIDs = $this->rowIDs())) {
            /** @var MaterialField $fieldValue Get additional field value instances */
            foreach ($this->query->entity(CMS::MATERIAL_FIELD_RELATION_ENTITY)
                         // Get only needed rows(materials)
                         ->where(Material::F_PRIMARY, $rowIDs)
                         ->where(Material::F_DELETION, 1)
                         // Get correct localizes field condition for columns
                         ->whereCondition($this->fieldsCondition($this->fields))
                         ->exec() as $fieldValue) {
                /** @var Field $field Try to find Field instance by identifier */
                $field = &$this->fields[$fieldValue[Field::F_PRIMARY]];
                if (null !== $field) {
                    /**
                     * As we generate camelCase names for fields we need to store
                     * original names to get their values and correctly set row
                     * fields.
                     */
                    $fieldName = null !== static::$fieldsRealNames[$field->Name]
                        ? static::$fieldsRealNames[$field->Name] : $field->Name;
                    /**
                     * Store table row(material) as it primary, store columns(Fields)
                     * by field primary. Use correct column for value.
                     */
                    $collection[$fieldValue[Material::F_PRIMARY]][$fieldName]
                        = $fieldValue[$field->valueFieldName()];
                }
            }

            /** @var Material[] $materials */
            $materials = $this->query->entity(Material::class)->where(Material::F_PRIMARY, array_keys($collection))->exec();



            // Go through collection again and created specific rows
            foreach ($collection as $materialID => $fields) {
                $this->collection[$materialID] = new $this->rowInstance($materialID, $fields, $materials[$materialID]->Created, $materials[$materialID]->Modyfied);
            }
        }
    }

    /**
     * FieldsTable constructor.
     *
     * @param QueryInterface $query        Database query interface
     * @param integer[]          $navigationID Navigation identifier for table structure
     * @param integer        $materialID   Table parent material identifier
     * @param string|null    $locale       Locale identifier
     */
    public function __construct(QueryInterface $query, $navigationID, $materialID, $locale = null)
    {
        $this->query = $query;
        $this->navigationID = $navigationID;
        $this->materialID = $materialID;
        $this->locale = $locale;

        $this->load();
    }
}
