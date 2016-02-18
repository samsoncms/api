<?php
namespace samsoncms\api;

/**
 * SamsonCMS additional field value table entity class
 * @package samson\cms
 */
class NavigationField extends \samson\activerecord\structurefield
{
    /** Store entity name */
    const ENTITY = __CLASS__;

    /** Entity field names constants for using in code */
    const F_PRIMARY = 'StructureFieldID';
    const F_STRUCTURE = 'StructureID';
    const F_FIELD = 'FieldID';
    const F_MODIFIED = 'Modyfied';
    const F_DELETION = 'Active';
}
