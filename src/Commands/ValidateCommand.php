<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Vaimo\ComposerChangelogs\Factories;

class ValidateCommand extends \Composer\Command\BaseCommand
{
    protected function configure()
    {
        $this->setName('changelog:validate');

        $this->setDescription('Validate changelog.');

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

        $packageRepositoryFactory = new Factories\PackageRepositoryFactory($composer);
        $changelogLoaderFactory = new Factories\Changelog\LoaderFactory($composer);

        $errorExtractor = new \Vaimo\ComposerChangelogs\Extractors\ErrorExtractor();

        $packageRepository = $packageRepositoryFactory->create();
        $changelogLoader = $changelogLoaderFactory->create($fromSource);

        try {
            $package = $packageRepository->getByName($packageName);

            $changelogLoader->load($package);
        } catch (\Exception $exception) {
            if ($output->getVerbosity() > OutputInterface::VERBOSITY_VERBOSE) {
                throw $exception;
            }

            if ($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
                $messages = $errorExtractor->extractMessages($exception);

                array_walk($messages, function (&$message, $index) {
                    $message = sprintf('#%s %s', $index, $message);
                });
            } else {
                $messages = array('Changelog is invalid');
            }

            foreach ($messages as $index => $message) {
                $output->writeln(
                    sprintf('<error>%s</error>', $message)
                );
            }

            $this->setCode(function () {
                return 1;
            });

            exit(1);
        }

        $output->writeln('<info>Changelog is valid</info>');
    }
}
