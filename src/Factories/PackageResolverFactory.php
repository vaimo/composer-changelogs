<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Factories;

class PackageResolverFactory
{
    /**
     * @var \Vaimo\ComposerChangelogs\Composer\
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

    public function create()
    {
        $packageRepoFactory = new PackageRepositoryFactory($this->composerContext);

        $composer = $this->composerContext->getLocalComposer();
        
        return new \Vaimo\ComposerChangelogs\Resolvers\PackageResolver(
            $packageRepoFactory->create(),
            $composer->getPackage()
        );
    }
}
