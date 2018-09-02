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
    private $io;

    /**
     * @var Factories\Changelog\ConfigResolverFactory 
     */
    private $configResolverFactory;
    
    /**
     * @param \Composer\Composer $composerRuntime
     * @param \Composer\IO\IOInterface $io
     */
    public function __construct(
        \Composer\Composer $composerRuntime,
        \Composer\IO\IOInterface $io
    ) {
        $this->composerRuntime = $composerRuntime;
        $this->io = $io;

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

        $docsGenerator = new \Vaimo\ComposerChangelogs\Generators\DocumentationGenerator(
            $configResolver,
            $changelogLoader
        );

        try {
            $docsGenerator->generate($package);
        } catch (\Exception $exception) {
            // Due to change-log not being all that important, we don't fail the installation just because of it
        }
    }
}
