<?php
/**
 * Created by PhpStorm.
 * User: nazarenko
 * Date: 22.03.2016
 * Time: 11:40
 */
namespace samsoncms\api\query;

use samsoncms\api\Material;
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

    /**
     * Prepare entity identifiers.
     *
     * @param array $entityIDs Collection of identifier for filtering
     *
     * @return array Collection of entity identifiers
     */
    protected function findEntityIDs(array $entityIDs = array())
    {
        // Get parent logic
        $entityIDs = parent::findEntityIDs($entityIDs);

        // Filter entity identifiers by parent
        return $this->query->entity(Material::class)
            ->where(Material::F_PRIMARY, $entityIDs)
            ->where(Material::F_PARENT, $this->parentID)
            ->fields(Material::F_PRIMARY);
    }
}
