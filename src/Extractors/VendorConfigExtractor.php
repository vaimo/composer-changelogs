<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Extractors;

class VendorConfigExtractor implements \Vaimo\ComposerChangelogs\Interfaces\PackageConfigExtractorInterface
{
    /**
     * @var \Vaimo\ComposerChangelogs\Resolvers\PackageInfoResolver
     */
    private $packageInfoResolver;

    /**
     * @var \Vaimo\ComposerChangelogs\Resolvers\PackageInfoResolver
     */
    private $configLoader;

    /**
     * @param \Vaimo\ComposerChangelogs\Resolvers\PackageInfoResolver $packageInfoResolver
     */
    public function __construct(
        \Vaimo\ComposerChangelogs\Resolvers\PackageInfoResolver $packageInfoResolver
    ) {
        $this->packageInfoResolver = $packageInfoResolver;

        $this->configLoader = new \Vaimo\ComposerChangelogs\Readers\JsonFileReader();
    }

    public function getConfig(\Composer\Package\PackageInterface $package)
    {
        $source = $this->packageInfoResolver->getSourcePath($package)
            . DIRECTORY_SEPARATOR
            . \Vaimo\ComposerChangelogs\Composer\Config::PACKAGE_CONFIG_FILE;

        if (file_exists($source)) {
            $fileContents = $this->configLoader->readToArray($source);

            if (isset($fileContents[\Vaimo\ComposerChangelogs\Composer\Config::CONFIG_ROOT])) {
                return $fileContents[\Vaimo\ComposerChangelogs\Composer\Config::CONFIG_ROOT];
            }
        }

        return array();
    }
}
