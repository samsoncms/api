<?php
//[PHPCOMPRESSOR(remove,start)]
/**
 * Created by PhpStorm.
 * User: nazarenko
 * Date: 29.03.2016
 * Time: 11:21
 */
namespace samsoncms\api\generator\analyzer;

use samsoncms\api\generator\metadata\GenericMetadata;

/**
 * Table trait analyzer.
 *
 * @package samsoncms\api\analyzer
 */
class TableTraitAnalyzer extends GenericAnalyzer
{
    /**
     * @return \samsoncms\api\generator\metadata\GenericMetadata[]
     */
    public function analyze()
    {
        return [new GenericMetadata()];
    }
}
//[PHPCOMPRESSOR(remove,end)]
