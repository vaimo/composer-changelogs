<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument as Argument;
use Symfony\Component\Console\Input\InputOption as Option;

use Vaimo\ComposerChangelogs\Composer\Plugin\Config as PluginConfig;
use Vaimo\ComposerChangelogs\Composer\Config as ComposerConfig;

class BootstrapCommand extends \Composer\Command\BaseCommand
{
    protected function configure()
    {
        $pluginConfig = new \Vaimo\ComposerChangelogs\Composer\Plugin\Config();

        $this->setName('changelog:bootstrap');

        $this->setDescription('Add basic configuration for the usage of generated change-logs');

        $this->addArgument(
            'name',
            Argument::OPTIONAL,
            'Targeted package name. Default: root package'
        );

        $this->addOption(
            '--format',
            null,
            Option::VALUE_OPTIONAL,
            sprintf('Format of the output (%s)', implode(', ', $pluginConfig->getAvailableFormats())),
            'md'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $packageName = $input->getArgument('name');
        $format = $input->getOption('format');

        $composerCtxFactory = new \Vaimo\ComposerChangelogs\Factories\ComposerContextFactory($this->getComposer());
        $composerCtx = $composerCtxFactory->create();

        $commandCtx = new \Vaimo\ComposerChangelogs\Console\Command\ExecutionContext($output, $composerCtx);
        
        $cfgResolverFactory = new \Vaimo\ComposerChangelogs\Factories\Changelog\ConfigResolverFactory($composerCtx);
        $cfgResolver = $cfgResolverFactory->create();

        $packageManager = new \Vaimo\ComposerChangelogs\Managers\PackageManager($composerCtx);

        $package = $commandCtx->resolvePackage($packageName);

        if ($package === null) {
            return 1;
        }

        $output->writeln(
            sprintf('Bootstrapping changelog generation for <info>%s</info>', $package->getName())
        );
        
        if ($cfgResolver->hasConfig($package)) {
            $rootPath = array(ComposerConfig::CONFIG_ROOT, PluginConfig::ROOT);

            $message = sprintf(
                'Configuration root (<comment>%s</comment>) already present in package config',
                implode('/', $rootPath)
            );
            
            $output->writeln($message);
            
            return 0;
        }
        
        try {
            $packageManager->bootstrapChangelogGeneration($package, $format);
        } catch (\Vaimo\ComposerChangelogs\Exceptions\UpdaterException $exception) {
            $message = sprintf('<error>%s</error>', $exception->getMessage());
            $output->writeln($message);
            
            return 1;
        }
        
        $output->writeln('<info>Done</info>');
        
        return 0;
    }
}
