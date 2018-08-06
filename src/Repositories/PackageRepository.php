<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Repositories;

class PackageRepository
{
    /**
     * @var \Composer\Package\RootPackageInterface
     */
    private $rootPackage;

    /**
     * @var \Composer\Repository\WritableRepositoryInterface
     */
    private $packageRepository;

    /**
     * @var \Vaimo\ComposerChangelogs\Resolvers\RealPackageResolver
     */
    private $realPackageResolver;

    /**
     * @param \Composer\Package\RootPackageInterface $rootPackage
     * @param \Composer\Repository\WritableRepositoryInterface $packageRepository
     */
    public function __construct(
        \Composer\Package\RootPackageInterface $rootPackage,
        \Composer\Repository\WritableRepositoryInterface $packageRepository
    ) {
        $this->rootPackage = $rootPackage;
        $this->packageRepository = $packageRepository;

        $this->realPackageResolver = new \Vaimo\ComposerChangelogs\Resolvers\RealPackageResolver();
    }

    public function getByName($packageName)
    {
        if (!$packageName) {
            $package = $this->rootPackage;
        } else {
            $matches = $this->packageRepository->findPackages($packageName, '*');

            $package = reset($matches);
        }

        if (!$package) {
            throw new \Vaimo\ComposerChangelogs\Exceptions\PackageNotFoundException(
                sprintf('Failed to locate the package: %s', $packageName)
            );
        }

        return $this->realPackageResolver->resolve($package);
    }
}
