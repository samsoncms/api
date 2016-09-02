<?php
//[PHPCOMPRESSOR(remove,start)]
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 22.03.16 at 15:46
 */
namespace samsoncms\api\generator;

use samsoncms\api\generator\metadata\VirtualMetadata;
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
            ->newLine('use samsonframework\orm\Query;')
            ->newLine();
    }

    /**
     * Query constructor.
     *
     * @param Generator $generator
     * @param           $metadata
     */
    public function __construct(Generator $generator, $metadata)
    {
        parent::__construct($generator, $metadata);

        $this->parentClass = '\\' . \samsoncms\api\query\Entity::class;
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
            ->commentVar('array', 'Collection of real additional field names')
            ->defClassVar('$fieldRealNames', 'public static', $metadata->realNames)
            ->commentVar('array', 'Collection of navigation identifiers')
            ->defClassVar('$navigationIDs', 'protected static', array($metadata->entityID))
            ->commentVar('array', 'Collection of localized additional fields identifiers')
            ->defClassVar('$localizedFieldIDs', 'protected static', $metadata->localizedFieldIDs)
            ->commentVar('array', 'Collection of NOT localized additional fields identifiers')
            ->defClassVar('$notLocalizedFieldIDs', 'protected static', $metadata->notLocalizedFieldIDs)
            ->commentVar('array', 'Collection of additional fields value column names')
            ->defClassVar('$fieldValueColumns', 'protected static', $metadata->allFieldValueColumns);

        parent::createStaticFields($metadata);
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
        $class .= "\n\t\t" . 'parent::__construct($query ?? $container->getQuery(), $locale);';
        $class .= "\n\t" . '}';

        $this->generator->text($class);
    }
}
//[PHPCOMPRESSOR(remove,end)]
