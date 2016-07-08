<?php
//[PHPCOMPRESSOR(remove,start)]
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 25.03.16 at 12:04
 */
namespace samsoncms\api\generator;

use samsonframework\orm\DatabaseInterface;
use samsonphp\generator\Generator;

/**
 * Generator classes file writer.
 *
 * @package samsoncms\api\generator
 */
class GenericWriter
{
    /** @var \samsoncms\api\generator\analyzer\GenericAnalyzer[string] Collection of entity analyzers */
    protected $analyzers = [];

    /** @var \samsoncms\api\generator\Generic[string] Collection of entity generators */
    protected $generators = [];

    /** @var array Analyzers metadata */
    protected $metadata = [];

    /** @var Generator Code generator */
    protected $codeGenerator;

    /** @var string Path to generated entities */
    protected $path;

    /**
     * Writer constructor.
     *
     * @param DatabaseInterface $db
     * @param Generator         $codeGenerator
     * @param string            $namespace
     * @param array             $analyzers Collection of analyzer class names
     * @param string            $path Path to generated entities
     *
     * @throws \Exception
     */
    public function __construct(DatabaseInterface $db, Generator $codeGenerator, $namespace, array $analyzers, $path)
    {
        $this->codeGenerator = $codeGenerator;
        $this->path = $path;
        $this->namespace = $namespace;

        // Create analyzer instances
        foreach ($analyzers as $analyzerClass => $generators) {
            if (class_exists($analyzerClass)) {
                $this->analyzers[$analyzerClass] = new $analyzerClass($db);

                // Analyze to get metadata
                $this->metadata[$analyzerClass] = $this->analyzers[$analyzerClass]->analyze();

                // Validate generator classes
                foreach ($generators as $generator) {
                    if (class_exists($generator)) {
                        $this->generators[$analyzerClass][] = $generator;
                    } else {
                        throw new \Exception('Entity generator class[' . $generator . '] not found');
                    }
                }
            } else {
                throw new \Exception('Entity analyzer class['.$analyzerClass.'] not found');
            }
        }
    }

    /**
     * Analyze, generate and write class files.
     */
    public function write()
    {
        // Create module cache folder if not exists
        if (!file_exists($this->path)) {
            @mkdir($this->path, 0777, true);
        }

        foreach ($this->analyzers as $analyzerClass => $analyzer) {
            // Analyze database structure and get entities metadata
            foreach ($this->metadata[$analyzerClass] as $metadata) {
                // Iterate all generators for analyzer
                foreach ($this->generators[$analyzerClass] as $generator) {
                    // TODO: Optimize generators creation by searching for existing files
                    /** @var Generic $generator Create class generator */
                    $generator = new $generator($this->codeGenerator->defNamespace($this->namespace), $metadata);

                    // Create entity generated class names
                    $file = $this->path . $generator->className . '.php';

                    // Do not generate file if its already there
                    if (!file_exists($file)) {
                        // Create entity query class files
                        file_put_contents($file, '<?php' . $generator->generate());
                    }

                    // Require files
                    require($file);
                }
            }
        }
    }
}
//[PHPCOMPRESSOR(remove,end)]
