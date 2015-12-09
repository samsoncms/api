<?php
/**
 * Created by PhpStorm.
 * User: VITALYIEGOROV
 * Date: 08.12.15
 * Time: 23:11
 */
namespace samsoncms\api\query;

use samson\activerecord\dbQuery;

/**
 * Material with additional fields query.
 * @package samsoncms\api
 */
class Material extends Base
{
    /**
     * MaterialField constructor
     * @param array $filteringIDs Collection of entity identifiers for filtering
     * @param string $identifier Entity identifier
     */
    public function __construct($filteringIDs = array(), $identifier = \samsoncms\api\Material::ENTITY)
    {
        parent::__construct(
            new dbQuery(),
            $identifier,
            $filteringIDs
        );
    }
}
