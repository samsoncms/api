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
class Gallery extends Virtual
{
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
            $metadata = new \samsoncms\api\generator\metadata\Gallery();

            // Iterate entity fields
            foreach ($this->getEntityFields($structureRow[Navigation::F_PRIMARY]) as $fieldID => $fieldRow) {
                // We need only gallery fields
                if ((int)$fieldRow[Field::F_TYPE] === Field::TYPE_GALLERY) {
                    // Get camelCase and transliterated field name
                    $metadata->entity = $this->fieldName($fieldRow[Field::F_IDENTIFIER]);

                    $metadata->entityClassName = $this->fullEntityName($metadata->entity);
                    $metadata->entityRealName = $structureRow[Navigation::F_NAME];
                    $metadata->entityID = $fieldRow[Field::F_TYPE];

                    // Try to find entity parent identifier for building future relations
                    $metadata->parentID = $structureRow[Navigation::F_PRIMARY];

                }

                // TODO: Set default for additional field storing type accordingly.

                // Store field metadata
                $metadata->realNames[$fieldRow['Name']] = $fieldName;
                $metadata->allFieldIDs[$fieldID] = $fieldName;
                $metadata->allFieldNames[$fieldName] = $fieldID;
                $metadata->allFieldValueColumns[$fieldID] = Field::valueColumn($fieldRow[Field::F_TYPE]);
                $metadata->allFieldTypes[$fieldID] = Field::phpType($fieldRow['Type']);
                $metadata->allFieldCmsTypes[$fieldID] = (int)$fieldRow['Type'];
                $metadata->fieldDescriptions[$fieldID] = $fieldRow['Description'] . ', ' . $fieldRow['Name'] . '#' . $fieldID;
                $metadata->fieldRawDescriptions[$fieldID] = $fieldRow['Description'];

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
        }


        return $metadataCollection;
    }
}
//[PHPCOMPRESSOR(remove,end)]