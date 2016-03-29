<?php
//[PHPCOMPRESSOR(remove,start)]
/**
 * Created by PhpStorm.
 * User: nazarenko
 * Date: 29.03.2016
 * Time: 11:21
 */
namespace samsoncms\api\generator\analyzer;

/**
 * Table entities metadata analyzer.
 *
 * @package samsoncms\api\analyzer
 */
class Table extends Virtual
{
    /**
     * Get virtual table entities from database by their type.
     *
     * @param int $type Virtual entity type
     *
     * @return array Get collection of navigation objects
     */
    protected function getVirtualEntities($type = 0)
    {
        return parent::getVirtualEntities(2);
    }
}
//[PHPCOMPRESSOR(remove,end)]
