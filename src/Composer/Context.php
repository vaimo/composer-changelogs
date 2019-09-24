<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Composer;

class Context
{
    /**
     * @var \Composer\Composer[]
     */
    private $instances;

    /**
     * @var \Composer\Package\PackageInterface[][]
     */
    private $packages;

    /**
     * @param \Composer\Composer[]
     */
    public function __construct(
        array $instances
    ) {
        $this->instances = $instances;
    }

    public function getActivePackages()
    {
        return array_reduce(
            $this->getPackageMap(),
            'array_replace',
            array()
        );
    }

    public function getLocalComposer()
    {
        return end($this->instances);
    }

    private function getPackageMap()
    {
        if ($this->packages === null) {
            $result = array();

            foreach ($this->instances as $instanceIndex => $instance) {
                $repository = $instance->getRepositoryManager()->getLocalRepository();

                if (!isset($this->packages[$instanceIndex])) {
                    $this->packages[$instanceIndex] = array();
                }

                foreach ($repository->getCanonicalPackages() as $package) {
                    $name = $package->getName();

                    if (isset($result[$name])) {
                        continue;
                    }

                    $result[$name] = true;
                    $this->packages[$instanceIndex][$name] = $package;
                }
            }
        }

        return $this->packages;
    }

    public function getInstanceForPackage(\Composer\Package\PackageInterface $package)
    {
        $name = $package->getName();
        $packageMap = $this->getPackageMap();

        foreach ($packageMap as $index => $packages) {
            if (!isset($packages[$name])) {
                continue;
            }

            return $this->instances[$index];
        }

        $message = sprintf(
            'Failed to resolve Composer instance for package: %s',
            $name
        );
        
        throw new \Vaimo\ComposerChangelogs\Exceptions\RuntimeException($message);
    }
}
