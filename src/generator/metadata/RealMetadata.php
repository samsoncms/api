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
    protected $fields = array();

    /** @var array Collection of entity field types by names */
    protected $types = array();

    /** @var array Collection of SamsonCMS entity field types by names */
    protected $internalTypes = array();

    /** @var array Collection of entity field default values by names */
    protected $defaults = array();
}
//[PHPCOMPRESSOR(remove,end)]
