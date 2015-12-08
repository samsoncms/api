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
     * Find additional field value records by its material identifiers.
     *
     * @param QueryInterface $query Query object instance
     * @param string $materialID Material identifier
     * @param self[]|null $return Variable to return found database record
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
            ->where('MaterialID', $materialID)
            ->where('Active', 1)
            ->where('locale', $locale)
            ->exec();

        // If only one argument is passed - return null, otherwise bool
        return func_num_args() > 1 ? sizeof($return) : $return;
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
            ->where('Active', 1)
            ->exec();

        // If only one argument is passed - return null, otherwise bool
        return func_num_args() > 1 ? sizeof($return): $return;
    }
}
