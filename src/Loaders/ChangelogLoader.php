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
                throw new \Vaimo\ComposerChangelogs\Exceptions\LoaderException(
                    sprintf('Changelog source path not defined for: %s', $package->getName())
                );
            }
            
            $dataUtils = new \Vaimo\ComposerChangelogs\Utils\DataUtils();

            $changelogData = $this->jsonFileReader->readToArray($sourcePath);
            
            $groups = $dataUtils->walkArrayNodes(
                $changelogData,
                function (array $value) use ($dataUtils) {
                    return $dataUtils->removeKeysByPrefix($value, '_');
                }
            );
            
            foreach ($groups as $version => $group) {
                $groups[$version]['version'] = $version;
            }

            $this->cache[$packageName] = $groups;
        }

        return $this->cache[$packageName];
    }

    function walkRecursiveRemove(array $array, callable $callback) {
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                $array[$k] = $this->walkRecursiveRemove($v, $callback);
            } else if ($callback($v, $k)) {
                unset($array[$k]);
            }
        }

        return $array;
    }
}
