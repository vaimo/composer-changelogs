<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Factories\Changelog;

use Vaimo\ComposerChangelogs\Factories;

class RepositoryFactory
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

    public function create($fromSource = false)
    {
        $packageRepository = $this->composerRuntime->getRepositoryManager()->getLocalRepository();

        $configResolverFactory = new Factories\Changelog\ConfigResolverFactory(
            $packageRepository,
            $this->composerRuntime->getInstallationManager()
        );

        $changelogLoader = new \Vaimo\ComposerChangelogs\Loaders\ChangelogLoader(
            $configResolverFactory->create($fromSource)
        );

        return new \Vaimo\ComposerChangelogs\Repositories\ChangelogRepository(
            $this->composerRuntime->getPackage(),
            $packageRepository,
            $changelogLoader
        );
    }
}
