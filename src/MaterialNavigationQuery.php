<?php
/**
 * Created by PhpStorm.
 * User: VITALYIEGOROV
 * Date: 08.12.15
 * Time: 23:11
 */
namespace samsoncms\api;

use samson\activerecord\dbQuery;

class MaterialNavigationQuery extends RelationQuery
{
    /** MaterialQuery constructor */
    public function __construct()
    {
        parent::__construct(
            new dbQuery(),
            __NAMESPACE__.'\Material',
            Navigation::$_primary,
            CMS::MATERIAL_NAVIGATION_RELATION_ENTITY
        );
    }
}
