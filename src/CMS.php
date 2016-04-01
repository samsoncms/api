<?php
namespace samsoncms\api;

// Backward compatibility
require('generated/Material.php');
require('generated/Field.php');
require('generated/MaterialField.php');
require('generated/Structure.php');
require('generated/StructureField.php');

use samson\activerecord\dbMySQLConnector;
use samson\activerecord\TableRelation;
use samson\core\CompressableService;
use samsoncms\api\generator\GenericWriter;
use samsonframework\core\ResourcesInterface;
use samsonframework\core\SystemInterface;
use samsonphp\generator\Generator;

/**
 * SamsonCMS API
 * @package samsoncms\api
 */
class CMS extends CompressableService
{
    /** Database entity name for relations between material and navigation */
    const MATERIAL_NAVIGATION_RELATION_ENTITY = '\samson\activerecord\structurematerial';
    /** Database entity name for relations between material and images */
    const MATERIAL_IMAGES_RELATION_ENTITY = GalleryField::class;
    /** Database entity name for relations between additional fields and navigation */
    const FIELD_NAVIGATION_RELATION_ENTITY = '\samson\activerecord\structurefield';
    /** Database entity name for relations between material and additional fields values */
    const MATERIAL_FIELD_RELATION_ENTITY = MaterialField::class;
    /** @var string Database table names prefix */
    public $tablePrefix = '';
    /** Identifier */
    protected $id = 'cmsapi2';
    /** @var \samsonframework\orm\DatabaseInterface */
    protected $database;
    /** @var array[string] Collection of generated queries */
    protected $queries;

    /**
     * CMS constructor.
     *
     * @param string $path
     * @param ResourcesInterface $resources
     * @param SystemInterface $system
     */
    public function  __construct($path, ResourcesInterface $resources, SystemInterface $system)
    {
        // TODO: This should changed to normal DI
        $this->database = db();

        parent::__construct($path, $resources, $system);
    }

    /**
     * Module initialization.
     *
     * @param array $params Initialization parameters
     * @return boolean|null Initialization result
     */
    public function init(array $params = array())
    {
        $this->rewriteEntityLocale();
    }

    /**
     * Entity additional fields localization support.
     */
    protected function rewriteEntityLocale()
    {
        // Iterate all generated entity classes
        foreach (get_declared_classes() as $entityClass) {
            if (is_subclass_of($entityClass, '\samsoncms\api\Entity')) {
                // Insert current application locale
                str_replace('@locale', locale(), $entityClass::$_sql_select);
            }
        }
    }

    public function beforeCompress(& $obj = null, array & $code = null)
    {

    }

    public function afterCompress(& $obj = null, array & $code = null)
    {
        // Iterate through generated php code
        $files = array();
        foreach (\samson\core\File::dir($this->cache_path, 'php', '', $files, 1) as $file) {
            // No namespace for global function file
            $ns = strpos($file, 'func') === false ? __NAMESPACE__ : '';

            // Compress generated php code
            $obj->compress_php($file, $this, $code, $ns);
        }
    }

    //[PHPCOMPRESSOR(remove,start)]

    /**
     * @see ModuleConnector::prepare()
     */
    public function prepare()
    {
        // Create cms_version
        $this->database->execute('
CREATE TABLE IF NOT EXISTS `cms_version`  (
  `version` varchar(15) NOT NULL DEFAULT \'30\'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;'
        );

        // Perform this migration and execute only once
        if ($this->migrator() != 40) {
            // Perform SQL table creation
            $path = __DIR__ . '/../sql/';
            foreach (array_slice(scandir($path), 2) as $file) {
                trace('Performing database script [' . $file . ']');
                foreach ($this->readSQL($path . $file, $this->tablePrefix) as $sql) {
                    $this->database->execute($sql);
                }
            }
            $this->migrator(40);
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
        //new TableRelation('related_materials', 'material', 'first_material', TableRelation::T_ONE_TO_MANY, 'MaterialID');
        //new TableRelation('related_materials', 'materialfield', 'first_material', TableRelation::T_ONE_TO_MANY, 'MaterialID');
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

        // TODO: Should be removed
        m('activerecord')->relations();

        $classWriter = new GenericWriter(
            $this->database,
            new Generator(),
            __NAMESPACE__ . '\\generated',
            [
                \samsoncms\api\generator\analyzer\Real::class => [
                    \samsoncms\api\generator\Real::class
                ],
                \samsoncms\api\generator\analyzer\TableTrait::class => [
                    \samsoncms\api\generator\TableTrait::class
                ],
                \samsoncms\api\generator\analyzer\Virtual::class => [
                    \samsoncms\api\generator\Entity::class,
                    \samsoncms\api\generator\Query::class,
                    \samsoncms\api\generator\Collection::class,
                ],
                \samsoncms\api\generator\analyzer\Gallery::class => [
                    \samsoncms\api\generator\Gallery::class,
                ],
                \samsoncms\api\generator\analyzer\Table::class => [
                    \samsoncms\api\generator\TableEntity::class,
                    \samsoncms\api\generator\TableQuery::class,
                    \samsoncms\api\generator\TableCollection::class,
                    \samsoncms\api\generator\Table::class,
                    \samsoncms\api\generator\Row::class
                ]
            ],
            $this->cache_path
        );

        $classWriter->write();

        return parent::prepare();
    }

    /**
     * Handler for CMSAPI database version manipulating
     *
     * @param string $toVersion Version to switch to
     *
     * @return string Current database version
     */
    public function migrator($toVersion = null)
    {
        // If something passed - change database version to it
        if (func_num_args()) {
            // Save current version to special db table
            $this->database->execute(
                "ALTER TABLE  `" . dbMySQLConnector::$prefix . "cms_version`
                CHANGE  `version`  `version` VARCHAR( 15 ) CHARACTER SET utf8
                COLLATE utf8_general_ci NOT NULL DEFAULT  '" . $toVersion . "';"
            );
            die('Database successfully migrated to [' . $toVersion . ']');
        } else { // Return current database version
            $version_row = $this->database->fetch('SHOW COLUMNS FROM `' . dbMySQLConnector::$prefix . 'cms_version`');
            if (isset($version_row[0]['Default'])) {
                return $version_row[0]['Default'];
            } else {
                return 0;
            }
        }
    }

    /**
     * Read SQL file with variables placeholders pasting
     *
     * @param string $filePath SQL file for reading
     * @param string $prefix   Prefix for addition
     *
     * @return array Collection of SQL command texts
     */
    public function readSQL($filePath, $prefix = '')
    {
        $sql = '';

        // Build path to SQL folder
        if (file_exists($filePath)) {
            // Replace prefix
            $sql = str_replace('@prefix', $prefix, file_get_contents($filePath));
        }

        // Split queries
        $sqlCommands = explode(';', str_replace("\n", '', $sql));

        // Always return array
        return array_filter(is_array($sqlCommands) ? $sqlCommands : array($sqlCommands));
    }
    //[PHPCOMPRESSOR(remove,end)]
}
