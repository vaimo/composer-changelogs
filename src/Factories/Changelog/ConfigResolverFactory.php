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
     * @param \Composer\Composer $composerRuntime
     */
    public function __construct(
        \Composer\Composer $composerRuntime
    ) {
        $this->composerRuntime = $composerRuntime;
    }

    /**
     * @param bool $fromSource
     * @return \Vaimo\ComposerChangelogs\Resolvers\ChangelogConfigResolver
     * @throws \Exception
     */
    public function create($fromSource = false)
    {
        $packageResolver = new \Vaimo\ComposerChangelogs\Resolvers\PluginPackageResolver();
        $packageInfoResolver = new \Vaimo\ComposerChangelogs\Resolvers\PackageInfoResolver(
            $this->composerRuntime->getInstallationManager()
        );

        if ($fromSource) {
            $infoExtractor = new Extractors\VendorConfigExtractor($packageInfoResolver);
        } else {
            $infoExtractor = new Extractors\InstalledConfigExtractor();
        }

        $packageRepository = $this->composerRuntime->getRepositoryManager()->getLocalRepository();

        return new \Vaimo\ComposerChangelogs\Resolvers\ChangelogConfigResolver(
            $packageResolver->resolveForNamespace($packageRepository, __NAMESPACE__),
            $packageInfoResolver,
            $infoExtractor
        );
    }
}
