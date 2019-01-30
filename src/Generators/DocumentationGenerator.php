<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Generators;

class DocumentationGenerator
{
    /**
     * @var \Vaimo\ComposerChangelogs\Resolvers\ChangelogConfigResolver
     */
    private $configResolver;

    /**
     * @var \Vaimo\ComposerChangelogs\Loaders\ChangelogLoader
     */
    private $changelogLoader;

    /**
     * @var \Vaimo\ComposerChangelogs\Resolvers\PackageInfoResolver
     */
    private $packageInfoResolver;

    /**
     * @var \Vaimo\ComposerChangelogs\Generators\Changelog\RenderContextGenerator
     */
    private $contextGenerator;

    /**
     * @var \Vaimo\ComposerChangelogs\Generators\TemplateOutputGenerator
     */
    private $templateRenderer;

    /**
     * @var \Vaimo\ComposerChangelogs\Interfaces\UrlResolverInterface
     */
    private $urlResolver;

    /**
     * @param \Vaimo\ComposerChangelogs\Resolvers\ChangelogConfigResolver $configResolver
     * @param \Vaimo\ComposerChangelogs\Loaders\ChangelogLoader $changelogLoader
     * @param \Vaimo\ComposerChangelogs\Resolvers\PackageInfoResolver $packageInfoResolver
     * @param \Vaimo\ComposerChangelogs\Interfaces\UrlResolverInterface $urlResolver
     */
    public function __construct(
        \Vaimo\ComposerChangelogs\Resolvers\ChangelogConfigResolver $configResolver,
        \Vaimo\ComposerChangelogs\Loaders\ChangelogLoader $changelogLoader,
        \Vaimo\ComposerChangelogs\Resolvers\PackageInfoResolver $packageInfoResolver,
        \Vaimo\ComposerChangelogs\Interfaces\UrlResolverInterface $urlResolver = null
    ) {
        $this->configResolver = $configResolver;
        $this->changelogLoader = $changelogLoader;
        $this->packageInfoResolver = $packageInfoResolver;
        $this->urlResolver = $urlResolver;

        $this->contextGenerator = new \Vaimo\ComposerChangelogs\Generators\Changelog\RenderContextGenerator();
        $this->templateRenderer = new \Vaimo\ComposerChangelogs\Generators\TemplateOutputGenerator();
    }

    public function generate(\Composer\Package\PackageInterface $package)
    {
        $changelog = $this->changelogLoader->load($package);

        $outputPaths = $this->configResolver->resolveOutputTargets($package);
        
        $templates = array_replace_recursive(
            $this->configResolver->resolveOutputTemplates(), 
            $this->configResolver->resolveTemplateOverrides($package)
        );

        $featureFlags = $this->configResolver->getFeatureFlags($package);
        
        $repositoryUrl = $this->urlResolver->resolveForPackage($package);
        $repositoryRoot = $this->packageInfoResolver->getSourcePath($package);
        
        $contextData = $this->contextGenerator->generate(
            $changelog,
            $repositoryUrl,
            $featureFlags['dates'] ? $repositoryRoot : ''
        );

        foreach ($outputPaths as $type => $target) {
            try {
                $output = $this->templateRenderer->generateOutput($contextData, $templates[$type]);
            } catch (\Vaimo\ComposerChangelogs\Exceptions\TemplateValidationException $exception) {
                $messages = array();

                do {
                    $messages[] = $exception->getMessage();
                } while ($exception = $exception->getPrevious());

                $messages = array_map(function ($message, $index) {
                    return sprintf('#%s - %s', $index + 1, $message);
                }, $messages, array_keys($messages));

                $errorMessage = sprintf(
                    "Generator run for '%s' caused an error:\n%s",
                    $type,
                    implode("\n", $messages)
                );

                throw new \Vaimo\ComposerChangelogs\Exceptions\GeneratorException($errorMessage);

                break;
            }

            file_put_contents($target, $output);
        }
    }
}
