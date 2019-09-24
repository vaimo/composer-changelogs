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
    private $composerCtx;

    /**
     * @param \Vaimo\ComposerChangelogs\Composer\Context $composerCtx
     */
    public function __construct(
        \Vaimo\ComposerChangelogs\Composer\Context $composerCtx
    ) {
        $this->composerCtx = $composerCtx;
    }

    public function create()
    {
        $packageRepoFactory = new PackageRepositoryFactory($this->composerCtx);

        $composer = $this->composerCtx->getLocalComposer();
        
        return new \Vaimo\ComposerChangelogs\Resolvers\PackageResolver(
            $packageRepoFactory->create(),
            $composer->getPackage()
        );
    }
}
