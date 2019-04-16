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

        $this->setDescription(
            'Display version information from changelog. <comment>[default: latest stable]</comment>'
        );

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
            'Show LATEST version (might be latest release, might be upcoming)'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $packageName = $input->getArgument('name');
        $fromSource = $input->getOption('from-source');
        $format = $input->getOption('format');
        $branch = $input->getOption('branch');
        $segmentsCount = (int)$input->getOption('segments');

        $showUpcoming = $input->getOption('upcoming');
        $showTip = $input->getOption('tip');
        
        $chLogRepoFactory = new Factories\ChangelogRepositoryFactory(
            $this->getComposer(),
            $output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL ? $output : null
        );
        
        $chLogRepo = $chLogRepoFactory->create($fromSource);

        $changelog = $chLogRepo->getByPackageName($packageName);

        if ($changelog === null) {
            return 1;
        }

        $releases = $changelog->getReleases();
        
        $releaseResolver = new \Vaimo\ComposerChangelogs\Resolvers\ChangelogReleaseResolver();

        $version = key($releases);

        if (!$showTip) {
            $version = $releaseResolver->resolveLatestVersionedRelease($releases, $branch);

            if ($showUpcoming) {
                $version = $releaseResolver->resolveUpcomingRelease($releases, $branch);
            }
        }

        if (!$version) {
            return 0;
        }

        $versionResolver = new \Vaimo\ComposerChangelogs\Resolvers\VersionResolver();

        $version = $versionResolver->resolveValidVersion($version);
            
        if ($format == 'regex') {
            $version = preg_quote($version);
        }
        
        if ($segmentsCount) {
            $version = implode(
                '.',
                array_slice(
                    explode('.', $version),
                    0,
                    $segmentsCount
                )
            );
        }
        
        $output->writeln($version);
        
        return 0;
    }
}
