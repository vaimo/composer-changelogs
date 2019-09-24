<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
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

        $composerCtxFactory = new \Vaimo\ComposerChangelogs\Factories\ComposerContextFactory(
            $composerRuntime
        );

        $composerContext = $composerCtxFactory->create();
        
        $chLogRepoFactory = new Factories\ChangelogRepositoryFactory($composerContext, $output);
        $chLogRepo = $chLogRepoFactory->create($fromSource);

        $changelog = $chLogRepo->getByPackageName(
            $packageName,
            $output->getVerbosity()
        );

        if ($changelog === null) {
            return 1;
        }

        $confResolverFactory = new Factories\Changelog\ConfigResolverFactory($composerContext);

        $confResolver = $confResolverFactory->create($fromSource);

        $package = $changelog->getOwner();
        
        $output->writeln(
            sprintf('Generating changelog output for <info>%s</info>', $package->getName())
        );

        $infoResolver = new Resolvers\PackageInfoResolver(
            $composerRuntime->getInstallationManager()
        );

        $featureFlags = $confResolver->getFeatureFlags($package);

        $urlResolver = $this->createUrlResolver($repositoryUrl, $featureFlags);
        
        $docsGenerator = new \Vaimo\ComposerChangelogs\Generators\DocumentationGenerator(
            $confResolver,
            $infoResolver,
            $urlResolver
        );

        try {
            $docsGenerator->generate($package, $changelog->getReleases());
        } catch (\Vaimo\ComposerChangelogs\Exceptions\GeneratorException $exception) {
            $output->writeln(
                sprintf('<error>%s</error>', $exception->getMessage())
            );

            return 1;
        }

        $output->writeln('<info>Done</info>');
        
        return 0;
    }
    
    private function createUrlResolver($repositoryUrl, array $featureFlags)
    {
        if ($repositoryUrl !== null || !$featureFlags['links']) {
            return new Resolvers\Url\CustomSourceResolver($repositoryUrl);
        }

        $composerRuntime = $this->getComposer();
        
        $infoResolver = new Resolvers\PackageInfoResolver(
            $composerRuntime->getInstallationManager()
        );

        return new Resolvers\Url\RemoteSourceResolver($infoResolver);
    }
}
