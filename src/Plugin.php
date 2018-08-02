<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs;

class Plugin implements \Composer\Plugin\PluginInterface, \Composer\Plugin\Capable
{
    public function activate(\Composer\Composer $composer, \Composer\IO\IOInterface $io)
    {
    }

    public function getCapabilities()
    {
        return array(
            'Composer\Plugin\Capability\CommandProvider' =>
                'Vaimo\ComposerChangelogs\Composer\Plugin\CommandsProvider'
        );
    }
}
