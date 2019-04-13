<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Analysers;

use Composer\DependencyResolver\Operation\OperationInterface;

class ComposerOperationAnalyser
{
    /**
     * @var \Vaimo\ComposerChangelogs\Analysers\PackageAnalyser
     */
    private $packageAnalyser;
    
    public function __construct()
    {
        $this->packageAnalyser = new \Vaimo\ComposerChangelogs\Analysers\PackageAnalyser();
    }

    public function isPluginUninstallOperation(OperationInterface $operation)
    {
        if (!$operation instanceof \Composer\DependencyResolver\Operation\UninstallOperation) {
            return false;
        };

        $package = $operation->getPackage();

        return $this->packageAnalyser->isPluginPackage($package)
            && $this->packageAnalyser->ownsNamespace($package, __NAMESPACE__);
    }
}
