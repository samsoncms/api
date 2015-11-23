<?php
namespace samsoncms\api;

use samson\activerecord\dbRelation;
use samson\activerecord\field;
use samson\activerecord\materialfield;
use samson\activerecord\structure;
use samson\activerecord\structurefield;
use samson\activerecord\structurematerial;
use samson\activerecord\TableRelation;
use samson\activerecord\material;
use samson\core\CompressableService;
use samson\activerecord\dbRecord;
use samson\activerecord\dbMySQLConnector;

/**
 * SamsonCMS API
 * @package samsoncms\api
 */
class CMS extends CompressableService
{
    /** Identifier */
    protected $id = 'cmsapi';

    /** @var string Database table names prefix */
    public $tablePrefix = '';

    /**
     * Read SQL file with variables placeholders pasting
     * @param string $filePath SQL file for reading
     * @param string $prefix Prefix for addition
     * @return string SQL command text
     */
    public function readSQL($filePath, $prefix = '')
    {
        $sql = '';

        // Build path to SQL folder
        if (file_exists($filePath)) {
            // Replace prefix
            $sql = str_replace('@prefix', $prefix, file_get_contents($filePath));
        }

        return $sql;
    }

    /**
     * @see ModuleConnector::prepare()
     */
    public function prepare()
    {
        // Perform SQL table creation
        foreach (scandir(__DIR__.'/../sql/') as $file) {
            db()->query($this->readSQL($file));
        }

        // Initiate migration mechanism
        db()->migration(get_class($this), array($this, 'migrator'));

        // Define permanent table relations
        new TableRelation('material', 'user', 'UserID', 0, 'user_id');
        new TableRelation('material', 'gallery', 'MaterialID', TableRelation::T_ONE_TO_MANY);
        new TableRelation('material', 'materialfield', 'MaterialID', TableRelation::T_ONE_TO_MANY);
        new TableRelation('material', 'field', 'materialfield.FieldID', TableRelation::T_ONE_TO_MANY);
        new TableRelation('material', 'structurematerial', 'MaterialID', TableRelation::T_ONE_TO_MANY);
        new TableRelation('material', 'structure', 'structurematerial.StructureID', TableRelation::T_ONE_TO_MANY);
        new TableRelation('materialfield', 'field', 'FieldID');
        new TableRelation('materialfield', 'material', 'MaterialID');
        new TableRelation('structurematerial', 'structure', 'StructureID');
        new TableRelation('structurematerial', 'materialfield', 'MaterialID', TableRelation::T_ONE_TO_MANY);
        new TableRelation('structurematerial', 'material', 'MaterialID', TableRelation::T_ONE_TO_MANY);
        new TableRelation('structure', 'material', 'structurematerial.MaterialID', TableRelation::T_ONE_TO_MANY, null, 'manymaterials');
        new TableRelation('structure', 'gallery', 'structurematerial.MaterialID', TableRelation::T_ONE_TO_MANY, null, 'manymaterials');
        /*new TableRelation( 'structure', 'material', 'MaterialID' );*/
        new TableRelation('structure', 'user', 'UserID', 0, 'user_id');
        new TableRelation('structure', 'materialfield', 'material.MaterialID', TableRelation::T_ONE_TO_MANY, 'MaterialID', '_mf');
        new TableRelation('structure', 'structurematerial', 'StructureID', TableRelation::T_ONE_TO_MANY);
        new TableRelation('related_materials', 'material', 'first_material', TableRelation::T_ONE_TO_MANY, 'MaterialID');
        new TableRelation('related_materials', 'materialfield', 'first_material', TableRelation::T_ONE_TO_MANY, 'MaterialID');
        new TableRelation('field', 'structurefield', 'FieldID');
        new TableRelation('field', 'structure', 'structurefield.StructureID');
        new TableRelation('structurefield', 'field', 'FieldID');
        new TableRelation('structurefield', 'materialfield', 'FieldID');
        new TableRelation('structurefield', 'material', 'materialfield.MaterialID');
        new TableRelation('structure', 'structure_relation', 'StructureID', TableRelation::T_ONE_TO_MANY, 'parent_id', 'children_relations');
        new TableRelation('structure', 'structure', 'children_relations.child_id', TableRelation::T_ONE_TO_MANY, 'StructureID', 'children');
        new TableRelation('structure', 'structure_relation', 'StructureID', TableRelation::T_ONE_TO_MANY, 'child_id', 'parents_relations');
        new TableRelation('structure', 'structure', 'parents_relations.parent_id', TableRelation::T_ONE_TO_MANY, 'StructureID', 'parents');
        new TableRelation('structurematerial', 'structure_relation', 'StructureID', TableRelation::T_ONE_TO_MANY, 'parent_id');
        new TableRelation('groupright', 'right', 'RightID', TableRelation::T_ONE_TO_MANY);
        //elapsed('CMS:prepare');

        // Все прошло успешно
        return true && parent::prepare();
    }

    /**
     * Handler for CMSAPI database version manipulating
     * @param string $to_version Version to switch to
     * @return string Current database version
     */
    public function migrator($to_version = null)
    {
        // If something passed - change database version to it
        if (func_num_args()) {
            // Save current version to special db table
            db()->query("ALTER TABLE  `" . dbMySQLConnector::$prefix . "cms_version` CHANGE  `version`  `version` VARCHAR( 15 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  '" . $to_version . "';");
            die('Database successfully migrated to [' . $to_version . ']');
        } else { // Return current database version
            $version_row = db()->fetch('SHOW COLUMNS FROM `' . dbMySQLConnector::$prefix . 'cms_version`');
            return $version_row[0]['Default'];
        }
    }

    /** @see \samson\core\CompressableExternalModule::afterCompress() */
    public function afterCompress(& $obj = null, array & $code = null)
    {
        // Fill additional fields data to material db request data for automatic altering material request
        self::$fields = array();

        $t_name = '_mf';

        // Save original material attributes
        self::$materialAttributes = &Material::$_attributes;

        // Copy original material table attributes
        Material::$_attributes = \samson\activerecord\material::$_attributes;
        Material::$_sql_select = \samson\activerecord\material::$_sql_select;
        Material::$_sql_from = \samson\activerecord\material::$_sql_from;
        Material::$_own_group = \samson\activerecord\material::$_own_group;
        Material::$_map = \samson\activerecord\material::$_map;

        // Perform db query to get all possible material fields
        if (dbQuery('field')->Active(1)->Name('', dbRelation::NOT_EQUAL)->exec($this->material_fields)) foreach ($this->material_fields as $db_field) {
            // Add additional field localization condition
            if ($db_field->local == 1) $equal = '((' . $t_name . '.FieldID = ' . $db_field->id . ')&&(' . $t_name . ".locale = '" . locale() . "'))";
            else $equal = '((' . $t_name . '.FieldID = ' . $db_field->id . ')&&(' . $t_name . ".locale = ''))";

            // Define field value DB column for storing data
            $v_col = 'Value';
            // We must get data from other column for this type of field
            if ($db_field->Type == 7 || $db_field->Type == 3 || $db_field->Type == 10) {
                $v_col = 'numeric_value';
            } else if ($db_field->Type == 6) {
                $v_col = 'key_value';
            }

            // Save additional field
            self::$fields[$db_field->Name] = "\n" . ' MAX(IF(' . $equal . ',' . $t_name . '.`' . $v_col . '`, NULL)) as `' . $db_field->Name . '`';

            // Set additional object metadata
            Material::$_attributes[$db_field->Name] = $db_field->Name;
            Material::$_map[$db_field->Name] = dbMySQLConnector::$prefix . 'material.' . $db_field->Name;
        }

        // Set additional object metadata
        Material::$_sql_select['this'] = ' STRAIGHT_JOIN ' . Material::$_sql_select['this'];
        if (sizeof(self::$fields)) {
            Material::$_sql_select['this'] .= ',' . implode(',', self::$fields);
        }
        Material::$_sql_from['this'] .= "\n" . 'LEFT JOIN ' . dbMySQLConnector::$prefix . 'materialfield as ' . $t_name . ' on ' . dbMySQLConnector::$prefix . 'material.MaterialID = ' . $t_name . '.MaterialID';
        Material::$_own_group[] = dbMySQLConnector::$prefix . 'material.MaterialID';
    }

    /** @see \samson\core\ExternalModule::init() */
    public function init(array $params = array())
    {
        // Change static class data
        $this->afterCompress();
    }
}
