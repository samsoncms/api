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
 * Additional field to navigation relation query.
 * @package samsoncms\api\query
 */
class FieldNavigation extends Base
{
    /**
     * FieldNavigation constructor.
     * @param array $filteringIDs Collection of entity identifiers for filtering
     */
    public function __construct($filteringIDs = array())
    {
        parent::__construct(
            new dbQuery(),
            '\samsoncms\api\Field',
            Navigation::$_primary,
            CMS::FIELD_NAVIGATION_RELATION_ENTITY,
            $filteringIDs
        );
    }
}
