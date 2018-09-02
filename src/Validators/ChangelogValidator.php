<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Validators;

use Composer\Package\PackageInterface as Package;
use Symfony\Component\Console\Output\OutputInterface as Output;

use Vaimo\ComposerChangelogs\Results\ValidationResult as Result;

class ChangelogValidator
{
    /**
     * @var \Vaimo\ComposerChangelogs\Loaders\ChangelogLoader
     */
    private $changelogLoader;

    /**
     * @var string[]
     */
    private $messageFormats;

    /**
     * @var \Vaimo\ComposerChangelogs\Extractors\ErrorExtractor
     */
    private $errorExtractor;

    /**
     * @param \Vaimo\ComposerChangelogs\Loaders\ChangelogLoader $changelogLoader
     * @param array $messageFormats
     */
    public function __construct(
        \Vaimo\ComposerChangelogs\Loaders\ChangelogLoader $changelogLoader,
        array $messageFormats = array()
    ) {
        $this->changelogLoader = $changelogLoader;
        $this->messageFormats = $messageFormats;

        $this->errorExtractor = new \Vaimo\ComposerChangelogs\Extractors\ErrorExtractor();
    }

    public function validateForPackage(Package $package, $vebosity = Output::VERBOSITY_NORMAL)
    {
        $formats = array_replace([
            'failure' => '%s',
            'success' => '%s'
        ], $this->messageFormats);

        try {
            $this->changelogLoader->load($package);
        } catch (\Exception $exception) {
            if ($vebosity > Output::VERBOSITY_VERBOSE) {
                throw $exception;
            }

            if ($vebosity > Output::VERBOSITY_NORMAL) {
                $messages = $this->errorExtractor->extractMessages($exception, true);
            } else {
                $messages = array(sprintf('The changelog of %s is invalid', $package->getName()));
            }

            return new Result(
                false,
                $this->formatMessages($messages, $formats['failure'])
            );
        }

        return new Result(
            true,
            $this->formatMessages(
                array(sprintf('The changelog of %s is valid', $package->getName())),
                $formats['success']
            )
        );
    }

    private function formatMessages(array $messages, $format)
    {
        return array_map(function ($message) use ($format) {
            return sprintf($format, $message);
        }, $messages);
    }
}
