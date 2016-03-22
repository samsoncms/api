<?php
/**
 * Created by PhpStorm.
 * User: nazarenko
 * Date: 22.03.2016
 * Time: 11:40
 */
namespace samsoncms\api\query;

use samsoncms\api\CMS;
use samsoncms\api\Material;
use samsoncms\api\NavigationMaterial;
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

    /** @var array Parent table structure identifiers collection */
    protected $structureID;

    /**
     * Generic constructor.
     *
     * @param array $structureID Parent table structure identifiers collection
     * @param int $parentID Parent entity identifier
     * @param QueryInterface $query Database query instance
     * @param string $locale Query localization
     */
    public function __construct(array $structureID, $parentID, QueryInterface $query = null, $locale = null)
    {
        $this->structureID = $structureID;
        $this->parentID = $parentID;

        parent::__construct($query, $locale);
    }

    /**
     * Prepare entity identifiers.
     *
     * @param array $entityIDs Collection of identifier for filtering
     * @return array Collection of entity identifiers
     */
    protected function findEntityIDs(array $entityIDs = array())
    {
        // Get all entities related to structure identifier
        $allStructureRelatedEntityIDs = $this->query
            ->entity(CMS::MATERIAL_NAVIGATION_RELATION_ENTITY)
            ->where(NavigationMaterial::F_STRUCTUREID, $this->structureID)
            ->where(NavigationMaterial::F_ACTIVE, true);

        // Filter by passed identifiers
        if (count($entityIDs)) {
            $allStructureRelatedEntityIDs->where(NavigationMaterial::F_MATERIALID, $entityIDs);
        }

        $result = array();

        $allStructureRelatedEntityIDs = $allStructureRelatedEntityIDs->fields(NavigationMaterial::F_MATERIALID);
        if (count($allStructureRelatedEntityIDs)) {
            // Retrieve nested entity identifiers
            $result = parent::findEntityIDs($this->query
                ->entity(Material::class)
                ->where(Material::F_PARENT, $this->parentID)
                ->where(Material::F_PRIMARY, $allStructureRelatedEntityIDs)
                ->fields(Material::F_PRIMARY)
            );
        }

        return $result;
    }
}