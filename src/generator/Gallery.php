<?php
//[PHPCOMPRESSOR(remove,start)]
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 22.03.16 at 15:46
 */
namespace samsoncms\api\generator;

/**
 * Entity gallery class generator.
 *
 * @package samsoncms\api\generator
 */
class Gallery extends Query
{
    /**
     * Class uses generation part.
     *
     * @param Metadata $metadata Entity metadata
     */
    protected function createUses(Metadata $metadata)
    {
        $this->generator
            ->newLine('use samsonframework\core\ViewInterface;')
            ->newLine('use samsonframework\orm\QueryInterface;')
            ->newLine('use samsonframework\orm\ArgumentInterface;')
            ->newLine('use samson\activerecord\dbQuery;')
            ->newLine();
    }

    /**
     * Class definition generation part.
     *
     * @param Metadata $metadata Entity metadata
     */
    protected function createDefinition(Metadata $metadata)
    {
        $this->generator
            ->multiComment(array(
                'Class for rendering "' . $metadata->entityRealName . '" gallery',
            ))
            ->defClass($metadata->entity . 'Gallery', '\\'.\samsoncms\api\Gallery::class)
            ->newLine('use \\'.\samsoncms\api\Renderable::class.';')
        ->newLine();
    }

    /**
     * Class constructor generation part.
     *
     * @param Metadata $metadata Entity metadata
     */
    protected function createConstructor(Metadata $metadata)
    {
        $class = "\n\t" . '/**';
        $class .= "\n\t" . ' * @param ViewInterface $renderer Rendering instance';
        $class .= "\n\t" . ' * @param ' . $metadata->parent->entity . ' $entity Parent entity';
        $class .= "\n\t" . ' * @param QueryInterface $query Database query instance';
        $class .= "\n\t" . ' */';
        $class .= "\n\t" . 'public function __construct(ViewInterface $renderer, ' . $entityType . ' $entity, QueryInterface $query = null)';
        $class .= "\n\t" . '{';
        $class .= "\n\t\t" . '$this->renderer = $renderer;';
        $class .= "\n\t\t" . 'parent::__construct(isset($query) ? $query : new dbQuery(), $entity->id, ' . $fieldID . ');';
        $class .= "\n\t" . '}' . "\n";

        $this->generator->text($class);
    }
}
//[PHPCOMPRESSOR(remove,end)]
