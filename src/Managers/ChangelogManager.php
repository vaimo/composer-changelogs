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
    private $composer;

    /**
     * @var \Composer\IO\IOInterface
     */
    private $io;

    /**
     * @param \Composer\Composer $composer
     * @param \Composer\IO\IOInterface $io
     */
    public function __construct(
        \Composer\Composer $composer,
        \Composer\IO\IOInterface $io
    ) {
        $this->composer = $composer;
        $this->io = $io;
    }

    public function bootstrap()
    {
        $configResolverFactory = new Factories\Changelog\ConfigResolverFactory($this->composer);

        $configResolver = $configResolverFactory->create();

        $package = $this->composer->getPackage();

        if (!$configResolver->hasConfig($package)) {
            return;
        }

        $docsGenerator = new \Vaimo\ComposerChangelogs\Generators\DocumentationGenerator(
            $configResolver
        );

        try {
            $docsGenerator->generate($package);
        } catch (\Exception $exception) {
            // Due to change-log not being all that important, we don't fail the installation just because of it
        }
    }
}
