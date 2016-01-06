<?php
/**
 * Created by PhpStorm.
 * User: myslyvyi
 * Date: 05.01.2016
 * Time: 15:25
 */
namespace samsoncms\api;

use samson\cms\CMSGallery;
use samsoncms\api\MaterialField;
use samsonframework\orm\QueryInterface;

/***
 *  Gallery additional field for material.
 *  This class enables getting all information about additional fields gallery for specific material.
 */
class Gallery
{
    /** @var integer materialFieldId Table materialField identifier */
    protected $materialFieldId = null;

    /** @var QueryInterface Database query interface */
    protected $query;

    /**
     * Constructor Gallery.
     * This constructor finds identifier additional field gallery from database record its material and field identifiers.
     *
     * @param QueryInterface $query Database query interface
     * @param integer $materialId material identifier
     * @param integer $fieldId field identifier
     */
    public function __construct(QueryInterface $query, $materialId, $fieldId)
    {
        /** @var object $materialField additional field value database record*/
        $materialField = null;

        //set query interface
        $this->query = $query;

        // Checking params by type
        if (is_int($materialId) && is_int($fieldId)) {
            //Find additional field value database record by its material and field identifiers.
            if (MaterialField::byFieldIDAndMaterialID($query, $materialId, $fieldId, $materialField)) {
                //Getting first record
                $materialField = array_shift($materialField);
                //Set materialFieldId
                $this->materialFieldId = $materialField->id;
            }
        }
    }

    /**
     * Check on empty gallery. If materialFieldId = 0 and quantity images not more 1 then material not has images.
     *
     * @return boolean
     **/
    public function hasImages()
    {
        if (isset($this->materialFieldId)) {
            // Getting quantity images, if quantity more 0 then material has images
            if ($this->query
            ->entity(CMS::MATERIAL_IMAGES_RELATION_ENTITY)
            ->cond(Field::F_DELETION, 1)
            ->where(MaterialField::F_PRIMARY, $this->materialFieldId)
            ->count() > 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get collection of images for material by gallery additional field selector. If none is passed
     * all images from gallery table would be returned empty array.
     *
     * @return array
     */
    public function getImages()
    {
        /** @var $images[] Get material images for this gallery */
        $images = array();

        if ($this->hasImages()) {
            //Get All images for materialFieldId
            $images = $this->query
                ->entity(CMS::MATERIAL_IMAGES_RELATION_ENTITY)
                ->cond(Field::F_DELETION, 1)
                ->where(MaterialField::F_PRIMARY, $this->materialFieldId)
                ->exec();
        }

        return $images;
    }
}