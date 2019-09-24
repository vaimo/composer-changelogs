<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Composer\Plugin;

class CommandsProvider implements \Composer\Plugin\Capability\CommandProvider
{
    public function getCommands()
    {
        return array(
            new \Vaimo\ComposerChangelogs\Commands\BootstrapCommand(),
            new \Vaimo\ComposerChangelogs\Commands\GenerateCommand(),
            new \Vaimo\ComposerChangelogs\Commands\VersionCommand(),
            new \Vaimo\ComposerChangelogs\Commands\InfoCommand(),
            new \Vaimo\ComposerChangelogs\Commands\ValidateCommand()
        );
    }
}
