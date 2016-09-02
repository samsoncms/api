<?php
/**
 * Created by PhpStorm.
 * User: VITALYIEGOROV
 * Date: 08.12.15
 * Time: 23:11
 */
namespace samsoncms\api\query;

use samson\activerecord\dbQuery;
use samsoncms\api\CMS;
use samsoncms\api\Material;
use samsoncms\api\Navigation;

/**
 * Material to navigation relation query.
 * @package samsoncms\api
 */
class MaterialNavigation extends Relational
{
    /**
     * MaterialNavigation constructor
     * @param array $filteringIDs Collection of entity identifiers for filtering
     * @param string $identifier Entity identifier
     */
    public function __construct($filteringIDs = array(), $identifier = Material::class)
    {
        parent::__construct(
            $GLOBALS['__core']->getContainer()->getQuery(),
            $identifier,
            Material::F_PRIMARY,
            Navigation::F_PRIMARY,
            CMS::MATERIAL_NAVIGATION_RELATION_ENTITY,
            $filteringIDs
        );
    }
}
