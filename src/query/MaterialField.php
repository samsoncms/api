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

/**
 * Retrieve materials by additional fields.
 * @package samsoncms\api
 */
class MaterialField extends Base
{
    /** MaterialQuery constructor */
    public function __construct()
    {
        parent::__construct(
            new dbQuery(),
            __NAMESPACE__.'\Material',
            Field::$_primary,
            CMS::MATERIAL_FIELD_RELATION_ENTITY
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
    public function idsByRelationID($relationID, $relationValue = null, $filteringIDs = null)
    {
        $return = array();

        /** @var Field $fieldRecord We need to have field record */
        $fieldRecord = null;
        if (Field::byID($this->query, $relationID, $fieldRecord)) {
            // Get material identifiers by field
            $this->query->entity($this->relationIdentifier)
                ->where(self::DELETE_FLAG_FIELD, 1)
                ->where($this->relationIdentifier, $relationID)
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
