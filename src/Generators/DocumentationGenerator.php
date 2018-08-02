<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Generators;

use Composer\Package\PackageInterface;
use Vaimo\ComposerChangelogs\Factories;

class DocumentationGenerator
{
    /**
     * @var \Vaimo\ComposerChangelogs\Resolvers\ChangelogConfigResolver
     */
    private $configResolver;

    /**
     * @var \Vaimo\ComposerChangelogs\Readers\JsonFileReader
     */
    private $jsonFileReader;

    /**
     * @var \Vaimo\ComposerChangelogs\Repositories\Documentation\TypeRepository
     */
    private $docTypeRepository;

    /**
     * @param \Vaimo\ComposerChangelogs\Resolvers\ChangelogConfigResolver $configResolver
     * @param \Vaimo\ComposerChangelogs\Repositories\Documentation\TypeRepository $docTypeRepository
     */
    public function __construct(
        \Vaimo\ComposerChangelogs\Resolvers\ChangelogConfigResolver $configResolver,
        \Vaimo\ComposerChangelogs\Repositories\Documentation\TypeRepository $docTypeRepository
    ) {
        $this->configResolver = $configResolver;
        $this->jsonFileReader = new \Vaimo\ComposerChangelogs\Readers\JsonFileReader();

        $this->docTypeRepository = $docTypeRepository;
    }

    public function generate(PackageInterface $package)
    {
        if (!$sourcePath = $this->configResolver->resolveSourcePath($package)) {
            throw new \Vaimo\ComposerChangelogs\Exceptions\GeneratorException(
                sprintf('Changelog source path not defined for: %s', $package->getName())
            );
        }

        $changelog = $this->jsonFileReader->readToArray($sourcePath);

        $outputPaths = $this->configResolver->resolveOutputTargets($package);

        $templates = $this->configResolver->resolveOutputTemplates($package);

        $docTypes = $this->docTypeRepository->getAllTypes();

        foreach (array_intersect_key($outputPaths, $docTypes) as $type => $target) {
            $generator = $docTypes[$type];

            try {
                $generator->generate($changelog,  $templates[$type], $target);
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
