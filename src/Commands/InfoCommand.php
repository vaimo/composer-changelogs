<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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

        $this->addOption(
            '--format',
            null,
            \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL,
            'Format of the output (json, sphinx, html, rst, md, yml)',
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
        
        $composer = $this->getComposer();

        $packageRepositoryFactory = new Factories\PackageRepositoryFactory($composer);
        $changelogLoaderFactory = new Factories\Changelog\LoaderFactory($composer);

        $packageRepository = $packageRepositoryFactory->create();
        $changelogLoader = $changelogLoaderFactory->create($fromSource);

        try {
            $package = $packageRepository->getByName($packageName);
        } catch (\Exception $e) {
            $output->writeln(
                sprintf('<error>%s</error>', $e->getMessage())
            );

            exit(1);
        }

        $validator = new \Vaimo\ComposerChangelogs\Validators\ChangelogValidator($changelogLoader, array(
            'failure' => '<error>%s</error>',
            'success' => '<info>%s</info>'
        ));

        $result = $validator->validateForPackage($package, $output->getVerbosity());

        if (!$result()) {
            array_map(array($output, 'writeln'), $result->getMessages());
            
            exit(1);
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
            return;
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
                'overview' => array_merge(
                    $generalInfo['overview'],
                    array_filter(array(
                        $generalInfo['overview'] ? '----------------------' : '',
                        'Includes: ' . implode(', ', $summary)
                    ))
                )
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
            $configResolverFactory = new Factories\Changelog\ConfigResolverFactory($composer);

            $configResolver = $configResolverFactory->create($fromSource);

            $templates = $configResolver->resolveOutputTemplates($package);

            $dataConverter = new \Vaimo\ComposerChangelogs\Generators\Changelog\RenderContextGenerator();
            $templateRenderer = new \Vaimo\ComposerChangelogs\Generators\TemplateOutputGenerator();

            $contextData = $dataConverter->generate(array('' => $groups));

            if (!isset($templates[$format])) {
                $output->writeln(
                    sprintf(
                        '<error>Unknown format: %s; available options: json, %s</error>', 
                        $format, 
                        implode(', ', array_keys($templates))
                    )
                );
                
                return;
            }
            
            $result = $templateRenderer->generateOutput(
                reset($contextData['releases']),
                array('root' => $templates[$format]['release'])
            );
        }

        $output->writeln($result);
    }
}
