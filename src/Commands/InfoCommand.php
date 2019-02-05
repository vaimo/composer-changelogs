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

class InfoCommand extends \Composer\Command\BaseCommand
{
    protected function configure()
    {
        $this->setName('changelog:info');

        $this->setDescription('Generates summary of a given release');

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
            '--brief',
            null,
            \Symfony\Component\Console\Input\InputOption::VALUE_NONE,
            'Output overview on the targeted release'
        );

        $this->addOption(
            '--release',
            null,
            \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL,
            'Target specific release in the changelog. Default: latest valid versioned release'
        );

        $this->addOption(
            '--branch',
            null,
            \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL,
            'Match release branch (if provided in changelog item)'
        );

        $pluginConfig = new \Vaimo\ComposerChangelogs\Composer\Plugin\Config();

        $this->addOption(
            '--format',
            null,
            \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL,
            sprintf('Format of the output (%s)', implode(', ', $pluginConfig->getAvailableFormats())),
            'json'
        );

        $this->addOption(
            '--upcoming',
            null,
            \Symfony\Component\Console\Input\InputOption::VALUE_NONE,
            'Show upcoming version (if there is one)'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $packageName = $input->getArgument('name');

        $fromSource = $input->getOption('from-source');
        $briefMode = $input->getOption('brief');
        $version = $input->getOption('release');
        $format = $input->getOption('format');
        $branch = $input->getOption('branch');
        $showUpcoming = $input->getOption('upcoming');
        
        $composerRuntime = $this->getComposer();

        $errorOutputGenerator = new \Vaimo\ComposerChangelogs\Console\OutputGenerator();
        $packageRepositoryFactory = new Factories\PackageRepositoryFactory($composerRuntime);

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

        $changelogLoaderFactory = new Factories\Changelog\LoaderFactory($composerRuntime);
        $changelogLoader = $changelogLoaderFactory->create($fromSource);

        $validator = new \Vaimo\ComposerChangelogs\Validators\ChangelogValidator($changelogLoader, array(
            'failure' => '<error>%s</error>',
            'success' => '<info>%s</info>'
        ));

        $result = $validator->validateForPackage($package, $output->getVerbosity());

        if (!$result()) {
            array_map(array($output, 'writeln'), $result->getMessages());
            
            return 1;
        }

        $changelog = $changelogLoader->load($package);

        if (!$version) {
            $changelogReleaseResolver = new \Vaimo\ComposerChangelogs\Resolvers\ChangelogReleaseResolver();

            if (!$showUpcoming) {
                $version = $changelogReleaseResolver->resolveLatestVersionedRelease($changelog, $branch);
            } else {
                $version = $changelogReleaseResolver->resolveUpcomingRelease($changelog, $branch);
            }
        }

        if (!$version || !isset($changelog[$version])) {
            return 0;
        }

        $details = $changelog[$version];

        $releaseDetailsResolver = new \Vaimo\ComposerChangelogs\Resolvers\ReleaseDetailsResolver();

        $generalInfo = $releaseDetailsResolver->resolveOverview($details);
        $groups = $releaseDetailsResolver->resolveChangeGroups($details);

        if ($briefMode) {
            $summary = array_map(function ($key, $group) {
                return sprintf('%s (%s)', $key, count($group));
            }, array_keys($groups), $groups);

            $generalInfo = $releaseDetailsResolver->resolveOverview($details);

            $groups = array(
                'overview' => $generalInfo['overview'],
                'summary' => sprintf('Includes: %s', implode(', ', $summary))
            );
        } else if ($generalInfo['overview']) {
            $groups = array_merge(
                $groups,
                array('overview' => $generalInfo['overview'])
            );
        }

        if ($format === 'json') {
            $result = json_encode($groups, JSON_PRETTY_PRINT);
        } else {
            $configResolverFactory = new Factories\Changelog\ConfigResolverFactory($composerRuntime);

            $configResolver = $configResolverFactory->create($fromSource);

            $templates = $configResolver->resolveOutputTemplates();

            $renderContextGenerator = new \Vaimo\ComposerChangelogs\Generators\Changelog\RenderContextGenerator();
            $templateRenderer = new \Vaimo\ComposerChangelogs\Generators\TemplateOutputGenerator();

            $contextData = $renderContextGenerator->generate(array('' => $groups));

            if (!isset($templates[$format])) {
                $output->writeln(
                    sprintf(
                        '<error>Unknown format: %s; available options: %s</error>', 
                        $format, 
                        implode(', ', array_merge(array('json'), array_keys($templates)))
                    )
                );
                
                return 1;
            }
            
            $result = $templateRenderer->generateOutput(
                reset($contextData['releases']),
                array('root' => $templates[$format]['release'])
            );
        }

        $output->writeln($result);
        
        return 0;
    }
}
