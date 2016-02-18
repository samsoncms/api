<?php
namespace samsoncms\api;

use samsonframework\orm\QueryInterface;

/**
 * SamsonCMS additional field value table entity class
 * @package samson\cms
 */
class MaterialField extends \samson\activerecord\materialfield
{
    /** Store entity name */
    const ENTITY = __CLASS__;

    /** Entity field names constants for using in code */
    const F_PRIMARY = 'MaterialFieldID';
    const F_DELETION = 'Active';
    const F_LOCALE = 'locale';
    const F_VALUE = 'Value';
    const F_NUMERIC = 'numeric_value';
    const F_KEY = 'key_value';
    const F_MATERIALID = 'MaterialID';
    const F_FIELDID = 'FieldID';

    /**
     * Find additional field value records by its material identifiers.
     *
     * @param QueryInterface $query Query object instance
     * @param string $materialID Material identifier
     * @param mixed $return Variable to return found database record
     * @param string $locale Locale identifier
     * @return bool|self[]  Field instance or null if 3rd parameter not passed
     */
    public static function byMaterialID(
        QueryInterface $query,
        $materialID,
        &$return = null,
        $locale = DEFAULT_LOCALE
    ) {
        $return = $query->entity(get_called_class())
            ->where(Material::F_PRIMARY, $materialID)
            ->where(Material::F_DELETION, true)
            ->where(self::F_LOCALE, $locale)
            ->exec();

        // If only one argument is passed - return null, otherwise bool
        return func_num_args() > 2 ? sizeof($return) : $return;
    }

    /**
     * Find additional field value database record by its material and field identifiers.
     * This is generic method that should be used in nested classes to find its
     * records by some its primary key value.
     *
     * @param QueryInterface $query Query object instance
     * @param string $materialID Material identifier
     * @param string $fieldID Additional field identifier
     * @param mixed $return Variable to return found database record
     * @param string $locale Locale identifier
     * @return bool|null|self  Field instance or null if 3rd parameter not passed
     */
    public static function byFieldIDAndMaterialID(
        QueryInterface $query,
        $materialID,
        $fieldID,
        &$return = null,
        $locale = null
    ) {
        $return = $query->entity(get_called_class())
            ->where(Material::F_PRIMARY, $materialID)
            ->where(Field::F_PRIMARY, $fieldID)
            ->where(self::F_LOCALE, $locale)
            ->where(Material::F_DELETION, 1)
            ->exec();

        // If only one argument is passed - return null, otherwise bool
        return func_num_args() > 3 ? sizeof($return): $return;
    }
}
