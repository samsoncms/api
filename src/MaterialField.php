<?php
namespace samsoncms\api;

use samsonframework\orm\QueryInterface;

/**
 * SamsonCMS additional field value table entity class
 * @package samson\cms
 */
class MaterialField extends \samson\activerecord\materialfield
{
    /** @var integer Primary key */
    public $FieldID;

    /** @var bool Internal existence flag */
    public $Active;

    /** @var integer Material identifier */
    public $MaterialID;

    /** @var string Additional field value */
    public $Value;

    /** @var string Additional field value */
    public $numeric_value;

    /** @var string Additional field value */
    public $key_value;

    /** @var string Additional field locale */
    public $locale;

    /**
     * Get identifiers collection by field identifier and its value.
     * Method is optimized for performance.
     *
     * @param QueryInterface $query Database query instance
     * @param string $materialID Additional field identifier
     * @param string $fieldValue Additional field value for searching
     * @param array|null $return Variable where request result would be returned
     * @param array $materialIDs Collection of material identifiers for filtering query
     * @return bool|array True if material entities has been found and $return is passed
     *                      or identifiers collection if only two parameters is passed.
     */
    public static function idsByMaterialId(QueryInterface $query, $materialID, &$return = array())
    {
        // Get material identifiers by field
        $return = $query->entity(CMS::MATERIAL_FIELD_RELATION_ENTITY)
            ->where('MaterialID', $materialID)
            ->where('Active', 1)
            ->fields();

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
     * @param self[]|null $return Variable to return found database record
     * @param string $locale Locale identifier
     * @return bool|null|self[]  Field instance or null if 3rd parameter not passed
     */
    public static function byMaterialID(
        QueryInterface $query,
        $materialID,
        &$return = null,
        $locale = DEFAULT_LOCALE
    ) {
        $return = $query->entity(get_called_class())
            ->where('MaterialID', $materialID)
            ->where('FieldID', $fieldID)
            ->where('locale', $locale)
            ->exec();

        // If only one argument is passed - return null, otherwise bool
        return func_num_args() > 1 ? $return == null : $return;
    }

    /**
     * Find additional field value database record by its material and field identifiers.
     * This is generic method that should be used in nested classes to find its
     * records by some its primary key value.
     *
     * @param QueryInterface $query Query object instance
     * @param string $materialID Material identifier
     * @param string $fieldID Additional field identifier
     * @param self[]|null $return Variable to return found database record
     * @param string $locale Locale identifier
     * @return bool|null|self[]  Field instance or null if 3rd parameter not passed
     */
    public static function byFieldIDAndMaterialID(
        QueryInterface $query,
        $materialID,
        $fieldID,
        &$return = null,
        $locale = DEFAULT_LOCALE
    ) {
        $return = $query->entity(get_called_class())
            ->where('MaterialID', $materialID)
            ->where('FieldID', $fieldID)
            ->where('locale', $locale)
            ->exec();

        // If only one argument is passed - return null, otherwise bool
        return func_num_args() > 1 ? $return == null : $return;
    }
}
