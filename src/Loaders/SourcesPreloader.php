<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Loaders;

use Composer\Package\PackageInterface;

class SourcesPreloader
{
    /**
     * @var \Vaimo\ComposerChangelogs\Resolvers\PackageInfoResolver
     */
    private $infoResolver;

    /**
     * @var \Vaimo\ComposerChangelogs\Utils\FileSystemUtils
     */
    private $fileSystemUtils;

    /**
     * @param \Vaimo\ComposerChangelogs\Resolvers\PackageInfoResolver $infoResolver
     */
    public function __construct(
        \Vaimo\ComposerChangelogs\Resolvers\PackageInfoResolver $infoResolver
    ) {
        $this->infoResolver = $infoResolver;

        $this->fileSystemUtils = new \Vaimo\ComposerChangelogs\Utils\FileSystemUtils();
    }
    
    public function preloadForPackage(PackageInterface $package)
    {
        $sourcePaths = $this->infoResolver->getAutoLoadPaths($package);
        
        $matchGroups = array();

        foreach ($sourcePaths as $sourcePath) {
            $matchGroups[] = $this->fileSystemUtils->collectPathsRecursively($sourcePath, '/.*\.php/');
        }

        $sourceFilePaths = array_unique(
            array_reduce($matchGroups, 'array_merge', array())
        );

        foreach ($sourceFilePaths as $filePath) {
            require_once($filePath);
        }
    }
}
