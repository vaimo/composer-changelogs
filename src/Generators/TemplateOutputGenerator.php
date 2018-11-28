<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Generators;

use LightnCandy\LightnCandy;
use Vaimo\ComposerChangelogs\Exceptions\TemplateValidationException;

class TemplateOutputGenerator implements \Vaimo\ComposerChangelogs\Interfaces\TemplateOutputGeneratorInterface
{
    /**
     * @var \Vaimo\ComposerChangelogs\Loaders\TemplateLoader
     */
    private $templateLoader;

    public function __construct()
    {
        $this->templateLoader = new \Vaimo\ComposerChangelogs\Loaders\TemplateLoader();
    }

    public function generateOutput(array $data, array $templatePaths)
    {
        $templateLoader = $this->templateLoader;

        $options = array(
            'flags' => LightnCandy::FLAG_MUSTACHE
                | LightnCandy::FLAG_NOESCAPE
                | LightnCandy::FLAG_ERROR_EXCEPTION,
            'helpers' => array(
                'line' => function ($context, $char) {
                    return str_pad('', strlen($context), $char);
                },
                'title' => function ($context) {
                    return strtoupper($context);
                } 
            ),
            'partialresolver' => function ($context, $name) use ($templatePaths, $templateLoader) {
                if (isset($templatePaths[$name])) {
                    try {
                        return $templateLoader->load($templatePaths[$name]);
                    } catch (\Exception $e) {
                        return sprintf('Missing template: %s', $templatePaths[$name]);
                    }
                }

                return sprintf('Unknown partial: %s', $name);
            }
        );

        $rootTemplate = $this->templateLoader->load($templatePaths['root']);

        try {
            $generatorCode = LightnCandy::compile($rootTemplate, $options);
        } catch (\Exception $exception) {
            throw new TemplateValidationException(
                sprintf('Failed to parse %s', $rootTemplate),
                null,
                $exception
            );
        }

        $outputGenerator = eval($generatorCode);

        return rtrim($outputGenerator($data), "\n");
    }
}
