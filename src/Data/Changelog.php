<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Data;

class Changelog
{
    /**
     * @var \Composer\Package\PackageInterface
     */
    private $package;

    /**
     * @var array
     */
    private $changelog;

    /**
     * @param \Composer\Package\PackageInterface $package
     * @param array $changelog
     */
    public function __construct(
        \Composer\Package\PackageInterface $package,
        array $changelog
    ) {
        $this->package = $package;
        $this->changelog = $changelog;
    }
    
    public function getOwner()
    {
        return $this->package;
    }
    
    public function getReleases()
    {
        return $this->changelog;
    }
}
