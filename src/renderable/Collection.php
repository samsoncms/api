<?php
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 11.02.16 at 14:15
 */
namespace samsoncms\api\renderable;

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

    /** @var string Block view file */
    protected $indexView = 'www/index';

    /** @var string Item view file */
    protected $itemView = 'www/item';

    /** @var string Empty view file */
    protected $emptyView = 'www/empty';

    /** @var ViewInterface View render object */
    protected $renderer;

    /**
     * Collection constructor.
     *
     * @param QueryInterface $query Instance for querying database
     * @param ViewInterface  $renderer Instance for rendering views
     * @param string|null    $locale Localization language
     */
    public function __construct(QueryInterface $query, ViewInterface $renderer, $locale = null)
    {
        $this->renderer = $renderer;

        parent::__construct($query, $locale);
    }

    /**
     * Set index view path.
     * @param string $indexView Index view path
     */
    public function indexView($indexView)
    {
        $this->indexView = $indexView;
    }

    /**
     * Set item view path.
     * @param string $itemView Item view path
     */
    public function itemView($itemView)
    {
        $this->itemView = $itemView;
    }

    /**
     * Set empty view path.
     * @param string $itemView Empty view path
     */
    public function emptyView($emptyView)
    {
        $this->emptyView = $emptyView;
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
        return $this->renderer->view($this->itemView)->set(self::ITEM_VIEW_VARIABLE, $item)->output();
    }

    /**
     * Render empty collection item.
     *
     * @return string Rendered HTML
     */
    public function renderEmpty()
    {
        return $this->renderer->view($this->emptyView)->output();
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
        return $this->renderer->view($this->indexView)
            ->set(self::ITEMS_VIEW_VARIABLE, $items)
            ->output();
    }

    /** @return string Rendered HTML for fields table */
    public function render($page, $count)
    {
        // Perform SamsonCMS query
        $collection = $this->find($page, $count);

        $html = '';
        if (count($collection)) {
            // Render each entity view in collection
            foreach ($collection as $row) {
                $html .= $this->renderItem($row);
            }

            // Render collection main view with items
            $html = $this->renderIndex($html);
        } else { // Render empty entity view
            $html .= $this->renderEmpty();
        }

        return $html;
    }
}
