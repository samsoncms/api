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
     */
    public function __construct($filteringIDs = array())
    {
        parent::__construct(
            new dbQuery(),
            '\samsoncms\api\Material',
            Navigation::$_primary,
            CMS::MATERIAL_NAVIGATION_RELATION_ENTITY,
            $filteringIDs
        );
    }
}
