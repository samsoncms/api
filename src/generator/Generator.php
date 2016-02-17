<?php
/**
 * Created by PhpStorm.
 * User: molodyko
 * Date: 13.02.2016
 * Time: 23:55
 */

namespace samsoncms\api\generator;

use samson\activerecord\dbMySQLConnector;
use samsoncms\api\Field;
use samsoncms\api\generator\exception\ParentEntityNotFound;
use samsonframework\orm\DatabaseInterface;

abstract class Generator
{
    /** @var DatabaseInterface */
    protected $database;

    /** @var \samsonphp\generator\Generator */
    protected $generator;

    /** @var Metadata[] Collection of entities metadata */
    protected $metadata = array();

    /**
     * Generator constructor.
     * @param DatabaseInterface $database Database instance
     * @throws ParentEntityNotFound
     * @throws \samsoncms\api\exception\AdditionalFieldTypeNotFound
     */
    public function __construct(DatabaseInterface $database)
    {
        $this->generator = new \samsonphp\generator\Generator();
        $this->database = $database;
    }

    /**
     * Make correct code formatting
     * @param $code
     * @return mixed
     */
    protected function formatTab($code)
    {
        // Replace indentation
        return str_replace("\t", '    ', $code);
    }

    /**
     * Fill metadata
     *
     * @param null $filter Filter navigations
     * @throws ParentEntityNotFound
     * @throws \samsoncms\api\exception\AdditionalFieldTypeNotFound
     */
    public function fillMetadata($filter = null)
    {
        // Iterate all metadata types
        foreach (Metadata::$types as $type) {

            // Iterate all structures, parents first
            foreach ($this->entityNavigations($type) as $structureRow) {

                // If filter is the function and filter return false then skip this structure
                if (is_callable($filter) && (false === $filter($structureRow))) {
                    continue;
                }

                // Fill in entity metadata
                $metadata = new Metadata($type);

                // Get CapsCase and transliterated entity name
                $metadata->entity = $this->entityName($structureRow['Name']);
                // Try to find entity parent identifier for building future relations
                $metadata->parentID = $this->entityParent($structureRow['StructureID']);
                // Generate application from current entity
                $metadata->generateApplication = $structureRow['applicationGenerate'];
                // Show application from current entity
                $metadata->showApplication = $structureRow['applicationOutput'];
                // Icon for application from current entity
                $metadata->iconApplication = $structureRow['applicationIcon'];
                // Render application on main page
                $metadata->renderMainApplication = $structureRow['applicationRenderMain'];

                // TODO: Add multiple parent and fetching their data in a loop

                // Set pointer to parent entity
                if (null !== $metadata->parentID) {
                    if (array_key_exists($metadata->parentID, $this->metadata)) {
                        $metadata->parent = $this->metadata[$metadata->parentID];
                        // Add all parent metadata to current object
                        $metadata->defaultValues = $metadata->parent->defaultValues;
                        $metadata->realNames = $metadata->parent->realNames;
                        $metadata->allFieldIDs = $metadata->parent->allFieldIDs;
                        $metadata->allFieldNames = $metadata->parent->allFieldNames;
                        $metadata->allFieldValueColumns = $metadata->parent->allFieldValueColumns;
                        $metadata->allFieldTypes = $metadata->parent->allFieldTypes;
                        $metadata->fieldDescriptions = $metadata->parent->fieldDescriptions;
                        $metadata->localizedFieldIDs = $metadata->parent->localizedFieldIDs;
                        $metadata->notLocalizedFieldIDs = $metadata->parent->notLocalizedFieldIDs;
                    } else {
                        throw new ParentEntityNotFound($metadata->parentID);
                    }
                }

                // Store entity original data
                $metadata->entityRealName = $structureRow['Name'];
                $metadata->entityID = $structureRow['StructureID'];

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

                    // TODO: Set default for additional field storing type accordingly.

                    // Store field metadata
                    $metadata->realNames[$fieldRow['Name']] = $fieldName;
                    $metadata->allFieldIDs[$fieldID] = $fieldName;
                    $metadata->allFieldNames[$fieldName] = $fieldID;
                    $metadata->allFieldValueColumns[$fieldID] = Field::valueColumn($fieldRow[Field::F_TYPE]);
                    $metadata->allFieldTypes[$fieldID] = Field::phpType($fieldRow['Type']);
                    $metadata->allFieldCmsTypes[$fieldID] = $fieldRow['Type'];
                    $metadata->fieldDescriptions[$fieldID] = $fieldRow['Description'] . ', ' . $fieldRow['Name'] . '#' . $fieldID;
                    $metadata->fieldRawDescriptions[$fieldID] = $fieldRow['Description'];

                    // Fill localization fields collections
                    if ($fieldRow[Field::F_LOCALIZED] === 1) {
                        $metadata->localizedFieldIDs[$fieldID] = $fieldName;
                    } else {
                        $metadata->notLocalizedFieldIDs[$fieldID] = $fieldName;
                    }

                    // Fill all fields which should display in list
                    if ($fieldRow['showInList'] == 1) {
                        $metadata->showFieldsInList[] = $fieldID;
                    }

                    // Save custom type
                    $metadata->customTypeFields[$fieldID] = $fieldRow['customTypeName'];

                    // Set old AR collections of metadata
                    $metadata->arAttributes[$fieldName] = $fieldName;
                    $metadata->arMap[$fieldName] = dbMySQLConnector::$prefix . 'material.' . $fieldName;

                    // Add additional field column to entity query
                    $equal = '((_mf.FieldID = ' . $fieldID . ')&&(_mf.locale ' . ($fieldRow['local'] ? ' = "@locale"' : 'IS NULL') . '))';
                    $metadata->arSelect['this'] .= "\n\t\t" . ',MAX(IF(' . $equal . ', _mf.`' . Field::valueColumn($fieldRow['Type']) . '`, NULL)) as `' . $fieldName . '`';
                }

                // Get id of child navigation
                foreach ($this->entityChildNavigation($structureRow['StructureID']) as $childNavigation) {
                    $metadata->childNavigationIDs[] = $childNavigation['StructureID'];
                }

                // Store metadata by entity identifier
                $this->metadata[$structureRow['StructureID']] = $metadata;
            }
        }
    }

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
        return '\\'.$namespace.'\\generated\\'.$this->entityName($navigationName);
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

    /** @return array Get collection of child navigation objects */
    protected function entityChildNavigation($parentId)
    {
        return $this->database->fetch('
        SELECT * FROM `structure`
        WHERE `Active` = "1" AND `ParentID` = ' . $parentId . '
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
}
