<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Composer\Plugin;

class Bootstrap
{
    /**
     * @var \Composer\Composer
     */
    private $composer;

    /**
     * @param \Composer\Composer $composer
     */
    public function __construct(
        \Composer\Composer $composer
    ) {
        $this->composer = $composer;
    }

    public function preloadPluginClasses()
    {
        $installationManager = $this->composer->getInstallationManager();
        $repository = $this->composer->getRepositoryManager()->getLocalRepository();

        $packageResolver = new \Vaimo\ComposerChangelogs\Resolvers\PluginPackageResolver(
            array($this->composer->getPackage())
        );

        $packageInfoResolver = new \Vaimo\ComposerChangelogs\Resolvers\PackageInfoResolver(
            $installationManager
        );

        $sourcesPreloader = new \Vaimo\ComposerChangelogs\Loaders\SourcesPreloader($packageInfoResolver);

        $sourcesPreloader->preloadForPackage(
            $packageResolver->resolveForNamespace($repository, __NAMESPACE__)
        );
    }
}
