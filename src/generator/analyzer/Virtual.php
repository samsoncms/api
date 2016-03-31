<?php
//[PHPCOMPRESSOR(remove,start)]
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 23.03.16 at 11:45
 */
namespace samsoncms\api\generator\analyzer;

use samson\activerecord\dbMySQLConnector;
use samsoncms\api\Field;
use samsoncms\api\generator\exception\ParentEntityNotFound;
use samsoncms\api\Navigation;

/**
 * Generic entities metadata analyzer.
 *
 * @package samsoncms\api\analyzer
 */
class Virtual extends GenericAnalyzer
{
    /** @var string Metadata class */
    protected $metadataClass = \samsoncms\api\generator\metadata\Virtual::class;

    /**
     * Analyze virtual entities and gather their metadata.
     *
     * @return \samsoncms\api\generator\metadata\Virtual[]
     * @throws ParentEntityNotFound
     */
    public function analyze()
    {
        $metadataCollection = [];

        // Iterate all structures, parents first
        foreach ($this->getVirtualEntities() as $structureRow) {
            // Fill in entity metadata
            $metadata = new $this->metadataClass;

            $this->analyzeEntityRecord($metadata, $structureRow);

            // TODO: Add multiple parent and fetching their data in a loop

            // Set pointer to parent entity
            if (null !== $metadata->parentID) {
                if (array_key_exists($metadata->parentID, $metadataCollection)) {
                    $metadata->parent = $metadataCollection[$metadata->parentID];
                    // Add all parent metadata to current object
                    $metadata->defaultValues = $metadata->parent->defaultValues;
                    $metadata->realNames = $metadata->parent->realNames;
                    $metadata->allFieldIDs = $metadata->parent->allFieldIDs;
                    $metadata->allFieldNames = $metadata->parent->allFieldNames;
                    $metadata->allFieldValueColumns = $metadata->parent->allFieldValueColumns;
                    $metadata->allFieldTypes = $metadata->parent->allFieldTypes;
                    $metadata->fieldDescriptions = $metadata->parent->fieldDescriptions;
                    $metadata->localizedFieldIDs = $metadata->parent->localizedFieldIDs;
                    $metadata->notLocalizedFieldIDs = $metadata->parent->notLocalizedFieldIDs;
                } else {
                    throw new ParentEntityNotFound($metadata->parentID);
                }
            }

            // Get old AR collections of metadata
            $metadata->arSelect = \samson\activerecord\material::$_sql_select;
            $metadata->arAttributes = \samson\activerecord\material::$_attributes;
            $metadata->arMap = \samson\activerecord\material::$_map;
            $metadata->arFrom = \samson\activerecord\material::$_sql_from;
            $metadata->arGroup = \samson\activerecord\material::$_own_group;
            $metadata->arRelationAlias = \samson\activerecord\material::$_relation_alias;
            $metadata->arRelationType = \samson\activerecord\material::$_relation_type;
            $metadata->arRelations = \samson\activerecord\material::$_relations;

            // Add SamsonCMS material needed data
            $metadata->arSelect['this'] = ' STRAIGHT_JOIN ' . $metadata->arSelect['this'];
            $metadata->arFrom['this'] .= "\n" .
                'LEFT JOIN ' . dbMySQLConnector::$prefix . 'materialfield as _mf
            ON ' . dbMySQLConnector::$prefix . 'material.MaterialID = _mf.MaterialID';
            $metadata->arGroup[] = dbMySQLConnector::$prefix . 'material.MaterialID';

            // Iterate entity fields
            foreach ($this->getEntityFields($structureRow['StructureID']) as $fieldID => $fieldRow) {
                $this->analyzeFieldRecord($metadata, $fieldID, $fieldRow);

                // Get camelCase and transliterated field name
                $fieldName = $this->fieldName($fieldRow['Name']);

                // Fill localization fields collections
                if ($fieldRow[Field::F_LOCALIZED] == 1) {
                    $metadata->localizedFieldIDs[$fieldID] = $fieldName;
                } else {
                    $metadata->notLocalizedFieldIDs[$fieldID] = $fieldName;
                }

                // Set old AR collections of metadata
                $metadata->arAttributes[$fieldName] = $fieldName;
                $metadata->arMap[$fieldName] = dbMySQLConnector::$prefix . 'material.' . $fieldName;

                // Add additional field column to entity query
                $equal = '((_mf.FieldID = ' . $fieldID . ')&&(_mf.locale ' . ($fieldRow['local'] ? ' = "@locale"' : 'IS NULL') . '))';
                $metadata->arSelect['this'] .= "\n\t\t" . ',MAX(IF(' . $equal . ', _mf.`' . Field::valueColumn($fieldRow['Type']) . '`, NULL)) as `' . $fieldName . '`';
            }

            // Store metadata by entity identifier
            $metadataCollection[$structureRow['StructureID']] = $metadata;
            // Store global collection
            self::$metadata[$structureRow['StructureID']] = $metadata;
        }


        return $metadataCollection;
    }

    /**
     * Get virtual entities from database by their type.
     *
     * @param int $type Virtual entity type
     *
     * @return array Get collection of navigation objects
     */
    protected function getVirtualEntities($type = 0)
    {
        return $this->database->fetch('
        SELECT * FROM `structure`
        WHERE `Active` = "1" AND `Type` = "' . $type . '"
        ORDER BY `ParentID` ASC
        ');
    }

    /**
     * Analyze entity.
     *
     * @param \samsoncms\api\generator\metadata\Virtual $metadata
     * @param array                                     $structureRow Entity database row
     */
    public function analyzeEntityRecord(&$metadata, array $structureRow)
    {
        $metadata->structureRow = $structureRow;

        // Get CapsCase and transliterated entity name
        $metadata->entity = $this->entityName($structureRow[Navigation::F_NAME]);
        $metadata->entityClassName = $this->fullEntityName($metadata->entity);
        $metadata->entityRealName = $structureRow[Navigation::F_NAME];
        $metadata->entityID = $structureRow[Navigation::F_PRIMARY];
        $metadata->type = $structureRow[Navigation::F_TYPE];

        // Try to find entity parent identifier for building future relations
        $metadata->parentID = $this->getParentEntity($structureRow[Navigation::F_PRIMARY]);
    }

    /**
     * Find entity parent identifier.
     *
     * @param int $entityID Entity identifier
     *
     * @return null|int Parent entity identifier
     */
    public function getParentEntity($entityID)
    {
        $parentData = $this->database->fetch('
SELECT *
FROM structure_relation as sm
JOIN structure as s ON s.StructureID = sm.parent_id
WHERE sm.child_id = "' . $entityID . '"
AND s.StructureID != "' . $entityID . '"
');
        // Get parent entity identifier
        return count($parentData) ? $parentData[0]['StructureID'] : null;
    }

    /**
     * Get entity fields.
     *
     * @param int $entityID Entity identifier
     *
     * @return array Collection of entity fields
     */
    protected function getEntityFields($entityID)
    {
        $return = array();
        // TODO: Optimize queries make one single query with only needed data
        foreach ($this->database->fetch('SELECT * FROM `structurefield` WHERE `StructureID` = "' . $entityID . '" AND `Active` = "1"') as $fieldStructureRow) {
            foreach ($this->database->fetch('SELECT * FROM `field` WHERE `FieldID` = "' . $fieldStructureRow['FieldID'] . '"') as $fieldRow) {
                $return[$fieldRow['FieldID']] = $fieldRow;
            }
        }

        return $return;
    }

    /**
     * Virtual entity additional field analyzer.
     *
     * @param \samsoncms\api\generator\metadata\Virtual $metadata Metadata instance for filling
     * @param int                                       $fieldID  Additional field identifier
     * @param array                                     $fieldRow Additional field database row
     */
    public function analyzeFieldRecord(&$metadata, $fieldID, array $fieldRow)
    {
        // Get camelCase and transliterated field name
        $fieldName = $this->fieldName($fieldRow['Name']);

        // TODO: Set default for additional field storing type accordingly.

        // Store field metadata
        $metadata->realNames[$fieldRow[Field::F_IDENTIFIER]] = $fieldName;
        $metadata->allFieldIDs[$fieldID] = $fieldName;
        $metadata->allFieldNames[$fieldName] = $fieldID;
        $metadata->allFieldValueColumns[$fieldID] = Field::valueColumn($fieldRow[Field::F_TYPE]);
        $metadata->allFieldTypes[$fieldID] = Field::phpType($fieldRow[Field::F_TYPE]);
        $metadata->allFieldCmsTypes[$fieldID] = (int)$fieldRow[Field::F_TYPE];
        $metadata->fieldDescriptions[$fieldID] = $fieldRow[Field::F_DESCRIPTION] . ', ' . $fieldRow['Name'] . '#' . $fieldID;
        $metadata->fieldRawDescriptions[$fieldID] = $fieldRow['Description'];
    }

    /**
     * Get child entities by parent identifier.
     *
     * @param int $parentId Parent entity identifier
     *
     * @return array Get collection of child navigation objects
     */
    protected function getChildEntities($parentId)
    {
        return $this->database->fetch('
        SELECT * FROM `structure`
        WHERE `Active` = "1" AND `ParentID` = ' . $parentId . '
        ORDER BY `ParentID` ASC
        ');
    }
}
//[PHPCOMPRESSOR(remove,end)]