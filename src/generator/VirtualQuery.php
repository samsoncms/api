<?php
//[PHPCOMPRESSOR(remove,start)]
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 22.03.16 at 15:46
 */
namespace samsoncms\api\generator;

use samsoncms\api\generator\metadata\VirtualMetadata;
use samsoncms\api\query\Entity;
use samsonphp\generator\Generator;

/**
 * Virtual entity query class generator.
 *
 * @package samsoncms\api\generator
 */
class VirtualQuery extends RealQuery
{
    /**
     * Class uses generation part.
     *
     * @param VirtualMetadata $metadata Entity metadata
     */
    protected function createUses($metadata)
    {
        $this->generator
            ->newLine('use samsonframework\orm\QueryInterface;')
            ->newLine('use samsonframework\orm\ArgumentInterface;')
            ->newLine('use samson\activerecord\dbQuery;')
            ->newLine();
    }

    /**
     * Query constructor.
     *
     * @param Generator $generator
     * @param VirtualMetadata $metadata
     */
    public function __construct(Generator $generator, $metadata)
    {
        parent::__construct($generator, $metadata);

        $this->parentClass = '\\'.Entity::class;
        $this->entityClass = '\samsoncms\api\generated\\' . $metadata->entity;
    }

    /**
     * Class static fields generation part.
     *
     * @param VirtualMetadata $metadata Entity metadata
     */
    protected function createStaticFields($metadata)
    {
        $this->generator
            ->commentVar('array', 'Collection of localized additional fields identifiers')
            ->defClassVar('$virtualFieldIDs', 'public static', $metadata->fields)
            ->commentVar('array', 'Collection of additional fields value column names')
            ->defClassVar('$virtualFieldValueColumns', 'public static', $metadata->allFieldValueColumns)
            ->commentVar('array', 'Collection of real additional field names')
            ->defClassVar('$virtualFieldRealNames', 'public static', $metadata->realNames)
            ->commentVar('array', 'Collection of additional field names')
            ->defClassVar('$virtualFieldNames', 'public static', $metadata->fieldNames)
            ->commentVar('array', 'Collection of additional field names')
            ->defClassVar('$virtualRealFieldNames', 'public static', $metadata->fieldNames)

            ->commentVar('array', 'Collection of localized additional fields identifiers')
            ->defClassVar('$fieldIDs', 'public static', $metadata->parent->fields)
            ->commentVar('array', 'Collection of additional field names')
            ->defClassVar('$fieldNames', 'public static', $metadata->parent->fieldNames)

            // TODO: two above fields should be protected
            ->commentVar('array', 'Collection of navigation identifiers')
            ->defClassVar('$navigationIDs', 'public static', array($metadata->entityID))
            ->commentVar('string', 'Entity full class name')
            ->defClassVar('$identifier', 'public static', $this->entityClass)
            ->commentVar('array', 'Collection of localized additional fields identifiers')
            ->defClassVar('$localizedFieldIDs', 'public static', $metadata->localizedFieldIDs)
            ->commentVar('array', 'Collection of NOT localized additional fields identifiers')
            ->defClassVar('$notLocalizedFieldIDs', 'public static', $metadata->notLocalizedFieldIDs)
        ;
    }

    /**
     * Class constructor generation part.
     *
     * @param \samsoncms\api\generator\metadata\VirtualMetadata $metadata Entity metadata
     */
    protected function createConstructor($metadata)
    {
        $class = "\n\t" . '/**';
        $class .= "\n\t" . ' * @param string $locale Localization identifier';
        $class .= "\n\t" . ' * @param QueryInterface $query Database query instance';
        $class .= "\n\t" . ' */';
        $class .= "\n\t" . 'public function __construct($locale = null, QueryInterface $query = null)';
        $class .= "\n\t" . '{';
        $class .= "\n\t\t" . '// TODO: This should be removed!';
        $class .= "\n\t\t" . '$container = $GLOBALS[\'__core\']->getContainer();';
        $class .= "\n\t\t" . 'parent::__construct($query ?? $container->get("query"), $locale);';
        $class .= "\n\t" . '}';

        $this->generator->text($class);
    }
}
//[PHPCOMPRESSOR(remove,end)]
