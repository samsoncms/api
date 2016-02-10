<?php
//[PHPCOMPRESSOR(remove,start)]
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
        return ucfirst($this->getValidName($this->transliterated($navigationName)));
    }
	
    /**
     * Remove all wrong characters from entity name
     *
     * @param string $navigationName Original navigation entity name
     * @return string Correct PHP-supported entity name
     */
    protected function getValidName($navigationName)
    {
        return preg_replace('/(^\d*)|([^\w\d_])/', '', $navigationName);
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
     * Generate Query::where() analog for specific field.
     *
     * @param string $fieldName Field name
     * @param string $fieldId Field primary identifier
     * @param string $fieldType Field PHP type
     * @return string Generated PHP method code
     */
    protected function generateLocalizedFieldConditionMethod($fieldName, $fieldId, $fieldType)
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
        $this->generator
            ->multicomment(array('"'.$navigationName.'" entity class'))
            ->defClass($entityName, 'Entity')
            ->commentVar('string', 'Entity full class name')
            ->defClassConst('ENTITY', $this->fullEntityName($entityName))
            ->commentVar('string', 'Entity manager full class name')
            ->defClassConst('MANAGER', $this->fullEntityName($entityName.'Query'))
            ->commentVar('string', 'Not transliterated entity name')
            ->defClassVar('$viewName', 'protected static');

        // Get old AR collections of metadata
        $select = \samson\activerecord\material::$_sql_select;
        $attributes = \samson\activerecord\material::$_attributes;
        $map = \samson\activerecord\material::$_map;
        $from = \samson\activerecord\material::$_sql_from;
        $group = \samson\activerecord\material::$_own_group;
        $relationAlias = \samson\activerecord\material::$_relation_alias;
        $relationType = \samson\activerecord\material::$_relation_type;
        $relations = \samson\activerecord\material::$_relations;


        // Add SamsonCMS material needed data
        $select['this'] = ' STRAIGHT_JOIN ' . $select['this'];
        $from['this'] .= "\n" . 'LEFT JOIN ' . dbMySQLConnector::$prefix . 'materialfield as _mf on ' . dbMySQLConnector::$prefix . 'material.MaterialID = _mf.MaterialID';
        $group[] = dbMySQLConnector::$prefix . 'material.MaterialID';

        foreach ($navigationFields as $fieldID => $fieldRow) {
            $fieldName = $this->fieldName($fieldRow['Name']);

            $attributes[$fieldName] = $fieldName;
            $map[$fieldName] = dbMySQLConnector::$prefix . 'material.' . $fieldName;

            $equal = '((_mf.FieldID = ' . $fieldID . ')&&(_mf.locale ' . ($fieldRow['local'] ? ' = "@locale"' : 'IS NULL') . '))';

            // Save additional field
            $select['this'] .= "\n\t\t" . ',MAX(IF(' . $equal . ', _mf.`' . Field::valueColumn($fieldRow['Type']) . '`, NULL)) as `' . $fieldName . '`';

            $this->generator
                ->commentVar('string', $fieldRow['Description'] . ' Field #' . $fieldID . ' variable name')
                ->defClassConst('F_' . $fieldName, $fieldName)
                ->commentVar(Field::phpType($fieldRow['Type']), $fieldRow['Description'] . ' Field #' . $fieldID)
                ->defClassVar('$' . $fieldName, 'public');
        }

        return $this->generator
            ->defClassVar('$_sql_select', 'public static ', $select)
            ->defClassVar('$_attributes', 'public static ', $attributes)
            ->defClassVar('$_map', 'public static ', $map)
            ->defClassVar('$_sql_from', 'public static ', $from)
            ->defClassVar('$_own_group', 'public static ', $group)
            ->defClassVar('$_relation_alias', 'public static ', $relationAlias)
            ->defClassVar('$_relation_type', 'public static ', $relationType)
            ->defClassVar('$_relations', 'public static ', $relations)
            ->endclass()
            ->flush();
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
     * Create fields table row PHP class code.
     *
     * @param string $navigationName Original entity name
     * @param string $entityName PHP entity name
     * @param array $navigationFields Collection of entity additional fields
     * @return string Generated entity query PHP class code
     */
    protected function createTableRowClass($navigationName, $entityName, $navigationFields)
    {
        $class = "\n";
        $class .= "\n" . '/**';
        $class .= "\n" . ' * Class for getting "'.$navigationName.'" fields table rows';
        $class .= "\n" . ' */';
        $class .= "\n" . 'class ' . $entityName . ' extends Row';
        $class .= "\n" . '{';

        // Iterate additional fields
        $constants = '';
        $variables = '';
        foreach ($navigationFields as $fieldID => $fieldRow) {
            $fieldName = $this->fieldName($fieldRow['Name']);

            $constants .= "\n\t" . '/** ' . Field::phpType($fieldRow['Type']) . ' '.$fieldRow['Description'].' Field #' . $fieldID . ' variable name */';
            // Store original field name
            $constants .= "\n\t" . 'const F_' . strtoupper($fieldName) . ' = "'.$fieldName.'";';

            $variables .= "\n\t" . '/** ' . Field::phpType($fieldRow['Type']) . ' '.$fieldRow['Description'].' Field #' . $fieldID . ' row value */';
            $variables .= "\n\t" . 'public $' . $fieldName . ';';
        }

        $class .= $constants;
        $class .= "\n\t";
        $class .= $variables;
        $class .= "\n" . '}';

        return $class;
    }

    /**
     * Create fields table PHP class code.
     *
     * @param integer $navigationID     Entity navigation identifier
     * @param string  $navigationName   Original entity name
     * @param string  $entityName       PHP entity name
     * @param array   $navigationFields Collection of entity additional fields
     * @param string  $rowClassName Row class name
     *
     * @return string Generated entity query PHP class code
     * @throws exception\AdditionalFieldTypeNotFound
     */
    protected function createTableClass($navigationID, $navigationName, $entityName, $navigationFields, $rowClassName)
    {
        $this->generator
            ->multiComment(array('Class for getting "'.$navigationName.'" fields table'))
            ->defClass($entityName, 'FieldsTable');

        // Iterate additional fields
        $fields = array();
        foreach ($navigationFields as $fieldID => $fieldRow) {
            $fieldName = $this->fieldName($fieldRow['Name']);

            $this->generator
                ->text($this->generateTableFieldMethod(
                    $fieldName,
                    $fieldRow[Field::F_PRIMARY],
                    $fieldRow[Field::F_TYPE]
                ))
                ->commentVar(Field::phpType($fieldRow['Type']), $fieldRow['Description'] . ' Field #' . $fieldID . ' variable name')
                ->defClassConst('F_' . $fieldName, $fieldName);

            // Collection original to new one field names
            $fields[$fieldRow['Name']] = $fieldName;
        }

        $class = "\n\t".'/**';
        $class .= "\n\t".' * @param QueryInterface $query Database query instance';
        $class .= "\n\t".' * @param ViewInterface $renderer Rendering instance';
        $class .= "\n\t".' * @param integer $entityID Entity identifier to whom this table belongs';
        $class .= "\n\t".' * @param string $locale Localization identifier';
        $class .= "\n\t".' */';
        $class .= "\n\t".'public function __construct(QueryInterface $query, ViewInterface $renderer, $entityID, $locale = null)';
        $class .= "\n\t".'{';
        $class .= "\n\t\t".'parent::__construct($query, $renderer, static::$navigationIDs, $entityID, $locale);';
        $class .= "\n\t".'}'."\n";

        $this->generator->text($class);

        return $this->generator
            ->commentVar('array', 'Collection of real additional field names')
            ->defClassVar('$fieldsRealNames', 'public static', $fields)
            ->commentVar('array', 'Collection of navigation identifiers')
            ->defClassVar('$navigationIDs', 'protected static', array($navigationID))
            ->commentVar('string', 'Row class name')
            ->defClassVar('$identifier', 'protected', $this->fullEntityName($rowClassName))
            ->endClass()
            ->flush();
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
        $this->generator->multicomment(array(
            'Class for getting "'.$navigationName.'" instances from database',
            '@method '.$this->entityName($navigationName).'[] find() Get entities collection',
            '@method '.$this->entityName($navigationName).' first() Get entity',
            '@method '.$entityName.' where($fieldName, $fieldValue = null, $fieldRelation = ArgumentInterface::EQUAL)',
            '@method '.$entityName.' primary($value) Query for chaining',
            '@method '.$entityName.' identifier($value) Query for chaining',
            '@method '.$entityName.' created($value) Query for chaining',
            '@method '.$entityName.' modified($value) Query for chaining',
            '@method '.$entityName.' published($value) Query for chaining'
        ))->defClass($entityName, '\samsoncms\api\query\Entity')
        ;

        // Iterate additional fields
        $localizedFieldIDs = array();
        $notLocalizedFieldIDs = array();
        $allFieldIDs = array();
        $allFieldNames = array();
        $allFieldValueColumns = array();
        $realNames = array();
        foreach ($navigationFields as $fieldID => $fieldRow) {
            $fieldName = $this->fieldName($fieldRow['Name']);

            // TODO: Add different method generation depending on their field type
            $this->generator->text($this->generateFieldConditionMethod(
                $fieldName,
                $fieldRow[Field::F_PRIMARY],
                $fieldRow[Field::F_TYPE]
            ));

            // Store field metadata
            $realNames[$fieldRow['Name']] = $fieldName;
            $allFieldIDs[$fieldID] = $fieldName;
            $allFieldNames[$fieldName] = $fieldID;
            $allFieldValueColumns[$fieldID] = Field::valueColumn($fieldRow[Field::F_TYPE]);
            if ($fieldRow[Field::F_LOCALIZED] == 1) {
                $localizedFieldIDs[$fieldID] = $fieldName;
            } else {
                $notLocalizedFieldIDs[$fieldID] = $fieldName;
            }
        }

        return $this->generator
            ->commentVar('array', 'Collection of real additional field names')
            ->defClassVar('$fieldRealNames', 'public static', $realNames)
            ->commentVar('array', 'Collection of navigation identifiers')
            ->defClassVar('$navigationIDs', 'protected static', array($navigationID))
            ->commentVar('string', 'Not transliterated entity name')
            ->defClassVar('$identifier', 'protected static', $this->fullEntityName($navigationName))
            ->commentVar('array', 'Collection of localized additional fields identifiers')
            ->defClassVar('$localizedFieldIDs', 'protected static', $localizedFieldIDs)
            ->commentVar('array', 'Collection of NOT localized additional fields identifiers')
            ->defClassVar('$notLocalizedFieldIDs', 'protected static', $notLocalizedFieldIDs)
            ->commentVar('array', 'Collection of localized additional fields identifiers')
            ->defClassVar('$fieldIDs', 'protected static', $allFieldIDs)
            ->commentVar('array', 'Collection of additional fields value column names')
            ->defClassVar('$fieldValueColumns', 'protected static', $allFieldValueColumns)
            ->commentVar('array', 'Collection of additional field names')
            ->defClassVar('$fieldNames', 'public static', $allFieldNames)
            ->endClass()
            ->flush()
        ;
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
        $classes .= "\n" . 'use '.$namespace.'\renderable\FieldsTable;';
        $classes .= "\n" . 'use '.$namespace.'\field\Row;';
        $classes .= "\n" . 'use \samsonframework\core\ViewInterface;';
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

            $rowClassName = $entityName.'TableRow';

            $classes .= $this->createTableRowClass(
                $structureRow['Name'],
                $rowClassName,
                $navigationFields
            );

            $classes .= $this->createTableClass(
                $structureRow['StructureID'],
                $structureRow['Name'],
                $entityName.'Table',
                $navigationFields,
                $rowClassName
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
        $this->generator = new \samsonphp\generator\Generator();
        $this->database = $database;
    }
}

//[PHPCOMPRESSOR(remove,end)]
