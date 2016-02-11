<?php
namespace samsoncms\api\generator;

/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 11.02.16 at 18:07
 */
class Metadata
{
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
    /** @var string Generated entity fully qualified class name */
    public $className;

    // To be commented
    public $realNames = array();
    public $allFieldIDs = array();
    public $allFieldNames = array();
    public $allFieldValueColumns = array();
    public $localizedFieldIDs = array();
    public $notLocalizedFieldIDs = array();
    public $allFieldTypes = array();
    public $fieldDescriptions= array();

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
