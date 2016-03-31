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
 * Table collection class generator.
 *
 * @package samsoncms\api\generator
 */
class TableCollection extends Collection
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

        $this->className = rtrim($this->metadata->entity, 'Table') . 'TableCollection';
        $this->parentClass = rtrim($this->metadata->entity, 'Table') . 'TableQuery';
        $this->entityClass = rtrim($this->metadata->entity, 'Table') . 'TableEntity';
    }

    /**
     * Class constructor generation part.
     *
     * @param \samsoncms\api\generator\metadata\Virtual $metadata Entity metadata
     */
    protected function createConstructor($metadata)
    {
        $class = "\n\t" . '/**';
        $class .= "\n\t" . ' * @param ViewInterface $renderer Renderer';
        $class .= "\n\t" . ' * @param int $parentID Parent entity identifier';
        $class .= "\n\t" . ' * @param QueryInterface $query Database query instance';
        $class .= "\n\t" . ' * @param string $locale Localization identifier';
        $class .= "\n\t" . ' */';
        $class .= "\n\t" . 'public function __construct(ViewInterface $renderer, $parentID, QueryInterface $query = null, $locale = null)';
        $class .= "\n\t" . '{';
        $class .= "\n\t\t" . '$this->renderer = $renderer;';

        $class .= "\n\t\t" . 'parent::__construct($parentID, isset($query) ? $query : new dbQuery(), $locale);';
        $class .= "\n\t" . '}';

        $this->generator->text($class);
    }
}

//[PHPCOMPRESSOR(remove,end)]
