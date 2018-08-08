<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Loaders;

class ChangelogLoader
{
    /**
     * @var \Vaimo\ComposerChangelogs\Resolvers\ChangelogConfigResolver
     */
    private $configResolver;

    /**
     * @var \Vaimo\ComposerChangelogs\Readers\JsonFileReader
     */
    private $jsonFileReader;

    /**
     * @var array
     */
    private $cache = array();

    /**
     * @param \Vaimo\ComposerChangelogs\Resolvers\ChangelogConfigResolver $configResolver
     */
    public function __construct(
        \Vaimo\ComposerChangelogs\Resolvers\ChangelogConfigResolver $configResolver
    ) {
        $this->configResolver = $configResolver;
        $this->jsonFileReader = new \Vaimo\ComposerChangelogs\Readers\JsonFileReader();
    }

    public function load(\Composer\Package\PackageInterface $package)
    {
        $packageName = $package->getName();

        if (!isset($this->cache[$packageName])) {
            if (!$sourcePath = $this->configResolver->resolveSourcePath($package)) {
                throw new \Vaimo\ComposerChangelogs\Exceptions\GeneratorException(
                    sprintf('Changelog source path not defined for: %s', $package->getName())
                );
            }

            $groups = $this->jsonFileReader->readToArray($sourcePath);

            foreach ($groups as $version => $group) {
                $groups[$version]['version'] = $version;
            }

            $this->cache[$packageName] = $groups;
        }

        return $this->cache[$packageName];
    }
}
