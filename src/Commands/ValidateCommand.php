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

        try {
            $package = $this->resolvePackage(is_string($packageName) ? $packageName : '');
        } catch (PackageResolverException $exception) {
            $this->printException($exception, $output);

            return 1;
        }

        $chLogLoaderFactory = new Factories\Changelog\LoaderFactory($composerRuntime);

        $chLogLoader = $chLogLoaderFactory->create($fromSource);

        $validator = new \Vaimo\ComposerChangelogs\Validators\ChangelogValidator($chLogLoader, array(
            'failure' => '<error>%s</error>',
            'success' => '<info>%s</info>'
        ));
        
        $result = $validator->validateForPackage($package, $output->getVerbosity());

        array_map(array($output, 'writeln'), $result->getMessages());
        
        return (int)!$result();
    }

    private function printException($exception, OutputInterface $output)
    {
        $errorOutputGenerator = new \Vaimo\ComposerChangelogs\Console\OutputGenerator();

        \array_map(
            array($output, 'writeln'),
            $errorOutputGenerator->generateForResolverException($exception)
        );
    }
    
    /**
     * @param string $packageName
     * @return \Composer\Package\PackageInterface
     * @throws PackageResolverException
     */
    private function resolvePackage($packageName)
    {
        $composerRuntime = $this->getComposer();

        if (!$packageName) {
            $packageName = $composerRuntime->getPackage()->getName();
        }

        $packageRepoFactory = new Factories\PackageRepositoryFactory($composerRuntime);

        $packageRepository = $packageRepoFactory->create();

        return $packageRepository->getByName($packageName);
    }
}
