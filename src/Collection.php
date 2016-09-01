<?php
/**
 * Created by PhpStorm.
 * User: egorov
 * Date: 26.12.2014
 * Time: 16:22
 */
namespace samsoncms\api;

use samson\activerecord\StructureMaterial;
use samsoncms\api\generated\Structurefield;
use samsoncms\api\query\Generic;
use samsonframework\orm\Condition;
use samsonframework\collection\Paged;
use samsonframework\orm\Relation;
use samsonframework\pager\PagerInterface;
use samsonframework\core\RenderInterface;
use samsonframework\orm\QueryInterface;

/**
 * Collection query builder for filtering
 * @package    samsonos\cms\collection
 * @author     Egorov Vitaly <egorov@samsonos.com>
 * @deprecated Use generated Entities and EntityQueries classes.
 */
class Collection extends Paged
{
    /** @var string Entity manager instance */
    protected $managerEntity = Generic::class;

    /** @var array Collection for current filtered material identifiers */
    protected $materialIDs = array();

    /** @var array Collection of navigation filters */
    protected $navigation = array();

    /** @var array Collection of field filters */
    protected $field = array();

    /** @var array Collection of query handlers */
    protected $idHandlers = array();

    /** @var array External material handler and params array */
    protected $entityHandlers = array();

    /** @var array Base material entity handler callbacks array */
    protected $baseEntityHandlers = array();

    /** @var string Collection entities class name */
    protected $entityName = Material::class;

    /**
     * Generic collection constructor
     *
     * @param RenderInterface $renderer View render object
     * @param QueryInterface  $query    Query object
     */
    public function __construct(RenderInterface $renderer, QueryInterface $query, PagerInterface $pager)
    {
        // Call parent initialization
        parent::__construct($renderer, $query->entity('\samson\activerecord\material'), $pager);
    }

    /**
     * Render products collection block
     *
     * @param string $prefix     Prefix for view variables
     * @param array  $restricted Collection of ignored keys
     *
     * @return array Collection key => value
     */
    public function toView($prefix = null, array $restricted = array())
    {
        // Render pager and collection
        return array_diff(array(
            $prefix . 'html' => $this->render(),
            $prefix . 'pager' => $this->pager->total > 1 ? $this->pager->toHTML() : ''
        ), $restricted);
    }

    /**
     * Add external identifier filter handler
     *
     * @param callback $handler
     * @param array    $params
     *
     * @return $this Chaining
     */
    public function handler($handler, array $params = array())
    {
        // Add callback with parameters to array
        $this->idHandlers[] = array($handler, $params);

        return $this;
    }

    /**
     * Set external entity handler
     *
     * @param callback $handler
     * @param array    $params
     *
     * @return $this Chaining
     */
    public function baseEntityHandler($handler, array $params = array())
    {
        // Add callback with parameters to array
        $this->baseEntityHandlers[] = array($handler, $params);

        return $this;
    }

    /**
     * Set external entity handler
     *
     * @param callback $handler
     * @param array    $params
     *
     * @return $this Chaining
     */
    public function entityHandler($handler, array $params = array())
    {
        // Add callback with parameters to array
        $this->entityHandlers[] = array($handler, $params);

        return $this;
    }

    /**
     * Set collection sorter parameters
     *
     * @param string|integer $field       Field identifier or name
     * @param string         $destination ASC|DESC
     *
     * @return void
     */
    public function sorter($field, $destination = 'ASC')
    {
        /**@var \samson\activerecord\field $field */
        // TODO: Add ability to sort with entity fields
        if (in_array($field, \samson\activerecord\material::$_attributes)) {
            $this->sorter = array(
                'field' => $field,
                'name' => $field,
                'destination' => $destination
            );
        } elseif ($this->isFieldObject($field)) {
            $this->sorter = array(
                'entity' => $field,
                'name' => $field->Name,
                'field' => in_array($field->Type, array(3, 7, 10)) ? 'numeric_value' : 'value',
                'destination' => $destination
            );
        }
    }

    /**
     * Filter collection using navigation entity or collection of them.
     * If collection of navigation Url or Ids is passed then this group will be
     * applied as single navigation filter to retrieve materials.
     *
     * @param string|integer|array $navigation Navigation URL or identifier for filtering
     *
     * @return $this Chaining
     */
    public function navigation($navigation)
    {
        // Do not allow empty strings
        if (!empty($navigation)) {
            // Create id or URL condition
            $idOrUrl = new Condition('OR');
            $idOrUrl->add('StructureID', $navigation)->add('Url', $navigation);

            /** @var array $navigationIds */
            $navigationIds = null;
            if ($this->query->entity('\samson\activerecord\structure')->whereCondition($idOrUrl)->fields('StructureID', $navigationIds)) {
                // Store all retrieved navigation elements as navigation collection filter
                $this->navigation[] = $navigationIds;
            }
        }

        // Chaining
        return $this;
    }

    /**
     * Filter collection using additional field entity.
     *
     * @param string|integer|Field $field    Additional field identifier or name
     * @param mixed                $value    Additional field value for filtering
     * @param string               $relation Additional field relation for filtering
     *
     * @return $this Chaining
     */
    public function field($field, $value, $relation = Relation::EQUAL)
    {
        // Do not allow empty strings
        if ($this->isFieldObject($field)) {
            // Get field value column
            $valueField = in_array($field->Type, array(3, 7, 10)) ? 'numeric_value' : 'value';
            $valueField = $field->Type == 6 ? 'key_value' : $valueField;

            /** @var Condition $condition Ranged condition */
            $condition = new Condition('AND');

            // Add min value for ranged condition
            $condition->add($valueField, $value, $relation);

            // Store retrieved field element and its value as field collection filter
            $this->field[] = array($field, $condition);
        }

        // Chaining
        return $this;
    }

    /**
     * Filter collection using additional field entity values and LIKE relation.
     * If this method is called more then once, it will use materials, previously filtered by this method.
     *
     * @param string $search Search string
     *
     * @return $this Chaining
     */
    public function search($search)
    {
        // If input parameter is a string add it to search string collection
        if (isset($search{0})) {
            $this->search[] = $search;
        }

        // Chaining
        return $this;
    }

    /**
     * Filter collection of numeric field in range from min to max values
     *
     * @param string|integer|Field $field    Additional field identifier or name
     * @param integer              $minValue Min value for range filter
     * @param integer              $maxValue Max value for range filter
     *
     * @return $this Chaining
     */
    public function ranged($field, $minValue, $maxValue)
    {
        // Check input parameters and try to find field
        if (($minValue <= $maxValue) && $this->isFieldObject($field)) {
            // TODO: Remove integers from code, handle else
            // Only numeric fields are supported
            if (in_array($field->Type, array(3, 7, 10))) {
                /** @var Condition $condition Ranged condition */
                $condition = new Condition('AND');

                // Add min value for ranged condition
                $condition->add('numeric_value', $minValue, Relation::GREATER_EQ);

                // Add max value for ranged condition
                $condition->add('numeric_value', $maxValue, Relation::LOWER_EQ);

                // Store created condition
                $this->field[] = array($field, $condition);
            }
        }

        // Chaining
        return $this;
    }

    /**
     * Try to find additional field record
     *
     * @param string|integer $field Additional field identifier or name
     *
     * @return bool True if field record has been found
     */
    protected function isFieldObject(&$field)
    {
        // Do not allow empty strings
        if (!empty($field)) {
            // Create id or URL condition
            $idOrUrl = new Condition('OR');
            $idOrUrl->add('FieldID', $field)->add('Name', $field);

            // Perform query
            return $this->query->entity('\samson\activerecord\field')->whereCondition($idOrUrl)->first($field);
        }

        // Field not found
        return false;
    }

    /**
     * Try to get all material identifiers filtered by navigation
     * if no navigation filtering is set - nothing will happen.
     *
     * @param array $filteredIds Collection of filtered material identifiers
     *
     * @return bool True if ALL navigation filtering succeeded or there was no filtering at all otherwise false
     */
    protected function applyNavigationFilter(&$filteredIds = array())
    {
        // Iterate all applied navigation filters
        foreach ($this->navigation as $navigation) {
            // Create navigation-material query
            $this->query->entity(StructureMaterial::class)
                ->where('StructureID', $navigation)
                ->where('Active', 1)
                ->groupBy('structurematerial', 'MaterialID');

            if (null !== $filteredIds) {
                $this->query->where('MaterialID', $filteredIds);
            }

            // Perform request to get next portion of filtered material identifiers
            if (!$this->query->fields('MaterialID', $filteredIds)) {
                // This filter applying failed
                return false;
            }
        }

        // We have no navigation collection filters
        return true;
    }

    /**
     * Try to get all material identifiers filtered by additional field
     * if no field filtering is set - nothing will happen.
     *
     * @param array $filteredIds Collection of filtered material identifiers
     *
     * @return bool True if ALL field filtering succeeded or there was no filtering at all otherwise false
     */
    protected function applyFieldFilter(&$filteredIds = array())
    {
        // Iterate all applied field filters
        foreach ($this->field as $field) {
            // Create material-field query
            $this->query->entity(MaterialField::class)
                ->where('FieldID', $field[0]->id)
                ->whereCondition($field[1])
                ->groupBy('materialfield', 'MaterialID');

            if (null !== $filteredIds) {
                $this->query->where('MaterialID', $filteredIds);
            }

            // Perform request to get next portion of filtered material identifiers
            if (!$this->query->fields('MaterialID', $filteredIds)) {
                // This filter applying failed
                return false;
            }
        }

        // We have no field collection filters
        return true;
    }

    /**
     * Try to find all materials which have fields similar to search strings
     *
     * @param array $filteredIds Collection of filtered material identifiers
     *
     * @return bool True if ALL field filtering succeeded or there was no filtering at all otherwise false
     */
    protected function applySearchFilter(&$filteredIds = array())
    {
        /** @var array $fields Variable to store all fields related to set navigation */
        $fields = array();
        /** @var array $navigationArray Array of set navigation identifiers */
        $navigationArray = array();
        /** @var array $fieldFilter Array of filtered material identifiers via materialfield table */
        $fieldFilter = array();
        /** @var array $materialFilter Array of filtered material identifiers via material table */
        $materialFilter = array();

        // If there are at least one search string
        if (!empty($this->search)) {
            // Create array containing all navigation identifiers
            foreach ($this->navigation as $navigation) {
                // Navigation hook for searching action
                $navigation = is_array($navigation) ? $navigation : array($navigation);
                $navigationArray = array_merge($navigationArray, $navigation);
            }

            // Get all related fields
            $this->query->entity(Structurefield::class)
                ->where('StructureID', $navigationArray)
                ->groupBy('structurefield', 'FieldID')
                ->fields('FieldID', $fields);

            // Iterate over search strings
            foreach ($this->search as $searchString) {
                // Try to find search value in materialfield table
                $this->query->entity(MaterialField::class)
                    ->where('FieldID', $fields)
                    ->where('MaterialID', $filteredIds)
                    ->where('Value', '%' . $searchString . '%', Relation::LIKE)
                    ->where('Active', 1)
                    ->groupBy('materialfield', 'MaterialID')
                    ->fields('MaterialID', $fieldFilter);

                // TODO: Add generic support for all native fields or their configuration
                // Condition to search in material table by Name and URL
                $materialCondition = new Condition('OR');
                $materialCondition->add('Name', '%' . $searchString . '%', Relation::LIKE)
                    ->add('Url', '%' . $searchString . '%', Relation::LIKE);


                // Try to find search value in material table
                $this->query->entity('\samson\activerecord\material')
                    ->whereCondition($materialCondition)
                    ->where('Active', 1);

                // If we have not empty collection of filtering identifiers
                if (count($filteredIds)) {
                    $this->query->where('MaterialID', $filteredIds);
                }

                $materialFilter = $this->query->fields('MaterialID');

                // If there are no materials with specified conditions
                if (empty($materialFilter) && empty($fieldFilter) && count($materialFilter) != 0 && count($fieldFilter != 0)) {
                    // Filter applying failed
                    return false;
                } else {// Otherwise set filtered material identifiers
                    $filteredIds = array_unique(array_merge($materialFilter, $fieldFilter));
                }
            }
        }

        // We have no search collection filters
        return true;
    }

    /**
     * Apply all possible material filters
     *
     * @param array $filteredIds Collection of material identifiers
     *
     * @return bool True if ALL filtering succeeded or there was no filtering at all otherwise false
     */
    protected function applyFilter(& $filteredIds = array())
    {
        return $this->applyNavigationFilter($filteredIds)
        && $this->applyFieldFilter($filteredIds)
        && $this->applySearchFilter($filteredIds)
        && $this->applyMaterialSorter($filteredIds);
    }

    /**
     * Perform material identifiers collection sorting
     *
     * @param array $materialIDs Variable to return sorted collection
     */
    protected function applyFieldSorter(&$materialIDs = array())
    {
        // Check if sorter is configured
        if (count($this->sorter)) {
            // If we need to sort by entity additional field(column)
            if (!in_array($this->sorter['field'], \samson\activerecord\material::$_attributes)) {
                // Sort material identifiers by its additional fields
                $this->query->entity('\samson\activerecord\materialfield')
                    ->where('FieldID', $this->sorter['entity']->id)
                    ->orderBy($this->sorter['field'], $this->sorter['destination'])
                    ->where('MaterialID', $materialIDs)
                    ->fields('MaterialID', $materialIDs);
            }
        }
    }

        /**
     * Perform material own fields sorting
     *
     * @param array $materialIDs Variable to return sorted collection
     *
     * @return bool Always true as we are just sorting
     */
    protected function applyMaterialSorter(&$materialIDs = array())
    {
        // Check if sorter is configured
        if (count($this->sorter)) {
            // If we need to sort by entity additional field(column)
            if (in_array($this->sorter['field'], \samson\activerecord\material::$_attributes)) {
                // Sort material identifiers by its additional fields
                $this->query->entity('\samson\activerecord\material')
                    ->where('MaterialID', $materialIDs)
                    ->orderBy($this->sorter['field'], $this->sorter['destination'])
                    ->fields('MaterialID', $materialIDs);
            }
        }

        return true;
    }

    /**
     * Call handlers stack
     *
     * @param array $handlers Collection of callbacks with their parameters
     * @param array $params   External parameters to pass to callback at first
     *
     * @return bool True if all handlers succeeded
     */
    protected function callHandlers(&$handlers = array(), $params = array())
    {
        // Call external handlers
        foreach ($handlers as $handler) {
            // Call external handlers chain
            if (call_user_func_array($handler[0], array_merge($params, $handler[1])) === false) {
                // Stop - if one of external handlers has failed
                return false;
            }
        }

        return true;
    }

    /**
     * Perform filtering on base material entity
     *
     * @param array $materialIDs Variable to return sorted collection
     */
    protected function applyBaseEntityFilter(&$materialIDs = array())
    {
        // TODO: Change this to new OOP approach
        $class = $this->entityName;

        // Configure query to base entity
        $this->query->entity('samson\activerecord\material');

        // Call base material entity handlers to prepare query
        $this->callHandlers($this->baseEntityHandlers, array(&$this->query));

        // Check if sorter is configured
        if (count($this->sorter)) {
            // If we need to sort by entity own field(column)
            if (in_array($this->sorter['field'], $class::$_attributes)) {
                // Add material entity sorter
                $this->query->orderBy($this->sorter['field'], $this->sorter['destination']);
            }
        }

        // Perform main entity query
        $this->materialIDs = $this->query
            ->where('Active', 1)// Remove deleted entities
            ->where('system', 0)// Remove system entities
            ->where($class::$_primary, $materialIDs)// Filter to current set
            ->fields($class::$_primary);
    }

    /**
     * Perform collection database retrieval using set filters
     *
     * @return $this Chaining
     */
    public function fill()
    {
        // Clear current materials identifiers list
        $this->materialIDs = null;

        // TODO: Change this to new OOP approach
        $class = $this->entityName;

        // If no filters is set
        if (!count($this->search) && !count($this->navigation) && !count($this->field)) {
            // Add sorting if present for material table
            if (count($this->sorter) && !array_key_exists('enitity', $this->sorter)) {
                $this->query->orderBy($this->sorter['field'], $this->sorter['destination']);
            }
            // Get all entity records identifiers
            $this->materialIDs = $this->query->where('Active', 1)->where('system', 0)->fields($class::$_primary);
        }

        // Perform material filtering
        if ($this->applyFilter($this->materialIDs)) {
            // Now we have all possible material filters applied and final material identifiers collection

            // Store filtered collection size
            $this->count = count($this->materialIDs);

            // Call material identifier handlers
            $this->callHandlers($this->idHandlers, array(&$this->materialIDs));

            // Perform base entity query for final filtering
            $this->applyBaseEntityFilter($this->materialIDs);

            // Perform sorting
            $this->applyFieldSorter($this->materialIDs);

            // Create count request to count pagination
            $this->pager->update(count($this->materialIDs));

            // Cut only needed materials identifiers from array
            $this->materialIDs = array_slice($this->materialIDs, $this->pager->start, $this->pager->end);

            // Create final material query
            $this->query->entity($this->entityName)->where($class::$_primary, $this->materialIDs);

            // Call material query handlers
            $this->callHandlers($this->entityHandlers, array(&$this->query));

            // Add query sorter for showed page
            if (count($this->sorter)) {
                $this->query->orderBy($this->sorter['name'], $this->sorter['destination']);
            }

            // Return final filtered entity query result
            $this->collection = $this->query->exec();

        } else { // Collection is empty

            // Clear current materials identifiers list
            $this->materialIDs = array();

            // Updated pagination
            $this->pager->update(count($this->materialIDs));
        }

        // Chaining
        return $this;
    }
}
