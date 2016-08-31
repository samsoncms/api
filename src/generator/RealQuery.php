<?php
//[PHPCOMPRESSOR(remove,start)]
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 22.03.16 at 15:46
 */
namespace samsoncms\api\generator;

use samsoncms\api\generator\metadata\RealMetadata;
use samsonphp\generator\Generator;

/**
 * Real entity query class generator.
 *
 * @package samsoncms\api\generator
 */
class RealQuery extends Generic
{
    /** @var string Query returned entity class name */
    protected $entityClass;

    /**
     * Query constructor.
     *
     * @param Generator $generator
     * @param           $metadata
     */
    public function __construct(Generator $generator, $metadata)
    {
        parent::__construct($generator, $metadata);

        $this->className = $metadata->entity.'Query';
        $this->parentClass = '\\' . \samsoncms\api\query\Record::class;
        $this->entityClass = '\samsoncms\api\generated\\' . $metadata->entity;
    }

    /**
     * Class uses generation part.
     *
     * @param RealMetadata $metadata Entity metadata
     */
    protected function createUses($metadata)
    {
        $this->generator
            ->newLine('use samsonframework\orm\ArgumentInterface;')
            ->newLine();
    }

    /**
     * Class definition generation part.
     *
     * @param RealMetadata $metadata Entity metadata
     */
    protected function createDefinition($metadata)
    {
        $this->generator
            ->multiComment(array(
                'Class for querying and fetching "' . $metadata->entity . '" instances from database',
                '@method ' . $this->entityClass . ' first();',
                '@method ' . $this->entityClass . '[] find();',
            ))
            ->defClass($this->className, $this->parentClass);
    }

    /**
     * Class static fields generation part.
     *
     * @param RealMetadata $metadata Entity metadata
     */
    protected function createStaticFields($metadata)
    {
        $this->generator
            ->commentVar('string', 'Entity table primary field name')
            ->defClassVar('$primaryFieldName', 'public static', $metadata->primaryField)
            ->commentVar('string', 'Entity full class name')
            ->defClassVar('$identifier', 'public static', $this->entityClass)
            ->commentVar('string', 'Entity table name')
            ->defClassVar('$tableName', 'public static', $metadata->entity)
            ->commentVar('array', 'Collection of entity field types')
            ->defClassVar('$fieldTypes', 'public static', $metadata->types)
            ->commentVar('array', 'Collection of entity field names to field aliases')
            ->defClassVar('$fieldIDs', 'public static', $metadata->fields)
            ->commentVar('array', 'Collection of entity field aliases to field names')
            ->defClassVar('$fieldNames', 'public static', $metadata->fieldNames);
    }

    /**
     * Class methods generation part.
     *
     * @param RealMetadata $metadata Entity metadata
     */
    protected function createMethods($metadata)
    {
        $methods = [];
        // TODO: Add different method generation depending on their field type
        // Generate Query::where() analog for specific field.
        foreach ($metadata->fields as $fieldID => $fieldName) {
            $code = "\n\t" . '/**';
            $code .= "\n\t" . ' * Add ' . $fieldName . '(#' . $fieldID . ') field query condition.';
            $code .= "\n\t" . ' * @see Generic::where()';
            $code .= "\n\t" . ' * @param ' . $metadata->types[$fieldID] . ' $value Field value';
            $code .= "\n\t" . ' * @param string $relation Field to value condition relation';
            $code .= "\n\t" . ' *';
            $code .= "\n\t" . ' * @return $this Chaining';
            $code .= "\n\t" . ' */';
            $code .= "\n\t" . 'public function ' . $fieldName . '($value, $relation = ArgumentInterface::EQUAL)';
            $code .= "\n\t" . '{';
            $code .= "\n\t\t" . 'return $this->where(' . $this->entityClass.'::F_'.strtoupper($fieldName). ', $value, $relation);';

            $code .= "\n\t" . '}';

            $methods[] = $code;
        }

        // Add method text to generator
        $this->generator->text(implode("\n", $methods));
    }
}
//[PHPCOMPRESSOR(remove,end)]
