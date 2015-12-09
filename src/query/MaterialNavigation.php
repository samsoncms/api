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
     * @param string $identifier Entity identifier
     */
    public function __construct($filteringIDs = array(), $identifier = '\samson\cms\CMSMaterial')
    {
        parent::__construct(
            new dbQuery(),
            $identifier,
            Navigation::$_primary,
            CMS::MATERIAL_NAVIGATION_RELATION_ENTITY,
            $filteringIDs
        );
    }
}
