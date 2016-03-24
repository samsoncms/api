<?php
//[PHPCOMPRESSOR(remove,start)]
namespace samsoncms\api\generator\metadata;

/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 22.03.16 at 19:15
 */
class Virtual extends Generic
{
    /** @var string Real database entity identifier */
    public $entityID;

    /** @var string Real database entity name */
    public $entityRealName;

    /** @var int Parent entity identifier  */
    public $parentID;

    /** @var self Metadata parent entity name */
    public $parent;

    /** @var array Collection of entity field default values */
    public $defaultValues = array();

    /** @var array Collection of entity additional field identifier to its name */
    public $allFieldIDs = array();

    /** @var array Collection of entity additional field name to its identifier */
    public $allFieldNames = array();

    /** @var array Collection of entity additional field id to its value column name */
    public $allFieldValueColumns = array();

    /** @var array Collection of entity additional field id that are localized */
    public $localizedFieldIDs = array();

    /** @var array Collection of entity additional field id that are NOT localized */
    public $notLocalizedFieldIDs = array();

    /** @var array Collection of entity additional field id to its real names */
    public $realNames = array();

    /** @var array Collection of entity additional field id to its php type */
    public $allFieldTypes = array();

    /** @var array Collection of entity additional field id to its SamsonCMS type identifier */
    public $allFieldCmsTypes = array();

    /** @var array Collection of entity additional field id to its description */
    public $fieldDescriptions = array();

    /** @var array WTF? Collection of entity additional field id to its description */
    public $fieldRawDescriptions = array();

    /** @var array Colllection of database strucutre row data */
    public $structureRow;
}
//[PHPCOMPRESSOR(remove,end)]
