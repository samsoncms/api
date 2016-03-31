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

    /** @var string Fully qualified entity class name */
    public $entityClassName;

    /** Old ActiveRecord fields */
    public $arSelect = array();
    public $arMap = array();
    public $arAttributes = array();
    public $arFrom = array();
    public $arGroup = array();
    public $arRelationAlias = array();
    public $arRelationType = array();
    public $arRelations = array();
}
//[PHPCOMPRESSOR(remove,end)]
