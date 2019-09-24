<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Console\Command;

use Symfony\Component\Console\Output\OutputInterface;

use Vaimo\ComposerChangelogs\Exceptions\PackageResolverException;
use Vaimo\ComposerChangelogs\Factories;

class ExecutionContext
{
    /**
     * @var \Vaimo\ComposerChangelogs\Composer\Context
     */
    private $composerCtx;

    /**
     * @var \Vaimo\ComposerChangelogs\Console\OutputGenerator
     */
    private $outputGenerator;

    /**
     * @param \Vaimo\ComposerChangelogs\Composer\Context $composerCtx
     */
    public function __construct(
        OutputInterface $output,
        \Vaimo\ComposerChangelogs\Composer\Context $composerCtx
    ) {
        $this->composerCtx = $composerCtx;

        $this->outputGenerator = new \Vaimo\ComposerChangelogs\Console\OutputGenerator($output);
    }
    
    public function resolvePackage($name)
    {
        $packageRepoFactory = new Factories\PackageResolverFactory($this->composerCtx);
        $packageResolver = $packageRepoFactory->create();
        
        try {
            return $packageResolver->resolvePackage(is_string($name) ? $name : '');
        } catch (PackageResolverException $exception) {
            $this->outputGenerator->writeResolverException($exception);

            return null;
        }
    }
}
