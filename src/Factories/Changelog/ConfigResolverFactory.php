<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Factories\Changelog;

use Vaimo\ComposerChangelogs\Extractors;

class ConfigResolverFactory
{
    /**
     * @var \Composer\Composer
     */
    private $composerRuntime;

    /**
     * @var 
     */
    private $packageResolver;

    /**
     * @param \Composer\Composer $composerRuntime
     */
    public function __construct(
        \Composer\Composer $composerRuntime
    ) {
        $this->composerRuntime = $composerRuntime;
        
        $this->packageResolver = new \Vaimo\ComposerChangelogs\Resolvers\PluginPackageResolver();
    }

    /**
     * @param bool $fromSource
     * @return \Vaimo\ComposerChangelogs\Resolvers\ChangelogConfigResolver
     * @throws \Exception
     */
    public function create($fromSource = false)
    {
        $packageRepository = $this->composerRuntime->getRepositoryManager()->getLocalRepository();
        
        $pluginPackage = $this->packageResolver->resolveForNamespace($packageRepository, __NAMESPACE__);
        
        $packageInfoResolver = new \Vaimo\ComposerChangelogs\Resolvers\PackageInfoResolver(
            $this->composerRuntime->getInstallationManager()
        );

        if ($fromSource) {
            $infoExtractor = new Extractors\VendorConfigExtractor($packageInfoResolver);
        } else {
            $infoExtractor = new Extractors\InstalledConfigExtractor();
        }
        
        return new \Vaimo\ComposerChangelogs\Resolvers\ChangelogConfigResolver(
            $pluginPackage,
            $packageInfoResolver,
            $infoExtractor
        );
    }
}
