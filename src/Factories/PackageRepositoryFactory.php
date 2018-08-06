<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Factories;

class PackageRepositoryFactory
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

    public function create()
    {
        return new \Vaimo\ComposerChangelogs\Repositories\PackageRepository(
            $this->composerRuntime->getPackage(),
            $this->composerRuntime->getRepositoryManager()->getLocalRepository()
        );
    }
}
