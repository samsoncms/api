<?php
/**
 * Created by PhpStorm.
 * User: VITALYIEGOROV
 * Date: 09.12.15
 * Time: 14:34
 */
namespace samsoncms\api;

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
        $class = "\n\n" . '/** Class for getting "'.$navigationName.'" instances from database */';
        $class .= "\n" . 'class ' . $entityName . ' extends Entity';
        $class .= "\n" . '{';

        // Iterate additional fields
        $constants = '';
        $constants .= "\n\t" .'/** Entity full class name */';
        $constants .= "\n\t" . 'const ENTITY = "'.$this->fullEntityName($entityName).'";';
        $variables = '';
        foreach ($navigationFields as $fieldID => $fieldRow) {
            $fieldName = $this->fieldName($fieldRow['Name']);

            $constants .= "\n\t" . '/** ' . Field::phpType($fieldRow['Type']) . ' Field #' . $fieldID . ' variable name */';
            $constants .= "\n\t" . 'const F_' . strtoupper($fieldName) . ' = "'.$fieldName.'";';

            $variables .= "\n\t" . '/** @var ' . Field::phpType($fieldRow['Type']) . ' Field #' . $fieldID . '*/';
            $variables .= "\n\t" . 'public $' . $fieldName . ';';
        }

        $class .= $constants;
        $class .= "\n\t";
        $class .= "\n\t" . '/** @var string Not transliterated entity name */';
        $class .= "\n\t" . 'protected static $viewName = "' . $navigationName . '";';
        $class .= "\n\t";
        $class .= $variables;
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
        $class .= "\n" . 'class ' . $entityName . ' extends EntityQuery';
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
        $classes .= "\n" . 'use \samsonframework\orm\ArgumentInterface;';

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

        // Make correct code formatting
        return str_replace("\t", '    ', $classes);
    }

    /**
     * Generator constructor.
     * @param DatabaseInterface $database Database instance
     */
    public function __construct(DatabaseInterface $database)
    {
        $this->database = $database;
    }
}
