<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Extractors;

use Composer\Package\PackageInterface as Package;

use Vaimo\ComposerChangelogs\Composer\Config as ComposerConfig;

class VendorConfigExtractor implements \Vaimo\ComposerChangelogs\Interfaces\PackageConfigExtractorInterface
{
    /**
     * @var \Vaimo\ComposerChangelogs\Resolvers\PackageInfoResolver
     */
    private $packageInfoResolver;

    /**
     * @var \Vaimo\ComposerChangelogs\Readers\JsonFileReader
     */
    private $configLoader;

    /**
     * @var \Vaimo\ComposerChangelogs\Utils\PathUtils
     */
    private $pathUtils;

    /**
     * @param \Vaimo\ComposerChangelogs\Resolvers\PackageInfoResolver $packageInfoResolver
     */
    public function __construct(
        \Vaimo\ComposerChangelogs\Resolvers\PackageInfoResolver $packageInfoResolver
    ) {
        $this->packageInfoResolver = $packageInfoResolver;

        $this->configLoader = new \Vaimo\ComposerChangelogs\Readers\JsonFileReader();
        $this->pathUtils = new \Vaimo\ComposerChangelogs\Utils\PathUtils();
    }

    public function getConfig(Package $package)
    {
        $packageConfig = $this->getPackageFullConfig($package);
        
        if (!$packageConfig) {
            return array();
        }

        if (!isset($packageConfig[ComposerConfig::CONFIG_ROOT])) {
            return array();
        }

        return $packageConfig[ComposerConfig::CONFIG_ROOT];
    }
    
    public function getPackageFullConfig(Package $package)
    {
        $source = $this->pathUtils->composePath(
            $this->packageInfoResolver->getInstallPath($package),
            \Vaimo\ComposerChangelogs\Composer\Files::PACKAGE_CONFIG
        );

        if (file_exists($source)) {
            return $this->configLoader->readToArray($source);
        }
        
        return array();
    }
}
