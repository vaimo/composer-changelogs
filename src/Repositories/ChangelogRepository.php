<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Repositories;

class ChangelogRepository
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
     * @var \Vaimo\ComposerChangelogs\Loaders\ChangelogLoader
     */
    private $changelogLoader;

    /**
     * @var \Vaimo\ComposerChangelogs\Resolvers\RealPackageResolver
     */
    private $realPackageResolver;

    /**
     * @param \Composer\Package\RootPackageInterface $rootPackage
     * @param \Composer\Repository\WritableRepositoryInterface $packageRepository
     * @param \Vaimo\ComposerChangelogs\Loaders\ChangelogLoader $changelogLoader
     */
    public function __construct(
        \Composer\Package\RootPackageInterface $rootPackage,
        \Composer\Repository\WritableRepositoryInterface $packageRepository,
        \Vaimo\ComposerChangelogs\Loaders\ChangelogLoader $changelogLoader
    ) {
        $this->rootPackage = $rootPackage;
        $this->packageRepository = $packageRepository;
        $this->changelogLoader = $changelogLoader;

        $this->realPackageResolver = new \Vaimo\ComposerChangelogs\Resolvers\RealPackageResolver();
    }

    public function getForPackage($packageName)
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

        return $this->changelogLoader->load(
            $this->realPackageResolver->resolve($package)
        );
    }
}
