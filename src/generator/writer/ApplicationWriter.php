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
class ApplicationWriter extends GenericWriter
{
    /**
     * Analyze, generate and write class files.
     */
    public function write()
    {
        // Create module cache folder if not exists
        if (!file_exists($this->path)) {
            @mkdir($path, 0777, true);
        }

        foreach ($this->analyzers as $analyzerClass => $analyzer) {
            // Analyze database structure and get entities metadata
            foreach ($analyzer->analyze() as $metadata) {
                // Iterate all generators for analyzer
                foreach ($this->generators[$analyzerClass] as $generator) {
                    /** @var Generic $generator Create class generator */
                    $generator = new $generator($this->codeGenerator->defNamespace($this->namespace), $metadata);

                    // Create entity generated class names
                    $file = $this->path . $generator->className . '.php';

                    // Create entity query class files
                    file_put_contents($file, '<?php' . $generator->generate());

                    // Require files
                    require($file);
                }
            }
        }
    }
}
//[PHPCOMPRESSOR(remove,end)]
