<?php
/**
 * Created by PhpStorm.
 * User: VITALYIEGOROV
 * Date: 08.12.15
 * Time: 23:11
 */
namespace samsoncms\api\query;

use samson\activerecord\dbQuery;
use samsoncms\api\Field;
use samsoncms\api\CMS;
use samsoncms\api\Material;

/**
 * Retrieve additional field values by material.
 * @package samsoncms\api
 */
class MaterialField extends Base
{
    /**
     * MaterialField constructor
     * @param array $filteringIDs Collection of entity identifiers for filtering
     */
    public function __construct($filteringIDs = array())
    {
        parent::__construct(
            new dbQuery(),
            '\samsoncms\api\Field',
            Material::$_primary,
            CMS::MATERIAL_FIELD_RELATION_ENTITY,
            $filteringIDs
        );
    }
}
