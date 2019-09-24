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
     * @var \Vaimo\ComposerChangelogs\Composer\Context
     */
    private $composerCtx;

    /**
     * @var \Vaimo\ComposerChangelogs\Resolvers\PluginPackageResolver
     */
    private $packageResolver;

    /**
     * @param \Vaimo\ComposerChangelogs\Composer\Context $composerCtx
     */
    public function __construct(
        \Vaimo\ComposerChangelogs\Composer\Context $composerCtx
    ) {
        $this->composerCtx = $composerCtx;

        $composer = $this->composerCtx->getLocalComposer();
        
        $this->packageResolver = new \Vaimo\ComposerChangelogs\Resolvers\PluginPackageResolver(
            array($composer->getPackage())
        );
    }

    /**
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     *
     * @param bool $fromSource
     * @return \Vaimo\ComposerChangelogs\Resolvers\ChangelogConfigResolver
     * @throws \Exception
     */
    public function create($fromSource = false)
    {
        $composer = $this->composerCtx->getLocalComposer();
        
        $pluginPackage = $this->packageResolver->resolveForNamespace(
            $this->composerCtx->getActivePackages(),
            __NAMESPACE__
        );
        
        $packageInfoResolver = new \Vaimo\ComposerChangelogs\Resolvers\PackageInfoResolver(
            $composer->getInstallationManager()
        );

        $infoExtractor = $this->createInfoExtractor($packageInfoResolver, $fromSource);

        $pluginConfig = new \Vaimo\ComposerChangelogs\Composer\Plugin\Config();
        
        return new \Vaimo\ComposerChangelogs\Resolvers\ChangelogConfigResolver(
            $pluginPackage,
            $pluginConfig,
            $packageInfoResolver,
            $infoExtractor
        );
    }
    
    private function createInfoExtractor($packageInfoResolver, $fromSource)
    {
        if ($fromSource) {
            return new Extractors\VendorConfigExtractor($packageInfoResolver);
        }

        return new Extractors\InstalledConfigExtractor();
    }
}
