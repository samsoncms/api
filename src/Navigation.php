<?php
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>
 * on 07.08.14 at 17:11
 */
namespace samsoncms\api;

use samson\activerecord\structure;
use samsonframework\orm\QueryInterface;

/**
 * SamsonCMS Navigation entity
 * @author Vitaly Egorov <egorov@samsonos.com>
 * @copyright 2014 SamsonOS
 */
class Navigation extends structure
{
    /** Store entity name */
    const ENTITY = __CLASS__;

    /** Entity field names constants for using in code */
    const F_PRIMARY = 'StructureID';
    const F_IDENTIFIER = 'Url';
    const F_DELETION = 'Active';
    const F_PARENT = 'ParentID';
    const F_PRIORITY = 'priority';
    const F_CREATED = 'Created';
    const F_MODIFIED = 'Modyfied';
    const F_DEF_MATERIAL = 'MaterialID';

    /** @var self[] Collection of child items */
    public $children = array();

    /**
     * Override standard view passing
     * @param string $prefix Prefix
     * @param array $restricted Collection of ignored entity fields
     * @return array Filled collection of key => values for view
     */
    public function toView($prefix = '', array $restricted = array())
    {
        return parent::toView(
            $prefix,
            array_merge(
                $restricted,
                array('parent', 'parents', 'children'
                )
            )
        );
    }
}
