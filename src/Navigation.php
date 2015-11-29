<?php
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>
 * on 07.08.14 at 17:11
 */
namespace samsoncms\api;

use samson\activerecord\structure;

/**
 * SamsonCMS Navigation entity
 * @author Vitaly Egorov <egorov@samsonos.com>
 * @copyright 2014 SamsonOS
 */
class Navigation extends structure
{
    /** @var string Navigation string identifier */
    public $url;

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
        return parent::toView($prefix, array_merge($this->restricted, array('parent', 'parents', 'children')));
    }

    /**
     * Material query injection
     * @param \samson\activerecord\dbQuery $query Query object
     */
    public function materialsHandlers(&$query)
    {
        $query->join('gallery');
    }
}
