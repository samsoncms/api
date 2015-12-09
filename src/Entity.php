<?php
/**
 * Created by PhpStorm.
 * User: VITALYIEGOROV
 * Date: 09.12.15
 * Time: 12:10
 */
namespace samsoncms\api;

use samsoncms\api\query\FieldNavigation;
use samsonframework\orm\QueryInterface;

/**
 * SamsonCMS Entity that has relation to specific navigation
 * and though has additional fields.
 *
 * @package samsoncms\api
 */
class Entity extends Material
{
    /** @var QueryInterface Database query instance */
    protected $query;

    /** @var array Collection of additional fields identifiers */
    protected static $fieldIDs;

    /** @var array Collection of navigation identifiers */
    protected static $navigationIDs;

    public static function byIDs(QueryInterface $query, $ids)
    {
        // Get additional fields records for passed materials and this entity fields
        foreach (MaterialField::byFieldIDAndMaterialID($query, self::$fieldIDs, $ids) as $additionalField) {

        }
    }
}
