<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Vaimo\ComposerChangelogs\Exceptions\PackageResolverException;
use Vaimo\ComposerChangelogs\Resolvers;

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

        $this->addOption(
            '--url',
            null,
            \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL,
            'Repository URL to use for generating version-bound links for sources and comparisons'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $packageName = $input->getArgument('name');
        
        $fromSource = $input->getOption('from-source');
        $repositoryUrl = $input->getOption('url');

        $composerRuntime = $this->getComposer();

        $packageRepositoryFactory = new Factories\PackageRepositoryFactory($composerRuntime);
        $errorOutputGenerator = new \Vaimo\ComposerChangelogs\Console\OutputGenerator();

        if (!$packageName) {
            $packageName = $composerRuntime->getPackage()->getName();
        }
        
        $packageRepository = $packageRepositoryFactory->create();
        
        try {
            $package = $packageRepository->getByName($packageName);
        } catch (PackageResolverException $exception) {
            \array_map(
                [$output, 'writeln'],
                $errorOutputGenerator->generateForResolverException($exception)
            );

            return 1;
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
            
            return 1;
        }

        $output->writeln(
            sprintf('Generating changelog output for <info>%s</info>', $package->getName())
        );

        $infoResolver = new Resolvers\PackageInfoResolver(
            $composerRuntime->getInstallationManager()
        );

        $featureFlags = $configResolver->getFeatureFlags($package);
        
        if ($repositoryUrl !== null || !$featureFlags['links']) {
            $urlResolver = new Resolvers\Url\CustomSourceResolver($repositoryUrl);            
        } else {
            $urlResolver = new Resolvers\Url\RemoteSourceResolver($infoResolver);
        }
        
        $docsGenerator = new \Vaimo\ComposerChangelogs\Generators\DocumentationGenerator(
            $configResolver,
            $changelogLoader,
            $infoResolver,
            $urlResolver
        );

        try {
            $docsGenerator->generate($package);
        } catch (\Vaimo\ComposerChangelogs\Exceptions\GeneratorException $exception) {
            $output->writeln(
                sprintf('<error>%s</error>', $exception->getMessage())
            );

            return 1;
        }

        $output->writeln('<info>Done</info>');
        
        return 0;
    }
}
