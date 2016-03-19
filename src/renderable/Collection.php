<?php
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 11.02.16 at 14:15
 */
namespace samsoncms\api\renderable;

use samson\activerecord\dbQuery;
use samsoncms\api\Entity;
use samsonframework\core\ViewInterface;
use samsonframework\orm\QueryInterface;

/**
 * Renderable fields table.
 * Class should be used to simplify SamsonCMS generated entities outputting.
 *
 * @see \samsoncms\api\FieldsTable This class is just a wrapper with rendering
 *
 * @package samsoncms\api\renderable
 */
class Collection extends \samsoncms\api\query\Entity
{
    /** Name of the items variable in index view */
    const ITEMS_VIEW_VARIABLE = 'items';

    /** Name of the item prefix for variable in item view */
    const ITEM_VIEW_VARIABLE = 'item';

    /** @var string|callable Block view file or callback */
    protected $indexView;

    /** @var string|callable Item view file or callback */
    protected $itemView ;

    /** @var string|callable Empty view file or callback */
    protected $emptyView = 'www/empty';

    /** @var ViewInterface View render object */
    protected $renderer;

    /** @var int Count of entities on one page */
    protected $pageSize;

    /** @var int Current page number */
    protected $pageNumber;

    /**
     * Collection constructor.
     *
     * @param ViewInterface  $renderer Instance for rendering views
     * @param QueryInterface $query Instance for querying database
     * @param string|null    $locale Localization language
     */
    public function __construct(ViewInterface $renderer, QueryInterface $query = null, $locale = null)
    {
        $this->renderer = $renderer;

        parent::__construct(null === $query ? new dbQuery() : $query, $locale);
    }

    /**
     * Set index view path.
     * @param string|callable $indexView Index view path or callback
     * @return $this Chaining
     */
    public function indexView($indexView)
    {
        $this->indexView = $indexView;
        return $this;
    }

    /**
     * Set item view path.
     * @param string|callable $itemView Item view path or callback
     * @return $this Chaining
     */
    public function itemView($itemView)
    {
        $this->itemView = $itemView;

        return $this;
    }

    /**
     * Set empty view path.
     * @param string|callable $emptyView Empty view path or callback
     * @return $this Chaining
     */
    public function emptyView($emptyView)
    {
        $this->emptyView = $emptyView;
        return $this;
    }

    /**
     * Render Entity collection item.
     *
     * @param Entity $item SamsonCMS entity for rendering
     *
     * @return string Rendered HTML
     */
    public function renderItem(Entity $item)
    {
        // Set correct renderer old style or new \samsonframework\view
        $renderer = ($this->itemView instanceof \samsonframework\view\View)
            ? $this->itemView
            : $this->renderer->view($this->itemView);

        return $renderer
            ->set($item, self::ITEM_VIEW_VARIABLE)
            ->output();

    }

    /**
     * Render empty collection item.
     *
     * @return string Rendered HTML
     */
    public function renderEmpty()
    {
        // Set correct renderer old style or new \samsonframework\view
        $renderer = ($this->emptyView instanceof \samsonframework\view\View)
            ? $this->emptyView
            : $this->renderer->view($this->emptyView);

        return $renderer->output();
    }

    /**
     * Render Entity collection index.
     *
     * @param string $items Collection of rendered items
     *
     * @return string Rendered HTML
     */
    public function renderIndex($items)
    {
        // Set correct renderer old style or new \samsonframework\view
        $renderer = ($this->indexView instanceof \samsonframework\view\View)
            ? $this->indexView
            : $this->renderer->view($this->indexView);

        return $renderer
            ->set($items, self::ITEMS_VIEW_VARIABLE)
            ->output();
    }

    /**
     * Set pagination for SamsonCMS query.
     *
     * @param int $pageNumber Result page number
     * @param int|null $pageSize Results page size
     * @return $this Chaining
     */
    public function pager($pageNumber, $pageSize = null)
    {
        $this->pageNumber = $pageNumber;
        $this->pageSize = null !== $pageSize ? $pageSize : $this->pageSize;

        return $this;
    }

    /** @return string Rendered fields table */
    public function __toString()
    {
        return $this->output();
    }

    /** @return string Rendered HTML for fields table */
    public function output()
    {
        // Perform SamsonCMS query
        $collection = $this->find($this->pageNumber, $this->pageSize);

        if (count($collection)) {
            // Render each entity view in collection
            $html = '';
            foreach ($collection as $row) {
                $html .= $this->innerRender($row, $collection, 'itemView', 'renderItem');
            }

            // Render collection main view with items
            return $this->innerRender($html, $collection, 'indexView', 'renderIndex');
        } else { // Render empty entity view
            return $this->innerRender('', $collection, 'emptyView', 'renderEmpty');
        }
    }

    /**
     * Generic view renderer.
     *
     * @param mixed    $item
     * @param Entity[] $collection
     * @param string   $variableName
     * @param string   $methodName
     *
     * @return mixed Rendered view
     */
    protected function innerRender($item, $collection, $variableName, $methodName)
    {
        return is_callable($this->$variableName)
            ? call_user_func($this->$variableName, $item, $this->renderer, $this->query, $collection)
            : $this->$methodName($item);
    }
}
