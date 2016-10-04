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
class RealEntity extends Generic
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
            ->defClass($this->className, '\\' . \samsonframework\orm\Record::class);
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
            ->defClassConst('MANAGER', $metadata->entityClassName . 'Query')
            // FIXME: Only related to cms api tables
            ->commentVar('string', 'Primary field name')
            ->defClassConst('F_PRIMARY', $metadata->primaryField)
            ->commentVar('string', 'Deletion field name')
            ->defClassConst('F_DELETION', 'Active')
        ;

        // Create all entity fields constants storing each additional field metadata
        foreach ($metadata->fields as $fieldID => $fieldName) {
            $this->generator
                ->commentVar('string', $fieldName . ' entity field name')
                ->defClassConst('F_' . $fieldName, $fieldName);
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
            ->commentVar('array', '@deprecated Old ActiveRecord data')
            ->defClassVar('$_sql_select', 'public static ', $metadata->arSelect)
            ->commentVar('array', '@deprecated Old ActiveRecord data')
            ->defClassVar('$_attributes', 'public static ', $metadata->arAttributes)
            ->commentVar('array', '@deprecated Old ActiveRecord data')
            ->defClassVar('$_types', 'public static ', $metadata->arTypes)
            ->commentVar('array', '@deprecated Old ActiveRecord data')
            ->defClassVar('$_table_attributes', 'public static ', $metadata->arTableAttributes)
            ->commentVar('array', '@deprecated Old ActiveRecord data')
            ->defClassVar('$_map', 'public static ', $metadata->arMap)
            ->commentVar('array', '@deprecated Old ActiveRecord data')
            ->defClassVar('$_sql_from', 'public static ', $metadata->arFrom)
            ->commentVar('array', '@deprecated Old ActiveRecord data')
            ->defClassVar('$_own_group', 'public static ', $metadata->arGroup)
            ->commentVar('array', '@deprecated Old ActiveRecord data')
            ->defClassVar('$_relation_alias', 'public static ', $metadata->arRelationAlias)
            ->commentVar('array', '@deprecated Old ActiveRecord data')
            ->defClassVar('$_relation_type', 'public static ', $metadata->arRelationType)
            ->commentVar('array', '@deprecated Old ActiveRecord data')
            ->defClassVar('$_relations', 'public static ', $metadata->arRelations)
            ->commentVar('array', '@deprecated Old ActiveRecord data')
            ->defClassVar('$fieldIDs', 'public static ', $metadata->fields);
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
}
//[PHPCOMPRESSOR(remove,end)]
