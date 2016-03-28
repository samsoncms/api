<?php
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 10.02.16 at 21:20
 */
namespace samsoncms\api\field;

/**
 * Additional fields table row.
 * This class is needed for generation of specific table row classes
 * with defined fields.
 *
 * @package samsoncms\api\field
 */
class Row
{
    /** @var array Collection of additional fields identifiers */
    public static $fieldIDs = array();

    /** @var array Field table row fields collection */
    protected $collection;

    /** @var int Material primary identifier */
    public $primary;

    /**
     * Row constructor.
     *
     * @param int   $primary Material entity identifier
     * @param array $collection Collection of row additional field values
     */
    public function __construct($primary, array $collection)
    {
        $this->primary = $primary;
        $this->collection = $collection;

        // Set row fields
        foreach ($collection as $key => $value) {
            if ($key === 'primary') {
                continue;
            }
            $this->$key = $value;
        }
    }
}
