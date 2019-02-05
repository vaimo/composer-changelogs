<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Vaimo\ComposerChangelogs\Exceptions\PackageResolverException;

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

        $composerRuntime = $this->getComposer();

        $packageRepositoryFactory = new Factories\PackageRepositoryFactory($composerRuntime);
        $errorOutputGenerator = new \Vaimo\ComposerChangelogs\Console\OutputGenerator();
        
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

        $changelogLoaderFactory = new Factories\Changelog\LoaderFactory($composerRuntime);

        $changelogLoader = $changelogLoaderFactory->create($fromSource);

        $validator = new \Vaimo\ComposerChangelogs\Validators\ChangelogValidator($changelogLoader, array(
            'failure' => '<error>%s</error>',
            'success' => '<info>%s</info>'
        ));
        
        $result = $validator->validateForPackage($package, $output->getVerbosity());

        array_map(array($output, 'writeln'), $result->getMessages());
        
        return (int)!$result();
    }
}
