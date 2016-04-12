<?php
//[PHPCOMPRESSOR(remove,start)]
namespace samsoncms\api\generator\metadata;

/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 23.03.16 at 16:21
 */
class ApplicationMetadata extends \samsoncms\api\generator\metadata\VirtualMetadata
{
    /** @var string Generate application from current entity */
    public $generateApplication;

    /** @var string Show application from current entity */
    public $showApplication;

    /** @var string Icon for application from current entity */
    public $iconApplication;

    /** @var string Application name */
    public $name;

    /** @var string Application description */
    public $description;

    /** @var string Application identifier */
    public $identifier;

    /** @var string Icon for application from current entity */
    public $renderMainApplication;

    /** @var array Collection of entity nested entities identifiers */
    public $childNavigationIDs = array();

    /** @var array Collection of entity additional field id to its value column name */
    public $showFieldsInList = array();

    /** @var array Collection of application custom additional field renderer */
    public $customTypeFields = array();
}
//[PHPCOMPRESSOR(remove,end)]
