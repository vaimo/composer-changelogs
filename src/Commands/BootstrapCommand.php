<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Vaimo\ComposerChangelogs\Composer\Plugin\Config as PluginConfig;
use Vaimo\ComposerChangelogs\Composer\Config as ComposerConfig;
use Vaimo\ComposerChangelogs\Composer\Files as ComposerFiles;
use Vaimo\ComposerChangelogs\Factories;

class BootstrapCommand extends \Composer\Command\BaseCommand
{
    protected function configure()
    {
        $this->setName('changelog:bootstrap');

        $this->setDescription('Add basic configuration for the usage of change-logs');

        $this->addArgument(
            'name',
            \Symfony\Component\Console\Input\InputArgument::OPTIONAL,
            'Targeted package name. Default: root package'
        );

        $this->addOption(
            '--type',
            null,
            \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL,
            'Type of output (sphinx, md)',
            'md'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $packageName = $input->getArgument('name');
        $type = $input->getOption('type');

        $composerRuntime = $this->getComposer();

        $composerCtxFactory = new \Vaimo\ComposerChangelogs\Factories\ComposerContextFactory(
            $composerRuntime
        );

        $composerContext = $composerCtxFactory->create();

        $packageRepoFactory = new Factories\PackageResolverFactory($composerContext);
        $packageResolver = $packageRepoFactory->create();

        $outputGenerator = new \Vaimo\ComposerChangelogs\Console\OutputGenerator($output);
        
        try {
            $package = $packageResolver->resolvePackage(is_string($packageName) ? $packageName : '');
        } catch (PackageResolverException $exception) {
            $outputGenerator->writeResolverException($exception);

            return null;
        }

        $output->writeln(
            sprintf('Bootstrapping changelogs for <info>%s</info>', $package->getName())
        );
        
        /** @var /Composer/Composer $composer */
        $composer = $composerContext->getLocalComposer();

        $packageInfoResolver = new \Vaimo\ComposerChangelogs\Resolvers\PackageInfoResolver(
            $composer->getInstallationManager()
        );
        
        $configExtractor = new \Vaimo\ComposerChangelogs\Extractors\VendorConfigExtractor($packageInfoResolver);
        
        $config = $configExtractor->getConfig($package);

        if (isset($config[PluginConfig::ROOT])) {
            $rootPath = array(ComposerConfig::CONFIG_ROOT, PluginConfig::ROOT);

            $message = sprintf(
                'Configuration root (<comment>%s</comment>) already present in package config', 
                implode('/', $rootPath)
            );
            
            $output->writeln($message);
            
            return 0;
        }
        
        $installPath = $packageInfoResolver->getInstallPath($package);

        $config = $configExtractor->getPackageFullConfig($package);

        $paths = [
            'md' => 'CHANGELOG.md',
            'sphinx' => 'docs/changelog.rst'
        ];

        $update = array(
            ComposerConfig::CONFIG_ROOT => array(
                PluginConfig::ROOT => array(
                    'source' => 'changelog.json',
                    'output' => array(
                        $type => $paths[$type]
                    )   
                )
            )
        );
        
        $config = array_replace_recursive($config, $update);

        $pathUtils = new \Vaimo\ComposerChangelogs\Utils\PathUtils();

        $encodedConfig = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        
        $pkgConfigPath = $pathUtils->composePath($installPath, ComposerFiles::PACKAGE_CONFIG);
        $chLogConfigPath = $pathUtils->composePath($installPath, 'changelog.json');
        
        file_put_contents($pkgConfigPath, $encodedConfig);
        
        if (!file_exists($chLogConfigPath)) {
            $changeLog = array(
                '_readme' => array(
                    'The contents of this file are used to generate CHANGELOG.md; It\'s kept in '
                        . 'JSON/parsable format to make it',
                    'possible to generate change-logs in other formats as well (when needed) and '
                        . 'to do automatic releases based on',
                    'added change-log records. More on how to use it: https://github.com/vaimo/composer-changelogs'
                )
            );
            
            file_put_contents($chLogConfigPath, json_encode($changeLog, JSON_PRETTY_PRINT));
        }

        $output->writeln('<info>Done</info>');
    }
}
