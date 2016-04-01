<?php
//[PHPCOMPRESSOR(remove,start)]
/**
 * Created by PhpStorm.
 * User: nazarenko
 * Date: 29.03.2016
 * Time: 12:38
 */

namespace samsoncms\api\generator;

use samsoncms\api\generator\metadata\VirtualMetadata;
use samsonphp\generator\Generator;

/**
 * Row class generator.
 *
 * @package samsoncms\api\generator
 * @deprecated
 */
class Row extends VirtualEntity
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

        $this->className = rtrim($this->className, 'Table').'TableRow';
    }

    /**
     * Class definition generation part.
     *
     * @param VirtualMetadata $metadata Entity metadata
     */
    protected function createDefinition($metadata)
    {
        $this->generator
            ->multiComment(array(
                'Class for rendering "' . $metadata->entityRealName . '" row',
                '@deprecated Use ' . $this->className . 'Entity instead'
            ))
            ->defClass($this->className, '\\'.\samsoncms\api\field\Row::class)
            ->newLine('use \\'.\samsoncms\api\Renderable::class.';')
            ->newLine();
    }
}
//[PHPCOMPRESSOR(remove,end)]
