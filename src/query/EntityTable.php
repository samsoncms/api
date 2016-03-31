<?php
/**
 * Created by PhpStorm.
 * User: nazarenko
 * Date: 22.03.2016
 * Time: 11:40
 */
namespace samsoncms\api\query;

use samsonframework\orm\QueryInterface;

/**
 * Class for querying nested entity tables.
 *
 * @package samsoncms\api\query
 */
class EntityTable extends Entity
{
    /** @var int Parent entity identifier */
    protected $parentID;

    /**
     * Generic constructor.
     *
     * @param int $parentID Parent entity identifier
     * @param QueryInterface $query Database query instance
     * @param string $locale Query localization
     */
    public function __construct($parentID, QueryInterface $query = null, $locale = null)
    {
        $this->parentID = $parentID;

        parent::__construct($query, $locale);
    }
}
