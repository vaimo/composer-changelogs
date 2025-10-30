<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Managers;

use Vaimo\ComposerChangelogs\Composer\Plugin\Config as PluginConfig;
use Vaimo\ComposerChangelogs\Composer\Config as ComposerConfig;
use Vaimo\ComposerChangelogs\Composer\Files as ComposerFiles;

class PackageManager
{
    /**
     * @var \Vaimo\ComposerChangelogs\Composer\Context
     */
    private $composerCtx;

    /**
     * @param \Vaimo\ComposerChangelogs\Composer\Context $composerCtx
     */
    public function __construct(
        \Vaimo\ComposerChangelogs\Composer\Context $composerCtx
    ) {
        $this->composerCtx = $composerCtx;
    }

    public function bootstrapChangelogGeneration(\Composer\Package\PackageInterface $package, $format = 'md')
    {
        $pluginConfig = new \Vaimo\ComposerChangelogs\Composer\Plugin\Config();
        $formats = $pluginConfig->getAvailableFormats();
        
        if (!in_array($format, $formats, true)) {
            throw new \Vaimo\ComposerChangelogs\Exceptions\UpdaterException(
                sprintf('Unknown format: %s', $format)
            );
        }
        
        $composer = $this->composerCtx->getLocalComposer();
        $installationManager = $composer->getInstallationManager();
        $packageInfoResolver = new \Vaimo\ComposerChangelogs\Resolvers\PackageInfoResolver($installationManager);
        $configExtractor = new \Vaimo\ComposerChangelogs\Extractors\VendorConfigExtractor($packageInfoResolver);
        $installPath = $packageInfoResolver->getInstallPath($package);
        $config = $configExtractor->getPackageFullConfig($package);

        $paths = array(
            'md' => 'CHANGELOG.md',
            'sphinx' => 'docs/changelog.rst',
            'rst' => 'docs/changelog.rst',
            'txt' => 'CHANGELOG.txt'
        );

        $filePath = isset($paths[$format]) ? $paths[$format] : sprintf('CHANGELOG.%s', $format);
        
        $update = array(
            ComposerConfig::CONFIG_ROOT => array(
                PluginConfig::ROOT => array(
                    'source' => 'changelog.json',
                    'output' => array($format => $filePath)
                )
            )
        );

        $pathUtils = new \Vaimo\ComposerChangelogs\Utils\PathUtils();
        $config = array_replace_recursive($config, $update);
        $encodedConfig = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $pkgConfigPath = $pathUtils->composePath($installPath, ComposerFiles::PACKAGE_CONFIG);
        $chLogConfigPath = $pathUtils->composePath($installPath, 'changelog.json');

        file_put_contents($pkgConfigPath, $encodedConfig);

        if (!file_exists($chLogConfigPath)) {
            $changeLog = array(
                '_readme' => array(
                    'The contents of this file are used to generate CHANGELOG.md; It\'s kept in '
                    . 'JSON/parsable format to make it',
                    'possible to generate change-logs in other formats as well (when needed) and '
                    . 'to do automatic releases based on',
                    'added change-log records. More on how to use it: https://github.com/vaimo/composer-changelogs'
                )
            );

            $encodedChangeLog = json_encode($changeLog, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            file_put_contents($chLogConfigPath, $encodedChangeLog);
        }
    }
}
