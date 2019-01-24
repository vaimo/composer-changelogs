<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Vaimo\ComposerChangelogs\Factories;

class VersionCommand extends \Composer\Command\BaseCommand
{
    protected function configure()
    {
        $this->setName('changelog:version');

        $this->setDescription('Display version information from changelog. <comment>[default: latest stable]</comment>');

        $this->addArgument(
            'name',
            \Symfony\Component\Console\Input\InputArgument::OPTIONAL,
            'Targeted package name. Default: root package'
        );

        $this->addOption(
            '--from-source',
            null,
            \Symfony\Component\Console\Input\InputOption::VALUE_NONE,
            'Extract configuration from vendor package instead of using global installation data'
        );

        $this->addOption(
            '--format',
            null,
            \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL,
            'Format of the output (regex)'
        );

        $this->addOption(
            '--segments',
            null,
            \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL,
            'Number of segments of the version to return. <comment>[default: all segments]</comment>'
        );
        
        $this->addOption(
            '--upcoming',
            null,
            \Symfony\Component\Console\Input\InputOption::VALUE_NONE,
            'Show upcoming version (if there is one)'
        );

        $this->addOption(
            '--branch',
            null,
            \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL,
            'Match release branch (if provided in changelog item)'
        );

        $this->addOption(
            '--tip',
            null,
            \Symfony\Component\Console\Input\InputOption::VALUE_NONE,
            'Show upcoming version (if there is one)'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $packageName = $input->getArgument('name');
        $fromSource = $input->getOption('from-source');
        $format = $input->getOption('format');
        $branch = $input->getOption('branch');
        $segmentsCount = $input->getOption('segments');

        $showUpcoming = $input->getOption('upcoming');
        $showTip = $input->getOption('tip');

        $composer = $this->getComposer();

        $packageRepositoryFactory = new Factories\PackageRepositoryFactory($composer);
        $changelogLoaderFactory = new Factories\Changelog\LoaderFactory($composer);

        $packageRepository = $packageRepositoryFactory->create();
        $changelogLoader = $changelogLoaderFactory->create($fromSource);

        try {
            $package = $packageRepository->getByName($packageName);
        } catch (\Exception $e) {
            return;
        }

        $validator = new \Vaimo\ComposerChangelogs\Validators\ChangelogValidator($changelogLoader);

        $result = $validator->validateForPackage($package, $output->getVerbosity());

        if (!$result()) {
            return;
        }

        $changelog = $changelogLoader->load($package);

        $changelogReleaseResolver = new \Vaimo\ComposerChangelogs\Resolvers\ChangelogReleaseResolver();

        if (!$showTip) {
            $version = $changelogReleaseResolver->resolveLatestVersionedRelease($changelog, $branch);

            if ($showUpcoming) {
                $version = $changelogReleaseResolver->resolveUpcomingRelease($changelog, $branch);
            }
        } else {
            $version = key($changelog);
        }

        if (!$version) {
            return;
        }

        if ($format == 'regex') {
            $version = preg_quote($version);
        }

        if ($segmentsCount) {
            $version = implode('.', array_slice(explode('.', $version), 0, $segmentsCount));
        }
        
        $output->writeln($version);
    }
}
