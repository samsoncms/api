<?php
//[PHPCOMPRESSOR(remove,start)]
/**
 * Created by PhpStorm.
 * User: VITALYIEGOROV
 * Date: 09.12.15
 * Time: 14:34
 */
namespace samsoncms\api;

use samsoncms\api\generator\exception\ParentEntityNotFound;
use samsoncms\api\generator\Generator;
use samsoncms\api\generator\Metadata;
use samsonframework\orm\DatabaseInterface;

/**
 * Entity classes generator.
 * @package samsoncms\api
 */
class GeneratorApi extends Generator
{

    /**
     * Generator constructor.
     * @param DatabaseInterface $database Database instance
     * @throws ParentEntityNotFound
     * @throws \samsoncms\api\exception\AdditionalFieldTypeNotFound
     */
    public function __construct(DatabaseInterface $database)
    {
        parent::__construct($database);

        /**
         * Fill metadata only with structures which have to be generated
         */
        $this->fillMetadata();
    }

    /**
     * Generate entity classes.
     *
     * @param string $namespace Base namespace for generated classes
     *
     * @return string Generated PHP code for entity classes
     * @throws ParentEntityNotFound
     * @throws \samsoncms\api\exception\AdditionalFieldTypeNotFound
     */
    public function createEntityClasses($namespace = __NAMESPACE__)
    {
        $classes = "\n" . 'namespace ' . $namespace . '\\generated;';
        $classes .= "\n";
        $classes .= "\n" . 'use ' . $namespace . '\renderable\FieldsTable;';
        $classes .= "\n" . 'use ' . $namespace . '\field\Row;';
        $classes .= "\n" . 'use \samsoncms\api\Entity;';
        $classes .= "\n" . 'use \samsonframework\core\ViewInterface;';
        $classes .= "\n" . 'use \samsonframework\orm\ArgumentInterface;';
        $classes .= "\n" . 'use \samsonframework\orm\QueryInterface;';
        $classes .= "\n" . 'use \samson\activerecord\dbQuery;';
        $classes .= "\n";

        // Iterate all entities metadata
        foreach ($this->metadata as $metadata) {
            // Generate classes of default type
            if ($metadata->type === Metadata::TYPE_DEFAULT) {
                // Generate entity class
                $classes .= $this->createEntityClass($metadata);
                // Generate query class for queries
                $classes .= $this->createQueryClass($metadata);
                // Generate collection class for rendering
                $classes .= $this->createCollectionClass(
                    $metadata,
                    $this->fullEntityName($metadata->entity . 'Query', $namespace),
                    array(\samsoncms\api\Renderable::class)
                );

                // Generate Gallery classes for this entity
                $classes .= $this->createGalleryClass($metadata);

                // Generate classes of table type
            } elseif ($metadata->type === Metadata::TYPE_TABLE) {
                $classes .= $this->createTableRowClass($metadata);
                $classes .= $this->createTableClass($metadata);
            }
        }

        // Make correct code formatting
        return $this->formatTab($classes);
    }

    /**
     * Create entity PHP class code.
     *
     * @param Metadata $metadata  Entity metadata
     * @param string   $namespace Namespace of generated class
     * @return string Generated entity query PHP class code
     */
    protected function createEntityClass(Metadata $metadata, $namespace = __NAMESPACE__)
    {
        /**
         * TODO: Parent problem
         * Should be changed to merging fields instead of extending with OOP for structure_relation support
         * or creating traits and using them on shared parent entities.
         */

        $this->generator
            ->multiComment(array('"' . $metadata->entityRealName . '" entity class'))
            ->defClass($metadata->entity, null !== $metadata->parent ? $this->fullEntityName($metadata->parent->entity, $namespace) : 'Entity')
            ->commentVar('string', '@deprecated Entity full class name, use ::class')
            ->defClassConst('ENTITY', $this->fullEntityName($metadata->entity, $namespace))
            ->commentVar('string', 'Entity manager full class name')
            ->defClassConst('MANAGER', $this->fullEntityName($metadata->entity, $namespace) . 'Query')
            ->commentVar('string', 'Entity database identifier')
            ->defClassConst('IDENTIFIER', $metadata->entityID)
            ->commentVar('string', 'Not transliterated entity name')
            ->defClassVar('$viewName', 'protected static', $metadata->entityRealName);

        foreach ($metadata->allFieldIDs as $fieldID => $fieldName) {
            $this->generator
                ->commentVar('string', $metadata->fieldDescriptions[$fieldID] . ' variable name')
                ->defClassConst('F_' . $fieldName, $fieldName)
                ->commentVar('string', $metadata->fieldDescriptions[$fieldID] . ' additional field identifier')
                ->defClassConst('F_' . $fieldName . '_ID', $fieldID)
                ->commentVar($metadata->allFieldTypes[$fieldID], $metadata->fieldDescriptions[$fieldID])
                ->defClassVar('$' . $fieldName, 'public');
        }

        return $this->generator
            ->commentVar('array', 'Collection of navigation identifiers')
            ->defClassVar('$navigationIDs', 'protected static', array($metadata->entityID))
            ->defClassVar('$_sql_select', 'public static ', $metadata->arSelect)
            ->defClassVar('$_attributes', 'public static ', $metadata->arAttributes)
            ->defClassVar('$_map', 'public static ', $metadata->arMap)
            ->defClassVar('$_sql_from', 'public static ', $metadata->arFrom)
            ->defClassVar('$_own_group', 'public static ', $metadata->arGroup)
            ->defClassVar('$_relation_alias', 'public static ', $metadata->arRelationAlias)
            ->defClassVar('$_relation_type', 'public static ', $metadata->arRelationType)
            ->defClassVar('$_relations', 'public static ', $metadata->arRelations)
            ->defClassVar('$fieldIDs', 'protected static ', $metadata->allFieldIDs)
            ->defClassVar('$fieldValueColumns', 'protected static ', $metadata->allFieldValueColumns)
            ->endClass()
            ->flush();
    }

    /**
     * Create entity query PHP class code.
     *
     * @param Metadata $metadata      Entity metadata
     * @param string   $defaultParent Parent class name
     * @param array    $use           Collection of traits
     * @param string   $namespace     Namespace of generated class
     *
     * @return string Generated entity query PHP class code
     */
    protected function createQueryClass(Metadata $metadata, $defaultParent = '\samsoncms\api\query\Entity', $use = array(), $namespace = __NAMESPACE__)
    {
        $this->generateQuerableClassHeader($metadata, 'Query', $defaultParent, $use, $namespace);

        foreach ($metadata->allFieldIDs as $fieldID => $fieldName) {
            // TODO: Add different method generation depending on their field type
            $this->generator->text($this->generateFieldConditionMethod(
                $fieldName,
                $fieldID,
                $metadata->allFieldTypes[$fieldID]
            ));
        }

        return $this->generator
            ->commentVar('array', 'Collection of real additional field names')
            ->defClassVar('$fieldRealNames', 'public static', $metadata->realNames)
            ->commentVar('array', 'Collection of additional field names')
            ->defClassVar('$fieldNames', 'public static', $metadata->allFieldNames)
            // TODO: two above fields should be protected
            ->commentVar('array', 'Collection of navigation identifiers')
            ->defClassVar('$navigationIDs', 'protected static', array($metadata->entityID))
            ->commentVar('string', 'Entity full class name')
            ->defClassVar('$identifier', 'protected static', $this->fullEntityName($metadata->entity, $namespace))
            ->commentVar('array', 'Collection of localized additional fields identifiers')
            ->defClassVar('$localizedFieldIDs', 'protected static', $metadata->localizedFieldIDs)
            ->commentVar('array', 'Collection of NOT localized additional fields identifiers')
            ->defClassVar('$notLocalizedFieldIDs', 'protected static', $metadata->notLocalizedFieldIDs)
            ->commentVar('array', 'Collection of localized additional fields identifiers')
            ->defClassVar('$fieldIDs', 'protected static', $metadata->allFieldIDs)
            ->commentVar('array', 'Collection of additional fields value column names')
            ->defClassVar('$fieldValueColumns', 'protected static', $metadata->allFieldValueColumns)
            ->endClass()
            ->flush();
    }

    /**
     * Start quearable class declaration.
     *
     * @param Metadata $metadata      Entity metadata
     * @param string   $suffix        Entity class name suffix
     * @param string   $defaultParent Parent class name
     * @param array    $use           Collection of traits
     * @param string   $namespace     Namespace of generated class
     */
    protected function generateQuerableClassHeader(Metadata $metadata, $suffix, $defaultParent, $use, $namespace = __NAMESPACE__)
    {
        $this->generator
            ->multiComment(array(
                'Class for fetching "' . $metadata->entityRealName . '" instances from database',
                '@method ' . $this->fullEntityName($metadata->entity, $namespace) . ' first();',
                '@method ' . $this->fullEntityName($metadata->entity, $namespace) . '[] find();',
            ))
            ->defClass($metadata->entity . $suffix, $defaultParent);


        // Add traits to generated classes
        $this->generateTraitsUsage($use);
    }

    /**
     * Generate class traits usage.
     *
     * @param array $use Collection of trait names
     */
    protected function generateTraitsUsage($use = array())
    {
        // Add traits to generated classes
        foreach ($use as $trait) {
            $this->generator->newLine();
            $this->generator->tabs('use \\' . ltrim($trait, '\\') . ';');
        }
        $this->generator->newLine();
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
        $code .= "\n\t" . ' * @param ' . $fieldType . ' $value Field value';
        $code .= "\n\t" . ' * @return $this Chaining';
        $code .= "\n\t" . ' * @see Generic::where()';
        $code .= "\n\t" . ' */';
        $code .= "\n\t" . 'public function ' . $fieldName . '($value, $relation = ArgumentInterface::EQUAL)';
        $code .= "\n\t" . "{";
        $code .= "\n\t\t" . 'return $this->where("' . $fieldName . '", $value, $relation);';

        return $code . "\n\t" . "}"."\n";
    }

    /**
     * Create entity collection PHP class code.
     *
     * @param Metadata $metadata      Entity metadata
     * @param string   $suffix        Generated class name suffix
     * @param string   $defaultParent Parent class name
     * @param array    $use           Collection of traits
     * @param string   $namespace     Namespace of generated class
     *
     * @return string Generated entity query PHP class code
     */
    protected function createCollectionClass(Metadata $metadata, $defaultParent, $use = array(), $suffix = 'Collection', $namespace = __NAMESPACE__)
    {
        $this->generateQuerableClassHeader($metadata, 'Collection', $defaultParent, $use, $namespace);

        return $this->generator
            ->text($this->generateConstructorCollectionClass())
            ->endClass()
            ->flush();
    }

    /**
     * Generate constructor for collection class.
     */
    protected function generateConstructorCollectionClass()
    {
        $class = "\n\t".'/**';
        $class .= "\n\t".' * @param ViewInterface $renderer Rendering instance';
        $class .= "\n\t" . ' * @param QueryInterface $query Database query instance';
        $class .= "\n\t".' * @param string $locale Localization identifier';
        $class .= "\n\t".' */';
        $class .= "\n\t" . 'public function __construct(ViewInterface $renderer, QueryInterface $query = null, $locale = null)';
        $class .= "\n\t".'{';
        $class .= "\n\t\t" . '$this->renderer = $renderer;';
        $class .= "\n\t\t" . 'parent::__construct(isset($query) ? $query : new dbQuery(), $locale);';
        $class .= "\n\t".'}'."\n";

        return $class;
    }

    /**
     * Generate classes for entity additional field gallery.
     *
     * @param Metadata $metadata Entity metadata
     *
     * @return string Generated Gallery additional field class
     */
    public function createGalleryClass(Metadata $metadata)
    {
        // Iterate entity additional fields
        foreach ($metadata->allFieldCmsTypes as $fieldID => $fieldType) {
            // We need only gallery fields
            if ($fieldType === Field::TYPE_GALLERY) {
                $fieldName = $metadata->allFieldIDs[$fieldID];
                // Declare class
                $this->generateQuerableClassHeader(
                    $metadata,
                    ucfirst($fieldName) . 'Gallery',
                    '\\' . \samsoncms\api\Gallery::class,
                    array(\samsoncms\api\Renderable::class)
                );

                return $this->generator
                    ->text($this->generateConstructorGalleryClass($metadata->entity . '::F_' . strtoupper($fieldName) . '_ID'))
                    ->endClass()
                    ->flush();
            }
        }


    }

    /**
     * Generate constructor for gallery class.
     */
    protected function generateConstructorGalleryClass($fieldID)
    {
        $class = "\n\t" . '/**';
        $class .= "\n\t" . ' * @param ViewInterface $renderer Rendering instance';
        $class .= "\n\t" . ' * @param int $materialID Gallery material identifier';
        $class .= "\n\t" . ' * @param QueryInterface $query Database query instance';
        $class .= "\n\t" . ' */';
        $class .= "\n\t" . 'public function __construct(ViewInterface $renderer, $materialID, QueryInterface $query = null)';
        $class .= "\n\t" . '{';
        $class .= "\n\t\t" . '$this->renderer = $renderer;';
        $class .= "\n\t\t" . 'parent::__construct(isset($query) ? $query : new dbQuery(), $materialID, ' . $fieldID . ');';
        $class .= "\n\t" . '}' . "\n";

        return $class;
    }

    /**
     * Create fields table row PHP class code.
     *
     * @param Metadata $metadata metadata of entity
     * @param string $namespace Namespace of generated class
     *
     * @return string Generated entity query PHP class code
     * @throws exception\AdditionalFieldTypeNotFound
     */
    protected function createTableRowClass(Metadata $metadata, $namespace = __NAMESPACE__)
    {
        $this->generator
            ->multiComment(array('Class for getting "' . $metadata->entityRealName . '" fields table rows'))
            ->defClass($this->entityName($metadata->entityRealName) . 'TableRow', 'Row');

        $fieldIDs = array();
        foreach ($this->navigationFields($metadata->entityID) as $fieldID => $fieldRow) {
            $fieldName = $this->fieldName($fieldRow['Name']);

            // Fill field ids array
            $fieldIDs[$fieldName] = $fieldID;

            $this->generator
                ->commentVar($metadata->allFieldTypes[$fieldID], $fieldRow['Description'] . ' Field #' . $fieldID . ' variable name')
                ->defClassConst('F_' . strtoupper($fieldName), $fieldName)
                ->commentVar($metadata->allFieldTypes[$fieldID], $fieldRow['Description'] . ' Field #' . $fieldID . ' row value')
                ->defVar('public $' . $fieldName)
                ->text("\n");
        }

        return $this->generator
            ->commentVar('array', 'Collection of additional fields identifiers')
            ->defClassVar('$fieldIDs', 'public static', $fieldIDs)
            ->endClass()
            ->flush();
    }

    /**
     * Create fields table PHP class code.
     *
     * @param Metadata $metadata metadata of entity
     * @param string $namespace Namespace of generated class
     * @return string Generated entity query PHP class code
     * @throws exception\AdditionalFieldTypeNotFound
     */
    protected function createTableClass(Metadata $metadata, $namespace = __NAMESPACE__)
    {
        $this->generator
            ->multiComment(array('Class for getting "'.$metadata->entityRealName.'" fields table'))
            ->defClass($this->entityName($metadata->entityRealName) . 'Table', 'FieldsTable');

        // Add renderable trait
        $this->generateTraitsUsage(array(\samsoncms\api\Renderable::class));

        // Iterate additional fields
        $fields = array();
        foreach ($this->navigationFields($metadata->entityID) as $fieldID => $fieldRow) {
            $fieldName = $this->fieldName($fieldRow['Name']);

            $this->generator
                ->text($this->generateTableFieldMethod(
                    $fieldName,
                    $fieldRow[Field::F_PRIMARY],
                    $fieldRow[Field::F_TYPE]
                ))
                ->commentVar($metadata->allFieldTypes[$fieldID], $fieldRow['Description'] . ' Field #' . $fieldID . ' variable name')
                ->defClassConst('F_' . $fieldName, $fieldName);

            // Collection original to new one field names
            $fields[$fieldRow['Name']] = $fieldName;
        }

        // TODO: Add generator method generation logic
        $constructor = $this->generateConstructorTableClass();

        $this->generator->text($constructor);

        return $this->generator
            ->commentVar('string', 'Entity database identifier')
            ->defClassConst('IDENTIFIER', $metadata->entityID)
            ->commentVar('array', 'Collection of real additional field names')
            ->defClassVar('$fieldsRealNames', 'public static', $fields)
            ->commentVar('array', 'Collection of navigation identifiers')
            ->defClassVar('$navigationIDs', 'protected static', array($metadata->entityID))
            ->commentVar('string', 'Row class name')
            ->defClassVar('$identifier', 'protected', $this->fullEntityName($this->entityName($metadata->entityRealName) . 'TableRow'))
            ->endClass()
            ->flush();
    }

    /**
     * Generate FieldsTable::values() analog for specific field.
     *
     * @param string $fieldName Field name
     * @param string $fieldId   Field primary identifier
     * @param string $fieldType Field PHP type
     *
     * @return string Generated PHP method code
     */
    protected function generateTableFieldMethod($fieldName, $fieldId, $fieldType)
    {
        $code = "\n\t" . '/**';
        $code .= "\n\t" . ' * Get table column ' . $fieldName . '(#' . $fieldId . ') values.';
        $code .= "\n\t" . ' * @return array Collection(' . Field::phpType($fieldType) . ') of table column values';
        $code .= "\n\t" . ' */';
        $code .= "\n\t" . 'public function ' . $fieldName . '()';
        $code .= "\n\t" . "{";
        $code .= "\n\t\t" . 'return $this->values(' . $fieldId . ');';

        return $code . "\n\t" . "}" . "\n";
    }

    /**
     * Generate constructor for table class.
     */
    protected function generateConstructorTableClass()
    {
        $class = "\n\t" . '/**';
        $class .= "\n\t" . ' * @param QueryInterface $query Database query instance';
        $class .= "\n\t" . ' * @param ViewInterface $renderer Rendering instance';
        $class .= "\n\t" . ' * @param integer $entityID Entity identifier to whom this table belongs';
        $class .= "\n\t" . ' * @param string $locale Localization identifier';
        $class .= "\n\t" . ' */';
        $class .= "\n\t" . 'public function __construct(QueryInterface $query, ViewInterface $renderer, $entityID, $locale = null)';
        $class .= "\n\t" . '{';
        $class .= "\n\t\t" . 'parent::__construct($query, $renderer, static::$navigationIDs, $entityID, $locale);';
        $class .= "\n\t" . '}' . "\n";

        return $class;
    }

    /**
     * Generate Query::where() analog for specific field.
     *
     * @param string $fieldName Field name
     * @param string $fieldId   Field primary identifier
     * @param string $fieldType Field PHP type
     *
     * @return string Generated PHP method code
     */
    protected function generateLocalizedFieldConditionMethod($fieldName, $fieldId, $fieldType)
    {
        $code = "\n\t" . '/**';
        $code .= "\n\t" . ' * Add ' . $fieldName . '(#' . $fieldId . ') field query condition.';
        $code .= "\n\t" . ' * @param ' . Field::phpType($fieldType) . ' $value Field value';
        $code .= "\n\t" . ' * @return $this Chaining';
        $code .= "\n\t" . ' * @see Generic::where()';
        $code .= "\n\t" . ' */';
        $code .= "\n\t" . 'public function ' . $fieldName . '($value)';
        $code .= "\n\t" . "{";
        $code .= "\n\t\t" . 'return $this->where("' . $fieldName . '", $value);';

        return $code . "\n\t" . "}" . "\n";
    }

    /**
     * Generate constructor for application class.
     */
    protected function generateConstructorApplicationClass()
    {
        $class = "\n\t" . '/**';
        $class .= "\n\t" . ' * Render materials list with pager';
        $class .= "\n\t" . ' *';
        $class .= "\n\t" . ' * @param string $navigationId Structure identifier';
        $class .= "\n\t" . ' * @param string $search Keywords to filter table';
        $class .= "\n\t" . ' * @param int $page Current table page';
        $class .= "\n\t" . ' * @return array Asynchronous response containing status and materials list with pager on success';
        $class .= "\n\t" . ' * or just status on asynchronous controller failure';
        $class .= "\n\t" . ' */';
        $class .= "\n\t" . 'public function __async_collection($navigationId = \'0\', $search = \'\', $page = 1)';
        $class .= "\n\t" . '{';
        $class .= "\n\t\t" . 'return parent::__async_collection(self::$navigation, $search, $page);';
        $class .= "\n\t" . '}' . "\n";

        return $class;
    }

    /**
     * Generate constructor for application class.
     */
    protected function generateConstructorApplicationCollectionClass()
    {
        $class = "\n\t" . '/**';
        $class .= "\n\t" . ' * Generic collection constructor';
        $class .= "\n\t" . ' *';
        $class .= "\n\t" . ' * @param RenderInterface $renderer View render object';
        $class .= "\n\t" . ' * @param QueryInterface $query Query object';
        $class .= "\n\t" . ' */';
        $class .= "\n\t" . 'public function __async_collection($renderer, $query = null, $pager = null)';
        $class .= "\n\t" . '{';
        $class .= "\n\t\t" . 'return parent::__async_collection($renderer, $query = null, $pager = null);';
        $class .= "\n\t\t" . '$this->fields = array(';
        $class .= "\n\t\t\t" . 'new Control(),';
        $class .= "\n\t\t" . ');';
        $class .= "\n\t" . '}' . "\n";

        return $class;
    }
}
//[PHPCOMPRESSOR(remove,end)]
