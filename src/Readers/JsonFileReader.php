<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Readers;

class JsonFileReader
{
    /**
     * @var \Vaimo\ComposerChangelogs\Decoders\JsonDecoder
     */
    private $jsonDecoder;

    public function __construct()
    {
        $this->jsonDecoder = new \Vaimo\ComposerChangelogs\Decoders\JsonDecoder();
    }

    public function readToArray($source)
    {
        if (!file_exists($source)) {
            throw new \Vaimo\ComposerChangelogs\Exceptions\ReaderException(
                sprintf('File not found: %s', $source)
            );
        }

        $sourceData = file_get_contents($source);

        try {
            $fileContents = $this->jsonDecoder->decode($sourceData);
        } catch (\Vaimo\ComposerChangelogs\Exceptions\DecoderException $exception) {
            $message = sprintf('Failed to retrieve contents of %s', $source);

            throw new \Vaimo\ComposerChangelogs\Exceptions\ReaderException($message, 0, $exception);
        }

        return $fileContents;
    }
}
