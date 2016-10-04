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
use samsoncms\api\Field;

/**
 * Additional field to navigation relation query.
 * @package samsoncms\api\query
 */
class FieldNavigation extends Relational
{
    /**
     * FieldNavigation constructor.
     * @param array $filteringIDs Collection of entity identifiers for filtering
     */
    public function __construct($filteringIDs = array())
    {
        parent::__construct(
            $GLOBALS['__core']->getContainer()->get('query'),
            Field::ENTITY,
            Field::F_PRIMARY,
            Navigation::F_PRIMARY,
            CMS::FIELD_NAVIGATION_RELATION_ENTITY,
            $filteringIDs
        );
    }
}
