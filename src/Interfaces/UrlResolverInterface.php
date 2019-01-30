<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Interfaces;

interface UrlResolverInterface
{
    /**
     * @param \Composer\Package\PackageInterface $package
     * @return string
     */
    public function resolveForPackage(\Composer\Package\PackageInterface $package);
}
