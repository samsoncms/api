<?php
/**
 * Created by PhpStorm.
 * User: VITALYIEGOROV
 * Date: 09.12.15
 * Time: 12:10
 */
namespace samsoncms\api;

use samson\activerecord\StructureMaterial;
use samsonframework\orm\DatabaseInterface;
use samsonframework\orm\QueryInterface;

/**
 * SamsonCMS Entity that has relation to specific navigation
 * and though has additional fields.
 *
 * @package samsoncms\api
 */
class Entity extends Material
{
    /** @var array Collection of navigation identifiers */
    protected static $navigationIDs = array();

    /** @var array Collection of localized additional fields identifiers */
    protected static $fieldIDs = array();

    /** @var array Collection of additional fields value column names */
    protected static $fieldValueColumns = array();

    /** @var array Collection of localized additional fields identifiers */
    protected static $localizedFieldIDs = array();

    /** @var string Locale */
    protected $locale;

    /**
     * Entity constructor.
     *
     * @param null|string            $locale Locale
     * @param null|DatabaseInterface $database
     * @param null|QueryInterface    $query
     */
    public function __construct($locale = null, $database = null, $query = null)
    {
        $this->locale = $locale;

        parent::__construct($database, $query);
    }

    /**
     * Override default entity saving
     */
    public function save()
    {
        // Format url
        $this->Url = str_replace(' ', '-', utf8_translit($this->Url));

        parent::save();

        $relationEntity = CMS::MATERIAL_FIELD_RELATION_ENTITY;
        foreach (static::$fieldIDs as $fieldID => $fieldName) {
            $type = static::$fieldValueColumns[$fieldID];

            // If material field relation exists use it or create new
            $materialField = null;
            if (!$this->query
                ->entity($relationEntity)
                ->where(Field::F_PRIMARY, $fieldID)
                ->where(Material::F_PRIMARY, $this->id)
                ->first($materialField)
            ) {
                /** @var Field $materialfield */
                $materialField = new $relationEntity();
                $materialField->Active = 1;
                $materialField->MaterialID = $this->id;
                $materialField->FieldID = $fieldID;
            }

            // Set locale only if this field is localized
            if (array_key_exists($fieldID, static::$localizedFieldIDs)) {
                $materialField->locale = $this->locale;
            }

            $materialField->$type = $this->$fieldName;
            $materialField->save();
        }
        $this->attachTo(static::$navigationIDs);
    }

    /**
     * Add entity structure relation
     * @param integer|array $structureID Structure identifier or their collection
     */
    public function attachTo($structureID)
    {
        $relationEntity = CMS::MATERIAL_NAVIGATION_RELATION_ENTITY;

        foreach (is_array($structureID) ? $structureID : array($structureID) as $structureID) {
            // Check if we do not have this relation already
            if ($this->query
                    ->entity($relationEntity)
                    ->where(NavigationMaterial::F_PRIMARY, $structureID)
                    ->where(self::F_PRIMARY, $this->id)
                    ->count() === 0
            ) {
                /** @var StructureMaterial $structureMaterial */
                $structureMaterial = new $relationEntity();
                $structureMaterial->Active = 1;
                $structureMaterial->MaterialID = $this->id;
                $structureMaterial->StructureID = $structureID;
                $structureMaterial->save();
            }
        }
    }
}
