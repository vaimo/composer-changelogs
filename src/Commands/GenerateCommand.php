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
            '--type',
            null,
            \Symfony\Component\Console\Input\InputOption::VALUE_NONE,
            'Generate documentation of certain type'
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
        $generatorType = $input->getOption('type');
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

        $configResolverFactory = new Factories\Changelog\ConfigResolverFactory(
            $composer->getRepositoryManager()->getLocalRepository(),
            $composer->getInstallationManager()
        );

        $configResolver = $configResolverFactory->create($fromSource);

        $jsonFileReader = new \Vaimo\ComposerChangelogs\Readers\JsonFileReader();

        if (!$sourcePath = $configResolver->resolveSourcePath($package)) {
            $output->writeln(sprintf(
                '<error>Changelog source path not defined for: %s</error>', $package->getName())
            );

            return;
        }

        $changelog = $jsonFileReader->readToArray($sourcePath);

        $outputPaths = $configResolver->resolveOutputTargets($package);

        $targets = $generatorType
            ? array_intersect_key($outputPaths, array($generatorType => true))
            : $outputPaths;

        /** @var \Vaimo\ComposerChangelogs\Interfaces\DocumentationGeneratorInterface[] $generators */
        $generators = array(
            'sphinx' => new \Vaimo\ComposerChangelogs\Generators\SphinxDocGenerator()
        );

        $templates = $configResolver->resolveOutputTemplates($package);

        foreach ($targets as $type => $target) {
            if (!isset($generators[$type])) {
                continue;
            }

            $generator = $generators[$type];

            try {
                $generator->generate($changelog,  $templates[$type], $target);
            } catch (\Vaimo\ComposerChangelogs\Exceptions\TemplateValidationException $exception) {
                $messages = array();

                do {
                    $messages[] = $exception->getMessage();
                } while ($exception = $exception->getPrevious());

                $messages = array_map(function ($message, $index) {
                    return sprintf('#%s - %s', $index + 1, $message);
                }, $messages, array_keys($messages));

                $errorMessage = sprintf(
                    "<error>Generator run for '%s' caused an error:<error>\n%s</error></error>",
                    $type,
                    implode("\n", $messages)
                );

                $output->writeln($errorMessage);

                break;
            }
        }
    }
}
