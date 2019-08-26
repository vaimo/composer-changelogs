<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Managers;

use Vaimo\ComposerChangelogs\Factories;

class ChangelogManager
{
    /**
     * @var \Composer\Composer
     */
    private $composerRuntime;

    /**
     * @var Factories\Changelog\ConfigResolverFactory
     */
    private $confResolverFactory;
    
    /**
     * @param \Composer\Composer $composerRuntime
     */
    public function __construct(
        \Composer\Composer $composerRuntime
    ) {
        $this->composerRuntime = $composerRuntime;

        $this->confResolverFactory = new Factories\Changelog\ConfigResolverFactory(
            $this->composerRuntime
        );
    }
    
    public function bootstrap()
    {
        $configResolver = $this->confResolverFactory->create();

        $package = $this->composerRuntime->getPackage();

        $chLogRepoFactory = new Factories\ChangelogRepositoryFactory($this->composerRuntime);
        $chLogRepo = $chLogRepoFactory->create(false);

        $changelog = $chLogRepo->getByPackageName($package->getName());
        
        if (!$changelog) {
            return;
        }

        $infoResolver = new \Vaimo\ComposerChangelogs\Resolvers\PackageInfoResolver(
            $this->composerRuntime->getInstallationManager()
        );

        $urlResolver = new \Vaimo\ComposerChangelogs\Resolvers\Url\RemoteSourceResolver($infoResolver);
        
        $docsGenerator = new \Vaimo\ComposerChangelogs\Generators\DocumentationGenerator(
            $configResolver,
            $infoResolver,
            $urlResolver
        );

        try {
            $docsGenerator->generate($package, $changelog->getReleases());
        } catch (\Exception $exception) {
            // Due to change-log not being all that important, we don't fail the installation just because of it
        }
    }
}
