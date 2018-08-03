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

        $composer = $this->getComposer();

        if (!$packageName) {
            $package = $composer->getPackage();
        } else {
            $matches = $composer->getRepositoryManager()
                ->getLocalRepository()
                ->findPackages($packageName, '*');

            $package = reset($matches);
        }

        if (!$package) {
            $output->writeln(
                sprintf('<error>Failed to locate the package: %s</error>', $packageName)
            );

            return;
        }

        $realPackageResolver = new \Vaimo\ComposerChangelogs\Resolvers\RealPackageResolver();

        $package = $realPackageResolver->resolve($package);

        $configResolverFactory = new \Vaimo\ComposerChangelogs\Factories\Changelog\ConfigResolverFactory(
            $composer->getRepositoryManager()->getLocalRepository(),
            $composer->getInstallationManager()
        );

        $changelogLoader = new \Vaimo\ComposerChangelogs\Loaders\ChangelogLoader(
            $configResolverFactory->create($fromSource)
        );

        $versions = array_keys($changelogLoader->load($package));

        $constraintValidator = new \Vaimo\ComposerChangelogs\Validators\ConstraintValidator();

        foreach ($versions as $version) {
            if (!$constraintValidator->isConstraint($version)) {
                continue;
            }

            $output->writeln($version);

            break;
        }
    }
}
