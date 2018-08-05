<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Vaimo\ComposerChangelogs\Factories;

class InfoCommand extends \Composer\Command\BaseCommand
{
    protected function configure()
    {
        $this->setName('changelog:info');

        $this->setDescription('Generates summary of a given release');

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
            '--brief',
            null,
            \Symfony\Component\Console\Input\InputOption::VALUE_NONE,
            'Output overview on the targeted release'
        );

        $this->addOption(
            '--release',
            null,
            \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL,
            'Target specific release in the changelog. Default: latest valid versioned release'
        );

        $this->addOption(
            '--format',
            null,
            \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL,
            'Format of the output (json, html, txt)',
            'txt'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $packageName = $input->getArgument('name');

        $fromSource = $input->getOption('from-source');
        $briefMode = $input->getOption('brief');
        $version = $input->getOption('release');
        $format = $input->getOption('format');

        $changelogRepository = new \Vaimo\ComposerChangelogs\Factories\Changelog\RepositoryFactory(
            $this->getComposer()
        );

        $changelogRepository = $changelogRepository->create($fromSource);

        try {
            $changelog = $changelogRepository->getForPackage($packageName);
        } catch (\Exception $e) {
            $output->writeln(
                sprintf('<error>%s</error>', $e->getMessage())
            );

            return;
        }

        if (!$version) {
            $changelogReleaseResolver = new \Vaimo\ComposerChangelogs\Resolvers\ChangelogReleaseResolver();

            $version = $changelogReleaseResolver->resolveLatestVersionedRelease($changelog);
        }

        if (!$version || !isset($changelog[$version])) {
            return;
        }

        $details = $changelog[$version];

        $releaseDetailsResolver = new \Vaimo\ComposerChangelogs\Resolvers\ReleaseDetailsResolver();

        $generalInfo = $releaseDetailsResolver->resolveOverview($details);
        $groups = $releaseDetailsResolver->resolveChangeGroups($details);

        if ($briefMode) {
            $summary = array_map(function ($key, $group) {
                return sprintf('%s (%s)', $key, count($group));
            }, array_keys($groups), $groups);

            $generalInfo = $releaseDetailsResolver->resolveOverview($details);

            $groups = array(
                'overview' => array_merge(
                    $generalInfo['overview'],
                    array('Includes: ' . implode(', ', $summary))
                )
            );
        } else if ($generalInfo['overview']) {
            $groups = array_merge(
                $groups,
                array('overview' => $generalInfo['overview'])
            );
        }

        $releaseInfoGenerator = new \Vaimo\ComposerChangelogs\Generators\ReleaseInfoGenerator();

        $releaseInfoGenerator->generate($groups, $format);

        $output->writeln(
            json_encode($groups, JSON_PRETTY_PRINT)
        );
    }
}
