<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Generators;

use Vaimo\ComposerChangelogs\Loaders\TemplateLoader;

class TemplateOutputGenerator implements \Vaimo\ComposerChangelogs\Interfaces\TemplateOutputGeneratorInterface
{
    /**
     * @var TemplateLoader
     */
    private $templateLoader;

    public function __construct()
    {
        $this->templateLoader = new TemplateLoader();
    }

    public function generateOutput(array $data, array $templatePaths)
    {
        $options = array(
            'loader' => new \Mustache_Loader_FilesystemLoader(DIRECTORY_SEPARATOR),
            'partials_loader' => new TemplateLoader(
                function ($name) use ($templatePaths) {
                    return $templatePaths[$name];
                }
            ),
            'strict_callables' => true,
            'helpers' => array(
                'line' => function ($template, $renderer) {
                    $character = substr($template, -1);
                    
                    $content = $renderer(substr($template, 0, -1));
                    
                    return str_pad('', strlen($content), $character);
                },
                'title' => function ($text) {
                    return strtoupper($text);
                }
            )
        );

        $mustacheEngine = new \Mustache_Engine($options);
        
        $templateInstance = $mustacheEngine->loadTemplate($templatePaths['root']);
        
        return rtrim(
            $templateInstance->render($data),
            PHP_EOL
        );
    }
}
