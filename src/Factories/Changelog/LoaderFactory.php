<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Factories\Changelog;

use Vaimo\ComposerChangelogs\Factories;

class LoaderFactory
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
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     *
     * @param bool $fromSource
     * @return \Vaimo\ComposerChangelogs\Loaders\ChangelogLoader
     * @throws \Exception
     */
    public function create($fromSource = false)
    {
        $confResolverFactory = new Factories\Changelog\ConfigResolverFactory($this->composerRuntime);

        return new \Vaimo\ComposerChangelogs\Loaders\ChangelogLoader(
            $confResolverFactory->create($fromSource)
        );
    }
}
