<?php
/**
 * Created by PhpStorm.
 * User: myslyvyi
 * Date: 05.01.2016
 * Time: 15:25
 */
namespace samsoncms\api;

use samsonframework\orm\QueryInterface;

/***
 *  Gallery additional field for material.
 *  This class enables getting all information about additional fields gallery for specific material.
 *  @author myslyvyi@samsonos.com
 * @deprecated Use \samsoncms\api\generator\GalleryCollection instead
 */
class Gallery
{
    /** @var integer materialFieldId Table materialField identifier */
    protected $materialFieldId = null;

    /** @var QueryInterface Database query interface */
    protected $query;

    /**
     * Constructor Gallery.
     * This constructor finds identifier additional field gallery from
     * database record its material and field identifiers.
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

        //Find additional field value database record by its material and field identifiers.
        $materialField = $this->query->entity(MaterialField::ENTITY)
            ->where(Material::F_PRIMARY, $materialId)
            ->where(Field::F_PRIMARY, $fieldId)
            ->where(Material::F_DELETION, 1)
            ->first();

        if ($materialField) {
            //Set materialFieldId
            $this->materialFieldId = $materialField->id;
        }

    }

    /**
     * Getting quantity images in additional field gallery
     *
     * @return integer $count
     */
    public function getCount()
    {
        /**@var integer $count quantity images in additional field gallery */
        $count = 0;

        if ($this->hasImages()) {
            // Getting quantity images for gallery
            $count = $this->query
                ->entity(CMS::MATERIAL_IMAGES_RELATION_ENTITY)
                ->where(Field::F_DELETION, 1)
                ->where(MaterialField::F_PRIMARY, $this->materialFieldId)
                ->count();
        }

        return $count;
    }

    /**
     * Check on empty gallery. If materialFieldId = null and quantity images not more 1 then material not has images.
     *
     * @return boolean
     **/
    public function hasImages()
    {
        /**@var $hasImages */
        $hasImages = false;

        if (isset($this->materialFieldId)) {
            // Getting quantity images, if quantity more 0 then material has images
            if ($this->query
            ->entity(CMS::MATERIAL_IMAGES_RELATION_ENTITY)
            ->where(Field::F_DELETION, 1)
            ->where(MaterialField::F_PRIMARY, $this->materialFieldId)
            ->count() > 0) {
                $hasImages = true;
            }
        }

        return $hasImages;
    }

    /**
     * Get collection of images for material by gallery additional field selector. If none is passed
     * all images from gallery table would be returned empty array.
     *
     * @param integer $currentPage current page with images. Min value = 1
     * @param integer $countView quantity view by page
     *
*@return array
     * @deprecated  Use find()
     */
    public function getImages($currentPage = null, $countView = 20)
    {
        return $this->find($currentPage, $countView);
    }

    /**
     * Perform SamsonCMS query and get entity gallery images.
     *
     * @param int $page Page number
     * @param int $size Page size
     *
     * @return GalleryField[] Collection of entity gallery images
     */
    public function find($page = null, $size = null)
    {
        /** @var GalleryField[] $images Get material images for this gallery */
        $images = array();

        /** @var QueryInterface $query Database query interface*/
        $query = null;

        if ($this->hasImages()) {
            // Select all images in DB by materialFieldId
            $query = $this->query
                ->entity(CMS::MATERIAL_IMAGES_RELATION_ENTITY)
                ->where(Field::F_DELETION, 1)
                ->where(MaterialField::F_PRIMARY, $this->materialFieldId);

            // Add paging
            if (isset($page) && $page > 0) {
                //Set limit for query
                $query->limit(--$page * $size, $size);
            }

            // Execute query
            $images = $query->exec();
        }

        return $images;
    }
}
