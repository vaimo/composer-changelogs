<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Factories;

class PackageRepositoryFactory
{
    /**
     * @var \Vaimo\ComposerChangelogs\Composer\Context
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
        $composer = $this->composerCtx->getLocalComposer();
        
        return new \Vaimo\ComposerChangelogs\Repositories\PackageRepository(
            $composer->getPackage(),
            $composer->getRepositoryManager()->getLocalRepository()
        );
    }
}
