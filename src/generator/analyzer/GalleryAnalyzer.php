<?php
//[PHPCOMPRESSOR(remove,start)]
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 23.03.16 at 11:45
 */
namespace samsoncms\api\generator\analyzer;

use samsoncms\api\Field;
use samsoncms\api\generator\exception\ParentEntityNotFound;
use samsoncms\api\generator\metadata\VirtualMetadata;
use samsoncms\api\Navigation;

/**
 * Generic entities metadata analyzer.
 *
 * @package samsoncms\api\analyzer
 */
class GalleryAnalyzer extends VirtualAnalyzer
{
    /**
     * Analyze virtual entities and gather their metadata.
     *
     * @return \samsoncms\api\generator\metadata\VirtualMetadata[]
     * @throws ParentEntityNotFound
     */
    public function analyze()
    {
        /** @var VirtualMetadata[] $metadataCollection Set pointer to global metadata collection */
        $metadataCollection = [];

        // Iterate all structures, parents first
        foreach ($this->getVirtualEntities() as $structureRow) {
            $navigationID = $structureRow[Navigation::F_PRIMARY];

            // Iterate entity fields
            foreach ($this->getEntityFields($navigationID) as $fieldID => $fieldRow) {
                // We need only gallery fields
                if ((int)$fieldRow[Field::F_TYPE] === Field::TYPE_GALLERY) {

                    // Avoid GalleryGallery
                    $entity = ucfirst($this->fieldName($fieldRow[Field::F_IDENTIFIER]));
                    // Avoid GalleryGallery
                    $entity = $entity !== 'Gallery' ? $entity . 'Gallery' : $entity;
                    $className = $this->fullEntityName($entity);

                    // Fill in entity metadata
                    $metadata = new \samsoncms\api\generator\metadata\GalleryMetadata($className);

                    $metadata->parentClassName = $this->entityName($structureRow[Navigation::F_NAME]);
                    $metadata->entity = $entity;
                    // Prepend Entity name
                    $metadata->entity = $metadata->parentClassName . $metadata->entity;
                    $metadata->realName = $fieldRow[Field::F_IDENTIFIER];
                    $metadata->fieldID = $fieldID;
                    $metadata->parentID = $navigationID;

                    // Store metadata by entity identifier
                    $metadataCollection[$navigationID] = $metadata;
                }
            }
        }

        return $metadataCollection;
    }
}
//[PHPCOMPRESSOR(remove,end)]