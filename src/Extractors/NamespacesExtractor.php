<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Extractors;

use Vaimo\ComposerChangelogs\Composer\Config as ComposerConfig;

class NamespacesExtractor implements \Vaimo\ComposerChangelogs\Interfaces\PackageConfigExtractorInterface
{
    public function getConfig(\Composer\Package\PackageInterface $package)
    {
        $autoload = $package->getAutoload();

        if (!isset($autoload[ComposerConfig::PSR4_CONFIG])) {
            return array();
        }

        return array_keys($autoload[ComposerConfig::PSR4_CONFIG]);
    }
}
