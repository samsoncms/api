<?php
//[PHPCOMPRESSOR(remove,start)]
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 22.03.16 at 15:46
 */
namespace samsoncms\api\generator;

/**
 * Virtual entity collection class generator.
 *
 * @package samsoncms\api\generator
 */
class VirtualCollection extends RealCollection
{
    /**
     * Class constructor generation part.
     *
     * @param \samsoncms\api\generator\metadata\VirtualMetadata $metadata Entity metadata
     */
    protected function createConstructor($metadata)
    {
        $class = "\n\t" . '/**';
        $class .= "\n\t" . ' * @param ViewInterface $renderer Rendering instance';
        $class .= "\n\t" . ' * @param QueryInterface $query Database query instance';
        $class .= "\n\t" . ' * @param string $locale Localization identifier';
        $class .= "\n\t" . ' */';
        $class .= "\n\t" . 'public function __construct(ViewInterface $renderer, QueryInterface $query = null, $locale = null)';
        $class .= "\n\t" . '{';
        $class .= "\n\t\t" . '$this->renderer = $renderer;';
        $class .= "\n\t\t" . 'parent::__construct($locale, isset($query) ? $query : new dbQuery());';
        $class .= "\n\t" . '}';

        $this->generator->text($class);
    }
}
//[PHPCOMPRESSOR(remove,end)]
