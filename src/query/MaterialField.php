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
use samsoncms\api\CMS;

/**
 * Material to additional fields relation query.
 * @package samsoncms\api
 */
class MaterialField extends Relational
{
    /**
     * MaterialField constructor
     * @param array $filteringIDs Collection of entity identifiers for filtering
     * @param string $identifier Entity identifier
     */
    public function __construct($filteringIDs = array(), $identifier = \samsoncms\api\Material::ENTITY)
    {
        parent::__construct(
            new dbQuery(),
            $identifier,
            Field::$_primary,
            CMS::MATERIAL_FIELD_RELATION_ENTITY,
            $filteringIDs
        );
    }

    /**
     * Get current entity identifiers collection by relation identifier ans its value.
     *
     * @param string $relationID Relation entity identifier
     * @param mixed $relationValue Relation entity value
     * @param array $filteringIDs Collection of entity identifiers for filtering query
     * @return array Collection of entity identifiers filtered by navigation identifier.
     */
    public function idsByRelationID($relationID, $relationValue = null, array $filteringIDs = array())
    {
        $return = array();

        /** @var Field $fieldRecord We need to have field record */
        $fieldRecord = null;
        if (Field::byID($this->query, $relationID, $fieldRecord)) {
            // Get material identifiers by field
            $this->query->entity($this->relationIdentifier)
                ->where(self::DELETE_FLAG_FIELD, 1)
                ->where($this->relationPrimary, $relationID)
                ->where($fieldRecord->valueFieldName(), $relationValue);

            // Add material identifier filter if passed
            if (isset($filteringIDs)) {
                $this->query->where($this->primaryField, $filteringIDs);
            }

            // Perform database query and get only material identifiers collection
            $return = $this->query->fields($this->primaryField);
        }

        return $return;
    }
}
