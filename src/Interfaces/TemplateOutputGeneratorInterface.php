<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Interfaces;

interface TemplateOutputGeneratorInterface
{
    /**
     * @param array $changelog
     * @param array $templatePaths
     */
    public function generateOutput(array $changelog, array $templatePaths);
}
