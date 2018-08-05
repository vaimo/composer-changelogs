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

        $this->setDescription('Generates documentation output from changelog source');

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
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $packageName = $input->getArgument('name');
        $fromSource = $input->getOption('from-source');

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

        $changelogReleaseResolver = new \Vaimo\ComposerChangelogs\Resolvers\ChangelogReleaseResolver();

        $version = $changelogReleaseResolver->resolveLatestVersionedRelease($changelog);

        if (!$version) {
            return;
        }

        $output->writeln($version);
    }
}
