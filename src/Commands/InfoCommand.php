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
        
        $composerRuntime = $this->getComposer();
        
        try {
            $package = $this->resolvePackage($packageName);
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
            array_map(array($output, 'writeln'), $result->getMessages());
            
            return 1;
        }

        $changelog = $chLogLoader->load($package);

        if (!$version) {
            $version = $this->resolveVersion($changelog, $branch, $showUpcoming);
        }

        if (!$version || !isset($changelog[$version])) {
            return 0;
        }

        $details = $changelog[$version];

        $detailsResolver = new \Vaimo\ComposerChangelogs\Resolvers\ReleaseDetailsResolver();

        $generalInfo = $detailsResolver->resolveOverview($details);
        $groups = $detailsResolver->resolveChangeGroups($details);

        if ($briefMode) {
            $summary = array_map(function ($key, $group) {
                return sprintf('%s (%s)', $key, count($group));
            }, array_keys($groups), $groups);

            $generalInfo = $detailsResolver->resolveOverview($details);

            $groups = array(
                'overview' => $generalInfo['overview'],
                'summary' => sprintf('Includes: %s', implode(', ', $summary))
            );
        } elseif ($generalInfo['overview']) {
            $groups = array_merge(
                $groups,
                array('overview' => $generalInfo['overview'])
            );
        }

        try {
            $result = $this->generateOutput($groups, $format, $fromSource);
        } catch (\Exception $exception) {
            $output->writeln(
                sprintf('<error>%s</error>', $exception->getMessage())
            );

            return 1;
        }

        $output->writeln($result);
        
        return 0;
    }
    
    private function generateOutput($groups, $format, $fromSource)
    {
        $composerRuntime = $this->getComposer();
        
        if ($format === 'json') {
            $jsonEncoder = new \Camspiers\JsonPretty\JsonPretty();

            return $jsonEncoder->prettify($groups, null, '    ');
        }

        $confResolverFactory = new Factories\Changelog\ConfigResolverFactory($composerRuntime);

        $confResolver = $confResolverFactory->create($fromSource);

        $templates = $confResolver->resolveOutputTemplates();

        $renderCtxGenerator = new \Vaimo\ComposerChangelogs\Generators\Changelog\RenderContextGenerator();
        $templateRenderer = new \Vaimo\ComposerChangelogs\Generators\TemplateOutputGenerator();

        $ctxData = $renderCtxGenerator->generate(
            array('' => $groups)
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
    
    private function resolveVersion($changelog, $branch, $showUpcoming)
    {
        $releaseResolver = new \Vaimo\ComposerChangelogs\Resolvers\ChangelogReleaseResolver();

        if (!$showUpcoming) {
            return $releaseResolver->resolveLatestVersionedRelease($changelog, $branch);
        }

        return $releaseResolver->resolveUpcomingRelease($changelog, $branch);
    }
}
