<?php
/**
 * Created by PhpStorm.
 * User: VITALYIEGOROV
 * Date: 08.12.15
 * Time: 22:14
 */
namespace samsoncms\api;

use samsonframework\orm\QueryInterface;

/**
 * Generic SamsonCMS entity which has Navigation relation.
 * @package samsoncms\api
 */
class EntityQuery
{
    /** Deletion flag field name */
    const DELETE_FLAG_FIELD = 'Active';

    /** @var QueryInterface Database query instance */
    protected $query;

    /** @var string Entity identifier */
    protected $identifier;

    /** @var string Entity primary field name */
    protected $primaryField;

    /** @var string Field entity primary field name */
    protected $fieldPrimary;

    /** @var string Navigation entity primary field name */
    protected $navigationPrimary;

    /** @var string Entity to navigation relation */
    protected $navigationRelation;

    /** @var string Entity to field relation */
    protected $fieldRelation;

    /**
     * Entity constructor.
     * @param QueryInterface $query Database query instance
     * @param string $identifier Entity identifier
     */
    public function __construct(QueryInterface $query, $identifier)
    {
        $this->query = $query;
        $this->identifier = $identifier;
        $this->primaryField = $identifier::$_primary;
        $this->fieldPrimary = Field::$_primary;
        $this->navigationPrimary = Navigation::$_primary;
        $this->navigationRelation = CMS::MATERIAL_NAVIGATION_RELATION_ENTITY;
        $this->fieldRelation = CMS::MATERIAL_FIELD_RELATION_ENTITY;
    }

    /**
     * Get identifiers collection by field identifier and its value.
     * Method is optimized for performance.
     *
     * @param string $fieldID Additional field identifier
     * @param string $fieldValue Additional field value for searching
     * @param array $entityIDs Collection of entity identifiers for filtering query
     * @return bool|array True if material entities has been found and $return is passed
     *                      or identifiers collection if only two parameters is passed.
     */
    public function idsByFieldValue($fieldID, $fieldValue, $entityIDs = null) {
        $return = array();

        /** @var Field $fieldRecord We need to have field record */
        $fieldRecord = null;
        if (Field::byID($this->query, $fieldID, $fieldRecord)) {
            // Get material identifiers by field
            $this->query->entity($this->fieldRelation)
                ->where(self::DELETE_FLAG_FIELD, 1)
                ->where($this->fieldPrimary, $fieldID)
                ->where($fieldRecord->valueFieldName(), $fieldValue);

            // Add material identifier filter if passed
            if (isset($$entityIDs)) {
                $this->query->where($this->primaryField, $entityIDs);
            }

            // Perform database query and get only material identifiers collection
            $return = $this->query->fields($this->primaryField);
        }

        return $return;
    }

    /**
     * Get current entity identifiers collection by navigation identifier.
     *
     * @param string $navigationID Navigation identifier
     * @param array $filteringIDs Collection of entity identifiers for filtering query
     * @return array Collection of entity identifiers filtered by navigation identifier.
     */
    public function idsByNavigationID($navigationID, $filteringIDs = null)
    {
        // Prepare query
        $this->query
            ->entity($this->navigationRelation)
            ->where($this->navigationPrimary, $navigationID)
            ->where(self::DELETE_FLAG_FIELD, 1);

        // Add material identifier filter if passed
        if (isset($filteringIDs)) {
            $this->query->where($this->primaryField, $filteringIDs);
        }

        // Perform database query and get only material identifiers collection
        return $this->query->fields($this->primaryField);
    }

    /**
     * Get current entity instances collection by their identifiers.
     * Method can accept different query executors.
     *
     * @param string|array $entityIDs Entity identifier or their collection
     * @param string $executor Method name for query execution
     * @return mixed[] Collection of entity instances
     */
    public function byIDs($entityIDs, $executor = 'exec')
    {
        return $this->query
            ->where($this->primaryField, $entityIDs)
            ->where(self::DELETE_FLAG_FIELD, 1)
            ->$executor();
    }

    /**
     * Get current entity instances collection by navigation identifier.
     *
     * @param string $navigationID Navigation identifier
     * @return mixed[] Collection of entity instances
     */
    public function byNavigationID($navigationID)
    {
        $return = array();
        /** @var array $materialIds Collection of entity identifiers filtered by additional field */
        if (sizeof($materialIds = $this->idsByNavigationID($navigationID))) {
            $return = $this->byIDs($materialIds);
        }

        return $return;
    }

    /**
     * Get entity collection by field identifier and its value.
     *
     * @param string $fieldID Additional field identifier
     * @param string $fieldValue Additional field value for searching
     * @return mixed[] Collection of entity instances
     */
    public function byFieldValue($fieldID, $fieldValue)
    {
        $return = array();
        /** @var array $materialIds Collection of entity identifiers filtered by additional field */
        if (sizeof($materialIds = $this->idsByFieldValue($fieldID, $fieldValue))) {
            $return = $this->byIDs($materialIds);
        }

        return $return;
    }

    /**
     * Get entity collection by field identifier and its value.
     *
     * @param string $fieldID Additional field identifier
     * @param string $fieldValue Additional field value for searching
     * @return mixed[] Collection of entity instances
     */
    public function amountByFieldValue($fieldID, $fieldValue)
    {
        $return = 0;
        /** @var array $materialIds Collection of entity identifiers filtered by additional field */
        if (sizeof($materialIds = $this->idsByFieldValue($fieldID, $fieldValue))) {
            $return = $this->byIDs($materialIds, 'count');
        }

        return $return;
    }

    /**
     * Get current entity instances amount by navigation identifier.
     *
     * @param string $navigationID Navigation identifier
     * @return integer Amount of entities related to Navigation identifier
     */
    public function amountByNavigationID($navigationID)
    {
        $return =0;
        /** @var array $materialIds Collection of entity identifiers filtered by additional field */
        if (sizeof($materialIds = $this->idsByNavigationID($navigationID))) {
            $return = $this->byIDs($materialIds, 'count');
        }

        return $return;
    }
}
