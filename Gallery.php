<?php
/**
 * Created by PhpStorm.
 * User: myslyvyi
 * Date: 05.01.2016
 * Time: 15:25
 */

namespace samsoncms\api;

class Gallery
{
    /** @var integer Table parent materialField identifier */
    protected $materialFieldId = null;

    /**
     * Constructor Gallery
     * @param integer $materialId material identifier
     * @param integer $fieldId field identifier
     */
    public function __construct($materialId, $fieldId)
    {
        $mf = null;
        if (is_int($materialId) && is_int($fieldId)) {
            if (dbQuery('materialfield')->cond('MaterialID', $materialId)->cond('FieldID', $fieldId)->first($mf)) {
                $this->materialFieldId = $mf->id;
            }
        }
    }

    /**
     * Get all images in gallery
     * @return array
     */
    public function getImages()
    {
        if ($this->issetImages()) {
            // array images from gallery
            $images = null;
            if (dbQuery('gallery')->cond('MaterialFieldID', $this->materialFieldId)->exec($images)) {
                return $images;
            }
        }
        return array();
    }

    /**
     * Check on empty gallery
     * @return boolean
     **/
    public function issetImages()
    {
        return (isset($this->materialFieldId)) ? true : false;
    }
}