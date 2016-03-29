<?php
/**
 * Created by PhpStorm.
 * User: nazarenko
 * Date: 29.03.2016
 * Time: 11:11
 */
namespace samsoncms\api\generator;

use samsoncms\api\generator\metadata\Virtual;
use samsonphp\generator\Generator;

/**
 * Table class generator.
 *
 * @package samsoncms\api\generator
 */
class Table extends Generic
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

        $this->className = rtrim($this->className, 'Table').'Table';
    }

    /**
     * Class uses generation part.
     *
     * @param \samsoncms\api\generator\metadata\Gallery $metadata Entity metadata
     */
    protected function createUses($metadata)
    {
        $this->generator
            ->newLine('use samsonframework\core\ViewInterface;')
            ->newLine('use samsonframework\orm\QueryInterface;')
            ->newLine('use samson\activerecord\dbQuery;')
            ->newLine();
    }

    /**
     * Class definition generation part.
     *
     * @param Virtual $metadata Entity metadata
     */
    protected function createDefinition($metadata)
    {
        $this->generator
            ->multiComment(array(
                'Class for rendering "' . $metadata->entityRealName . '" table',
            ))
            ->defClass($this->className, '\\'.\samsoncms\api\field\Table::class)
            ->newLine('use \\'.\samsoncms\api\Renderable::class.';')
            ->newLine();
    }

    /**
     * Class constants generation part.
     *
     * @param Virtual $metadata Entity metadata
     */
    protected function createConstants($metadata)
    {
        $this->generator
            ->commentVar('string', 'Entity full class name, use ::class instead')
            ->defClassConst('ENTITY', $metadata->entityClassName)
            ->commentVar('string', 'Entity database identifier')
            ->defClassConst('IDENTIFIER', $metadata->entityID)
            ->commentVar('string', 'Not transliterated entity name')
            ->defClassConst('NAME', $metadata->entityRealName);
    }

    /**
     * Class static fields generation part.
     *
     * @param Virtual $metadata Entity metadata
     */
    protected function createStaticFields($metadata)
    {
        $this->generator
            ->commentVar('array', 'Collection of real additional field names')
            ->defClassVar('$fieldsRealNames', 'public static', $metadata->realNames);
    }

    /**
     * Class constructor generation part.
     *
     * @param Virtual $metadata Entity metadata
     */
    protected function createConstructor($metadata)
    {
        $class = "\n\t" . '/**';
        $class .= "\n\t" . ' * @param ViewInterface $renderer Rendering instance';
        $class .= "\n\t" . ' * @param int $materialID material identifier';
        $class .= "\n\t" . ' * @param QueryInterface $query Database query instance';
        $class .= "\n\t" . ' * @param string $locale locale';
        $class .= "\n\t" . ' */';
        $class .= "\n\t" . 'public function __construct(ViewInterface $renderer, $materialID, QueryInterface $query = null, $locale = null)';
        $class .= "\n\t" . '{';
        $class .= "\n\t\t" . '$this->renderer = $renderer;';
        $class .= "\n\t\t" . 'parent::__construct(null !== $query ? $query : new dbQuery(), array('. $metadata->entityID .'), $materialID, $locale);';
        $class .= "\n\t" . '}' . "\n";

        $this->generator->text($class);
    }
}