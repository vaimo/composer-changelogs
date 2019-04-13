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
     * @var \Composer\IO\IOInterface
     */
    private $cliIO;

    /**
     * @var Factories\Changelog\ConfigResolverFactory
     */
    private $configResolverFactory;
    
    /**
     * @param \Composer\Composer $composerRuntime
     * @param \Composer\IO\IOInterface $cliIO
     */
    public function __construct(
        \Composer\Composer $composerRuntime,
        \Composer\IO\IOInterface $cliIO
    ) {
        $this->composerRuntime = $composerRuntime;
        $this->cliIO = $cliIO;

        $this->configResolverFactory = new Factories\Changelog\ConfigResolverFactory(
            $this->composerRuntime
        );
    }
    
    public function bootstrap()
    {
        $configResolver = $this->configResolverFactory->create();

        $package = $this->composerRuntime->getPackage();

        if (!$configResolver->hasConfig($package)) {
            return;
        }

        $changelogLoader = new \Vaimo\ComposerChangelogs\Loaders\ChangelogLoader($configResolver);

        $validator = new \Vaimo\ComposerChangelogs\Validators\ChangelogValidator($changelogLoader);

        $result = $validator->validateForPackage($package);

        if (!$result()) {
            return;
        }

        $infoResolver = new \Vaimo\ComposerChangelogs\Resolvers\PackageInfoResolver(
            $this->composerRuntime->getInstallationManager()
        );

        $urlResolver = new \Vaimo\ComposerChangelogs\Resolvers\Url\RemoteSourceResolver($infoResolver);
        
        $docsGenerator = new \Vaimo\ComposerChangelogs\Generators\DocumentationGenerator(
            $configResolver,
            $changelogLoader,
            $infoResolver,
            $urlResolver
        );

        try {
            $docsGenerator->generate($package);
        } catch (\Exception $exception) {
            // Due to change-log not being all that important, we don't fail the installation just because of it
        }
    }
}
