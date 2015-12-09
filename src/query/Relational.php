<?php
/**
 * Created by PhpStorm.
 * User: VITALYIEGOROV
 * Date: 08.12.15
 * Time: 22:14
 */
namespace samsoncms\api\query;

use samsonframework\orm\QueryInterface;

/**
 * Generic SamsonCMS query for retrieving entities that have relation between each other
 * through other relational entity.
 *
 * @package samsoncms\api
 */
class Relational extends Base
{
    /** @var string Related entity primary field */
    protected $relationPrimary;

    /** @var string Relation entity identifier */
    protected $relationIdentifier;

    /**
     * Entity constructor.
     * @param QueryInterface $query Database query instance
     * @param string $identifier Entity identifier
     * @param string $relationPrimary Relation entity primary field name
     * @param string $relationIdentifier Relation entity identifier
     * @param array $filteringIDs Collection of entity identifiers for filtering
     */
    public function __construct(
        QueryInterface $query,
        $identifier,
        $relationPrimary,
        $relationIdentifier,
        $filteringIDs = array()
    ) {
        parent::__construct($query, $identifier, $filteringIDs);

        $this->relationPrimary = $relationPrimary;
        $this->relationIdentifier = $relationIdentifier;
    }

    /**
     * Get current entity identifiers collection by navigation identifier.
     *
     * @param string $relationID Relation entity identifier
     * @param mixed $relationValue Relation entity value
     * @param array $filteringIDs Collection of entity identifiers for filtering query
     * @return array Collection of entity identifiers filtered by navigation identifier.
     */
    public function idsByRelationID($relationID, $relationValue = null, array $filteringIDs = array())
    {
        // Prepare query
        $this->query
            ->entity($this->relationIdentifier)
            ->where($this->relationPrimary, $relationID)
            ->where(self::DELETE_FLAG_FIELD, 1);

        // Add entity identifier filter if passed
        if (sizeof($filteringIDs)) {
            $this->query->where($this->primaryField, $filteringIDs);
        }

        // Perform database query and get only material identifiers collection
        return $this->query->fields($this->primaryField);
    }

    /**
     * Retrieve entities from database.
     *
     * @param string|array $relationID Relation entity identifier or collection
     * @param mixed $relationValue Relation entity value
     * @param string $executor Query execution function name
     * @return mixed[] Collection of entity instances for this relation identifier
     */
    protected function retrieve($relationID, $relationValue, $executor)
    {
        $return = array();
        /** @var array $ids Collection of entity identifiers filtered by additional field */
        if (sizeof($ids = $this->idsByRelationID($relationID, $relationValue, $this->filteringIDs))) {
            $return = $this->byIDs($ids, $executor);
        }

        return $return;
    }

    /**
     * Get current entity instances collection by navigation identifier.
     *
     * @param string $relationID Relation entity identifier
     * @param mixed $relationValue Relation entity value
     * @return mixed[] Collection of entity instances
     */
    public function byRelationID($relationID, $relationValue = null)
    {
        return $this->retrieve($relationID, $relationValue, 'exec');
    }

    /**
     * Get current entity instances amount by navigation identifier.
     *
     * @param string $relationID Relation entity identifier
     * @param mixed $relationValue Relation entity value
     * @return integer Amount of entities related to Navigation identifier
     */
    public function amountByRelationID($relationID, $relationValue = null)
    {
        return $this->retrieve($relationID, $relationValue, 'count');
    }
}
