<?php
//[PHPCOMPRESSOR(remove,start)]
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 22.03.16 at 15:46
 */
namespace samsoncms\api\generator;

use samsoncms\api\generator\metadata\Virtual;
use samsonphp\generator\Generator;

/**
 * Virtual entity query class generator.
 *
 * @package samsoncms\api\generator
 */
class VirtualQuery extends RealQuery
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

        $this->className .= 'Query';
        $this->parentClass = '\\' . \samsoncms\api\query\Entity::class;
        $this->entityClass = '\samsoncms\api\generated\\' . $metadata->entity;
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
            ->defClassVar('$fieldRealNames', 'public static', $metadata->realNames)
            ->commentVar('array', 'Collection of additional field names')
            ->defClassVar('$fieldNames', 'public static', $metadata->fieldNames)
            // TODO: two above fields should be protected
            ->commentVar('array', 'Collection of navigation identifiers')
            ->defClassVar('$navigationIDs', 'protected static', array($metadata->entityID))
            ->commentVar('string', 'Entity full class name')
            ->defClassVar('$identifier', 'protected static', $this->entityClass)
            ->commentVar('array', 'Collection of localized additional fields identifiers')
            ->defClassVar('$localizedFieldIDs', 'protected static', $metadata->localizedFieldIDs)
            ->commentVar('array', 'Collection of NOT localized additional fields identifiers')
            ->defClassVar('$notLocalizedFieldIDs', 'protected static', $metadata->notLocalizedFieldIDs)
            ->commentVar('array', 'Collection of localized additional fields identifiers')
            ->defClassVar('$fieldIDs', 'protected static', $metadata->fields)
            ->commentVar('array', 'Collection of additional fields value column names')
            ->defClassVar('$fieldValueColumns', 'protected static', $metadata->allFieldValueColumns);
    }
}
//[PHPCOMPRESSOR(remove,end)]
