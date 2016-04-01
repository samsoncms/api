<?php
//[PHPCOMPRESSOR(remove,start)]
namespace samsoncms\api\generator\metadata;

/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 22.03.16 at 19:15
 */
class RealMetadata extends GenericMetadata
{
    /** @var array Collection of entity field names */
    public $fields = array();

    /** @var array Collection of real entity field names to generated */
    public $fieldNames = array();

    /** @var array Collection of entity field types by names */
    public $types = array();

    /** @var array Collection of SamsonCMS entity field types by names */
    public $internalTypes = array();

    /** @var array Collection of entity field default values by names */
    public $defaults = array();
}
//[PHPCOMPRESSOR(remove,end)]
