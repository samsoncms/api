<?php
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 19.03.16 at 16:02
 */
namespace samsoncms\api;

use samsoncms\api\exception\RenderableViewNotSet;
use samsonframework\core\ViewInterface;
use samsonframework\view\View;

define('RENDERABLE_ITEMS_VARIABLE', 'items');
define('RENDERABLE_ITEM_VARIABLE', 'item');

/**
 * Generic renderable methods and variables.
 *
 * @package samsoncms\api
 */
trait Renderable
{
    /** @var string|callable Block view file or callback */
    protected $indexView;

    /** @var string|callable Item view file or callback */
    protected $itemView;

    /** @var string|callable Empty view file or callback */
    protected $emptyView;

    /** @var ViewInterface View render object */
    protected $renderer;

    /** @var int Count of entities on one page */
    protected $pageSize;

    /** @var int Current page number */
    protected $pageNumber;

    /**
     * Set pagination for SamsonCMS query.
     *
     * @param int      $pageNumber Result page number
     * @param int|null $pageSize   Results page size
     *
     * @return $this Chaining
     */
    public function pager($pageNumber, $pageSize = null)
    {
        $this->pageNumber = $pageNumber;
        $this->pageSize = null !== $pageSize ? $pageSize : $this->pageSize;

        return $this;
    }

    /**
     * Set index view path.
     *
     * @param string|callable|View $indexView Index view path or callback
     *
     * @return $this Chaining
     */
    public function indexView($indexView)
    {
        $this->indexView = $indexView;
        return $this;
    }

    /**
     * Set item view path.
     *
     * @param string|callable|View $itemView Item view path or callback
     *
     * @return $this Chaining
     */
    public function itemView($itemView)
    {
        $this->itemView = $itemView;

        return $this;
    }

    /**
     * Set empty view path.
     *
     * @param string|callable|View $emptyView Empty view path or callback
     *
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
     * @param mixed $item SamsonCMS entity for rendering
     *
     * @return string Rendered HTML
     */
    public function renderItem($item)
    {
        // Set correct renderer old style or new \samsonframework\view
        $renderer = ($this->itemView instanceof View)
            ? $this->itemView
            : $this->renderer->view($this->itemView);

        return $renderer
            ->set($item, RENDERABLE_ITEM_VARIABLE)
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
        $renderer = ($this->emptyView instanceof View)
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
        $renderer = ($this->indexView instanceof View)
            ? $this->indexView
            : $this->renderer->view($this->indexView);

        return $renderer
            ->set($items, RENDERABLE_ITEMS_VARIABLE)
            ->output();
    }

    /** @return string Rendered fields table */
    public function __toString()
    {
        return $this->output();
    }

    /** @return string Rendered HTML for fields table */
    public function output()
    {
        // Validate renderable views
        if ($this->indexView === null) {
            throw new RenderableViewNotSet('indexView');
        }

        if ($this->itemView === null) {
            throw new RenderableViewNotSet('itemView');
        }

        if ($this->emptyView === null) {
            throw new RenderableViewNotSet('emptyView');
        }

        // Perform SamsonCMS query
        $collection = $this->find($this->pageNumber, $this->pageSize);

        if (count($collection)) {
            return $this->renderer($collection);
        } else { // Render empty entity view
            return $this->innerRender('', $collection, 'emptyView', 'renderEmpty');
        }
    }

    /**
     * Collection items renderer.
     *
     * @param mixed $collection Items collection for rendering
     * @return string Rendered items
     */
    protected function renderer($collection)
    {
        // Render each entity view in collection
        $html = '';
        foreach ($collection as $row) {
            $html .= $this->innerRender($row, $collection, 'itemView', 'renderItem');
        }

        // Render collection main view with items
        return $this->innerRender($html, $collection, 'indexView', 'renderIndex');
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
