<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Resolvers;

use Composer\Repository\WritableRepositoryInterface;
use Vaimo\ComposerChangelogs\Composer\Config as ComposerConfig;

class PluginPackageResolver
{
    /**
     * @var \Vaimo\ComposerChangelogs\Analysers\PackageAnalyser
     */
    private $packageAnalyser;
    
    public function __construct()
    {
        $this->packageAnalyser = new \Vaimo\ComposerChangelogs\Analysers\PackageAnalyser();
    }

    /**
     * @param WritableRepositoryInterface $repository
     * @param string $namespace
     * @return \Composer\Package\PackageInterface
     * @throws \Exception
     */
    public function resolveForNamespace(WritableRepositoryInterface $repository, $namespace)
    {
        foreach ($repository->getCanonicalPackages() as $package) {
            if ($package->getType() !== ComposerConfig::COMPOSER_PLUGIN_TYPE) {
                continue;
            }

            if (!$this->packageAnalyser->ownsNamespace($package, $namespace)) {
                continue;
            }

            return $package;
        }

        throw new \Vaimo\ComposerChangelogs\Exceptions\PackageResolverException(
            'Failed to detect the plugin package'
        );
    }
}
