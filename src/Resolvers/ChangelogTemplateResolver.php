<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Resolvers;

use Composer\Package\PackageInterface;

class ChangelogTemplateResolver
{
    /**
     * @var \Vaimo\ComposerChangelogs\Resolvers\ChangelogConfigResolver
     */
    private $configResolver;

    /**
     * @param \Vaimo\ComposerChangelogs\Resolvers\ChangelogConfigResolver $configResolver
     */
    public function __construct(
        \Vaimo\ComposerChangelogs\Resolvers\ChangelogConfigResolver $configResolver
    ) {
        $this->configResolver = $configResolver;
    }

    public function getTemplates(PackageInterface $package)
    {
        return array_replace_recursive(
            $this->resolveOutputTemplates(),
            $this->resolveTemplateOverrides($package)
        );
    }
    
    private function resolveOutputTemplates()
    {
        $outputFormats = $this->configResolver->resolveAvailableOutputFormats();

        $pluginRoot = $this->configResolver->resolveRunnerInstallPath();

        $templateGroups = array_combine(
            $outputFormats,
            array_map(function ($type) use ($pluginRoot) {
                return array(
                    'root' => array($pluginRoot, 'views', $type, 'changelog.mustache'),
                    'release' => array($pluginRoot, 'views', $type, 'release.mustache')
                );
            }, $outputFormats)
        );

        if ($templateGroups === false) {
            return array();
        }

        return $this->assembleGroupedFilePaths($templateGroups);
    }

    private function resolveTemplateOverrides(PackageInterface $package)
    {
        $config = $this->configResolver->getConfig($package);

        $installPath = $this->configResolver->resolveInstallPath($package);

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

    private function assembleGroupedFilePaths(array $groups)
    {
        return array_map(function (array $group) {
            return array_map(function (array $segments) {
                return implode(DIRECTORY_SEPARATOR, $segments);
            }, $group);
        }, $groups);
    }
}
