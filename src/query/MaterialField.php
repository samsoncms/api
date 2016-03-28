<?php
/**
 * Created by PhpStorm.
 * User: VITALYIEGOROV
 * Date: 08.12.15
 * Time: 23:11
 */
namespace samsoncms\api\query;

use samson\activerecord\dbQuery;
use samsoncms\api\Material;
use samsoncms\api\Field;
use samsoncms\api\CMS;
use samsonframework\orm\Condition;
use samsonframework\orm\ArgumentInterface;
use samsonframework\orm\ConditionInterface;

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
    public function __construct($filteringIDs = array(), $identifier = '\samson\cms\CMSMaterial')
    {
        parent::__construct(
            new dbQuery(),
            $identifier,
            Material::F_PRIMARY,
            Field::F_PRIMARY,
            CMS::MATERIAL_FIELD_RELATION_ENTITY,
            $filteringIDs
        );
    }

    /**
     * Get current entity identifiers collection by relation identifier ans its value.
     *
     * @param string $relationID Relation entity identifier
     * @param mixed|Condition $relationValue Relation entity value or relation condition
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
                ->where(\samsoncms\api\MaterialField::F_DELETION, 1)
                ->where($this->relationPrimary, $relationID);

            if ($relationValue instanceof ConditionInterface) {
                $this->query->whereCondition($relationValue);
            } else {
                $this->query->where($fieldRecord->valueFieldName(), $relationValue);
            }

            // Add material identifier filter if passed
            if (sizeof($filteringIDs)) {
                $this->query->where($this->primaryField, $filteringIDs);
            }

            // Perform database query and get only material identifiers collection
            $return = $this->query->fields($this->primaryField);
        }

        return $return;
    }
}
