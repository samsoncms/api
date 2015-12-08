<?php
/**
 * Created by PhpStorm.
 * User: VITALYIEGOROV
 * Date: 08.12.15
 * Time: 22:14
 */
namespace samsoncms\api;

use samsonframework\orm\QueryInterface;

/**
 * Generic SamsonCMS entity which has Navigation relation.
 * @package samsoncms\api
 */
class Entity
{
    /** Deletion flag field name */
    const DELETE_FLAG_FIELD = 'Active';

    /** @var QueryInterface Database query instance */
    protected $query;

    /** @var string Entity identifier */
    protected $identifier;

    /** @var string Entity primary field name */
    protected $primaryField;

    /**
     * Entity constructor.
     * @param QueryInterface $query Database query instance
     * @param string $identifier Entity identifier
     */
    public function __construct(QueryInterface $query, $identifier)
    {
        $this->query = $query;
        $this->identifier = $identifier;
        $this->primaryField = $identifier::$_primary;
    }

    /**
     * Get current entity identifiers collection by navigation identifier.
     *
     * @param string $navigationID Navigation identifier
     * @param array $filteringIDs Collection of entity identifiers for filtering query
     * @return array Collection of entity identifiers filtered by navigation identifier.
     */
    public function idsByNavigationID($navigationID, $filteringIDs = null)
    {
        // Prepare query
        $this->query->where(Navigation::$_primary, $navigationID)->where(self::DELETE_FLAG_FIELD, 1);

        // Add material identifier filter if passed
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
     * @return self[] Collection of entity instances
     */
    public function byIDs($entityIDs, $executor = 'exec')
    {
        return $this->query
            ->where($this->primaryField, $entityIDs)
            ->where(self::DELETE_FLAG_FIELD, 1)
            ->$executor();
    }

    /**
     * Get current entity instances collection by navigation identifier.
     *
     * @param string $navigationID Navigation identifier
     * @return self[] Collection of entity instances
     */
    public function byNavigationID($navigationID)
    {
        $return = array();
        /** @var array $materialIds Collection of entity identifiers filtered by additional field */
        if (sizeof($materialIds = $this->idsByNavigationID($navigationID))) {
            $return = $this->byIDs($materialIds);
        }

        return $return;
    }

    /**
     * Get current entity instances amount by navigation identifier.
     *
     * @param string $navigationID Navigation identifier
     * @return integer Amount of entities related to Navigation identifier
     */
    public function amountByNavigationID($navigationID)
    {
        $return =0;
        /** @var array $materialIds Collection of entity identifiers filtered by additional field */
        if (sizeof($materialIds = $this->idsByNavigationID($navigationID))) {
            $return = $this->byIDs($materialIds, 'count');
        }

        return $return;
    }
}
