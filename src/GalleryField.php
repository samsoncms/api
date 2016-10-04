<?php
namespace samsoncms\api;

/**
 * SamsonCMS additional field value table entity class
 * @package samson\cms
 */
class GalleryField extends \samson\activerecord\gallery
{
    /** Entity field names constants for using in code */
    const F_PRIMARY = 'PhotoID';
    const F_DELETION = 'Active';
    const F_NAME = 'Name';
    const F_DESCRIPTION = 'Description';
    const F_LOADED = 'Loaded';
    const F_SIZE = 'Size';
    const F_SRC = 'Src';
    const F_PATH = 'Path';
    const F_PRIORITY = 'Priority';
}
