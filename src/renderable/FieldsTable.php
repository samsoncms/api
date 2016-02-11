<?php
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 10.02.16 at 18:15
 */
namespace samsoncms\api\renderable;

use samsoncms\api\field\Row;
use samsonframework\core\RenderInterface;
use samsonframework\core\ViewInterface;
use samsonframework\orm\QueryInterface;

/**
 * Renderable fields table.
 * Class should be used to simplify additional field tables rendering.
 * If your additional fields table has special logic just extend this class
 * and change any ot its method.
 *
 * @see \samsoncms\api\FieldsTable This class is just a wrapper with rendering
 *
 * @package samsoncms\api\renderable
 */
class FieldsTable extends \samsoncms\api\field\Table implements RenderInterface
{
    /** Name of the rows variable in index view */
    const ROWS_VIEW_VARIABLE = 'rows';

    /** Name of the row prefix for variable in row view */
    const ROW_VIEW_VARIABLE = 'row';

    /** @var string Index view path */
    protected $indexView = 'index';

    /** @var string Row view path */
    protected $rowView = 'row';

    /** @var ViewInterface */
    protected $renderer;

    /**
     * GeneralInfo constructor.
     *
     * @param QueryInterface $query
     * @param ViewInterface $renderer
     * @param int[] $navigationID Collection of entity navigation identifiers
     * @param int $entityID Entity identifier
     * @param string|null $locale Table localization
     */
    public function __construct(QueryInterface $query, ViewInterface $renderer, $navigationID, $entityID, $locale = null)
    {
        // Store renderer
        $this->renderer = $renderer;

        parent::__construct($query, $navigationID, $entityID, $locale);
    }

    /**
     * Render table row.
     *
     * @param Row $row Collection of column values.
     *
     * @return string Rendered HTML
     */
    public function renderRow(Row $row)
    {
        return $this->renderer->view($this->rowView)->set(self::ROW_VIEW_VARIABLE, $row)->output();
    }

    /**
     * Render table row.
     *
     * @param string $items Collection of rendered rows.
     *
     * @return string Rendered HTML
     */
    public function renderIndex($items)
    {
        return $this->renderer->view($this->indexView)
            ->set(self::ROWS_VIEW_VARIABLE, $items)
            ->output();
    }

    /** @return string Rendered HTML for fields table */
    public function render()
    {
        $html = '';
        foreach ($this->collection as $row) {
            $html .= $this->renderRow($row);
        }

        $html = $this->renderIndex($html);

        return $html;
    }

    /**
     * Prepare fields table for rendering in the view.
     *
     * @param string|null $prefix Prefix to be added to view variables
     * @param array $restricted Collection of fields to be restricted
     *
     * @return array Collection of view variables for rendering
     */
    public function toView($prefix = null, array $restricted = array())
    {
        return array_diff_key(array($prefix.'html' => $this->render()), $restricted);
    }
}
