<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Resolvers;

use Composer\Package\PackageInterface;

use Vaimo\ComposerChangelogs\Composer\Config as ConfigKeys;

class PackageInfoResolver
{
    const DEFAULT_PATH = '.';

    /**
     * @var \Composer\Installer\InstallationManager
     */
    private $installationManager;

    /**
     * @param \Composer\Installer\InstallationManager $installationManager
     */
    public function __construct(
        \Composer\Installer\InstallationManager $installationManager
    ) {
        $this->installationManager = $installationManager;
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     *
     * @param PackageInterface $package
     * @return bool|string
     */
    public function getInstallPath(PackageInterface $package)
    {
        return !$package instanceof \Composer\Package\RootPackage
            ? $this->installationManager->getInstallPath($package)
            : realpath(dirname(\Composer\Factory::getComposerFile()));
    }

    public function resolveNamesFromPaths(array $packagesByName, array $paths)
    {
        $paths = array_unique(
            array_map('dirname', $paths)
        );

        $names = array();

        foreach ($paths as $path) {
            $segments = explode(DIRECTORY_SEPARATOR, $path);

            while ($chunk = array_slice($segments, 0, 2)) {
                array_shift($segments);

                $name = implode(DIRECTORY_SEPARATOR, $chunk);

                if (!isset($packagesByName[$name])) {
                    continue;
                }

                $names[] = $name;

                break;
            }
        }

        return $names;
    }

    public function getAutoLoadPaths(PackageInterface $package)
    {
        $autoloadConfig = $package->getAutoload();

        if (!isset($autoloadConfig[ConfigKeys::PSR4_CONFIG])) {
            return array();
        }

        $installPath = $this->getInstallPath($package);

        $sourcePaths = array_map(
            function ($path) use ($installPath) {
                return $installPath . DIRECTORY_SEPARATOR . $path;
            },
            $autoloadConfig[ConfigKeys::PSR4_CONFIG]
        );

        return array_values($sourcePaths);
    }
}
