<?php
//[PHPCOMPRESSOR(remove,start)]
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 22.03.16 at 17:50
 */
namespace samsoncms\api\generator;

use samsoncms\api\generator\metadata\Generic;
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
    public function __construct(Generator $generator, Generic $metadata)
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
    public function generate(Generic $metadata = null)
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
    protected function createUses(Generic $metadata)
    {

    }

    /**
     * Class definition generation part.
     *
     * @param Metadata $metadata Entity metadata
     */
    abstract protected function createDefinition(Generic $metadata);

    /**
     * Class constants generation part.
     *
     * @param Metadata $metadata Entity metadata
     */
    protected function createConstants(Generic $metadata)
    {

    }

    /**
     * Class static fields generation part.
     *
     * @param Metadata $metadata Entity metadata
     */
    protected function createStaticFields(Generic $metadata)
    {

    }

    /**
     * Class static methods generation part.
     *
     * @param Metadata $metadata Entity metadata
     */
    protected function createStaticMethods(Generic $metadata)
    {

    }

    /**
     * Class fields generation part.
     *
     * @param Metadata $metadata Entity metadata
     */
    protected function createFields(Generic $metadata)
    {

    }

    /**
     * Class methods generation part.
     *
     * @param Metadata $metadata Entity metadata
     */
    protected function createMethods(Generic $metadata)
    {

    }

    /**
     * Class constructor generation part.
     *
     * @param Metadata $metadata Entity metadata
     */
    protected function createConstructor(Generic $metadata)
    {

    }
}
//[PHPCOMPRESSOR(remove,end)]
