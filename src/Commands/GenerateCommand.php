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

        $composerRuntime = $this->getComposer();

        $packageRepositoryFactory = new Factories\PackageRepositoryFactory($composerRuntime);

        $packageRepository = $packageRepositoryFactory->create();

        try {
            $package = $packageRepository->getByName($packageName);
        } catch (\Exception $e) {
            $output->writeln(
                sprintf('<error>%s</error>', $e->getMessage())
            );

            return;
        }

        $configResolverFactory = new Factories\Changelog\ConfigResolverFactory($composerRuntime);

        $configResolver = $configResolverFactory->create($fromSource);

        $changelogLoader = new \Vaimo\ComposerChangelogs\Loaders\ChangelogLoader($configResolver);

        $validator = new \Vaimo\ComposerChangelogs\Validators\ChangelogValidator($changelogLoader, array(
            'failure' => '<error>%s</error>',
            'success' => '<info>%s</info>'
        ));

        $result = $validator->validateForPackage($package, $output->getVerbosity());

        if (!$result()) {
            array_map(array($output, 'writeln'), $result->getMessages());
            
            exit(1);
        }

        $output->writeln(
            sprintf('Generating changelog output for <info>%s</info>', $package->getName())
        );

        $infoResolver = new \Vaimo\ComposerChangelogs\Resolvers\PackageInfoResolver(
            $composerRuntime->getInstallationManager()
        );
        
        $docsGenerator = new \Vaimo\ComposerChangelogs\Generators\DocumentationGenerator(
            $configResolver,
            $changelogLoader,
            $infoResolver
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
