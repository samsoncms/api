<?php
namespace samsoncms\api;

/**
 * SamsonCMS Material to navigation relation entity
 * @package samson\cms
 */
class NavigationMaterial extends \samson\activerecord\StructureMaterial
{
    /** Store entity name */
    const ENTITY = __CLASS__;

    /** Entity field names constants for using in code */
    const F_PRIMARY = 'StructureMaterialID';
    const F_ACTIVE = 'Active';
    const F_STRUCTUREID = 'StructureID';
    const F_MATERIALID = 'MaterialID';
    const F_MODIFIED = 'Modified';
}
