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
use samsoncms\api\generator\exception\ParentEntityNotFound;
use samsoncms\api\generator\Metadata;
use samsoncms\api\query\Generic;
use samsonframework\orm\ArgumentInterface;
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

    /** @var Metadata[] Collection of entities metadata */
    protected $metadata;

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
        if (null !== $nameByValue[$value]) {
            // Return constant name
            return $nameByValue[$value];
        }

        return '';
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
        return '\\'.$namespace.'\\'.$this->entityName($navigationName);
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
        $code .= "\n\t" . ' * @return $this Chaining';
        $code .= "\n\t" . ' * @see Generic::where()';
        $code .= "\n\t" . ' */';
        $code .= "\n\t" . 'public function ' . $fieldName . '($value, $relation = ArgumentInterface::EQUAL)';
        $code .= "\n\t" . "{";
        $code .= "\n\t\t" . 'return $this->where("'.$fieldName.'", $value, $relation);';

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
        $code .= "\n\t" . ' * @return $this Chaining';
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
     * @param Metadata $metadata Entity metadata
     * @return string Generated entity query PHP class code
     */
    protected function createEntityClass(Metadata $metadata)
    {
        /**
         * TODO: Parent problem
         * Should be changed to merging fields instead of extending with OOP for structure_relation support
         * or creating traits and using them on shared parent entities.
         */

        $this->generator
            ->multiComment(array('"'.$metadata->entityRealName.'" entity class'))
            ->defClass($metadata->entity, null !== $metadata->parent ? $metadata->parent->className : 'Entity')
            ->commentVar('string', '@deprecated Entity full class name, use ::class')
            ->defClassConst('ENTITY', $metadata->className)
            ->commentVar('string', 'Entity manager full class name')
            ->defClassConst('MANAGER', $metadata->className.'Query')
            ->commentVar('string', 'Entity database identifier')
            ->defClassConst('IDENTIFIER', $metadata->entityID)
            ->commentVar('string', 'Not transliterated entity name')
            ->defClassVar('$viewName', 'protected static', $metadata->entityRealName);

        foreach ($metadata->allFieldIDs as $fieldID => $fieldName) {
            $this->generator
                ->commentVar('string', $metadata->fieldDescriptions[$fieldID].' variable name')
                ->defClassConst('F_' . $fieldName, $fieldName)
                ->commentVar($metadata->allFieldTypes[$fieldID], $metadata->fieldDescriptions[$fieldID])
                ->defClassVar('$' . $fieldName, 'public');
        }

        return $this->generator
            ->defClassVar('$_sql_select', 'public static ', $metadata->arSelect)
            ->defClassVar('$_attributes', 'public static ', $metadata->arAttributes)
            ->defClassVar('$_map', 'public static ', $metadata->arMap)
            ->defClassVar('$_sql_from', 'public static ', $metadata->arFrom)
            ->defClassVar('$_own_group', 'public static ', $metadata->arGroup)
            ->defClassVar('$_relation_alias', 'public static ', $metadata->arRelationAlias)
            ->defClassVar('$_relation_type', 'public static ', $metadata->arRelationType)
            ->defClassVar('$_relations', 'public static ', $metadata->arRelations)
            ->endClass()
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

        // TODO: Add generator method generation logic
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
     * @param string $parentClass Parent entity class name
     * @return string Generated entity query PHP class code
     */
    protected function createCollectionClass($navigationID, $navigationName, $entityName, $navigationFields, $parentClass = '\samsoncms\api\renderable\Collection')
    {
        $this->generator
            ->multiComment(array('Class for getting "'.$navigationName.'" instances from database',))
            ->defClass($entityName, $parentClass)
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

        // TODO: Add generator method generation logic
        $class = "\n\t".'/**';
        $class .= "\n\t".' * @param ViewInterface $renderer Rendering instance';
        $class .= "\n\t".' * @param QueryInterface $query Querying instance';
        $class .= "\n\t".' * @param string $locale Localization identifier';
        $class .= "\n\t".' */';
        $class .= "\n\t".'public function __construct(ViewInterface $renderer, QueryInterface $query = null, $locale = null)';
        $class .= "\n\t".'{';
        $class .= "\n\t\t".'parent::__construct($renderer, $query === null ? new dbQuery() : $query, $locale);';
        $class .= "\n\t".'}'."\n";

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
            ->text($class)
            ->endClass()
            ->flush()
            ;
    }

    /**
     * Create entity query PHP class code.
     *
     * @param integer $navigationID Entity navigation identifier
     * @param string $navigationName Original entity name
     * @param string $entityName PHP entity name
     * @param array $navigationFields Collection of entity additional fields
     * @param string $parentClass Parent entity class name
     * @return string Generated entity query PHP class code
     */
    protected function createQueryClass($navigationID, $navigationName, $entityName, $navigationFields, $parentClass = '\samsoncms\api\query\Entity')
    {
        $this->generator
            ->multiComment(array('Class for getting "'.$navigationName.'" instances from database'))
            ->defClass($entityName, $parentClass)
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

        // TODO: Add generator method generation logic
        $class = "\n\t".'/**';
        $class .= "\n\t".' * @param QueryInterface $query Rendering instance';
        $class .= "\n\t".' * @param string $locale Localization identifier';
        $class .= "\n\t".' */';
        $class .= "\n\t".'public function __construct(QueryInterface $query = null, $locale = null)';
        $class .= "\n\t".'{';
        $class .= "\n\t\t".'parent::__construct($query === null ? new dbQuery() : $query, $locale);';
        $class .= "\n\t".'}'."\n";

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
            ->text($class)
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

    /**
     * Find entity parent.
     *
     * @param $entityID
     *
     * @return null|int Parent entity identifier
     */
    public function entityParent($entityID)
    {
        $parentData = $this->database->fetch('
SELECT *
FROM structure_relation as sm
JOIN structure as s ON s.StructureID = sm.parent_id
WHERE sm.child_id = "' . $entityID . '"
AND s.StructureID != "' . $entityID . '"
');

        // Get parent entity identifier
        return count($parentData) ? $parentData[0]['StructureID'] : null;
    }

    /** @return array Get collection of navigation objects */
    protected function entityNavigations($type = 0)
    {
        return $this->database->fetch('
        SELECT * FROM `structure`
        WHERE `Active` = "1" AND `Type` = "'.$type.'"
        ORDER BY `ParentID` ASC
        ');
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
        $classes .= "\n" . 'use \samson\activerecord\dbQuery;';
        $classes .= "\n";

        // Iterate all structures, parents first
        foreach ($this->entityNavigations() as $structureRow) {
            // Fill in entity metadata
            $metadata = new Metadata();
            // Get CapsCase and transliterated entity name
            $metadata->entity = $this->entityName($structureRow['Name']);
            // Try to find entity parent identifier for building future relations
            $metadata->parentID = $this->entityParent($structureRow['StructureID']);

            // Set pointer to parent entity
            if (null !== $metadata->parentID) {
                if (array_key_exists($metadata->parentID, $this->metadata)) {
                    $metadata->parent = $this->metadata[$metadata->parentID];
                } else {
                    throw new ParentEntityNotFound($metadata->parentID);
                }
            }

            // Store entity original data
            $metadata->entityRealName = $structureRow['Name'];
            $metadata->entityID = $structureRow['StructureID'];
            $metadata->className = $this->fullEntityName($metadata->entity, __NAMESPACE__);

            // Get old AR collections of metadata
            $metadata->arSelect = \samson\activerecord\material::$_sql_select;
            $metadata->arAttributes = \samson\activerecord\material::$_attributes;
            $metadata->arMap = \samson\activerecord\material::$_map;
            $metadata->arFrom = \samson\activerecord\material::$_sql_from;
            $metadata->arGroup = \samson\activerecord\material::$_own_group;
            $metadata->arRelationAlias = \samson\activerecord\material::$_relation_alias;
            $metadata->arRelationType = \samson\activerecord\material::$_relation_type;
            $metadata->arRelations = \samson\activerecord\material::$_relations;

            // Add SamsonCMS material needed data
            $metadata->arSelect['this'] = ' STRAIGHT_JOIN ' . $metadata->arSelect['this'];
            $metadata->arFrom['this'] .= "\n" .
                'LEFT JOIN ' . dbMySQLConnector::$prefix . 'materialfield as _mf
                ON ' . dbMySQLConnector::$prefix . 'material.MaterialID = _mf.MaterialID';
            $metadata->arGroup[] = dbMySQLConnector::$prefix . 'material.MaterialID';

            // Iterate entity fields
            foreach ($this->navigationFields($structureRow['StructureID']) as $fieldID => $fieldRow) {
                // Get camelCase and transliterated field name
                $fieldName = $this->fieldName($fieldRow['Name']);

                // Store field metadata
                $metadata->realNames[$fieldRow['Name']] = $fieldName;
                $metadata->allFieldIDs[$fieldID] = $fieldName;
                $metadata->allFieldNames[$fieldName] = $fieldID;
                $metadata->allFieldValueColumns[$fieldID] = Field::valueColumn($fieldRow[Field::F_TYPE]);
                $metadata->allFieldTypes[$fieldID] = Field::phpType($fieldRow['Type']);
                $metadata->fieldDescriptions[$fieldID] = $fieldRow['Description'] . ', '.$fieldRow['Name'].'#' . $fieldID;

                // Fill localization fields collections
                if ($fieldRow[Field::F_LOCALIZED] === 1) {
                    $metadata->localizedFieldIDs[$fieldID] = $fieldName;
                } else {
                    $metadata->notLocalizedFieldIDs[$fieldID] = $fieldName;
                }

                // Set old AR collections of metadata
                $metadata->arAttributes[$fieldName] = $fieldName;
                $metadata->arMap[$fieldName] = dbMySQLConnector::$prefix . 'material.' . $fieldName;

                // Add additional field column to entity query
                $equal = '((_mf.FieldID = ' . $fieldID . ')&&(_mf.locale ' . ($fieldRow['local'] ? ' = "@locale"' : 'IS NULL') . '))';
                $metadata->arSelect['this'] .= "\n\t\t" . ',MAX(IF(' . $equal . ', _mf.`' . Field::valueColumn($fieldRow['Type']) . '`, NULL)) as `' . $fieldName . '`';
            }

            // Store metadata by entity identifier
            $this->metadata[$structureRow['StructureID']] = $metadata;
        }

        // Iterate all entities metadata
        foreach ($this->metadata as $metadata) {
            $classes .= $this->createEntityClass($metadata);
        }

        // Iterate all structures
        foreach ($this->entityNavigations() as $structureRow) {
            $navigationFields = $this->navigationFields($structureRow['StructureID']);
            $entityName = $this->entityName($structureRow['Name']);

            $classes .= $this->createQueryClass(
                $structureRow['StructureID'],
                $structureRow['Name'],
                $entityName.'Query',
                $navigationFields,
                isset($parentEntity) ? $parentEntity.'Query' : '\samsoncms\api\query\Entity'
            );

            $classes .= $this->createCollectionClass(
                $structureRow['StructureID'],
                $structureRow['Name'],
                $entityName.'Collection',
                $navigationFields,
                isset($parentEntity) ? $parentEntity.'Collection' : '\samsoncms\api\renderable\Collection'
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
