<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Interfaces;

interface PackageConfigExtractorInterface
{
    /**
     * @param \Composer\Package\PackageInterface $package
     * @return array
     */
    public function getConfig(\Composer\Package\PackageInterface $package);
}
