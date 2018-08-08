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

        $this->setDescription('Validate package changelog markup and structure');

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

        $validator = new \Vaimo\ComposerChangelogs\Validators\ChangelogValidator($changelogLoader, [
            'failure' => '<error>%s</error>',
            'success' => '<info>%s</info>'
        ]);

        try {
            $package = $packageRepository->getByName($packageName);
        } catch (\Exception $e) {
            $output->writeln(
                sprintf('<error>%s</error>', $e->getMessage())
            );

            exit(1);
        }

        $result = $validator->validateForPackage($package, $output->getVerbosity());

        array_map([$output, 'writeln'], $result->getMessages());

        if (!$result()) {
            exit(1);
        }
    }
}
