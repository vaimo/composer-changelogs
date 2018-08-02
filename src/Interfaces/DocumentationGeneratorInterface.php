<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Interfaces;

interface DocumentationGeneratorInterface
{
    /**
     * @param array $changelog
     * @param string $templatePath
     * @param string $outputPath
     */
    public function generate(array $changelog, $templatePath, $outputPath);
}
