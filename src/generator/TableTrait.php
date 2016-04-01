<?php
//[PHPCOMPRESSOR(remove,start)]
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 22.03.16 at 15:46
 */
namespace samsoncms\api\generator;

use samsoncms\api\generator\metadata\GenericMetadata;
use samsoncms\api\generator\metadata\VirtualMetadata;
use samsonphp\generator\Generator;

/**
 * Table trait class generator. As all entities should
 * be able to create Table class instances for their selves
 * this class is a trait with methods to receive all available virtual
 * entity tables.
 *
 * @package samsoncms\api\generator
 */
class TableTrait extends Generic
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

        $this->className = 'TableTrait';
    }

    /**
     * Class uses generation part.
     *
     * @param VirtualMetadata $metadata Entity metadata
     */
    protected function createUses($metadata)
    {
        $this->generator
            ->newLine('use samsonframework\core\ViewInterface;')
            ->newLine();
    }

    /**
     * Class definition generation part.
     *
     * @param VirtualMetadata $metadata Entity metadata
     */
    protected function createDefinition($metadata)
    {
        $this->generator
            ->multiComment(array('"TableTrait database entity class'))
            ->defTrait($this->className);
    }

    /**
     * Class methods generation part.
     *
     * @param VirtualMetadata $metadata Entity metadata
     */
    protected function createMethods($metadata)
    {
        $methods = [];
        /** @var VirtualMetadata $metadataInstance Iterate all metadata entities */
        foreach (GenericMetadata::$instances as $metadataInstance) {
            if ($metadataInstance->type === VirtualMetadata::TYPE_TABLE) {
                // Create table virtual entity with correct name ending
                $replaced = preg_replace('/Table$/i', '',$metadataInstance->entity);
                $tableEntity = $replaced . 'TableCollection';

                $code = "\n\t" . '/**';
                $code .= "\n\t" . ' * Create virtual ' . $metadataInstance->entityRealName . ' table instance.';
                $code .= "\n\t" . ' * @param ViewInterface $renderer Renderer instance';
                $code .= "\n\t" . ' * @param string $locale Locale';
                $code .= "\n\t" . ' *';
                $code .= "\n\t" . ' * @return ' . $tableEntity . ' Table instance';
                $code .= "\n\t" . ' */';
                $code .= "\n\t" . 'public function ' . lcfirst($tableEntity) . '(ViewInterface $renderer, $locale = null)';
                $code .= "\n\t" . '{';
                $code .= "\n\t\t" . 'return new ' . $tableEntity . '($renderer, $this->id, $this->query, $locale);';
                $code .= "\n\t" . '}';

                $methods[] = $code;

                // Create table virtual entity with correct name ending
                $tableEntity = $replaced . 'TableEntity';

                $code = "\n\t" . '/**';
                $code .= "\n\t" . ' * Create virtual ' . $metadataInstance->entityRealName . ' table row instance.';
                $code .= "\n\t" . ' * @param string $locale Locale';
                $code .= "\n\t" . ' *';
                $code .= "\n\t" . ' * @return ' . $tableEntity . ' Table instance';
                $code .= "\n\t" . ' */';
                $code .= "\n\t" . 'public function create' . $tableEntity . 'Row($locale = null)';
                $code .= "\n\t" . '{';
                $code .= "\n\t\t" . '$row = new ' . $tableEntity . '();';
                $code .= "\n\t\t" . '$row->parent_id = $this->id;';
                $code .= "\n\t\t" . 'return $row;';
                $code .= "\n\t" . '}';

                $methods[] = $code;
            }
        }

        // Add method text to generator
        $this->generator->text(implode("\n", $methods));
    }
}
//[PHPCOMPRESSOR(remove,end)]