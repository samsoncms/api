<?php
//[PHPCOMPRESSOR(remove,start)]
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 22.03.16 at 15:46
 */
namespace samsoncms\api\generator;

use samsoncms\api\Field;
use samsoncms\api\generator\metadata\VirtualMetadata;

/**
 * Virtual entity class generator.
 *
 * @package samsoncms\api\generator
 */
class VirtualEntity extends RealEntity
{
    /**
     * Class uses generation part.
     *
     * @param VirtualMetadata $metadata Entity metadata
     */
    protected function createUses($metadata)
    {
        $this->generator
            ->newLine('use samsonframework\core\ViewInterface;')
            ->newLine('use samsonframework\orm\QueryInterface;')
            ->newLine();
    }

    /**
     * Class definition generation part.
     *
     * @param VirtualMetadata $metadata Entity metadata
     */
    protected function createDefinition($metadata)
    {
        /**
         * TODO: Parent problem
         * Should be changed to merging fields instead of extending with OOP for structure_relation support
         * or creating traits and using them on shared parent entities.
         */
        $parentClass = null !== $metadata->parent
            ? $metadata->parent->entityClassName
            : '\\'.\samsoncms\api\Entity::class;

        $this->generator
            ->multiComment(array('"' . $metadata->entityRealName . '" database entity class'))
            ->defClass($this->className, $parentClass)
            ->newLine('use \samsoncms\api\generated\TableTrait;')
            ->newLine();
    }

    /**
     * Class constants generation part.
     *
     * @param VirtualMetadata $metadata Entity metadata
     */
    protected function createConstants($metadata)
    {
        $this->generator
            ->commentVar('string', 'Entity full class name, use ::class instead')
            ->defClassConst('ENTITY', $metadata->entityClassName)
            ->commentVar('string', 'Entity manager full class name')
            ->defClassConst('MANAGER', $metadata->entityClassName . 'Query')
            ->commentVar('string', 'Entity database identifier')
            ->defClassConst('IDENTIFIER', $metadata->entityID)
            ->commentVar('string', 'Not transliterated entity name')
            ->defClassConst('NAME', $metadata->entityRealName);

        // Create all entity fields constants storing each additional field metadata
        foreach ($metadata->fields as $fieldID => $fieldName) {
            $this->generator
                ->commentVar('string', $metadata->fieldDescriptions[$fieldID] . ' variable name')
                ->defClassConst('F_' . $fieldName, $fieldName)
                ->commentVar('string', $metadata->fieldDescriptions[$fieldID] . ' additional field identifier')
                ->defClassConst('F_' . $fieldName . '_ID', $fieldID);
        }
    }

    /**
     * Class static fields generation part.
     *
     * @param VirtualMetadata $metadata Entity metadata
     */
    protected function createStaticFields($metadata)
    {
        $this->generator
            ->commentVar('array', '@deprecated Old ActiveRecord data')
            ->defClassVar('$_sql_select', 'public static ', $metadata->arSelect)
            ->commentVar('array', '@deprecated Old ActiveRecord data')
            ->defClassVar('$_attributes', 'public static ', $metadata->arAttributes)
            ->commentVar('array', '@deprecated Old ActiveRecord data')
            ->defClassVar('$_map', 'public static ', $metadata->arMap)
            ->commentVar('array', '@deprecated Old ActiveRecord data')
            ->defClassVar('$_sql_from', 'public static ', $metadata->arFrom)
            ->commentVar('array', '@deprecated Old ActiveRecord data')
            ->defClassVar('$_own_group', 'public static ', $metadata->arGroup)
            ->commentVar('array', '@deprecated Old ActiveRecord data')
            ->defClassVar('$_relation_alias', 'public static ', $metadata->arRelationAlias)
            ->commentVar('array', '@deprecated Old ActiveRecord data')
            ->defClassVar('$_relation_type', 'public static ', $metadata->arRelationType)
            ->commentVar('array', '@deprecated Old ActiveRecord data')
            ->defClassVar('$_relations', 'public static ', $metadata->arRelations)
            ->commentVar('array', 'Collection of navigation identifiers')
            ->defClassVar('$navigationIDs', 'protected static', array($metadata->entityID))
            ->defClassVar('$fieldIDs', 'protected static', $metadata->fields)
            ->commentVar('array', 'Collection of additional fields value column names')
            ->defClassVar('$fieldValueColumns', 'protected static', $metadata->allFieldValueColumns)
        ;
    }

    /**
     * Class fields generation part.
     *
     * @param VirtualMetadata $metadata Entity metadata
     */
    protected function createFields($metadata)
    {
        foreach ($metadata->fields as $fieldID => $fieldName) {
            $this->generator
                ->commentVar($metadata->types[$fieldID], $metadata->fieldDescriptions[$fieldID])
                ->defClassVar('$' . $fieldName, 'public');
        }
    }

    /**
     * Class methods generation part.
     *
     * @param VirtualMetadata $metadata Entity metadata
     */
    protected function createMethods($metadata)
    {
        $methods = [];
        // Generate Query::where() analog for specific field.
        foreach ($metadata->fields as $fieldID => $fieldName) {
            try {
                // We need only gallery fields
                if ($metadata->allFieldCmsTypes[$fieldID] === Field::TYPE_GALLERY) {
                    $galleryName = preg_replace('/Gallery$/i', '', $fieldName) . 'Gallery';

                    $code = "\n\t" . '/**';
                    $code .= "\n\t" . ' * Get ' . $fieldName . '(#' . $fieldID . ') gallery collection instance.';
                    $code .= "\n\t" . ' * @param ViewInterface $renderer Render instance';
                    $code .= "\n\t" . ' *';
                    $code .= "\n\t" . ' * @return GalleryCollection Gallery collection instance';
                    $code .= "\n\t" . ' */';
                    $code .= "\n\t" . 'public function create' . ucfirst($galleryName) . '(ViewInterface $renderer)';
                    $code .= "\n\t" . '{';
                    $code .= "\n\t\t" . '$gallery = (new GalleryCollection($renderer, $this->query))->materialID($this->id);';
                    $code .= "\n\t\t" . 'if(null !== ($materialFieldID = (new MaterialFieldQuery($this->query))->materialID($this->id)->fieldID('.$fieldID.')->first())) {';
                    $code .= "\n\t\t\t" . '$gallery->materialFieldID($materialFieldID->id);';
                    $code .= "\n\t\t" . '}';
                    $code .= "\n\t\t" . 'return $gallery;';
                    $code .= "\n\t" . '}';

                    $methods[] = $code;
                }
            } catch (\Exception $e) {
                throw new \Exception($metadata->entity . ' cms field type for [' . $fieldName . '] not found');
            }
        }

        // Add method text to generator
        $this->generator->text(implode("\n", $methods));
    }
}
//[PHPCOMPRESSOR(remove,end)]
