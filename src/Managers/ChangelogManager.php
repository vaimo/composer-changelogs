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
     * @var \Vaimo\ComposerChangelogs\Composer\Context
     */
    private $composerContext;

    /**
     * @var Factories\Changelog\ConfigResolverFactory
     */
    private $confResolverFactory;
    
    /**
     * @param \Vaimo\ComposerChangelogs\Composer\Context $composerContext
     */
    public function __construct(
        \Vaimo\ComposerChangelogs\Composer\Context $composerContext
    ) {
        $this->composerContext = $composerContext;

        $this->confResolverFactory = new Factories\Changelog\ConfigResolverFactory(
            $this->composerContext
        );
    }
    
    public function bootstrap()
    {
        $configResolver = $this->confResolverFactory->create();

        $composer = $this->composerContext->getLocalComposer();
        
        $package = $composer->getPackage();

        $chLogRepoFactory = new Factories\ChangelogRepositoryFactory($this->composerContext);
        $chLogRepo = $chLogRepoFactory->create(false);

        $changelog = $chLogRepo->getByPackageName($package->getName());
        
        if (!$changelog) {
            return;
        }

        $infoResolver = new \Vaimo\ComposerChangelogs\Resolvers\PackageInfoResolver(
            $composer->getInstallationManager()
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
