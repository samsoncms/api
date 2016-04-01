<?php
//[PHPCOMPRESSOR(remove,start)]
namespace samsoncms\api\generator\analyzer;

use samsoncms\api\Field;

/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 23.03.16 at 16:21
 */
class Application extends \samsoncms\api\generator\analyzer\Virtual
{
    /** @var string Metadata class */
    protected $metadataClass = \samsoncms\api\generator\metadata\Application::class;

    /**
     * Analyze entity.
     *
     * @param \samsoncms\api\generator\metadata\Virtual $metadata
     * @param array $structureRow Entity database row
     */
    public function analyzeEntityRecord(&$metadata, array $structureRow)
    {
        $metadata->structureRow = $structureRow;

        // Get CapsCase and transliterated entity name
        $metadata->entity = $this->entityName($structureRow['Name']);
        $metadata->entityClassName = $this->fullEntityName($metadata->entity);
        $metadata->entityRealName = $structureRow['Name'];
        $metadata->entityID = $structureRow['StructureID'];

        // Try to find entity parent identifier for building future relations
        $metadata->parentID = $this->getParentEntity($structureRow['StructureID']);
    }

    /**
     * Virtual entity additional field analyzer.
     *
     * @param \samsoncms\api\generator\metadata\Virtual $metadata Metadata instance for filling
     * @param int      $fieldID Additional field identifier
     * @param array $fieldRow Additional field database row
     */
    public function analyzeFieldRecord(&$metadata, $fieldID, array $fieldRow)
    {
        // Get camelCase and transliterated field name
        $fieldName = $this->fieldName($fieldRow['Name']);

        // TODO: Set default for additional field storing type accordingly.

        // Store field metadata
        $metadata->realNames[$fieldRow['Name']] = $fieldName;
        $metadata->fields[$fieldID] = $fieldName;
        $metadata->fieldNames[$fieldName] = $fieldID;
        $metadata->allFieldValueColumns[$fieldID] = Field::valueColumn($fieldRow[Field::F_TYPE]);
        $metadata->types[$fieldID] = Field::phpType($fieldRow['Type']);
        $metadata->allFieldCmsTypes[$fieldID] = (int)$fieldRow['Type'];
        $metadata->fieldDescriptions[$fieldID] = $fieldRow['Description'] . ', ' . $fieldRow['Name'] . '#' . $fieldID;
        $metadata->fieldRawDescriptions[$fieldID] = $fieldRow['Description'];
    }
}
//[PHPCOMPRESSOR(remove,end)]
