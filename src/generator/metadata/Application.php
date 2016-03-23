<?php
//[PHPCOMPRESSOR(remove,start)]
namespace samsoncms\api\generator\metadata;

/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 22.03.16 at 19:15
 */
class Application extends Generic
{
    /** @var string Generate application from current entity */
    public $generateApplication;

    /** @var string Show application from current entity */
    public $showApplication;

    /** @var string Icon for application from current entity */
    public $iconApplication;

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
