<?php
namespace samsoncms\api;

use samson\activerecord\dbMySQLConnector;
use samson\activerecord\TableRelation;
use samsoncms\api\generator\GenericWriter;
use samsonframework\core\ResourcesInterface;
use samsonframework\core\SystemInterface;
use samsonframework\core\CompressInterface;
use samsonphp\generator\Generator;
use samson\core\CompressableExternalModule;

/**
 * SamsonCMS API
 * @package samsoncms\api
 */
class CMS extends CompressableExternalModule implements CompressInterface
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
                try {
                    foreach ($this->readSQL($path . $file, $this->tablePrefix) as $sql) {
                        $this->database->execute($sql);
                    }
                } catch(\Exception $e) {
                    throw new \Exception('Canot execute file: "'.$file.'"'."\n".$e->getMessage());
                }
            }
            $this->migrator(40);
        }

        // Initiate migration mechanism
        //$this->database->migration(get_class($this), array($this, 'migrator'));

        $classWriter = new GenericWriter(
            $this->database,
            new Generator(),
            __NAMESPACE__ . '\\generated',
            [
                \samsoncms\api\generator\analyzer\RealAnalyzer::class => [
                    \samsoncms\api\generator\RealEntity::class,
                    \samsoncms\api\generator\RealQuery::class,
                    \samsoncms\api\generator\RealCollection::class,
                ],
                \samsoncms\api\generator\analyzer\TableTraitAnalyzer::class => [
                    \samsoncms\api\generator\TableTrait::class
                ],
                \samsoncms\api\generator\analyzer\VirtualAnalyzer::class => [
                    \samsoncms\api\generator\VirtualEntity::class,
                    \samsoncms\api\generator\VirtualQuery::class,
                    \samsoncms\api\generator\VirtualCollection::class,
                ],
                \samsoncms\api\generator\analyzer\GalleryAnalyzer::class => [
                    \samsoncms\api\generator\Gallery::class,
                ],
                \samsoncms\api\generator\analyzer\TableAnalyzer::class => [
                    \samsoncms\api\generator\TableVirtualEntity::class,
                    \samsoncms\api\generator\TableVirtualQuery::class,
                    \samsoncms\api\generator\TableVirtualCollection::class,
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
