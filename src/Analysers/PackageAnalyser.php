<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Analysers;

use Vaimo\ComposerChangelogs\Composer\Config as ComposerConfig;

class PackageAnalyser
{
    /**
     * @var \Vaimo\ComposerChangelogs\Extractors\NamespacesExtractor 
     */
    private $namespacesExtractor;
    
    public function __construct() 
    {
        $this->namespacesExtractor = new \Vaimo\ComposerChangelogs\Extractors\NamespacesExtractor();
    }

    public function isPluginPackage(\Composer\Package\PackageInterface $package)
    {
        return $package->getType() === ComposerConfig::COMPOSER_PLUGIN_TYPE;
    }
    
    public function ownsNamespace(\Composer\Package\PackageInterface $package, $namespace)
    {
        return (bool)array_filter(
            $this->namespacesExtractor->getConfig($package),
            function ($item) use ($namespace) {
                return strpos($namespace, rtrim($item, '\\')) === 0;
            }
        );
    }
}
