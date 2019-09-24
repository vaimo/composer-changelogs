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
        $composer = $this->composerContext->getLocalComposer();
        
        return new \Vaimo\ComposerChangelogs\Repositories\PackageRepository(
            $composer->getPackage(),
            $composer->getRepositoryManager()->getLocalRepository()
        );
    }
}
