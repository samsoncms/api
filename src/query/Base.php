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
class Base
{
    /** Deletion flag field name */
    const DELETE_FLAG_FIELD = 'Active';

    /** @var QueryInterface Database query instance */
    protected $query;

    /** @var string Entity identifier */
    protected $identifier;

    /** @var string Entity primary field name */
    protected $primaryField;

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
     */
    public function __construct(QueryInterface $query, $identifier, $relationPrimary, $relationIdentifier)
    {
        $this->query = $query;
        $this->identifier = $identifier;
        $this->primaryField = $identifier::$_primary;
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
    public function idsByRelationID($relationID, $relationValue = null, $filteringIDs = null)
    {
        // Prepare query
        $this->query
            ->entity($this->relationIdentifier)
            ->where($this->relationPrimary, $relationID)
            ->where(self::DELETE_FLAG_FIELD, 1);

        // Add entity identifier filter if passed
        if (isset($filteringIDs)) {
            $this->query->where($this->primaryField, $filteringIDs);
        }

        // Perform database query and get only material identifiers collection
        return $this->query->fields($this->primaryField);
    }

    /**
     * Get current entity instances collection by their identifiers.
     * Method can accept different query executors.
     *
     * @param string|array $entityIDs Entity identifier or their collection
     * @param string $executor Method name for query execution
     * @return mixed[] Collection of entity instances
     */
    public function byIDs($entityIDs, $executor)
    {
        return $this->query
            ->where($this->primaryField, $entityIDs)
            ->where(self::DELETE_FLAG_FIELD, 1)
            ->$executor();
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
        /** @var array $materialIds Collection of entity identifiers filtered by additional field */
        if (sizeof($materialIds = $this->idsByRelationID($relationID, $relationValue))) {
            $return = $this->byIDs($materialIds, $executor);
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
