<?php
//[PHPCOMPRESSOR(remove,start)]
/**
 * Created by PhpStorm.
 * User: nazarenko
 * Date: 29.03.2016
 * Time: 11:11
 */
namespace samsoncms\api\generator;

use samsonphp\generator\Generator;

/**
 * Table entity class generator.
 *
 * @package samsoncms\api\generator
 */
class TableVirtualEntity extends VirtualEntity
{
    /**
     * Query constructor.
     *
     * @param Generator $generator
     * @param           $metadata
     */
    public function __construct(Generator $generator, $metadata)
    {
        parent::__construct($generator, $metadata);

        $this->className = preg_replace('/Table$/i', '', $this->className) . 'TableEntity';
    }
}
//[PHPCOMPRESSOR(remove,end)]
