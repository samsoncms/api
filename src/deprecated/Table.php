<?php
//[PHPCOMPRESSOR(remove,start)]
/**
 * Created by PhpStorm.
 * User: nazarenko
 * Date: 29.03.2016
 * Time: 11:11
 */
namespace samsoncms\api\generator;

use samsoncms\api\generator\metadata\VirtualMetadata;
use samsonphp\generator\Generator;

/**
 * Table class generator.
 *
 * @package samsoncms\api\generator
 * @deprecated
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

        $this->className = preg_replace('/Table$/i', '', $this->className) . 'Table';
    }

    /**
     * Class uses generation part.
     *
     * @param \samsoncms\api\generator\metadata\GalleryMetadata $metadata Entity metadata
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
     * @param VirtualMetadata $metadata Entity metadata
     */
    protected function createDefinition($metadata)
    {
        $this->generator
            ->multiComment(array(
                'Class for rendering "' . $metadata->entityRealName . '" table',
                '@deprecated Use ' . $this->className . 'Collection instead'
            ))
            ->defClass($this->className, '\\'.\samsoncms\api\field\Table::class)
            ->newLine('use \\'.\samsoncms\api\Renderable::class.';')
            ->newLine();
    }

    /**
     * Class constants generation part.
     *
     * @param VirtualMetadata $metadata Entity metadata
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
     * @param VirtualMetadata $metadata Entity metadata
     */
    protected function createStaticFields($metadata)
    {
        $this->generator
            ->commentVar('array', 'Collection of real additional field names')
            ->defClassVar('$fieldsRealNames', 'public static', $metadata->realNames);
    }

    /**
     * Class methods generation part.
     *
     * @param VirtualMetadata $metadata Entity metadata
     */
    protected function createMethods($metadata)
    {
        $methods = [];
        // TODO: Add different method generation depending on their field type
        // Generate Query::where() analog for specific field.
        foreach ($metadata->fields as $fieldID => $fieldName) {
            $code = "\n\t" . '/**';
            $code .= "\n\t" . ' * Get collection of ' . $fieldName . '(#' . $fieldID . ') table column values.';
            $code .= "\n\t" . ' * @see \samsoncms\api\field\Table::values($fieldID)';
            $code .= "\n\t" . ' * @param string $relation Field to value condition relation';
            $code .= "\n\t" . ' *';
            $code .= "\n\t" . ' * @return ' . $metadata->types[$fieldID] . '[] ' . $fieldName . ' values collection';
            $code .= "\n\t" . ' */';
            $code .= "\n\t" . 'public function ' . $fieldName . '()';
            $code .= "\n\t" . '{';
            $code .= "\n\t\t" . 'return $this->values(' . $fieldID . ');';
            $code .= "\n\t" . '}';

            $methods[] = $code;
        }

        // Add method text to generator
        $this->generator->text(implode("\n", $methods));
    }

    /**
     * Class constructor generation part.
     *
     * @param VirtualMetadata $metadata Entity metadata
     */
    protected function createConstructor($metadata)
    {
        $class = "\n\t" . '/**';
        $class .= "\n\t" . ' * @param ViewInterface $renderer Rendering instance';
        $class .= "\n\t" . ' * @param int $materialID material identifier';
        $class .= "\n\t" . ' * @param QueryInterface $query Database query instance';
        $class .= "\n\t" . ' * @param string $locale locale';
        $class .= "\n\t" . ' * @deprecated Use ' . $this->className . 'Collection instead';
        $class .= "\n\t" . ' */';
        $class .= "\n\t" . 'public function __construct(ViewInterface $renderer, $materialID, QueryInterface $query = null, $locale = null)';
        $class .= "\n\t" . '{';
        $class .= "\n\t\t" . '$this->renderer = $renderer;';
        $class .= "\n\t\t" . 'parent::__construct(null !== $query ? $query : new dbQuery(), array('. $metadata->entityID .'), $materialID, $locale);';
        $class .= "\n\t" . '}' . "\n";

        $this->generator->text($class);
    }
}
//[PHPCOMPRESSOR(remove,end)]
