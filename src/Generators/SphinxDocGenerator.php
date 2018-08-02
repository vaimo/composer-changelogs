<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Generators;

use LightnCandy\LightnCandy;
use Vaimo\ComposerChangelogs\Exceptions\TemplateValidationException;

class SphinxDocGenerator implements \Vaimo\ComposerChangelogs\Interfaces\DocumentationGeneratorInterface
{
    public function generate(array $changelog, $templatePath, $outputPath)
    {
        $templateContents = file_get_contents($templatePath);

        $options = array(
            'flags' => LightnCandy::FLAG_MUSTACHE
                | LightnCandy::FLAG_NOESCAPE
                | LightnCandy::FLAG_ERROR_EXCEPTION,
            'helpers' => array(
                'line' => function ($context, $char) {
                    return str_pad('', strlen($context), $char);
                }
            )
        );

        try {
            $generatorSeed = LightnCandy::compile($templateContents, $options);
        } catch (\Exception $exception) {
            throw new TemplateValidationException(
                sprintf('Failed to parse %s', $templatePath),
                null,
                $exception
            );
        }

        $outputGenerator = eval($generatorSeed);

        $contextData = array();

        foreach ($changelog as $version => $details) {
            $item = array();

            $item['version'] = $version;
            $item['overview'] = isset($details['overview']) ? $details['overview'] : '';
            $item['groups'] = array();

            $groups = array_diff_key($details, array('overview' => true));

            foreach ($groups as $name => $groupItems) {
                $group = array();

                $group['name'] = ucfirst($name);
                $group['items'] = $groupItems;

                $item['groups'][] = $group;
            }

            $contextData[] = $item;
        }

        $output = $outputGenerator(
            array('log' => $contextData)
        );

        file_put_contents($outputPath, $output);
    }
}
