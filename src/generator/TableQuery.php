<?php
//[PHPCOMPRESSOR(remove,start)]
/**
 * Created by PhpStorm.
 * User: nazarenko
 * Date: 29.03.2016
 * Time: 11:11
 */
namespace samsoncms\api\generator;

use samsonphp\generator\Generator;

/**
 * Table query class generator.
 *
 * @package samsoncms\api\generator
 */
class TableVirtualQuery extends VirtualQuery
{
    /**
     * Query constructor.
     *
     * @param Generator $generator
     * @param           $metadata
     */
    public function __construct(Generator $generator, $metadata)
    {
        parent::__construct($generator, $metadata);

        $replaced = preg_replace('/Table$/i', '', $this->metadata->entity);
        $this->className = $replaced . 'TableQuery';
        $this->parentClass = '\\' . \samsoncms\api\query\EntityTable::class;
        $this->entityClass = '\samsoncms\api\generated\\' . $replaced . 'TableEntity';
    }

    /**
     * Class uses generation part.
     *
     * @param \samsoncms\api\generator\metadata\VirtualMetadata $metadata Entity metadata
     */
    protected function createUses($metadata)
    {
        $this->generator
            ->newLine('use samsonframework\orm\QueryInterface;')
            ->newLine('use samson\activerecord\dbQuery;')
            ->newLine('use samsonframework\orm\ArgumentInterface;')
            ->newLine();
    }

    /**
     * Class constructor generation part.
     *
     * @param \samsoncms\api\generator\metadata\VirtualMetadata $metadata Entity metadata
     */
    protected function createConstructor($metadata)
    {
        $class = "\n\t" . '/**';
        $class .= "\n\t" . ' * @param int $parentID Parent entity identifier';
        $class .= "\n\t" . ' * @param QueryInterface $query Database query instance';
        $class .= "\n\t" . ' * @param string $locale Localization identifier';
        $class .= "\n\t" . ' */';
        $class .= "\n\t" . 'public function __construct($parentID, QueryInterface $query = null, $locale = null)';
        $class .= "\n\t" . '{';
        $class .= "\n\t\t" . 'parent::__construct($parentID, isset($query) ? $query : new dbQuery(), $locale);';
        $class .= "\n\t" . '}';

        $this->generator->text($class);
    }
}
//[PHPCOMPRESSOR(remove,end)]
