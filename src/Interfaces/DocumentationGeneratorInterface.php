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
     * @param array $templatePaths
     */
    public function generate(array $changelog, array $templatePaths);
}
