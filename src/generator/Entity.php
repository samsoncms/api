<?php
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 22.03.16 at 15:46
 */
namespace samsoncms\api\generator;

use samsoncms\api\generator\exception\ParentEntityNotFound;
use samsonframework\orm\DatabaseInterface;

/**
 * Entity generator.
 *
 * @package samsoncms\api\generator
 */
class Entity extends Generator
{
    /**
     * Generator constructor.
     * @param DatabaseInterface $database Database instance
     * @throws ParentEntityNotFound
     * @throws \samsoncms\api\exception\AdditionalFieldTypeNotFound
     */
    public function __construct(DatabaseInterface $database)
    {
        parent::__construct($database);
        $this->metadata = $this->fillMetadata(Metadata::TYPE_DEFAULT);
    }

    /**
     * Create entity PHP class code.
     *
     * @param Metadata $metadata  Entity metadata
     * @param string   $namespace Namespace of generated class
     * @return string Generated entity query PHP class code
     */
    protected function createEntityClass(Metadata $metadata, $namespace = __NAMESPACE__)
    {
        /**
         * TODO: Parent problem
         * Should be changed to merging fields instead of extending with OOP for structure_relation support
         * or creating traits and using them on shared parent entities.
         */

        $this->generator
            ->multiComment(array('"' . $metadata->entityRealName . '" entity class'))
            ->defClass($metadata->entity, null !== $metadata->parent ? $this->fullEntityName($metadata->parent->entity, $namespace) : 'Entity')
            ->commentVar('string', '@deprecated Entity full class name, use ::class')
            ->defClassConst('ENTITY', $this->fullEntityName($metadata->entity, $namespace))
            ->commentVar('string', 'Entity manager full class name')
            ->defClassConst('MANAGER', $this->fullEntityName($metadata->entity, $namespace) . 'Query')
            ->commentVar('string', 'Entity database identifier')
            ->defClassConst('IDENTIFIER', $metadata->entityID)
            ->commentVar('string', 'Not transliterated entity name')
            ->defClassVar('$viewName', 'protected static', $metadata->entityRealName);

        foreach ($metadata->allFieldIDs as $fieldID => $fieldName) {
            $this->generator
                ->commentVar('string', $metadata->fieldDescriptions[$fieldID] . ' variable name')
                ->defClassConst('F_' . $fieldName, $fieldName)
                ->commentVar('string', $metadata->fieldDescriptions[$fieldID] . ' additional field identifier')
                ->defClassConst('F_' . $fieldName . '_ID', $fieldID)
                ->commentVar($metadata->allFieldTypes[$fieldID], $metadata->fieldDescriptions[$fieldID])
                ->defClassVar('$' . $fieldName, 'public');
        }

        /** Iterate all metadata to find nested structure tables */
        foreach ($this->metadata as $structureID => $tableMetadata) {
            // Check if this is nested table structure metadata
            if ($tableMetadata->parentID === $metadata->entityID && $tableMetadata->type === Metadata::TYPE_TABLE) {
                $this->generator->text($this->generateEntityTableMethod($tableMetadata->entity, $tableMetadata->entityID));
            }
        }

        return $this->generator
            ->commentVar('array', 'Collection of navigation identifiers')
            ->defClassVar('$navigationIDs', 'protected static', array($metadata->entityID))
            ->defClassVar('$_sql_select', 'public static ', $metadata->arSelect)
            ->defClassVar('$_attributes', 'public static ', $metadata->arAttributes)
            ->defClassVar('$_map', 'public static ', $metadata->arMap)
            ->defClassVar('$_sql_from', 'public static ', $metadata->arFrom)
            ->defClassVar('$_own_group', 'public static ', $metadata->arGroup)
            ->defClassVar('$_relation_alias', 'public static ', $metadata->arRelationAlias)
            ->defClassVar('$_relation_type', 'public static ', $metadata->arRelationType)
            ->defClassVar('$_relations', 'public static ', $metadata->arRelations)
            ->defClassVar('$fieldIDs', 'protected static ', $metadata->allFieldIDs)
            ->defClassVar('$fieldValueColumns', 'protected static ', $metadata->allFieldValueColumns)
            ->endClass()
            ->flush();
    }
}
