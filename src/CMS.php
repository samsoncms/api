<?php
namespace samsoncms\api;

use samson\activerecord\dbRelation;
use samson\activerecord\materialfield;
use samson\activerecord\structurefield;
use samson\activerecord\structurematerial;
use samson\activerecord\TableRelation;
use samson\core\CompressableService;
use samson\activerecord\dbRecord;
use samson\activerecord\dbMySQLConnector;

/**
 * SamsonCMS API
 * @package samsoncms\api
 */
class CMS extends CompressableService
{
    /** Database entity name for relations between material and navigation */
    const MATERIAL_NAVIGATION_RELATION_ENTITY = '\samson\activerecord\structurematerial';
    /** Database entity name for relations between additional fields and navigation */
    const FIELD_NAVIGATION_RELATION_ENTITY = '\samson\activerecord\structurefield';
    /** Database entity name for relations between material and additional fields values */
    const MATERIAL_FIELD_RELATION_ENTITY = '\samson\activerecord\materialfield';

    /** Identifier */
    protected $id = 'cmsapi2';

    /** @var \samsonframework\orm\DatabaseInterface */
    protected $database;

    /** @var string Database table names prefix */
    public $tablePrefix = '';

    /**
     * CMS constructor.
     * @param null|string $path
     * @param null|string $vid
     * @param mixed|null $resources
     */
    public function __construct($path, $vid, $resources)
    {
        // TODO: This should changed to normal DI
        $this->database = db();

        parent::__construct($path, $vid, $resources);
    }

    //[PHPCOMPRESSOR(remove,start)]
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
        $path = __DIR__.'/../sql/';
        foreach (array_slice(scandir($path), 2) as $file) {
            $this->database->query($this->readSQL($path.$file, $this->tablePrefix));
        }

        // Initiate migration mechanism
        $this->database->migration(get_class($this), array($this, 'migrator'));

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

        return parent::prepare();
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
            if (isset($version_row[0]['Default'])) {
                return $version_row[0]['Default'];
            } else {
                return false;
            }
        }
    }
    //[PHPCOMPRESSOR(remove,end)]
}
