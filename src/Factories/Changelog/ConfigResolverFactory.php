<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Factories\Changelog;

use Vaimo\ComposerChangelogs\Extractors;
use Vaimo\ComposerChangelogs\Resolvers;

class ConfigResolverFactory
{
    /**
     * @var \Composer\Repository\WritableRepositoryInterface
     */
    private $packageRepository;

    /**
     * @var \Composer\Installer\InstallationManager
     */
    private $installationManager;

    /**
     * @param \Composer\Repository\WritableRepositoryInterface $packageRepository
     * @param \Composer\Installer\InstallationManager $installationManager
     */
    public function __construct(
        \Composer\Repository\WritableRepositoryInterface $packageRepository,
        \Composer\Installer\InstallationManager $installationManager
    ) {
        $this->packageRepository = $packageRepository;
        $this->installationManager = $installationManager;
    }

    public function create($fromSource = false)
    {
        $packageResolver = new \Vaimo\ComposerChangelogs\Resolvers\PluginPackageResolver();

        if ($fromSource) {
            $packageInfoResolver = new Resolvers\PackageInfoResolver($this->installationManager);
            $infoExtractor = new Extractors\VendorConfigExtractor($packageInfoResolver);
        } else {
            $infoExtractor = new Extractors\InstalledConfigExtractor();
        }

        return new Resolvers\ChangelogConfigResolver(
            $packageResolver->resolveForNamespace($this->packageRepository, __NAMESPACE__),
            $this->installationManager,
            $infoExtractor
        );
    }
}
