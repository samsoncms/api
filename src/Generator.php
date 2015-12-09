<?php
/**
 * Created by PhpStorm.
 * User: VITALYIEGOROV
 * Date: 09.12.15
 * Time: 14:34
 */
namespace samsoncms\api;

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
            return '\\' . $className . '::' . $nameByValue[$value];
        }
    }

    /**
     * Create entity PHP class code.
     *
     * @param array $structureRow Collection of structure info
     * @return string Generated entitiy class code
     */
    protected function createEntityClass($structureRow)
    {
        $structureKey = ucfirst($this->transliterated($structureRow['Name']));

        $class = "\n" . 'class ' . $structureKey . ' extends Entity';
        $class .= "\n" . '{';
        $class .= "\n\t" . '/** @var string Not transliterated entity name */';
        $class .= "\n\t" . 'protected $identifier = "' . $structureRow['Name'] . '";';

        // Get structure fields
        //$fieldMap = array();
        $fields = array();
        $fieldIDs = array();

        // TODO: Optimize queries
        foreach ($this->database->fetch('SELECT * FROM `structurefield` WHERE `StructureID` = "' . $structureRow['StructureID'] . '" AND `Active` = "1"') as $fieldStructureRow) {
            foreach ($this->database->fetch('SELECT * FROM `field` WHERE `FieldID` = "' . $fieldStructureRow['FieldID'] . '"') as $fieldRow) {
                $type = str_replace(
                    '\samsoncms\api\Field',
                    'Field',
                    $this->constantNameByValue($fieldRow['Type'])
                );
                $commentType = Field::$PHP_TYPE[$fieldRow['Type']];
                $fieldName = lcfirst($this->transliterated($fieldRow['Name']));

                $class .= "\n\t" . '/** @var ' . $commentType . ' Field #' . $fieldRow['FieldID'] . '*/';
                $class .= "\n\t" . 'protected $' . $fieldName . ';';

                // Store field metadata
                $fields[$fieldName][] = $fieldRow;
                $fieldIDs[] = $fieldRow['FieldID'];
                //$fieldMap[] = '"'.$fieldName.'" => array("Id" => "'.$fieldRow['FieldID'].'", "Type" => ' . $type . ', "Name" => "' . $fieldRow['Name'] . '")';
            }
        }

        //$class .= "\n\t" . '/** @var array Entity additional fields metadata */';
        //$class .= "\n\t" .'protected $fieldsData = array('."\n\t\t".implode(','."\n\t\t", $fieldMap)."\n\t".');';
        $class .= "\n\t";
        $class .= "\n\t" . '/** @var array Collection of additional fields identifiers */';
        $class .= "\n\t" . 'protected static $fieldIDs = array(' . implode(',', $fieldIDs) . ');';
        $class .= "\n\t" . '/** @var array Collection of navigation identifiers */';
        $class .= "\n\t" . 'protected static $navigationIDs = array(' . $structureRow['StructureID'] . ');';
        $class .= "\n" . '}';

        // Replace tabs with spaces
        return str_replace("\t", '    ', $class);
    }

    /**
     * Create entity PHP class code.
     *
     * @param array $structureRow Collection of structure info
     * @return string Generated entitiy class code
     */
    protected function createQueryClass($structureRow)
    {
        $structureKey = ucfirst($this->transliterated($structureRow['Name']));

        $class = "\n" . 'class ' . $structureKey . ' extends Base';
        $class .= "\n" . '{';
        $class .= "\n\t" . '/** @var string Not transliterated entity name */';
        $class .= "\n\t" . 'protected $identifier = "' . $structureRow['Name'] . '";';

        // Get structure fields
        //$fieldMap = array();
        $fields = array();
        $fieldIDs = array();

        // TODO: Optimize queries
        foreach ($this->database->fetch('SELECT * FROM `structurefield` WHERE `StructureID` = "' . $structureRow['StructureID'] . '" AND `Active` = "1"') as $fieldStructureRow) {
            foreach ($this->database->fetch('SELECT * FROM `field` WHERE `FieldID` = "' . $fieldStructureRow['FieldID'] . '"') as $fieldRow) {
                $type = str_replace(
                    '\samsoncms\api\Field',
                    'Field',
                    $this->constantNameByValue($fieldRow['Type'])
                );
                $commentType = Field::$PHP_TYPE[$fieldRow['Type']];
                $fieldName = lcfirst($this->transliterated($fieldRow['Name']));

                $class .= "\n\t" . '/** @var ' . $commentType . ' Field #' . $fieldRow['FieldID'] . '*/';
                $class .= "\n\t" . 'protected $' . $fieldName . ';';

                // Store field metadata
                $fields[$fieldName][] = $fieldRow;
                $fieldIDs[] = $fieldRow['FieldID'];
                //$fieldMap[] = '"'.$fieldName.'" => array("Id" => "'.$fieldRow['FieldID'].'", "Type" => ' . $type . ', "Name" => "' . $fieldRow['Name'] . '")';
            }
        }

        //$class .= "\n\t" . '/** @var array Entity additional fields metadata */';
        //$class .= "\n\t" .'protected $fieldsData = array('."\n\t\t".implode(','."\n\t\t", $fieldMap)."\n\t".');';
        $class .= "\n\t";
        $class .= "\n\t" . '/** @var array Collection of additional fields identifiers */';
        $class .= "\n\t" . 'protected static $fieldIDs = array(' . implode(',', $fieldIDs) . ');';
        $class .= "\n\t" . '/** @var array Collection of navigation identifiers */';
        $class .= "\n\t" . 'protected static $navigationIDs = array(' . $structureRow['StructureID'] . ');';
        $class .= "\n" . '}';

        // Replace tabs with spaces
        return str_replace("\t", '    ', $class);
    }

    /** @return string Entity state hash */
    public function entityHash()
    {
        // Получим информацию о всех таблицах из БД
        return md5($this->database->fetch(
            'SELECT `TABLES`.`TABLE_NAME` as `TABLE_NAME`
              FROM `information_schema`.`TABLES` as `TABLES`
              WHERE `TABLES`.`TABLE_SCHEMA`="' . $this->database->database() . '";'
        ));
    }

    /** @return mixed Get collection of structures object */
    protected function entityStructures()
    {
        return $this->database->fetch('
        SELECT * FROM `structure`
        WHERE `Active` = "1" AND `Type` = "0"'
        );
    }

    /** @return string Generate entity classes */
    public function createEntityClasses()
    {
        $classes = "\n" . 'namespace ' . __NAMESPACE__ . ';';
        $classes .= "\n";
        $classes .= "\n" . 'use \samsoncms\api\Field;';
        $classes .= "\n";
        // Iterate all structures
        foreach ($this->entityStructures() as $structureRow) {
            $classes .= $this->createEntityClass($structureRow);
        }

        return $classes;
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
