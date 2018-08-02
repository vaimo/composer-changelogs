<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Vaimo\ComposerChangelogs\Factories;

class GenerateCommand extends \Composer\Command\BaseCommand
{
    protected function configure()
    {
        $this->setName('changelog:generate');

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

        $output->writeln(
            sprintf('Generating change-log output for <info>%s</info>', $package->getName())
        );

        $configResolverFactory = new \Vaimo\ComposerChangelogs\Factories\Changelog\ConfigResolverFactory(
            $composer->getRepositoryManager()->getLocalRepository(),
            $composer->getInstallationManager()
        );

        $docsGenerator = new \Vaimo\ComposerChangelogs\Generators\DocumentationGenerator(
            $configResolverFactory->create($fromSource),
            new \Vaimo\ComposerChangelogs\Repositories\Documentation\TypeRepository()
        );

        try {
            $docsGenerator->generate($package);
        } catch (\Vaimo\ComposerChangelogs\Exceptions\GeneratorException $exception) {
            $output->writeln(
                sprintf('<error>%s</error>', $exception->getMessage())
            );

            return;
        }

        $output->writeln('<info>Done</info>');
    }
}
