<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Factories;

class ChangelogRepositoryFactory
{
    /**
     * @var \Composer\Composer
     */
    private $composerRuntime;

    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    private $output;

    /**
     * @param \Composer\Composer $composerRuntime
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function __construct(
        \Composer\Composer $composerRuntime,
        \Symfony\Component\Console\Output\OutputInterface $output = null
    ) {
        $this->composerRuntime = $composerRuntime;
        $this->output = $output;
    }
    
    public function create($fromSource)
    {
        $pkgResolverFactory = new PackageResolverFactory(
            $this->composerRuntime
        );
        
        $chLogLoaderFactory = new Changelog\LoaderFactory($this->composerRuntime);

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
