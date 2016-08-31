<?php
namespace samsoncms\api;

use samson\activerecord\Gallery;

/**
 * SamsonCMS additional field value table entity class
 * @package samson\cms
 */
class GalleryField extends Gallery
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
