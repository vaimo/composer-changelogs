<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Generators;

use Vaimo\ComposerChangelogs\Factories;

class DocumentationGenerator
{
    /**
     * @var \Vaimo\ComposerChangelogs\Resolvers\ChangelogConfigResolver
     */
    private $configResolver;

    /**
     * @var \Vaimo\ComposerChangelogs\Repositories\Documentation\TypeRepository
     */
    private $docTypeRepository;

    /**
     * @var \Vaimo\ComposerChangelogs\Loaders\ChangelogLoader
     */
    private $changelogLoader;

    /**
     * @param \Vaimo\ComposerChangelogs\Resolvers\ChangelogConfigResolver $configResolver
     * @param \Vaimo\ComposerChangelogs\Repositories\Documentation\TypeRepository $docTypeRepository
     */
    public function __construct(
        \Vaimo\ComposerChangelogs\Resolvers\ChangelogConfigResolver $configResolver,
        \Vaimo\ComposerChangelogs\Repositories\Documentation\TypeRepository $docTypeRepository
    ) {
        $this->configResolver = $configResolver;
        $this->docTypeRepository = $docTypeRepository;

        $this->changelogLoader = new \Vaimo\ComposerChangelogs\Loaders\ChangelogLoader($configResolver);
    }

    public function generate(\Composer\Package\PackageInterface $package)
    {
        $changelog = $this->changelogLoader->load($package);

        $outputPaths = $this->configResolver->resolveOutputTargets($package);
        $templates = $this->configResolver->resolveOutputTemplates($package);

        $docTypes = $this->docTypeRepository->getAllTypes();

        foreach (array_intersect_key($outputPaths, $docTypes) as $type => $target) {
            $generator = $docTypes[$type];

            try {
                // @todo: move this somewhere, where it can be switched by type (needed for info as well)
                $mustacheConverter = new \Vaimo\ComposerChangelogs\Converters\MustacheConverter();
                $contextData = $mustacheConverter->convertChangelog($changelog);

                file_put_contents(
                    $target,
                    $generator->generate($contextData,  $templates[$type])
                );
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
        }
    }
}
