<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Composer\Plugin;

class Bootstrap
{
    /**
     * @var \Vaimo\ComposerChangelogs\Composer\Context
     */
    private $composerCtx;

    /**
     * @param \Vaimo\ComposerChangelogs\Composer\Context $composerCtx
     */
    public function __construct(
        \Vaimo\ComposerChangelogs\Composer\Context $composerCtx
    ) {
        $this->composerCtx = $composerCtx;
    }

    public function preloadPluginClasses()
    {
        $composer = $this->composerCtx->getLocalComposer();
        
        $installationManager = $composer->getInstallationManager();

        $packageResolver = new \Vaimo\ComposerChangelogs\Resolvers\PluginPackageResolver(
            array($composer->getPackage())
        );

        $packageInfoResolver = new \Vaimo\ComposerChangelogs\Resolvers\PackageInfoResolver(
            $installationManager
        );

        $sourcesPreloader = new \Vaimo\ComposerChangelogs\Loaders\SourcesPreloader($packageInfoResolver);

        $packages = $this->composerCtx->getActivePackages();
        
        $pluginPackage = $packageResolver->resolveForNamespace($packages, __NAMESPACE__);
        
        $sourcesPreloader->preloadForPackage($pluginPackage);
    }
}
