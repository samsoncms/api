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
        $idOrUrl->add('FieldID', $nameOrID)->add('Name', $field);

        // Perform query
        $return = $query->className('field')->cond($idOrUrl)->first();

        // If only one argument is passed - return null, otherwise bool
        return func_num_args() > 1 ? $return == null : $return;
    }

    /** @return string Get additional field value field name depending on its type */
    public function valueFieldName()
    {
        switch ($this->Type) {
            case 7:
                return 'numeric_value';
                break;
            case 6:
                return 'key_value';
                break;
            default:
                return 'Value';
        }
    }
}
