<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Resolvers;

use Composer\Package\PackageInterface;

use Vaimo\ComposerChangelogs\Composer\Plugin\Config as PluginConfig;

class ChangelogConfigResolver
{
    /**
     * @var \Composer\Package\PackageInterface
     */
    private $pluginPackage;

    /**
     * @var \Vaimo\ComposerChangelogs\Composer\Plugin\Config
     */
    private $pluginConfig;

    /**
     * @var \Vaimo\ComposerChangelogs\Interfaces\PackageConfigExtractorInterface
     */
    private $configExtractor;

    /**
     * @var \Vaimo\ComposerChangelogs\Resolvers\PackageInfoResolver
     */
    private $packageInfoResolver;

    /**
     * @var \Vaimo\ComposerChangelogs\Utils\DataUtils
     */
    private $dataUtils;
    
    /**
     * @var \Vaimo\ComposerChangelogs\Utils\PathUtils
     */
    private $pathUtils;

    /**
     * @param \Composer\Package\PackageInterface $pluginPackage
     * @param \Vaimo\ComposerChangelogs\Composer\Plugin\Config $pluginConfig
     * @param \Vaimo\ComposerChangelogs\Resolvers\PackageInfoResolver $packageInfoResolver
     * @param \Vaimo\ComposerChangelogs\Interfaces\PackageConfigExtractorInterface $configExtractor
     */
    public function __construct(
        \Composer\Package\PackageInterface $pluginPackage,
        \Vaimo\ComposerChangelogs\Composer\Plugin\Config $pluginConfig,
        \Vaimo\ComposerChangelogs\Resolvers\PackageInfoResolver $packageInfoResolver,
        \Vaimo\ComposerChangelogs\Interfaces\PackageConfigExtractorInterface $configExtractor
    ) {
        $this->pluginPackage = $pluginPackage;
        $this->pluginConfig = $pluginConfig;
        $this->packageInfoResolver = $packageInfoResolver;
        $this->configExtractor = $configExtractor;

        $this->dataUtils = new \Vaimo\ComposerChangelogs\Utils\DataUtils();
        $this->pathUtils = new \Vaimo\ComposerChangelogs\Utils\PathUtils();
    }

    public function resolveOutputTargets(PackageInterface $package)
    {
        $config = $this->getConfig($package);

        if (!isset($config['output'])) {
            return array();
        }

        $installPath = $this->packageInfoResolver->getInstallPath($package);
        
        $dataUtils = $this->dataUtils;
        $pathUtils = $this->pathUtils;

        return array_filter(
            array_map(function ($config) use ($installPath, $dataUtils, $pathUtils) {
                $path = is_array($config) ? $dataUtils->extractValue($config, 'path') : $config;

                if (!$path) {
                    return false;
                }

                return $pathUtils->composePath($installPath, $path);
            }, $config['output'])
        );
    }
    
    public function resolveOutputEscapersForType($type)
    {
        $escapers = $this->pluginConfig->getEscapers();

        if (isset($escapers[$type])) {
            return $escapers[$type];
        }

        return array();
    }
    
    public function resolveRunnerInstallPath()
    {
        return $this->resolveInstallPath($this->pluginPackage);
    }

    public function resolveInstallPath(PackageInterface $package)
    {
        return $this->packageInfoResolver->getInstallPath($package);
    }
    
    public function resolveAvailableOutputFormats()
    {
        return $this->pluginConfig->getAvailableFormats();
    }
    
    public function resolveSourcePath(PackageInterface $package)
    {
        $config = $this->getConfig($package);

        if (!isset($config['source'])) {
            return false;
        }

        return $this->pathUtils->composePath(
            $this->packageInfoResolver->getInstallPath($package),
            $config['source']
        );
    }
    
    public function hasConfig(PackageInterface $package)
    {
        $pkgExtraConfig = $this->configExtractor->getConfig($package);

        return isset($pkgExtraConfig[PluginConfig::ROOT]) 
            && is_array($pkgExtraConfig[PluginConfig::ROOT]);
    }

    public function getFeatureFlags(PackageInterface $package)
    {
        $config = $this->getConfig($package);
        
        return array_replace(
            array_fill_keys(array('links', 'dates'), true),
            isset($config['features']) ? $config['features'] : array()
        );
    }
    
    public function getConfig(PackageInterface $package)
    {
        $pkgExtraConfig = $this->configExtractor->getConfig($package);

        if (!isset($pkgExtraConfig[PluginConfig::ROOT]) || !is_array($pkgExtraConfig[PluginConfig::ROOT])) {
            return array();
        }

        return $pkgExtraConfig['changelog'];
    }
}
