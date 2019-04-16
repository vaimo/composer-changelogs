<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Repositories;

use Symfony\Component\Console\Output\OutputInterface as Output;

use Vaimo\ComposerChangelogs\Exceptions\PackageResolverException;

class ChangelogRepository
{
    /**
     * @var \Vaimo\ComposerChangelogs\Resolvers\PackageResolver
     */
    private $packageResolver;

    /**
     * @var \Vaimo\ComposerChangelogs\Loaders\ChangelogLoader
     */
    private $changelogLoader;

    /**
     * @var \Vaimo\ComposerChangelogs\Validators\ChangelogValidator
     */
    private $changelogValidator;

    /**
     * @var \Vaimo\ComposerChangelogs\Console\OutputGenerator
     */
    private $outputGenerator;

    /**
     * @param \Vaimo\ComposerChangelogs\Resolvers\PackageResolver $packageResolver
     * @param \Vaimo\ComposerChangelogs\Loaders\ChangelogLoader $changelogLoader
     * @param \Vaimo\ComposerChangelogs\Validators\ChangelogValidator $changelogValidator
     * @param \Vaimo\ComposerChangelogs\Console\OutputGenerator $outputGenerator
     */
    public function __construct(
        \Vaimo\ComposerChangelogs\Resolvers\PackageResolver $packageResolver,
        \Vaimo\ComposerChangelogs\Loaders\ChangelogLoader $changelogLoader,
        \Vaimo\ComposerChangelogs\Validators\ChangelogValidator $changelogValidator,
        \Vaimo\ComposerChangelogs\Console\OutputGenerator $outputGenerator
    ) {
        $this->packageResolver = $packageResolver;
        $this->changelogLoader = $changelogLoader;
        $this->changelogValidator = $changelogValidator;
        $this->outputGenerator = $outputGenerator;
    }

    public function getByPackageName($query, $verbosity = Output::VERBOSITY_NORMAL)
    {
        try {
            $package = $this->packageResolver->resolvePackage(is_string($query) ? $query : '');
        } catch (PackageResolverException $exception) {
            $this->outputGenerator->writeResolverException($exception);

            return null;
        }
        
        $result = $this->changelogValidator->validateForPackage($package, $verbosity);

        if (!$result()) {
            $this->outputGenerator->writeLines($result->getMessages());
            
            return null;
        }

        $changelog = $this->changelogLoader->load($package);

        return new \Vaimo\ComposerChangelogs\Data\Changelog(
            $package,
            $changelog
        );
    }
}
