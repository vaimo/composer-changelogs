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

class VersionCommand extends \Composer\Command\BaseCommand
{
    protected function configure()
    {
        $this->setName('changelog:version');

        $this->setDescription(
            'Display version information from changelog. <comment>[default: latest stable]</comment>'
        );

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
            '--format',
            null,
            \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL,
            'Format of the output (regex)'
        );

        $this->addOption(
            '--segments',
            null,
            \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL,
            'Number of segments of the version to return. <comment>[default: all segments]</comment>'
        );
        
        $this->addOption(
            '--upcoming',
            null,
            \Symfony\Component\Console\Input\InputOption::VALUE_NONE,
            'Show upcoming version (if there is one)'
        );

        $this->addOption(
            '--branch',
            null,
            \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL,
            'Match release branch (if provided in changelog item)'
        );

        $this->addOption(
            '--tip',
            null,
            \Symfony\Component\Console\Input\InputOption::VALUE_NONE,
            'Show LATEST version (might be latest release, might be upcoming)'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $packageName = $input->getArgument('name');
        $fromSource = $input->getOption('from-source');
        $format = $input->getOption('format');
        $branch = $input->getOption('branch');
        $segmentsCount = $input->getOption('segments');

        $showUpcoming = $input->getOption('upcoming');
        $showTip = $input->getOption('tip');

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

        if (!$result()) {
            return 1;
        }

        $changelog = $chLogLoader->load($package);

        $releaseResolver = new \Vaimo\ComposerChangelogs\Resolvers\ChangelogReleaseResolver();

        $version = key($changelog);

        if (!$showTip) {
            $version = $releaseResolver->resolveLatestVersionedRelease($changelog, $branch);

            if ($showUpcoming) {
                $version = $releaseResolver->resolveUpcomingRelease($changelog, $branch);
            }
        }

        if (!$version) {
            return 0;
        }

        $versionResolver = new \Vaimo\ComposerChangelogs\Resolvers\VersionResolver();

        $version = $versionResolver->resolveValidVersion($version);
            
        if ($format == 'regex') {
            $version = preg_quote($version);
        }
        
        if ($segmentsCount) {
            $version = implode('.', array_slice(explode('.', $version), 0, $segmentsCount));
        }
        
        $output->writeln($version);
        
        return 0;
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
