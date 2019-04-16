<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Factories;

class PackageResolverFactory
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
        $packageRepoFactory = new PackageRepositoryFactory($this->composerRuntime);
        
        return new \Vaimo\ComposerChangelogs\Resolvers\PackageResolver(
            $packageRepoFactory->create(),
            $this->composerRuntime->getPackage()
        );
    }
}
