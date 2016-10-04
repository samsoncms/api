<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: VITALYIEGOROV
 * Date: 08.12.15
 * Time: 23:11
 */
namespace samsoncms\api\query;

use samsoncms\api\generated\Material;
use samsonframework\orm\ArgumentInterface;

/**
 * TODO: We have create a Record class that lays behind Entity-Generic and we store entity fields in fieldIDs static variable
 * which cannot be accessed in parent function calls like orderBy, applySorting, to get intermediary static class variables
 * from Generic class. This affect code duplication of method in Record to Generic to give access to static class fields
 * described in fieldIDs.
 */

/**
 * Material with additional fields query.
 *
 * @package samsoncms\api
 */
class Generic extends Record
{
    /** @var string Table class name */
    public static $identifier = Material::class;

    /** @var string Database table name */
    public static $tableName = 'material';

    /** @var string Table primary field name */
    public static $primaryFieldName = Material::F_PRIMARY;

       /** @var array Collection of all supported entity fields */
    public static $fieldIDs = array(
        Material::F_PRIMARY=> Material::F_PRIMARY,
        Material::F_PRIORITY => Material::F_PRIORITY,
        Material::F_URL => Material::F_URL,
        Material::F_DELETION => Material::F_DELETION,
        Material::F_PUBLISHED => Material::F_PUBLISHED,
        Material::F_PARENTID => Material::F_PARENTID,
        Material::F_CREATED => Material::F_CREATED,
        Material::F_MODYFIED => Material::F_MODYFIED,
    );

    /** @var array Collection of all supported entity fields */
    public static $fieldNames = array(
        Material::F_PRIMARY => Material::F_PRIMARY,
        Material::F_PRIORITY => Material::F_PRIORITY,
        Material::F_URL => Material::F_URL,
        Material::F_DELETION => Material::F_DELETION,
        Material::F_PUBLISHED => Material::F_PUBLISHED,
        Material::F_PARENTID => Material::F_PARENTID,
        Material::F_CREATED => Material::F_CREATED,
        Material::F_MODYFIED => Material::F_MODYFIED
    );

    /** @var array Collection of entity field types */
    public static $fieldTypes = [
        'MaterialID' => 'int',
        'parent_id' => 'int',
        'priority' => 'int',
        'Name' => 'string',
        'Url' => 'string',
        'Created' => 'int',
        'Modyfied' => 'int',
        'UserID' => 'int',
        'Draft' => 'int',
        'type' => 'int',
        'Published' => 'int',
        'Active' => 'int',
        'system' => 'int',
        'remains' => 'float',
    ];

    /** @var array Collection of entity field database types */
    public static $fieldDataTypes = [
        'MaterialID' => 'int',
        'parent_id' => 'int',
        'priority' => 'int',
        'Name' => 'varchar',
        'Url' => 'varchar',
        'Created' => 'datetime',
        'Modyfied' => 'timestamp',
        'UserID' => 'int',
        'Draft' => 'int',
        'type' => 'int',
        'Published' => 'int',
        'Active' => 'int',
        'system' => 'int',
        'remains' => 'float',
    ];

    /** @var array Collection of entity field database default values */
    public static $fieldDefaults = [
        'MaterialID' => '',
        'parent_id' => '',
        'priority' => 0,
        'Name' => '',
        'Url' => '',
        'Created' => '',
        'Modyfied' => 'CURRENT_TIMESTAMP',
        'UserID' => '',
        'Draft' => 0,
        'type' => 0,
        'Published' => '',
        'Active' => '',
        'system' => 0,
        'remains' => 0,
    ];

    /** @var array Collection of entity field database is nullable values */
    public static $fieldNullable = [
        'MaterialID' => 'NO',
        'parent_id' => 'YES',
        'priority' => 'NO',
        'Name' => 'NO',
        'Url' => 'NO',
        'Created' => 'YES',
        'Modyfied' => 'NO',
        'UserID' => 'YES',
        'Draft' => 'NO',
        'type' => 'NO',
        'Published' => 'YES',
        'Active' => 'YES',
        'system' => 'NO',
        'remains' => 'NO',
    ];

    /** @var string Entity navigation identifiers */
    public static $navigationIDs = array();

    /**
     * Add primary field query condition.
     *
     * @param string $value Field value
     * @param string $relation @see ArgumentInterface types
     *
     * @return $this Chaining
     * @see Material::where()
     */
    public function primary($value, $relation = ArgumentInterface::EQUAL)
    {
        return $this->where(Material::F_PRIMARY, $value, $relation);
    }

    /**
     * Add identifier field query condition.
     *
     * @param string $value Field value
     * @param string $relation @see ArgumentInterface types
     *
     * @return $this Chaining
     * @see Material::where()
     */
    public function identifier($value, $relation = ArgumentInterface::EQUAL)
    {
        return $this->where(Material::F_IDENTIFIER, $value, $relation);
    }

    /**
     * Add active flag condition.
     *
     * @param bool $value Field value
     * @param string $relation @see ArgumentInterface types
     *
     * @return $this Chaining
     * @see Material::where()
     */
    public function active($value, $relation = ArgumentInterface::EQUAL)
    {
        return $this->where(Material::F_DELETION, $value, $relation);
    }

    /**
     * Add entity published field query condition.
     *
     * @param string $value Field value
     * @param string $relation @see ArgumentInterface types
     *
     * @return $this Chaining
     * @see Material::where()
     */
    public function published($value, $relation = ArgumentInterface::EQUAL)
    {
        return $this->where(Material::F_PUBLISHED, $value, $relation);
    }

    /**
     * Add entity creation field query condition.
     *
     * @param string $value Field value
     * @param string $relation @see ArgumentInterface types
     *
     * @return $this Chaining
     * @see Material::where()
     */
    public function created($value, $relation = ArgumentInterface::EQUAL)
    {
        return $this->where(Material::F_CREATED, $this->convertToDateTime($value), $relation);
    }

    /**
     * Add entity modification field query condition.
     *
     * @param string $value Field value
     * @param string $relation @see ArgumentInterface types
     * @return $this Chaining
     * @see Material::where()
     */
    public function modified($value, $relation = ArgumentInterface::EQUAL)
    {
        return $this->where(Material::F_MODIFIED, $this->convertToDateTime($value), $relation);
    }

    /**
     * Set field for sorting.
     * TODO: We have code duplication in Record::orderBy() due to late static binding
     * @param string $fieldName Additional field name
     * @param string $order     Sorting order
     *
     * @return $this Chaining
     */
    public function orderBy($fieldName, $order = 'ASC')
    {
        if (in_array($fieldName, self::$fieldIDs)) {
            $this->orderBy = array($fieldName, $order);
        }

        return $this;
    }

    /**
     * Add sorting to entity identifiers.
     * TODO: We have code duplication in Record::orderBy() due to late static binding
     * @param array  $entityIDs
     * @param string $fieldName Additional field name for sorting
     * @param string $order     Sorting order(ASC|DESC)
     *
     * @return array Collection of entity identifiers ordered by additional field value
     */
    protected function applySorting(array $entityIDs, $fieldName, $order = 'ASC')
    {
        if (array_key_exists($fieldName, self::$fieldIDs)) {
            // Order by parent fields
            return $this->query
                ->entity(static::$identifier)
                ->where(static::$primaryFieldName, $entityIDs)
                ->orderBy($fieldName, $order)
                ->fields(static::$primaryFieldName);
        } else { // Nothing is changed
            return $entityIDs;
        }
    }
}
