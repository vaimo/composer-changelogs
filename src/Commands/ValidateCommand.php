<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Vaimo\ComposerChangelogs\Factories;

class ValidateCommand extends \Composer\Command\BaseCommand
{
    protected function configure()
    {
        $this->setName('changelog:validate');

        $this->setDescription('Validate package changelog markup and structure');

        $this->addArgument(
            'name',
            \Symfony\Component\Console\Input\InputArgument::OPTIONAL,
            'Targeted package name. Default: root package'
        );

        $this->addOption(
            '--from-source',
            null,
            \Symfony\Component\Console\Input\InputOption::VALUE_NONE,
            'Extract configuration from vendor package instead of using global installation data'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $packageName = $input->getArgument('name');
        $fromSource = $input->getOption('from-source');

        $composerCtxFactory = new \Vaimo\ComposerChangelogs\Factories\ComposerContextFactory(
            $this->getComposer()
        );

        $composerCtx = $composerCtxFactory->create();
        
        $chLogRepoFactory = new Factories\ChangelogRepositoryFactory($composerCtx, $output);
        $chLogRepo = $chLogRepoFactory->create($fromSource);

        $changelog = $chLogRepo->getByPackageName(
            $packageName,
            $output->getVerbosity()
        );
        
        return (int)($changelog === null);
    }
}
