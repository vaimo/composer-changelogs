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
        
        $this->packageResolver = new \Vaimo\ComposerChangelogs\Resolvers\PluginPackageResolver(
            array($this->composerRuntime->getPackage())
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
        $packageRepository = $this->composerRuntime->getRepositoryManager()->getLocalRepository();
        
        $pluginPackage = $this->packageResolver->resolveForNamespace($packageRepository, __NAMESPACE__);
        
        $packageInfoResolver = new \Vaimo\ComposerChangelogs\Resolvers\PackageInfoResolver(
            $this->composerRuntime->getInstallationManager()
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
