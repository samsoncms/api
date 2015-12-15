<?php
/**
 * Created by PhpStorm.
 * User: VITALYIEGOROV
 * Date: 09.12.15
 * Time: 14:34
 */
namespace samsoncms\api;

use samson\activerecord\dbMySQLConnector;
use samson\cms\CMSMaterial;
use samsoncms\api\query\Generic;
use samsonframework\orm\DatabaseInterface;

/**
 * Entity classes generator.
 * @package samsoncms\api
 */
class Generator
{
    /** @var DatabaseInterface */
    protected $database;

    /** @var \samsonphp\generator\Generator */
    protected $generator;

    /**
     * Transliterate string to english.
     *
     * @param string $string Source string
     * @return string Transliterated string
     */
    protected function transliterated($string)
    {
        return str_replace(
            ' ',
            '',
            ucwords(iconv("UTF-8", "UTF-8//IGNORE", strtr($string, array(
                            "'" => "",
                            "`" => "",
                            "-" => " ",
                            "_" => " ",
                            "а" => "a", "А" => "a",
                            "б" => "b", "Б" => "b",
                            "в" => "v", "В" => "v",
                            "г" => "g", "Г" => "g",
                            "д" => "d", "Д" => "d",
                            "е" => "e", "Е" => "e",
                            "ж" => "zh", "Ж" => "zh",
                            "з" => "z", "З" => "z",
                            "и" => "i", "И" => "i",
                            "й" => "y", "Й" => "y",
                            "к" => "k", "К" => "k",
                            "л" => "l", "Л" => "l",
                            "м" => "m", "М" => "m",
                            "н" => "n", "Н" => "n",
                            "о" => "o", "О" => "o",
                            "п" => "p", "П" => "p",
                            "р" => "r", "Р" => "r",
                            "с" => "s", "С" => "s",
                            "т" => "t", "Т" => "t",
                            "у" => "u", "У" => "u",
                            "ф" => "f", "Ф" => "f",
                            "х" => "h", "Х" => "h",
                            "ц" => "c", "Ц" => "c",
                            "ч" => "ch", "Ч" => "ch",
                            "ш" => "sh", "Ш" => "sh",
                            "щ" => "sch", "Щ" => "sch",
                            "ъ" => "", "Ъ" => "",
                            "ы" => "y", "Ы" => "y",
                            "ь" => "", "Ь" => "",
                            "э" => "e", "Э" => "e",
                            "ю" => "yu", "Ю" => "yu",
                            "я" => "ya", "Я" => "ya",
                            "і" => "i", "І" => "i",
                            "ї" => "yi", "Ї" => "yi",
                            "є" => "e", "Є" => "e"
                        )
                    )
                )
            )
        );
    }

    /**
     * Get class constant name by its value.
     *
     * @param string $value Constant value
     * @param string $className Class name
     * @return string Full constant name
     */
    protected function constantNameByValue($value, $className = Field::ENTITY)
    {
        // Get array where class constants are values and their values are keys
        $nameByValue = array_flip((new \ReflectionClass($className))->getConstants());

        // Try to find constant by its value
        if (isset($nameByValue[$value])) {
            // Return constant name
            return $nameByValue[$value];
        }
    }

    /**
     * Get correct entity name.
     *
     * @param string $navigationName Original navigation entity name
     * @return string Correct PHP-supported entity name
     */
    protected function entityName($navigationName)
    {
        return ucfirst($this->transliterated($navigationName));
    }

    /**
     * Get correct full entity name with name space.
     *
     * @param string $navigationName Original navigation entity name
     * @param string $namespace Namespace
     * @return string Correct PHP-supported entity name
     */
    protected function fullEntityName($navigationName, $namespace = __NAMESPACE__)
    {
        return str_replace('\\', '\\\\' , '\\'.$namespace.'\\'.$this->entityName($navigationName));
    }

    /**
     * Get correct field name.
     *
     * @param string $fieldName Original field name
     * @return string Correct PHP-supported field name
     */
    protected function fieldName($fieldName)
    {
        return $fieldName = lcfirst($this->transliterated($fieldName));
    }

    /**
     * Get additional field type in form of Field constant name
     * by database additional field type identifier.
     *
     * @param integer $fieldType Additional field type identifier
     * @return string Additional field type constant
     */
    protected function additionalFieldType($fieldType)
    {
        return 'Field::'.$this->constantNameByValue($fieldType);
    }

    /**
     * Generate Query::where() analog for specific field.
     *
     * @param string $fieldName Field name
     * @param string $fieldId Field primary identifier
     * @param string $fieldType Field PHP type
     * @return string Generated PHP method code
     */
    protected function generateFieldConditionMethod($fieldName, $fieldId, $fieldType)
    {
        $code = "\n\t" . '/**';
        $code .= "\n\t" . ' * Add '.$fieldName.'(#' . $fieldId . ') field query condition.';
        $code .= "\n\t" . ' * @param '.Field::phpType($fieldType).' $value Field value';
        $code .= "\n\t" . ' * @return self Chaining';
        $code .= "\n\t" . ' * @see Generic::where()';
        $code .= "\n\t" . ' */';
        $code .= "\n\t" . 'public function ' . $fieldName . '($value)';
        $code .= "\n\t" . "{";
        $code .= "\n\t\t" . 'return $this->where("'.$fieldName.'", $value);';

        return $code . "\n\t" . "}"."\n";
    }

    /**
     * Create entity PHP class code.
     *
     * @param string $navigationName Original entity name
     * @param string $entityName PHP entity name
     * @param array $navigationFields Collection of entity additional fields
     * @return string Generated entity query PHP class code
     */
    protected function createEntityClass($navigationName, $entityName, $navigationFields)
    {
        $this->generator->multicomment(array('"'.$navigationName.'" entity class'));
        $this->generator->defclass($entityName, 'Entity');

        $this->generator->comment('Entity full class name');
        $this->generator->defvar('const ENTITY', $this->fullEntityName($entityName));

        $this->generator->comment('@var string Not transliterated entity name');
        $this->generator->defvar('protected static $viewName', $navigationName);

        $select = \samson\activerecord\material::$_sql_select;
        $attributes = \samson\activerecord\material::$_attributes;
        $map = \samson\activerecord\material::$_map;
        $from = \samson\activerecord\material::$_sql_from;
        $group = \samson\activerecord\material::$_own_group;

        $select['this'] = ' STRAIGHT_JOIN ' . $select['this'];
        $from['this'] .= "\n" . 'LEFT JOIN ' . dbMySQLConnector::$prefix . 'materialfield as _mf on ' . dbMySQLConnector::$prefix . 'material.MaterialID = _mf.MaterialID';
        $group[] = dbMySQLConnector::$prefix . 'material.MaterialID';

        foreach ($navigationFields as $fieldID => $fieldRow) {
            $fieldName = $this->fieldName($fieldRow['Name']);

            $attributes[$fieldName] = $fieldName;
            $map[$fieldName] = dbMySQLConnector::$prefix . 'material.' . $fieldName;

            $equal = '((_mf.FieldID = ' . $fieldID . ')&&(_mf.locale = \"' . ($fieldRow['local'] ? locale() : "NULL") . '\"))';

            // Save additional field
            $select['this'] .= "\n\t\t".',MAX(IF(' . $equal . ', _mf.`' . Field::valueColumn($fieldRow['Type']) . '`, NULL)) as `' . $fieldName . '`';

            $this->generator->comment(Field::phpType($fieldRow['Type']) . ' '.$fieldRow['Description'].' Field #' . $fieldID . ' variable name');
            $this->generator->defvar('const F_' . strtoupper($fieldName), $fieldName);
            $this->generator->comment(Field::phpType($fieldRow['Type']) . ' '.$fieldRow['Description'].' Field #' . $fieldID);
            $this->generator->defvar('public $'.$fieldName.';');
        }

        $this->generator->defvar('public static $_sql_select', $select);
        $this->generator->defvar('public static $_attributes', $attributes);
        $this->generator->defvar('public static $_map', $map);
        $this->generator->defvar('public static $_sql_from', $from);
        $this->generator->defvar('public static $_own_group', $group);

        $this->generator->endclass();

        return $this->generator->flush();
    }

    /**
     * Generate FieldsTable::values() analog for specific field.
     *
     * @param string $fieldName Field name
     * @param string $fieldId Field primary identifier
     * @param string $fieldType Field PHP type
     * @return string Generated PHP method code
     */
    protected function generateTableFieldMethod($fieldName, $fieldId, $fieldType)
    {
        $code = "\n\t" . '/**';
        $code .= "\n\t" . ' * Get table column '.$fieldName.'(#' . $fieldId . ') values.';
        $code .= "\n\t" . ' * @return array Collection('.Field::phpType($fieldType).') of table column values';
        $code .= "\n\t" . ' */';
        $code .= "\n\t" . 'public function ' . $fieldName . '()';
        $code .= "\n\t" . "{";
        $code .= "\n\t\t" . 'return $this->values('.$fieldId.');';

        return $code . "\n\t" . "}"."\n";
    }

    /**
     * Create fields table PHP class code.
     *
     * @param integer $navigationID Entity navigation identifier
     * @param string $navigationName Original entity name
     * @param string $entityName PHP entity name
     * @param array $navigationFields Collection of entity additional fields
     * @return string Generated entity query PHP class code
     */
    protected function createTableClass($navigationID, $navigationName, $entityName, $navigationFields)
    {
        $class = "\n";
        $class .= "\n" . '/**';
        $class .= "\n" . ' * Class for getting "'.$navigationName.'" fields table';
        $class .= "\n" . ' */';
        $class .= "\n" . 'class ' . $entityName . ' extends FieldsTable';
        $class .= "\n" . '{';

        // Iterate additional fields
        $constants = '';
        $variables = '';
        $methods = '';
        foreach ($navigationFields as $fieldID => $fieldRow) {
            $fieldName = $this->fieldName($fieldRow['Name']);

            $methods .= $this->generateTableFieldMethod(
                $fieldName,
                $fieldRow[Field::F_PRIMARY],
                $fieldRow[Field::F_TYPE]
            );
            $constants .= "\n\t" . '/** ' . Field::phpType($fieldRow['Type']) . ' '.$fieldRow['Description'].' Field #' . $fieldID . ' variable name */';
            $constants .= "\n\t" . 'const F_' . strtoupper($fieldName) . ' = "'.$fieldName.'";';

            $variables .= "\n\t" . '/** @var array Collection of '.$fieldRow['Description'].' Field #' . $fieldID . ' values */';
            $variables .= "\n\t" . 'protected $' . $fieldName . ';';
        }

        $class .= $constants;
        $class .= "\n\t";
        $class .= "\n\t" . '/** @var array Collection of navigation identifiers */';
        $class .= "\n\t" . 'protected static $navigationIDs = array(' . $navigationID . ');';
        $class .= "\n\t";
        $class .= $variables;
        $class .= "\n\t";
        $class .= $methods;
        $class .= "\n\t".'/**';
        $class .= "\n\t".' * @param QueryInterface $query Database query instance';
        $class .= "\n\t".' * @param integer $entityID Entity identifier to whom this table belongs';
        $class .= "\n\t".' * @param string $locale Localization identifier';
        $class .= "\n\t".' */';
        $class .= "\n\t".'public function __construct(QueryInterface $query, $entityID, $locale = "")';
        $class .= "\n\t".'{';
        $class .= "\n\t\t".'parent::__construct($query, static::$navigationIDs, $entityID, $locale);';
        $class .= "\n\t".'}';
        $class .= "\n" . '}';

        return $class;
    }

    /**
     * Create entity query PHP class code.
     *
     * @param integer $navigationID Entity navigation identifier
     * @param string $navigationName Original entity name
     * @param string $entityName PHP entity name
     * @param array $navigationFields Collection of entity additional fields
     * @return string Generated entity query PHP class code
     */
    protected function createQueryClass($navigationID, $navigationName, $entityName, $navigationFields)
    {
        $class = "\n";
        $class .= "\n" . '/**';
        $class .= "\n" . ' * Class for getting "'.$navigationName.'" instances from database';
        $class .= "\n" . ' * @method '.$this->entityName($navigationName).'[] find() Get entities collection';
        $class .= "\n" . ' * @method '.$this->entityName($navigationName).' first() Get entity';
        $class .= "\n" . ' * @method '.$entityName.' where($fieldName, $fieldValue = null, $fieldRelation = ArgumentInterface::EQUAL)';
        $class .= "\n" . ' * @method '.$entityName.' primary($value) Query for chaining';
        $class .= "\n" . ' * @method '.$entityName.' identifier($value) Query for chaining';
        $class .= "\n" . ' * @method '.$entityName.' created($value) Query for chaining';
        $class .= "\n" . ' * @method '.$entityName.' modified($value) Query for chaining';
        $class .= "\n" . ' * @method '.$entityName.' published($value) Query for chaining';
        $class .= "\n" . ' */';
        $class .= "\n" . 'class ' . $entityName . ' extends \samsoncms\api\query\Entity';
        $class .= "\n" . '{';

        // Iterate additional fields
        $localizedFieldIDs = array();
        $notLocalizedFieldIDs = array();
        $allFieldIDs = array();
        $allFieldNames = array();
        $allFieldValueColumns = array();
        foreach ($navigationFields as $fieldID => $fieldRow) {
            $fieldName = $this->fieldName($fieldRow['Name']);

            // TODO: Add different method generation depending on their field type
            $class .= $this->generateFieldConditionMethod(
                $fieldName,
                $fieldRow[Field::F_PRIMARY],
                $fieldRow[Field::F_TYPE]
            );

            // Store field metadata
            $allFieldIDs[] = '"' . $fieldID . '" => "' . $fieldName . '"';
            $allFieldNames[] = '"' . $fieldName . '" => "' . $fieldID . '"';
            $allFieldValueColumns[] = '"' . $fieldID . '" => "' . Field::valueColumn($fieldRow[Field::F_TYPE]) . '"';
            if ($fieldRow[Field::F_LOCALIZED] == 1) {
                $localizedFieldIDs[] = '"' . $fieldID . '" => "' . $fieldName . '"';
            } else {
                $notLocalizedFieldIDs[] = '"' . $fieldID . '" => "' . $fieldName . '"';
            }
        }

        $class .= "\n\t";
        $class .= "\n\t" . '/** @var string Not transliterated entity name */';
        $class .= "\n\t" . 'protected static $identifier = "'.$this->fullEntityName($navigationName).'";';
        $class .= "\n\t" . '/** @var array Collection of navigation identifiers */';
        $class .= "\n\t" . 'protected static $navigationIDs = array(' . $navigationID . ');';
        $class .= "\n\t" . '/** @var array Collection of localized additional fields identifiers */';
        $class .= "\n\t" . 'protected static $localizedFieldIDs = array(' . "\n\t\t". implode(','."\n\t\t", $localizedFieldIDs) . "\n\t".');';
        $class .= "\n\t" . '/** @var array Collection of NOT localized additional fields identifiers */';
        $class .= "\n\t" . 'protected static $notLocalizedFieldIDs = array(' . "\n\t\t". implode(','."\n\t\t", $notLocalizedFieldIDs) . "\n\t".');';
        $class .= "\n\t" . '/** @var array Collection of all additional fields identifiers */';
        $class .= "\n\t" . 'protected static $fieldIDs = array(' . "\n\t\t". implode(','."\n\t\t", $allFieldIDs) . "\n\t".');';
        $class .= "\n\t" . '/** @var array Collection of additional fields value column names */';
        $class .= "\n\t" . 'protected static $fieldValueColumns = array(' . "\n\t\t". implode(','."\n\t\t", $allFieldValueColumns) . "\n\t".');';
        $class .= "\n\t" . '/** @var array Collection of additional field names */';
        $class .= "\n\t" . 'public static $fieldNames = array(' . "\n\t\t". implode(','."\n\t\t", $allFieldNames) . "\n\t".');';
        $class .= "\n" . '}';

        // Replace tabs with spaces
        return $class;
    }

    /** @return string Entity state hash */
    public function entityHash()
    {
        // Получим информацию о всех таблицах из БД
        return md5(serialize($this->database->fetch(
            'SELECT `TABLES`.`TABLE_NAME` as `TABLE_NAME`
              FROM `information_schema`.`TABLES` as `TABLES`
              WHERE `TABLES`.`TABLE_SCHEMA`="' . $this->database->database() . '";'
        )));
    }

    /** @return array Get collection of navigation objects */
    protected function entityNavigations($type = 0)
    {
        return $this->database->fetch('
        SELECT * FROM `structure`
        WHERE `Active` = "1" AND `Type` = "'.$type.'"'
        );
    }

    /** @return array Collection of navigation additional fields */
    protected function navigationFields($navigationID)
    {
        $return = array();
        // TODO: Optimize queries make one single query with only needed data
        foreach ($this->database->fetch('SELECT * FROM `structurefield` WHERE `StructureID` = "' . $navigationID . '" AND `Active` = "1"') as $fieldStructureRow) {
            foreach ($this->database->fetch('SELECT * FROM `field` WHERE `FieldID` = "' . $fieldStructureRow['FieldID'] . '"') as $fieldRow) {
                $return[$fieldRow['FieldID']] = $fieldRow;
            }
        }

        return $return;
    }

    /**
     * Generate entity classes.
     *
     * @param string $namespace Base namespace for generated classes
     * @return string Generated PHP code for entity classes
     */
    public function createEntityClasses($namespace = __NAMESPACE__)
    {
        $classes = "\n" . 'namespace ' . $namespace . ';';
        $classes .= "\n";
        $classes .= "\n" . 'use '.$namespace.'\Field;';
        $classes .= "\n" . 'use '.$namespace.'\query\EntityQuery;';
        $classes .= "\n" . 'use '.$namespace.'\FieldsTable;';
        $classes .= "\n" . 'use \samsonframework\orm\ArgumentInterface;';
        $classes .= "\n" . 'use \samsonframework\orm\QueryInterface;';

        // Iterate all structures
        foreach ($this->entityNavigations() as $structureRow) {
            $navigationFields = $this->navigationFields($structureRow['StructureID']);
            $entityName = $this->entityName($structureRow['Name']);

            $classes .= $this->createEntityClass(
                $structureRow['Name'],
                $entityName,
                $navigationFields
            );

            $classes .= $this->createQueryClass(
                $structureRow['StructureID'],
                $structureRow['Name'],
                $entityName.'Query',
                $navigationFields
            );
        }

        // Iterate table structures
        foreach ($this->entityNavigations(2) as $structureRow) {
            $navigationFields = $this->navigationFields($structureRow['StructureID']);
            $entityName = $this->entityName($structureRow['Name']);

            $classes .= $this->createTableClass(
                $structureRow['StructureID'],
                $structureRow['Name'],
                $entityName.'Table',
                $navigationFields
            );

        }

        // Make correct code formatting
        return str_replace("\t", '    ', $classes);
    }

    /**
     * Generator constructor.
     * @param DatabaseInterface $database Database instance
     */
    public function __construct(DatabaseInterface $database)
    {
        $this->generator = new \samsonphp\generator\Generator(__NAMESPACE__);
        $this->database = $database;
    }
}
