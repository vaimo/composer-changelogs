<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Repositories;

use Vaimo\ComposerChangelogs\Exceptions\PackageResolverException;

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
     * @param \Composer\Package\RootPackageInterface $rootPackage
     * @param \Composer\Repository\WritableRepositoryInterface $packageRepository
     */
    public function __construct(
        \Composer\Package\RootPackageInterface $rootPackage,
        \Composer\Repository\WritableRepositoryInterface $packageRepository
    ) {
        $this->rootPackage = $rootPackage;
        $this->packageRepository = $packageRepository;
    }

    public function getByName($query)
    {
        /** @var \Composer\Repository\RepositoryInterface[] $repositories */
        $repositories = array(
            new \Composer\Repository\ArrayRepository(array($this->rootPackage)),
            $this->packageRepository
        );

        $matchesGroups = array();
        
        foreach ($repositories as $repositoryId => $item) {
            $matchesGroups[$repositoryId] = $item->search($query);
        }

        if (!array_filter($matchesGroups)) {
            throw new PackageResolverException(
                sprintf('No packages for query %s', $query)
            );
        }

        $matches = array_reduce($matchesGroups, 'array_merge', array());

        $exactMatches = array_filter($matches, function (array $match) use ($query) {
            return $match['name'] === $query;
        });
        
        if (!empty($exactMatches)) {
            $matches = $exactMatches;
        }
        
        if (count($matches) > 1) {
            $exception = new PackageResolverException(
                sprintf('Multiple packages found for query %s:', $query)
            );

            $exception->setExtraInfo(array_map(function ($match) {
                return $match['name'];
            }, $matches));

            throw $exception;
        }

        $repositoryKey = key(array_filter($matchesGroups));

        $repository = $repositories[$repositoryKey];
        $firstMatch = reset($matches);

        $package = $repository->findPackage($firstMatch['name'], '*');
        
        if (!$package) {
            throw new PackageResolverException(
                sprintf('No packages for query %s', $query)
            );
        }
        
        return $package;
    }
}
