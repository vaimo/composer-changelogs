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
     * @var \Vaimo\ComposerChangelogs\Composer\Context
     */
    private $composerContext;

    /**
     * @param \Vaimo\ComposerChangelogs\Composer\Context $composerContext
     */
    public function __construct(
        \Vaimo\ComposerChangelogs\Composer\Context $composerContext
    ) {
        $this->composerContext = $composerContext;
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
        $confResolverFactory = new Factories\Changelog\ConfigResolverFactory($this->composerContext);

        return new \Vaimo\ComposerChangelogs\Loaders\ChangelogLoader(
            $confResolverFactory->create($fromSource)
        );
    }
}
