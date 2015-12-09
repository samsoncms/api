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

    /** @var array Collection of entity identifiers for filtering */
    protected $filteringIDs;

    /**
     * Entity constructor.
     * @param QueryInterface $query Database query instance
     * @param string $identifier Entity identifier
     * @param array $filteringIDs Collection of entity identifiers for filtering
     */
    public function __construct(
        QueryInterface $query,
        $identifier,
        $filteringIDs = array()
    ) {
        $this->query = $query;
        $this->identifier = $identifier;
        $this->primaryField = $identifier::$_primary;
        $this->filteringIDs = $filteringIDs;
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
            ->entity($this->identifier)
            ->where($this->primaryField, $entityIDs)
            ->where(self::DELETE_FLAG_FIELD, 1)
            ->$executor();
    }
}
