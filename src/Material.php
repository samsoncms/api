<?php
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>
 * on 07.08.14 at 17:11
 */
namespace samsoncms\api;

use samson\activerecord\dbQuery;
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
    /** Store entity name */
    const ENTITY = __CLASS__;

    /** Entity field names constants for using in code */
    const F_PRIMARY = 'MaterialID';
    const F_IDENTIFIER = 'Url';
    const F_DELETION = 'Active';
    const F_PUBLISHED = 'Published';
    const F_PARENT = 'parent_id';
    const F_PRIORITY = 'priority';
    const F_CREATED = 'Created';
    const F_MODIFIED = 'Modyfied';

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

    /** @var integer Primary field */
    public $MaterialID;

    /** @var string Unique identifier */
    public $Url;

    /** @var bool Internal existence flag */
    public $Active;

    /** @var bool Published flag */
    public $Published;

    /** @var integer Parent material identifier */
    public $parent_id;

    /** @var integer Priority inside material relation */
    public $priority;

    /**
     * Set additional material field value by field identifier
     * @param string $fieldID Field identifier
     * @param string $value Value to be stored
     * @param string $locale Locale identifier
     */
    public function setFieldByID($fieldID, $value, $locale = null)
    {
        /** @var QueryInterface $query This should be removed to use $this->database*/
        $query = new dbQuery();

        /** @var Field $fieldRecord Try to find this additional field */
        $fieldRecord = null;
        if (Field::byID($query, $fieldID, $fieldRecord)) {
            /** @var MaterialField $materialFieldRecord Try to find additional field value */
            $materialFieldRecord = null;
            if (!MaterialField::byFieldIDAndMaterialID($query, $this->id, $fieldRecord->id, $materialFieldRecord, $locale)) {
                // Create new additional field value record if it does not exists
                $materialFieldRecord = new MaterialField();
                $materialFieldRecord->FieldID = $fieldRecord->id;
                $materialFieldRecord->MaterialID = $this->id;
                $materialFieldRecord->Active = 1;

                // Add locale if field needs it
                if ($fieldRecord->localized()) {
                    $materialFieldRecord->locale = $locale;
                }
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
        // TODO: this is absurd as we do not have any additional values here
        /** @var Field $field */
        $field = null;
        if (Field::byID(new Query(Field::ENTITY, $this->database), $fieldID, $fieldID)) {
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
}
