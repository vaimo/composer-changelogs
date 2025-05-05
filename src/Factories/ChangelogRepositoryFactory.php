<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Factories;

class ChangelogRepositoryFactory
{
    /**
     * @var \Vaimo\ComposerChangelogs\Composer\Context
     */
    private $composerCtx;

    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    private $output;

    /**
     * @param \Vaimo\ComposerChangelogs\Composer\Context $composerCtx
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function __construct(
        \Vaimo\ComposerChangelogs\Composer\Context $composerCtx,
        ?\Symfony\Component\Console\Output\OutputInterface $output = null
    ) {
        $this->composerCtx = $composerCtx;
        $this->output = $output;
    }
    
    public function create($fromSource)
    {
        $pkgResolverFactory = new PackageResolverFactory($this->composerCtx);
        
        $chLogLoaderFactory = new Changelog\LoaderFactory($this->composerCtx);

        $chLogLoader = $chLogLoaderFactory->create($fromSource);
        
        $validator = new \Vaimo\ComposerChangelogs\Validators\ChangelogValidator($chLogLoader, array(
            'failure' => '<error>%s</error>',
            'success' => '<info>%s</info>'
        ));

        $outputGenerator = new \Vaimo\ComposerChangelogs\Console\OutputGenerator($this->output);

        return new \Vaimo\ComposerChangelogs\Repositories\ChangelogRepository(
            $pkgResolverFactory->create(),
            $chLogLoader,
            $validator,
            $outputGenerator
        );
    }
}
