<?php
namespace samsoncms\api;

use samsonframework\orm\QueryInterface;

/**
 * SamsonCMS additional field value table entity class
 * @package samson\cms
 */
class MaterialField extends \samson\activerecord\materialfield
{
    /**
     * Find additional field value database record by its material and field identifiers.
     * This is generic method that should be used in nested classes to find its
     * records by some its primary key value.
     *
     * @param QueryInterface $query Query object instance
     * @param string $materialID Material identifier
     * @param string $fieldID Additional field identifier
     * @param self $return Variable to return found database record
     * @param string $locale Locale identifier
     * @return bool|null|self  Field instance or null if 3rd parameter not passed
     */
    public static function byFieldIDAndMaterialID(
        QueryInterface $query,
        $materialID,
        $fieldID,
        self & $return = null,
        $locale = DEFAULT_LOCALE
    ) {
        $return = $query->className(__CLASS__)
            ->cond('MaterialID', $materialID)
            ->cond('FieldID', $fieldID)
            ->cond('locale', $locale)
            ->first();

        // If only one argument is passed - return null, otherwise bool
        return func_num_args() > 1 ? $return == null : $return;
    }
}
