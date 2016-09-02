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

/**
 * Generic real table entities metadata analyzer.
 *
 * @package samsoncms\api\analyzer
 */
class RealAnalyzer extends GenericAnalyzer
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
        $metadataCollection = [];

        // Iterate all structures, parents first
        foreach ($this->getEntities() as $columnRow) {
            $table = $columnRow['Table'];

            /** @var RealMetadata $metadata Set pointer to metadata instance by table name */
            $metadata = &$metadataCollection[$table];

            // If this is a new table - create metadata instance
            if (null === $metadata) {
                $metadata = new $this->metadataClass;
                $metadata->entity = $this->entityName($table);
                $metadata->tableName = $table;
                $metadata->entityName = $table;
                $metadata->entityClassName = $this->fullEntityName($metadata->entity);

                // Get old AR collections of metadata
//                $arEntity = '\samson\activerecord\\'.$metadata->entity;
//                if (class_exists($arEntity)) {
//                    foreach ($arEntity::$_attributes as $attribute) {
//                        $metadata->arAttributes[$this->fieldName($attribute)] = $attribute;
//                    }
//                    foreach ($arEntity::$_table_attributes as $attribute) {
//                        $metadata->arTableAttributes[$this->fieldName($attribute)] = $attribute;
//                    }
//                    foreach ($arEntity::$_types as $attribute => $oldType) {
//                        $metadata->arTypes[$attribute] = $oldType;
//                    }
//                    $metadata->arSelect = $arEntity::$_sql_select;
//                    $metadata->arMap = $arEntity::$_map;
//                    $metadata->arFrom = $arEntity::$_sql_from;
//                    $metadata->arGroup = $arEntity::$_own_group;
//                    $metadata->arRelationAlias = $arEntity::$_relation_alias;
//                    $metadata->arRelationType = $arEntity::$_relation_type;
//                    $metadata->arRelations = $arEntity::$_relations;
//                }
            }

            // Generate correct PSR-2 field name
            $fieldName = $this->fieldName($columnRow['Field']);
            if (!in_array($fieldName, $metadata->fields)) {
                $metadata->fieldNames[$fieldName] = $columnRow['Field'];
                $metadata->fields[$columnRow['Field']] = $fieldName;
                $metadata->types[$columnRow['Field']] = $this->databaseTypeToPHP($columnRow['Type']);
                $metadata->internalTypes[$columnRow['Field']] = $columnRow['Type'];
                $metadata->defaults[$columnRow['Field']] = $columnRow['Default'];
                $metadata->nullable[$columnRow['Field']] = $columnRow['Null'];

                // Store entity primary field
                if (strtolower($columnRow['Key']) === 'pri') {
                    $metadata->primaryField = $columnRow['Field'];
                }
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
              WHERE `TABLES`.`TABLE_SCHEMA`="' . $this->database->database() . '"
              AND `COLUMNS`.`TABLE_SCHEMA`="' . $this->database->database() . '"
              AND `TABLES`.`TABLE_NAME` != "cms_version"
         ');
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
        switch (strtoupper($type)) {
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
}
//[PHPCOMPRESSOR(remove,end)]