<?php
namespace samsoncms\api;

use samsonframework\orm\QueryInterface;

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

    /** @var integer Primary key */
    public $PhotoID;

    /** @var integer Material identifier */
    public $MaterialID;

    /** @var integer MaterialField identifier */
    public $materialFieldid;

    /** @var integer Priority inside material relation */
    public $priority;

    /** @var string Path for picture */
    public $Path;

    /** @var string Name identifier  */
    public $Src;

    /** @var int Size picture*/
    public $size;

    /** @var timestamp The field with upload date picture */
    public $Loaded;

    /** @var string Description alt value for picture */
    public $Description;

    /** @var string Additional field name */
    public $Name;

    /** @var bool Internal existence flag */
    public $Active;
}
