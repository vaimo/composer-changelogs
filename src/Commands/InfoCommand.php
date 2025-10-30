<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Composer\Package\PackageInterface as Package;

use Vaimo\ComposerChangelogs\Resolvers;
use Vaimo\ComposerChangelogs\Factories;
use Vaimo\ComposerChangelogs\Composer\Context as ComposerContext;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
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

        $composerCtxFactory = new \Vaimo\ComposerChangelogs\Factories\ComposerContextFactory(
            $this->getComposer()
        );
        
        $composerCtx = $composerCtxFactory->create();

        $chLogRepoFactory = new Factories\ChangelogRepositoryFactory($composerCtx, $output);
        $chLogRepo = $chLogRepoFactory->create($fromSource);
        
        $changelog = $chLogRepo->getByPackageName(
            $packageName,
            $output->getVerbosity()
        );

        if ($changelog === null) {
            return 1;
        }

        $releases = $changelog->getReleases();
        
        if (!$version) {
            $version = $this->resolveVersion($releases, $branch, $showUpcoming);
        }

        if (!$version || !isset($releases[$version])) {
            return 0;
        }

        $groups = $this->resolveOutputGroups(
            $releases[$version],
            $briefMode
        );

        try {
            $result = $this->generateOutput(
                $composerCtx,
                $changelog->getOwner(),
                $groups,
                $format,
                $fromSource
            );
        } catch (\Exception $exception) {
            $output->writeln(
                sprintf('<error>%s</error>', $exception->getMessage())
            );

            return 1;
        }

        $output->writeln($result);

        return 0;
    }

    /**
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     *
     * @param array $details
     * @param bool $briefMode
     * @return array
     */
    private function resolveOutputGroups(array $details, $briefMode = false)
    {
        $detailsResolver = new \Vaimo\ComposerChangelogs\Resolvers\ReleaseDetailsResolver();

        $generalInfo = $detailsResolver->resolveOverview($details);
        $groups = $detailsResolver->resolveChangeGroups($details);

        if ($briefMode) {
            $summary = array_map(function ($key, $group) {
                return sprintf('%s (%s)', $key, count($group));
            }, array_keys($groups), $groups);

            $generalInfo = $detailsResolver->resolveOverview($details);

            $groups = array_filter(array(
                'overview' => $generalInfo['overview'],
                'summary' => sprintf('Includes: %s', implode(', ', $summary))
            ));
        } elseif ($generalInfo['overview']) {
            $groups = array_merge(
                $groups,
                array('overview' => $generalInfo['overview'])
            );
        }

        return $groups;
    }

    private function generateOutput(ComposerContext $composerCtx, Package $package, $groups, $format, $fromSource)
    {
        $composerRuntime = $this->getComposer();

        if ($format === 'json') {
            return json_encode($groups, JSON_PRETTY_PRINT);
        }
        
        $confResolverFactory = new Factories\Changelog\ConfigResolverFactory($composerCtx);

        $confResolver = $confResolverFactory->create($fromSource);

        $templateResolver = new \Vaimo\ComposerChangelogs\Resolvers\ChangelogTemplateResolver($confResolver);
        
        $templates = $templateResolver->getTemplates($package);

        $renderCtxGenerator = new \Vaimo\ComposerChangelogs\Generators\Changelog\RenderContextGenerator();
        $templateRenderer = new \Vaimo\ComposerChangelogs\Generators\TemplateOutputGenerator();

        $infoResolver = new Resolvers\PackageInfoResolver(
            $composerRuntime->getInstallationManager()
        );
        
        $repositoryRoot = $infoResolver->getInstallPath($package);
        
        $ctxData = $renderCtxGenerator->generate(
            array('' => $groups),
            '',
            $repositoryRoot
        );

        if (!isset($templates[$format])) {
            throw new \Vaimo\ComposerChangelogs\Exceptions\GeneratorException(sprintf(
                'Unknown format: %s; available options: %s',
                $format,
                implode(', ', array_merge(array('json'), array_keys($templates)))
            ));
        }

        return $templateRenderer->generateOutput(
            reset($ctxData['releases']),
            array('root' => $templates[$format]['release'])
        );
    }
    
    private function resolveVersion($changelog, $branch, $showUpcoming)
    {
        $releaseResolver = new \Vaimo\ComposerChangelogs\Resolvers\ChangelogReleaseResolver();

        if (!$showUpcoming) {
            return $releaseResolver->resolveLatestVersionedRelease($changelog, $branch);
        }

        return $releaseResolver->resolveUpcomingRelease($changelog, $branch);
    }
}
