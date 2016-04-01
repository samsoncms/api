<?php
//[PHPCOMPRESSOR(remove,start)]
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 22.03.16 at 17:50
 */
namespace samsoncms\api\generator;

use samsoncms\api\generator\metadata\RealMetadata;
use samsoncms\api\Record;

/**
 * Real database instances class generator.
 *
 * @package samsoncms\api\generator
 */
class Real extends Generic
{
    /**
     * Class definition generation part.
     *
     * @param \samsoncms\api\generator\metadata\GenericMetadata $metadata Entity metadata
     */
    protected function createDefinition($metadata)
    {
        $this->generator
            ->multiComment(array('"' . $metadata->entity . '" database entity class'))
            ->defClass($this->className, '\\' . Record::class);
    }

    /**
     * Class constants generation part.
     *
     * @param RealMetadata $metadata Entity metadata
     */
    protected function createConstants($metadata)
    {
        $this->generator
            ->commentVar('string', 'Entity full class name, use ::class instead')
            ->defClassConst('ENTITY', $metadata->entityClassName)
            ->commentVar('string', 'Entity manager full class name')
            ->defClassConst('MANAGER', $metadata->entityClassName . 'Query');

        // Create all entity fields constants storing each additional field metadata
        foreach ($metadata->fields as $fieldID => $fieldName) {
            $this->generator
                ->commentVar('string', $fieldName . ' entity field')
                ->defClassConst('F_' . $fieldName, $fieldName);
        }
    }

    /**
     * Class fields generation part.
     *
     * @param RealMetadata $metadata Entity metadata
     */
    protected function createFields($metadata)
    {
        foreach ($metadata->fields as $fieldID => $fieldName) {
            $this->generator
                ->commentVar($metadata->types[$fieldID], $fieldName . ' entity field')
                ->defClassVar('$' . $fieldName, 'public');
        }
    }

    /**
     * Class static fields generation part.
     *
     * @param RealMetadata $metadata Entity metadata
     */
    protected function createStaticFields($metadata)
    {
        $this->generator
            ->commentVar('array', 'Collection of all entity fields')
            ->defClassVar('$fieldIDs', 'protected static', $metadata->fields);
    }
}
//[PHPCOMPRESSOR(remove,end)]
