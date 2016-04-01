<?php
//[PHPCOMPRESSOR(remove,start)]
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 22.03.16 at 15:46
 */
namespace samsoncms\api\generator;

use samsoncms\api\generator\metadata\Virtual;

/**
 * Virtual entity class generator.
 *
 * @package samsoncms\api\generator
 */
class VirtualEntity extends Generic
{
    /**
     * Class definition generation part.
     *
     * @param Virtual $metadata Entity metadata
     */
    protected function createDefinition($metadata)
    {
        /**
         * TODO: Parent problem
         * Should be changed to merging fields instead of extending with OOP for structure_relation support
         * or creating traits and using them on shared parent entities.
         */
        $parentClass = null !== $metadata->parent
            ? $metadata->parent->entityClassName
            : '\\'.\samsoncms\api\Entity::class;

        $this->generator
            ->multiComment(array('"' . $metadata->entityRealName . '" database entity class'))
            ->defClass($this->className, $parentClass)
            ->newLine('use TableTrait;')
            ->newLine();;
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
            ->commentVar('string', 'Entity manager full class name')
            ->defClassConst('MANAGER', $metadata->entityClassName . 'Query')
            ->commentVar('string', 'Entity database identifier')
            ->defClassConst('IDENTIFIER', $metadata->entityID)
            ->commentVar('string', 'Not transliterated entity name')
            ->defClassConst('NAME', $metadata->entityRealName);

        // Create all entity fields constants storing each additional field metadata
        foreach ($metadata->fields as $fieldID => $fieldName) {
            $this->generator
                ->commentVar('string', $metadata->fieldDescriptions[$fieldID] . ' variable name')
                ->defClassConst('F_' . $fieldName, $fieldName)
                ->commentVar('string', $metadata->fieldDescriptions[$fieldID] . ' additional field identifier')
                ->defClassConst('F_' . $fieldName . '_ID', $fieldID);
        }
    }

    /**
     * Class fields generation part.
     *
     * @param Virtual $metadata Entity metadata
     */
    protected function createFields($metadata)
    {
        foreach ($metadata->fields as $fieldID => $fieldName) {
            $this->generator
                ->commentVar($metadata->types[$fieldID], $metadata->fieldDescriptions[$fieldID])
                ->defClassVar('$' . $fieldName, 'public');
        }
    }
}
//[PHPCOMPRESSOR(remove,end)]