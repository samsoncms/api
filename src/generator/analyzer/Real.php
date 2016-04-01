<?php
//[PHPCOMPRESSOR(remove,start)]
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 23.03.16 at 11:45
 */
namespace samsoncms\api\generator\analyzer;

use samsoncms\api\Field;
use samsoncms\api\generator\exception\ParentEntityNotFound;
use samsoncms\api\generator\metadata\RealMetadata;
use samsoncms\api\Navigation;

/**
 * Generic real table entities metadata analyzer.
 *
 * @package samsoncms\api\analyzer
 */
class Real extends GenericAnalyzer
{
    /** @var string Metadata class */
    protected $metadataClass = RealMetadata::class;

    /**
     * Analyze virtual entities and gather their metadata.
     *
     * @return RealMetadata[] Collection of filled metadata
     * @throws ParentEntityNotFound
     */
    public function analyze()
    {
        /** @var RealMetadata[] $metadataCollection Set pointer to global metadata collection */
        $metadataCollection = &self::$metadata;

        // Iterate all structures, parents first
        foreach ($this->getEntities() as $columnRow) {
            $table = $columnRow['Table'];

            /** @var RealMetadata $metadata Set pointer to metadata instance by table name */
            $metadata = &$metadataCollection[$table];

            // If this is a new table - create metadata instance
            if (null === $metadata) {
                $metadata = new $this->metadataClass;
                $metadata->entity = $this->entityName($table);
                $metadata->entityClassName = $this->fullEntityName($metadata->entity);
            }

            // Generate correct PSR-2 field name
            $fieldName = $this->fieldName($columnRow['Field']);
            if (!in_array($fieldName, $metadata->fields)) {
                $metadata->fieldNames[$fieldName] = $columnRow['Field'];
                $metadata->fields[$columnRow['Field']] = $fieldName;
                $metadata->types[$columnRow['Field']] = $this->databaseTypeToPHP($columnRow['Type']);
                $metadata->internalTypes[$columnRow['Field']] = $columnRow['Type'];
                $metadata->defaults[$columnRow['Field']] = $columnRow['Default'];
            }
        }

        return $metadataCollection;
    }

    /**
     * Get real entities from database.
     *
     * @return array Get collection of database entities metadata
     */
    public function getEntities()
    {
        // Get tables data
        return $this->database->fetch(
            'SELECT
              `TABLES`.`TABLE_NAME` as `Table`,
              `COLUMNS`.`COLUMN_NAME` as `Field`,
              `COLUMNS`.`DATA_TYPE` as `Type`,
              `COLUMNS`.`IS_NULLABLE` as `Null`,
              `COLUMNS`.`COLUMN_KEY` as `Key`,
              `COLUMNS`.`COLUMN_DEFAULT` as `Default`,
              `COLUMNS`.`EXTRA` as `Extra`
              FROM `information_schema`.`TABLES` as `TABLES`
              LEFT JOIN `information_schema`.`COLUMNS` as `COLUMNS`
              ON `TABLES`.`TABLE_NAME`=`COLUMNS`.`TABLE_NAME`
              WHERE `TABLES`.`TABLE_SCHEMA`="' . $this->database->database() . '" AND `COLUMNS`.`TABLE_SCHEMA`="' . $this->database->database() . '"'
        );
    }

    /**
     * Get PHP data type from database column type.
     *
     * @param string $type Database column type
     *
     * @return string PHP data type
     */
    protected function databaseTypeToPHP($type)
    {
        switch ($type) {
            case 'DECIMAL':
            case 'TINY':
            case 'TINYINT':
            case 'BIT':
            case 'INT':
            case 'SMALLINT':
            case 'MEDIUMINT':
            case 'INTEGER':
            case 'BIGINT':
            case 'SHORT':
            case 'LONG':
            case 'LONGLONG':
            case 'INT24':
                return 'int';
            case 'FLOAT':
                return 'float';
            case 'DOUBLE':
            case 'DOUBLE PRECISION':
                return 'double';
            case 'DATETIME':
            case 'DATE':
            case 'TIMESTAMP':
                return 'int';
            case 'BOOL':
            case 'BOOLEAN':
                return 'bool';
            case 'CHAR':
            case 'VARCHAR':
            case 'TEXT':
                return 'string';
            default:
                return 'mixed';
        }
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
        $metadata->type = (int)$structureRow[Navigation::F_TYPE];

        // Try to find entity parent identifier for building future relations
        $metadata->parentID = $this->getParentEntity($structureRow[Navigation::F_PRIMARY]);
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
        $fieldName = $this->fieldName($fieldRow[Field::F_IDENTIFIER]);

        // TODO: Set default for additional field storing type accordingly.

        // Store field metadata
        $metadata->realNames[$fieldRow[Field::F_IDENTIFIER]] = $fieldName;
        $metadata->allFieldIDs[$fieldID] = $fieldName;
        $metadata->allFieldNames[$fieldName] = $fieldID;
        $metadata->allFieldValueColumns[$fieldID] = Field::valueColumn($fieldRow[Field::F_TYPE]);
        $metadata->allFieldTypes[$fieldID] = Field::phpType($fieldRow[Field::F_TYPE]);
        $metadata->allFieldCmsTypes[$fieldID] = (int)$fieldRow[Field::F_TYPE];
        $metadata->fieldDescriptions[$fieldID] = $fieldRow[Field::F_DESCRIPTION] . ', ' . $fieldRow[Field::F_IDENTIFIER] . '#' . $fieldID;
        $metadata->fieldRawDescriptions[$fieldID] = $fieldRow[Field::F_DESCRIPTION];
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
}
//[PHPCOMPRESSOR(remove,end)]