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
     * @var \Composer\Installer\InstallationManager
     */
    private $installationManager;

    /**
     * @var \Vaimo\ComposerChangelogs\Interfaces\PackageConfigExtractorInterface
     */
    private $configExtractor;

    /**
     * @param \Composer\Package\PackageInterface $pluginPackage
     * @param \Composer\Installer\InstallationManager $installationManager
     * @param \Vaimo\ComposerChangelogs\Interfaces\PackageConfigExtractorInterface $configExtractor
     */
    public function __construct(
        \Composer\Package\PackageInterface $pluginPackage,
        \Composer\Installer\InstallationManager $installationManager,
        \Vaimo\ComposerChangelogs\Interfaces\PackageConfigExtractorInterface $configExtractor
    ) {
        $this->pluginPackage = $pluginPackage;
        $this->installationManager = $installationManager;
        $this->configExtractor = $configExtractor;
    }

    public function resolveOutputTargets(\Composer\Package\PackageInterface $package)
    {
        $config = $this->getConfig($package);

        if (!isset($config['output'])) {
            return array();
        }

        $installPath = $this->installationManager->getInstallPath($package);

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

    public function resolveOutputTemplates(\Composer\Package\PackageInterface $package)
    {
        $config = $this->getConfig($package);

        if (!isset($config['output'])) {
            return array();
        }

        $installPath = $this->installationManager->getInstallPath($package);
        $pluginRoot = $this->installationManager->getInstallPath($this->pluginPackage);

        $templatePathSegments = array();

        foreach ($config['output'] as $type => $outputConfig) {
            if (is_array($outputConfig) && isset($outputConfig['template']) && $outputConfig['template']) {
                $templatePathSegments[$type] = array($installPath, $outputConfig['template']);
            }

            $templatePathSegments[$type] = array($pluginRoot, 'views', $type . '.mustache');
        }

        return array_map(function ($segments) {
            return implode(DIRECTORY_SEPARATOR, $segments);
        }, $templatePathSegments);
    }

    public function resolveSourcePath(\Composer\Package\PackageInterface $package)
    {
        $config = $this->getConfig($package);

        if (!isset($config['source'])) {
            return false;
        }

        return $this->installationManager->getInstallPath($package)
            . DIRECTORY_SEPARATOR
            . $config['source'];
    }

    private function getConfig(\Composer\Package\PackageInterface $package)
    {
        $packageExtraConfig = $this->configExtractor->getConfig($package);

        if (!isset($packageExtraConfig['changelog'])) {
            return array();
        }

        return $packageExtraConfig['changelog'];
    }
}
