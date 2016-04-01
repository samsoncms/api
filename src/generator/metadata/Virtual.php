<?php
//[PHPCOMPRESSOR(remove,start)]
namespace samsoncms\api\generator\metadata;

/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 22.03.16 at 19:15
 */
class Virtual extends RealMetadata
{
    /** Virtual entity types */
    const TYPE_STRUCTURE = 0;
    const TYPE_TABLE = 2;
    const TYPE_RELATED = 1;

    /** @var string Real database entity identifier */
    public $entityID;

    /** @var string Real database entity name */
    public $entityRealName;

    /** @var int Parent entity identifier  */
    public $parentID;

    /** @var int Virtual entity type */
    public $type;

    /** @var self Metadata parent entity name */
    public $parent;

    /** @var array Collection of entity field default values */
    public $defaultValues = array();

    /** @var array Collection of entity additional field identifier to its name */
    public $fields = array();

    /** @var array Collection of entity additional field name to its identifier */
    public $fieldNames = array();

    /** @var array Collection of entity additional field id to its php type */
    public $types = array();

    /** @var array Collection of entity additional field id to its value column name */
    public $allFieldValueColumns = array();

    /** @var array Collection of entity additional field id that are localized */
    public $localizedFieldIDs = array();

    /** @var array Collection of entity additional field id that are NOT localized */
    public $notLocalizedFieldIDs = array();

    /** @var array Collection of entity additional field id to its real names */
    public $realNames = array();

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
