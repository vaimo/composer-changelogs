<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Loaders;

class TemplateLoader implements \Mustache_Loader
{
    /**
     * @var array
     */
    private $content = array();

    /**
     * @var \Closure
     */
    private $pathResolver;

    public function __construct(
        \Closure $pathResolver = null
    ) {
        $this->pathResolver = $pathResolver;
    }

    public function load($path)
    {
        if ($this->pathResolver !== null) {
            $resolver = $this->pathResolver;
            $path = $resolver($path);
        }
        
        if (!isset($this->content[$path])) {
            if (!file_exists($path)) {
                $message = sprintf('Could not find template file %s', $path);
                
                throw new \Vaimo\ComposerChangelogs\Exceptions\LoaderException($message);
            }

            $this->content[$path] = file_get_contents($path);
        }

        return $this->content[$path];
    }
}
