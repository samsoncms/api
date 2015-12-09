<?php
namespace samsoncms\api;

use samsonframework\orm\Condition;
use samsonframework\orm\QueryInterface;

/**
 * SamsonCMS additional field table entity class
 * @package samson\cms
 */
class Field extends \samson\activerecord\field
{
    /** Store entity name */
    const ENTITY = __CLASS__;

    /** Entity field names constants for using in code */
    const F_PRIMARY = 'FieldID';
    const F_IDENTIFIER = 'Name';
    const F_DELETION = 'Active';
    const F_DEFAULT = 'Value';
    const F_LOCALIZED = 'local';

    /** Additional field storing text value */
    const TYPE_TEXT = 0;
    /** Additional field storing resource link */
    const TYPE_RESOURCE = 1;
    /** Additional field storing options value */
    const TYPE_OPTIONS = 4;
    /** Additional field storing other entity identifier */
    const TYPE_ENTITYID = 6;
    /** Additional field storing numeric value */
    const TYPE_NUMERIC = 7;
    /** Additional field storing long text value */
    const TYPE_LONGTEXT = 8;
    /** Additional field storing datetime value */
    const TYPE_DATETIME = 10;
    /** Additional field storing boolean value */
    const TYPE_BOOL = 11;

    /** @var string Additional field value type */
    public $Type;

    /** @var string Additional field name */
    public $Name;

    /** @var string Default field value */
    public $Value;

    /** @var bool Flag is localized */
    public $local;

    /** @var bool Internal existence flag */
    public $Active;

    /**
     * Get current entity instances collection by their identifiers.
     * Method can accept different query executors.
     *
     * @param QueryInterface $query Database query
     * @param string|array $fieldIDs Field identifier or their colleciton
     * @param self[]|array|null $return Variable where request result would be returned
     * @param string $executor Method name for query execution
     * @return bool|self[] True if material entities has been found and $return is passed
     *                      or self[] if only two parameters is passed.
     */
    public static function byIDs(QueryInterface $query, $fieldIDs, &$return = array(), $executor = 'exec')
    {
        $return = $query->entity(get_called_class())
            ->where('FieldID', $fieldIDs)
            ->where('Active', 1)
            ->orderBy('priority')
            ->$executor();

        // If only one argument is passed - return null, otherwise bool
        return func_num_args() > 2 ? sizeof($return) : $return;
    }

    /**
     * Get current entity identifiers collection by navigation identifier.
     *
     * @param QueryInterface $query Database query
     * @param string $navigationID Navigation identifier
     * @param array $return Variable where request result would be returned
     * @param array $materialIDs Collection of material identifiers for filtering query
     * @return bool|array True if field entities has been found and $return is passed
     *                      or collection of identifiers if only two parameters is passed.
     */
    public static function idsByNavigationID(
        QueryInterface $query,
        $navigationID,
        &$return = array(),
        $materialIDs = null
    ) {
        // Prepare query
        $query->entity(CMS::FIELD_NAVIGATION_RELATION_ENTITY)
            ->where('StructureID', $navigationID)
            ->where('Active', 1);

        // Add material identifier filter if passed
        if (isset($materialIDs)) {
            $query->where('MaterialID', $materialIDs);
        }

        // Perform database query and get only material identifiers collection
        $return = $query->fields('FieldID');

        // If only one argument is passed - return null, otherwise bool
        return func_num_args() > 2 ? sizeof($return) : $return;
    }

    /**
     * Get current entity instances collection by navigation identifier.
     *
     * @param QueryInterface $query Database query
     * @param string $navigationID Navigation identifier
     * @param self[]|array|null $return Variable where request result would be returned
     * @return bool|self[] True if field entities has been found and $return is passed
     *                      or self[] if only two parameters is passed.
     */
    public static function byNavigationID(QueryInterface $query, $navigationID, &$return = array())
    {
        /** @var array $fieldIDs Collection of entity identifiers filtered by additional field */
        $fieldIDs = null;
        if (static::idsByNavigationID($query, $navigationID, $fieldIDs)) {
            static::byIDs($query, $fieldIDs, $return);
        }

        // If only one argument is passed - return null, otherwise bool
        return func_num_args() > 2 ? sizeof($return) : $return;
    }

    /**
     * Find additional field database record by Name.
     * This is generic method that should be used in nested classes to find its
     * records by some its primary key value.
     *
     * @param QueryInterface $query Query object instance
     * @param string $name Additional field name
     * @param self $return Variable to return found database record
     * @return bool|null|self  Field instance or null if 3rd parameter not passed
     */
    public static function byName(QueryInterface $query, $name, self & $return = null)
    {
        // Get field record by name column
        $return = static::oneByColumn($query, 'Name', $name);

        // If only one argument is passed - return null, otherwise bool
        return func_num_args() > 1 ? $return == null : $return;
    }

    /**
     * Find additional field database record by Name or ID.
     * This is generic method that should be used in nested classes to find its
     * records by some its primary key value.
     *
     * @param QueryInterface $query Query object instance
     * @param string $nameOrID Additional field name or identifier
     * @param self $return Variable to return found database record
     * @return bool|null|self  Field instance or null if 3rd parameter not passed
     */
    public static function byNameOrID(QueryInterface $query, $nameOrID, self & $return = null)
    {
        // Create id or URL condition
        $idOrUrl = new Condition('OR');
        $idOrUrl->add('FieldID', $nameOrID)->add('Name', $nameOrID);

        // Perform query
        $return = $query->entity(get_called_class())->whereCondition($idOrUrl)->first();

        // If only one argument is passed - return null, otherwise bool
        return func_num_args() > 1 ? $return == null : $return;
    }

    /**
     * If this field has defined key=>value set.
     *
     * @return array|mixed Grouped collection of field key => value possible values or value for key passed.
     */
    public function options($key = null)
    {
        $types = array();
        // Convert possible field values to array
        foreach (explode(',', $this->Value) as $typeValue) {
            // Split view and value
            $typeValue = explode(':', $typeValue);

            // Store to key => value collection
            $types[$typeValue[0]] = $typeValue[1];
        }

        return isset($key) ? $types[$key] : $types;
    }

    /** @return string Get additional field value field name depending on its type */
    public function valueFieldName()
    {
        switch ($this->Type) {
            case self::TYPE_NUMERIC:
                return 'numeric_value';
            case self::TYPE_ENTITYID:
                return 'key_value';
            default:
                return 'Value';
        }
    }

    /** @return bool True if field is localized */
    public function localized()
    {
        return $this->local == 1;
    }
}
