<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Resolvers;

class ChangelogConfigResolver
{
    /**
     * @var \Composer\Package\PackageInterface
     */
    private $pluginPackage;

    /**
     * @var \Vaimo\ComposerChangelogs\Interfaces\PackageConfigExtractorInterface
     */
    private $configExtractor;

    /**
     * @var \Vaimo\ComposerChangelogs\Resolvers\PackageInfoResolver
     */
    private $packageInfoResolver;

    /**
     * @var \Vaimo\ComposerChangelogs\Composer\Plugin\Config 
     */
    private $pluginConfig;

    /**
     * @param \Composer\Package\PackageInterface $pluginPackage
     * @param \Vaimo\ComposerChangelogs\Resolvers\PackageInfoResolver $packageInfoResolver
     * @param \Vaimo\ComposerChangelogs\Interfaces\PackageConfigExtractorInterface $configExtractor
     */
    public function __construct(
        \Composer\Package\PackageInterface $pluginPackage,
        \Vaimo\ComposerChangelogs\Resolvers\PackageInfoResolver $packageInfoResolver,
        \Vaimo\ComposerChangelogs\Interfaces\PackageConfigExtractorInterface $configExtractor
    ) {
        $this->pluginPackage = $pluginPackage;
        $this->packageInfoResolver = $packageInfoResolver;
        $this->configExtractor = $configExtractor;

        $this->pluginConfig = new \Vaimo\ComposerChangelogs\Composer\Plugin\Config();
    }

    public function resolveOutputTargets(\Composer\Package\PackageInterface $package)
    {
        $config = $this->getConfig($package);

        if (!isset($config['output'])) {
            return array();
        }

        $installPath = $this->packageInfoResolver->getSourcePath($package);

        return array_filter(
            array_map(function ($config) use ($installPath) {
                $path = is_array($config) ? (isset($config['path']) ? $config['path'] : '') : $config;

                if (!$path) {
                    return false;
                }

                return $installPath . DIRECTORY_SEPARATOR . $path;
            }, $config['output'])
        );
    }

    public function resolveOutputTemplates()
    {
        $pluginRoot = $this->packageInfoResolver->getSourcePath($this->pluginPackage);
        
        $types = $this->pluginConfig->getAvailableFormats();

        $templateGroups = array_combine(
            $types,
            array_map(function ($type) use ($pluginRoot) {
                return array(
                    'root' => array($pluginRoot, 'views', $type, 'changelog.mustache'),
                    'release' => array($pluginRoot, 'views', $type, 'release.mustache')
                );
            }, $types)
        );

        return $this->assembleGroupedFilePaths($templateGroups);
    }

    public function resolveTemplateOverrides(\Composer\Package\PackageInterface $package)
    {
        $config = $this->getConfig($package);

        $installPath = $this->packageInfoResolver->getSourcePath($package);
        
        $outputPaths = isset($config['output']) ? $config['output'] : array();

        $templateGroups = array();
        
        foreach ($outputPaths as $type => $outputConfig) {
            if (!is_array($outputConfig) || !isset($outputConfig['template']) || !$outputConfig['template']) {
                continue;
            }

            if (!is_array($outputConfig['template'])) {
                $outputConfig['template'] = array(
                    'root' => $outputConfig['template']
                );
            }

            $templateGroups[$type] = array_map(
                function ($templatePath) use ($installPath) {
                    return array($installPath, $templatePath);
                },
                $outputConfig['template']
            );
        }

        return $this->assembleGroupedFilePaths($templateGroups);
    }

    private function assembleGroupedFilePaths($groups)
    {
        return array_map(function (array $group) {
            return array_map(function (array $segments) {
                return implode(DIRECTORY_SEPARATOR, $segments);
            }, $group);
        }, $groups);
    }
    
    public function resolveSourcePath(\Composer\Package\PackageInterface $package)
    {
        $config = $this->getConfig($package);

        if (!isset($config['source'])) {
            return false;
        }

        return $this->packageInfoResolver->getSourcePath($package)
            . DIRECTORY_SEPARATOR
            . $config['source'];
    }

    public function hasConfig(\Composer\Package\PackageInterface $package)
    {
        $packageExtraConfig = $this->configExtractor->getConfig($package);

        return isset($packageExtraConfig['changelog']) && is_array($packageExtraConfig['changelog']);
    }

    private function getConfig(\Composer\Package\PackageInterface $package)
    {
        $packageExtraConfig = $this->configExtractor->getConfig($package);

        if (!isset($packageExtraConfig['changelog']) || !is_array($packageExtraConfig['changelog'])) {
            return array();
        }

        return $packageExtraConfig['changelog'];
    }
}
