<?php
namespace samsoncms\api\generator;

/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 11.02.16 at 18:07
 */
class Metadata
{
    /** Default type of metadata */
    const TYPE_DEFAULT = 0;

    /** Nested materials type of metadata */
    const TYPE_NESTED_MATERIAL = 1;

    /** Table type of metadata */
    const TYPE_TABLE = 2;

    /** @var array List of types */
    public static $types = array(
        self::TYPE_DEFAULT,
        self::TYPE_NESTED_MATERIAL,
        self::TYPE_TABLE,
    );

    /**
     * Metadata constructor.
     * @param int $type
     */
    public function __construct($type = self::TYPE_DEFAULT)
    {
        $this->type = $type;
    }

    /** @var string Type of entity */
    public $type;
    /** @var string Transliterated and CapsCase entity name */
    public $entity;
    /** @var string Real database entity identifier */
    public $entityID;
    /** @var string Real database entity name */
    public $entityRealName;
    /** @var int Parent entity identifier  */
    public $parentID;
    /** @var $this Metadata parent entity name */
    public $parent;
    /** @var string Generate application from current entity */
    public $generateApplication;
    /** @var string Show application from current entity */
    public $showApplication;
    /** @var string Icon for application from current entity */
    public $iconApplication;
    /** @var string Icon for application from current entity */
    public $renderMainApplication;

    // To be commented
    public $realNames = array();
    public $allFieldIDs = array();
    public $allFieldNames = array();
    public $allFieldValueColumns = array();
    public $localizedFieldIDs = array();
    public $notLocalizedFieldIDs = array();
    public $allFieldTypes = array();
    public $allFieldCmsTypes = array();
    public $fieldDescriptions = array();
    public $fieldRawDescriptions = array();
    public $childNavigationIDs = array();
    public $showFieldsInList = array();

    // Old AR fields
    public $arSelect = array();
    public $arMap = array();
    public $arAttributes = array();
    public $arFrom = array();
    public $arGroup = array();
    public $arRelationAlias = array();
    public $arRelationType = array();
    public $arRelations = array();
}
