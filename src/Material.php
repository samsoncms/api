<?php
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>
 * on 07.08.14 at 17:11
 */
namespace samsoncms\api;

use samson\activerecord\dbQuery;
use samsoncms\api\query\FieldNavigation;
use samsoncms\api\query\MaterialNavigation;
use \samsonframework\orm\Condition;
use samsonframework\orm\Query;
use \samsonframework\orm\QueryInterface;

/**
 * SamsonCMS Material database record object.
 * This class extends default ActiveRecord material table record functionality.
 * @package samson\cms
 * @author Vitaly Egorov <egorov@samsonos.com>
 */
class Material extends \samson\activerecord\material
{
    /** Override table attributes for late static binding */
    public static $_attributes = array();
    public static $_sql_select = array();
    public static $_sql_from = array();
    public static $_own_group = array();
    public static $_map = array();

    /** @var integer Primary field */
    public $MaterialID;

    /** @var string Unique identifier */
    public $Url;

    /** @var bool Internal existence flag */
    public $Active;

    /** @var bool Published flag */
    public $Published;

    /**
     * Get material entity by URL(s).
     *
     * @param QueryInterface $query Object for performing database queries
     * @param array|string $url Material URL or collection of material URLs
     * @param self|array|null $return Variable where request result would be returned
     * @return bool|self True if material entities has been found
     */
    public static function byUrl(QueryInterface $query, $url, & $return = array())
    {
        // Get entities by filtered identifiers
        $return = $query->entity(get_called_class())
            ->where('Url', $url)
            ->where('Active', 1)
            ->first();

        // If only one argument is passed - return null, otherwise bool
        return func_num_args() > 2 ? $return !== null : $return;
    }

    /**
     * Set additional material field value by field identifier
     * @param string $fieldID Field identifier
     * @param string $value Value to be stored
     * @param string $locale Locale identifier
     */
    public function setFieldByID($fieldID, $value, $locale = DEFAULT_LOCALE)
    {
        // TODO: This should be removed
        /** @var QueryInterface $query This should be removed to use $this->database*/
        $query = dbQuery();

        /** @var Field $fieldRecord Try to find this additional field */
        $fieldRecord = null;
        if (Field::byID($query, $fieldID, $fieldRecord)) {
            /** @var MaterialField[] $materialFieldRecord Try to find additional field value */
            $materialFieldRecord = null;
            if (!MaterialField::byFieldIDAndMaterialID($query, $this->id, $fieldRecord->id, $materialFieldRecord)) {
                // Create new additional field value record if it does not exists
                $materialFieldRecord = new MaterialField();
                $materialFieldRecord->FieldID = $fieldRecord->id;
                $materialFieldRecord->MaterialID = $this->id;
                $materialFieldRecord->Active = 1;
                $materialFieldRecord->locale = $locale;
            } else { // Get first record(actually it should be only one)
                $materialFieldRecord = array_shift($materialFieldRecord);
            }

            // At this point we already have database record instance
            $valueFieldName = $fieldRecord->valueFieldName();
            $materialFieldRecord->$valueFieldName = $value;
            $materialFieldRecord->save();
        }
    }

    /**
     * Get select additional field text value.
     *
     * @param string $fieldID Field identifier
     * @return string Select field text
     */
    public function selectText($fieldID)
    {
        /** @var Field $field */
        $field = null;
        if (Field::byID(new Query('\samsoncms\api\Field', $this->database), $fieldID, $fieldID)) {
            // If this entity has this field set
            if (isset($this[$field->Name]{0})) {
                return $field->options($this[$field->Name]);
            }
        }

        // Value not set
        return '';
    }

    /**
     * Get collection of images for material by gallery additional field selector. If none is passed
     * all images from gallery table would be returned for this material entity.
     *
     * @param string|null $fieldSelector Additional field selector value
     * @param string $selector Additional field field name to search for
     * @return \samsonframework\orm\RecordInterface[] Collection of images in this gallery additional field for material
     */
    public function &gallery($fieldSelector = null, $selector = 'FieldID')
    {
        /** @var \samsonframework\orm\RecordInterface[] $images Get material images for this gallery */
        $images = array();

        // Create query
        $query = new dbQuery();

        $query->entity(CMS::MATERIAL_FIELD_RELATION_ENTITY);

        /* @var Field Get field object if we need to search it by other fields */
        $field = null;
        if ($selector != 'FieldID' && Field::oneByColumn($query, $selector, $fieldSelector)) {
            $fieldSelector = $field->id;
        }

        // Add field filter if present
        if (isset($fieldSelector)) {
            $query->where("FieldID", $fieldSelector);
        }

        /** @var \samson\activerecord\materialfield $dbMaterialField Find material field gallery record */
        $dbMaterialField = null;
        if ($query->where('MaterialID', $this->id)->first($dbMaterialField)) {
            // Get material images for this materialfield
            $images = $query->entity(CMS::MATERIAL_IMAGES_RELATION_ENTITY)
                ->where('materialFieldId', $dbMaterialField->id)
                ->exec();
        }

        return $images;
    }

    /**
     * Copy this material related entities.
     *
     * @param QueryInterface $query Database query instance
     * @param string $entity Entity identifier
     * @param string $newIdentifier Copied material idetifier
     * @param array $excludedIDs Collection of related entity identifier to exclude from copying
     */
    protected function copyRelatedEntity(QueryInterface $query, $entity, $newIdentifier, $excludedIDs = array())
    {
        // Copy additional fields
        foreach ($query->entity($entity)
                     ->where('MaterialID', $this->MaterialID)
                     ->exec() as $copiedEntity) {
            // Check if field is NOT excluded from copying
            if (!in_array($copiedEntity->id, $excludedIDs)) {
                /** @var MaterialField $copy Copy instance */
                $copy = $copiedEntity->copy();
                $copy->MaterialID = $newIdentifier;
                $copy->save();
            }
        }
    }

    /**
     * Create copy of current object.
     *
     * @param mixed $clone Material for cloning
     * @param array $excludedFields Additional fields identifiers not copied
     * @returns self New copied instance
     */
    public function &copy(&$clone = null, $excludedFields = array())
    {
        /** @var Material $clone Create new instance by copying */
        $clone = parent::copy($clone);

        // Create query
        $query = new dbQuery();

        $this->copyRelatedEntity($query, CMS::MATERIAL_NAVIGATION_RELATION_ENTITY, $clone->id);
        $this->copyRelatedEntity($query, CMS::MATERIAL_FIELD_RELATION_ENTITY, $clone->id, $excludedFields);
        $this->copyRelatedEntity($query, CMS::MATERIAL_IMAGES_RELATION_ENTITY, $clone->id);

        return $clone;
    }

    public function nestedIDs($navigationID = null, &$return = array())
    {
        // Create query
        $query = new dbQuery();

        /** @var array $nestedIDs Get collection of materials by navigation */
        if (sizeof($nestedIDs = (new MaterialNavigation())->byRelationID($navigationID))) {
            // Get collection of nested materials
            $return = $query->entity(get_class($this))
                ->where('MaterialID', $nestedIDs)
                ->where('Active', 1)
                ->where('parent_id', $this->id)
                ->orderBy('priority')
                ->fields('MaterialID');
        }

        return $return;
    }

    /**
     * Get material additional fields table.
     *
     * @param string $navigationID Navigation table identifier
     * @param array $tableColumns Columns names list
     * @param string $externalHandler External handler to perform some extra code
     * @param array $params External handler params
     * @return array Collection of collections of table cells, represented as materialfield objects
     */
    public function table($navigationID, &$tableColumns = null) {
        // Create query
        $query = new dbQuery();

        /** @var array $resultTable Collection of collections of field cells */
        $resultTable = array();

        /** @var array $dbTableFieldsIds Array of table structure column identifiers */
        if (sizeof($dbTableFieldsIds = (new FieldNavigation())->byRelationID($navigationID))) {
            // Get localized and not localized fields
            $localizedFields = array();
            $unlocalizedFields = array();

            /** @var Field $field Table column */
            foreach ($dbTableFieldsIds as $field) {
                /** Add table columns names */
                $tableColumns[] = $field->Name;
                if ($field->local == 1) {
                    $localizedFields[] = $field->id;
                } else {
                    $unlocalizedFields[] = $field->id;
                }
            }

            // Get table row materials
            $tableRowsIDs = array();
            if ($this->nestedIDs($navigationID, $tableRowsIDs)) {
                // Create field condition
                $localizationFieldCond = new Condition('or');

                // Create localized condition
                if (sizeof($localizedFields)) {
                    $localizedFieldCond = new Condition('and');
                    $localizedFieldCond->add('FieldID', $localizedFields)
                        ->add('locale', locale());
                    // Add this condition to condition group
                    $localizationFieldCond->add($localizedFieldCond);
                }

                // Create not localized condition
                if (sizeof($unlocalizedFields)) {
                    $localizationFieldCond->add('FieldID', $unlocalizedFields);
                }// Create db query
                ;

                // Flip field identifiers as keys
                $tableColumnIds = array_flip($dbTableFieldsIds);
                $resultTable = array_flip($tableRowsIDs);

                /** @var \samson\activerecord\material $dbTableRow Material object (table row) */
                foreach ($query->entity(CMS::MATERIAL_FIELD_RELATION_ENTITY)
                             ->where('MaterialID', $tableRowsIDs)
                             ->whereCondition($localizationFieldCond)->exec() as $mf) {
                    if (!is_array($resultTable[$mf['MaterialID']])) {
                        $resultTable[$mf['MaterialID']] = array();
                    }

                    $resultTable[$mf['MaterialID']][$tableColumnIds[$mf->FieldID]] =
                        !empty($mf->Value) ? $mf->Value : (!empty($mf->numeric_value) ? $mf->numeric_value : $mf->key_value);
                }
            }
        }

        return array_values($resultTable);
    }

    /**
     * Function to retrieve this material table by specified field
     * @param string $tableSelector Selector to identify table structure
     * @param string $selector Database field by which search is performed
     * @param array $tableColumns Columns names list
     * @param string $externalHandler External handler to perform some extra code
     * @param array $params External handler params
     * @return array Collection of collections of table cells, represented as materialfield objects
     * @deprecated Use table()
     */
    public function getTable(
        $tableSelector,
        $selector = 'StructureID',
        &$tableColumns = null,
        $externalHandler = null,
        $params = array()
    ) {
        // Create query
        $query = new dbQuery();

        /** @var array $resultTable Collection of collections of field cells */
        $resultTable = array();
        /** @var array $dbTableFieldsIds Array of table structure column identifiers */
        $dbTableFieldsIds = array();

        // Get structure object if we need to search it by other fields
        if ($selector != 'StructureID') {
            $structure = dbQuery('structure')->cond($selector, $tableSelector)->first();
            $tableSelector = $structure->id;
        }

        /** If this table has columns */
        if (dbQuery('structurefield')
            ->cond("StructureID", $tableSelector)
            ->fields('FieldID', $dbTableFieldsIds)
        ) {
            // Get localized and not localized fields
            $localizedFields = array();
            $unlocalizedFields = array();
            /** @var \samson\cms\CMSField $dbTableField Table column */
            foreach (dbQuery('field')->order_by('priority')->cond('FieldID', $dbTableFieldsIds)->exec() as $field) {
                /** Add table columns names */
                $tableColumns[] = $field->Name;
                if ($field->local == 1) {
                    $localizedFields[] = $field->id;
                } else {
                    $unlocalizedFields[] = $field->id;
                }
            }

            // Query to get table rows(table materials)
            $tableQuery = dbQuery('material')
                ->cond('parent_id', $this->MaterialID)
                ->cond('Active', '1')
                ->join('structurematerial')
                ->cond('structurematerial_StructureID', $tableSelector)
                ->order_by('priority');

            // Call user function if exists
            if (is_callable($externalHandler)) {
                // Give it query as parameter
                call_user_func_array($externalHandler, array_merge(array(&$tableQuery), $params));
            }

            // Get table row materials
            $tableMaterialIds = array();
            if ($tableQuery->fields('MaterialID', $tableMaterialIds)) {
                // Create field condition
                $localizationFieldCond = new Condition('or');

                // Create localized condition
                if (sizeof($localizedFields)) {
                    $localizedFieldCond = new Condition('and');
                    $localizedFieldCond->add('materialfield_FieldID', $localizedFields)
                        ->add('materialfield_locale', locale());
                    // Add this condition to condition group
                    $localizationFieldCond->add($localizedFieldCond);
                }

                // Create not localized condition
                if (sizeof($unlocalizedFields)) {
                    $localizationFieldCond->add('materialfield_FieldID', $unlocalizedFields);
                }

                // Create db query
                $materialFieldQuery = dbQuery('materialfield')
                    ->cond('MaterialID', $tableMaterialIds)
                    ->cond($localizationFieldCond);

                // Flip field identifiers as keys
                $tableColumnIds = array_flip($dbTableFieldsIds);
                $resultTable = array_flip($tableMaterialIds);

                /** @var \samson\activerecord\material $dbTableRow Material object (table row) */
                foreach ($materialFieldQuery->exec() as $mf) {
                    if (!is_array($resultTable[$mf['MaterialID']])) {
                        $resultTable[$mf['MaterialID']] = array();
                    }

                    $resultTable[$mf['MaterialID']][$tableColumnIds[$mf->FieldID]] =
                        !empty($mf->Value) ? $mf->Value : (!empty($mf->numeric_value) ? $mf->numeric_value : $mf->key_value);
                }
            }
        }

        return array_values($resultTable);
    }
}
