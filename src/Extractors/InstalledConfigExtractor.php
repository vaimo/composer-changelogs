<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Extractors;

class InstalledConfigExtractor implements \Vaimo\ComposerChangelogs\Interfaces\PackageConfigExtractorInterface
{
    public function getConfig(\Composer\Package\PackageInterface $package)
    {
        return $package->getExtra();
    }
}
