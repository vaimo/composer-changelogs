<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Resolvers\Url;

use Composer\Package\PackageInterface;

class RemoteSourceResolver implements \Vaimo\ComposerChangelogs\Interfaces\UrlResolverInterface
{
    /**
     * @var \Vaimo\ComposerChangelogs\Resolvers\PackageInfoResolver
     */
    private $packageInfoResolver;

    /**
     * @var \Vaimo\ComposerChangelogs\Normalizers\UrlNormalizer
     */
    private $urlNormalizer;

    /**
     * @var \Vaimo\ComposerChangelogs\Utils\PathUtils
     */
    private $pathUtils;
    
    /**
     * @var \Vaimo\ComposerChangelogs\Utils\SystemUtils
     */
    private $systemUtils;

    /**
     * @var string[]
     */
    private $queryCommands = array(
        '.hg' => 'hg path default',
        '.git' => 'git remote get-url origin'
    );
    
    /**
     * @param \Vaimo\ComposerChangelogs\Resolvers\PackageInfoResolver $packageInfoResolver,
     */
    public function __construct(
        \Vaimo\ComposerChangelogs\Resolvers\PackageInfoResolver $packageInfoResolver
    ) {
        $this->packageInfoResolver = $packageInfoResolver;

        $this->urlNormalizer = new \Vaimo\ComposerChangelogs\Normalizers\UrlNormalizer();
        
        $this->pathUtils = new \Vaimo\ComposerChangelogs\Utils\PathUtils();
        $this->systemUtils = new \Vaimo\ComposerChangelogs\Utils\SystemUtils();
    }

    public function resolveForPackage(PackageInterface $package)
    {
        if (!$package instanceof \Composer\Package\CompletePackageInterface) {
            return '';
        }

        $support = $package->getSupport();
        
        $source = isset($support['source']) ? $support['source'] : '';
        
        if (!$source) {
            $commandsConfig = $this->getVcsCommandsConfig($package);
            
            foreach ($commandsConfig as $command => $sourcePath) {
                $result = $this->systemUtils->getCommandStdOut($command, $sourcePath);
                
                if (!$result) {
                    continue;
                }

                return $this->urlNormalizer->assureHttpAccessibility($result);
            }
        }

        return $source;
    }
    
    private function getVcsCommandsConfig(PackageInterface $package)
    {
        $result = array();

        foreach ($this->queryCommands as $folder => $command) {
            $sourcePath = $this->packageInfoResolver->getInstallPath($package);

            if (!file_exists($this->pathUtils->composePath($sourcePath, $folder))) {
                continue;
            }

            $result[$command] = $sourcePath;
        }
        
        return $result;
    }
}
