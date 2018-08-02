<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Resolvers;

class RealPackageResolver
{
    public function resolve(\Composer\Package\PackageInterface $package)
    {
        while ($package instanceof \Composer\Package\AliasPackage) {
            $package = $package->getAliasOf();
        }

        return $package;
    }
}
