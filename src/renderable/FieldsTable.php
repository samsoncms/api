<?php
/**
 * Created by PhpStorm.
 * User: nazarenko
 * Date: 10.02.2016
 * Time: 18:51
 */
namespace samsoncms\api\renderable;

use samsonframework\core\RenderInterface;
use samsonframework\core\ViewInterface;
use samsonframework\orm\QueryInterface;

/**
 * Rendereable fields table.
 * Class should be used to simplify additional field tables rendering.
 * If your additional fields table has special logic just extend this class
 * and change any ot its method.
 *
 * @see \samsoncms\api\FieldsTable This class is just a wrapper with rendering
 *
 * @package samsoncms\api\renderable
 */
class FieldsTable extends \samsoncms\api\FieldsTable implements RenderInterface
{
    /** Name of the rows variable in index view */
    const ROWS_VIEW_VARIABLE = 'rows';

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
     * @param string|null $entityID
     * @param string|null $locale
     */
    public function __construct(QueryInterface $query, ViewInterface $renderer, $entityID, $locale = null)
    {
        // Store renderer
        $this->renderer = $renderer;

        parent::__construct($query, $entityID, $locale);
    }

    /**
     * Render table row.
     *
     * @param array $row Collection of column values.
     *
     * @return string Rendered HTML
     */
    public function renderRow(array $row)
    {
        // Generator should be modified
        $values = array();
        foreach ($row as $fieldId => $column) {
            $values[$this->fields[$fieldId]->Name] = $column;
        }

        return $this->renderer->view($this->rowView)->row($values)->output();
    }

    /**
     * Render table row.
     *
     * @param array $items Collection of rendered rows.
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
