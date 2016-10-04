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
    public function __construct($filteringIDs = array(), $identifier = Material::class)
    {
        parent::__construct(
            $GLOBALS['__core']->getContainer()->get('query'),
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
     * @param string $locale Locale for requests
     * @return array Collection of entity identifiers filtered by navigation identifier.
     */
    public function idsByRelationID($relationID, $relationValue = null, array $filteringIDs = array(), $locale = null)
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
            if (count($filteringIDs)) {
                $this->query->where($this->primaryField, $filteringIDs);
            }

            // If field is localized
            if ((int)$fieldRecord->local === 1) {
                // Add localization filter
                $this->query->where(\samsoncms\api\MaterialField::F_LOCALE, $locale);
            }

            // Perform database query and get only material identifiers collection
            $return = $this->query->fields($this->primaryField);
        }

        return $return;
    }
}
