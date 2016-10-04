<?php
//[PHPCOMPRESSOR(remove,start)]
namespace samsoncms\api\generator\metadata;

/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 22.03.16 at 19:15
 */
class GenericMetadata
{
    /** @var array Collection of all metadata instances */
    public static $instances = array();

    /** @var string Transliterated and CapsCase database entity name */
    public $entity;

    /** @var string Real entity name */
    public $entityName;

    /** @var string Fully qualified entity class name */
    public $entityClassName;

    /** Old ActiveRecord fields */
    /** @deprecated */
    public $arSelect = array();
    /** @deprecated */
    public $arMap = array();
    /** @deprecated */
    public $arAttributes = array();
    /** @deprecated */
    public $arTableAttributes = array();
    /** @deprecated */
    public $arTypes = array();
    /** @deprecated */
    public $arFrom = array();
    /** @deprecated */
    public $arGroup = array();
    /** @deprecated */
    public $arRelationAlias = array();
    /** @deprecated */
    public $arRelationType = array();
    /** @deprecated */
    public $arRelations = array();

    public function __construct(string $className)
    {
        self::$instances[ltrim($className, '\\')] = $this;
        $this->entityClassName = $className;
    }
}
//[PHPCOMPRESSOR(remove,end)]
