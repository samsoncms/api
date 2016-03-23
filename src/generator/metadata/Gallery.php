<?php
//[PHPCOMPRESSOR(remove,start)]
namespace samsoncms\api\generator\metadata;

/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 22.03.16 at 19:15
 */
class Gallery extends Generic
{
    /** @var string Real database gallery field name */
    public $realName;

    /** @var int Field identifier  */
    public $fieldID;

    /** @var int Parent entity identifier  */
    public $parentID;
}
//[PHPCOMPRESSOR(remove,end)]