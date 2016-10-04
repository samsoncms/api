<?php
//[PHPCOMPRESSOR(remove,start)]
namespace samsoncms\api\generator\metadata;

/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 22.03.16 at 19:15
 */
class GalleryMetadata extends GenericMetadata
{
    /** @var string Real database gallery field name */
    public $realName;

    /** @var int Field identifier  */
    public $fieldID;

    /** @var int Parent entity identifier  */
    public $parentID;

    /** @var int Parent entity classname  */
    public $parentClassName;
}
//[PHPCOMPRESSOR(remove,end)]