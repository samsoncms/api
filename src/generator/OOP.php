<?php
//[PHPCOMPRESSOR(remove,start)]
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 22.03.16 at 17:50
 */
namespace samsoncms\api\generator;

use samsonphp\generator\Generator;

/**
 * Generic object-oriented programming class generator.
 *
 * @package samsoncms\api\generator
 */
abstract class OOP
{
    /** @var Generator Code generation instance */
    protected $generator;

    /** @var Metadata Entity query metadata */
    protected $metadata;

    /**
     * OOP constructor.
     *
     * @param Generator $generator Code generation instance
     * @param Metadata  $metadata Entity query metadata
     */
    public function __construct(Generator $generator, Metadata $metadata)
    {
        $this->metadata = $metadata;
        $this->generator = $generator;
    }

    /**
     * Generic class generation.
     *
     * @param Metadata $metadata Entity metadata
     *
     * @return string Generated PHP class code
     */
    public function generate(Metadata $metadata = null)
    {
        $metadata = null === $metadata ? $this->metadata : $metadata;

        $this->createUses($metadata);
        $this->createDefinition($metadata);
        $this->createConstants($metadata);
        $this->createStaticFields($metadata);
        $this->createStaticMethods($metadata);
        $this->createFields($metadata);
        $this->createMethods($metadata);
        $this->createConstructor($metadata);

        return $this->generator->endClass()->flush();
    }

    /**
     * Class uses generation part.
     *
     * @param Metadata $metadata Entity metadata
     */
    protected function createUses(Metadata $metadata)
    {

    }

    /**
     * Class definition generation part.
     *
     * @param Metadata $metadata Entity metadata
     */
    abstract protected function createDefinition(Metadata $metadata);

    /**
     * Class constants generation part.
     *
     * @param Metadata $metadata Entity metadata
     */
    protected function createConstants(Metadata $metadata)
    {

    }

    /**
     * Class static fields generation part.
     *
     * @param Metadata $metadata Entity metadata
     */
    protected function createStaticFields(Metadata $metadata)
    {

    }

    /**
     * Class static methods generation part.
     *
     * @param Metadata $metadata Entity metadata
     */
    protected function createStaticMethods(Metadata $metadata)
    {

    }

    /**
     * Class fields generation part.
     *
     * @param Metadata $metadata Entity metadata
     */
    protected function createFields(Metadata $metadata)
    {

    }

    /**
     * Class methods generation part.
     *
     * @param Metadata $metadata Entity metadata
     */
    protected function createMethods(Metadata $metadata)
    {

    }

    /**
     * Class constructor generation part.
     *
     * @param Metadata $metadata Entity metadata
     */
    protected function createConstructor(Metadata $metadata)
    {

    }
}
//[PHPCOMPRESSOR(remove,end)]
