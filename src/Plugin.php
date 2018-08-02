<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs;

class Plugin implements \Composer\Plugin\PluginInterface,
    \Composer\EventDispatcher\EventSubscriberInterface, \Composer\Plugin\Capable
{
    /**
     * @var \Vaimo\ComposerChangelogs\Managers\ChangelogManager
     */
    private $changelogManager;

    public function activate(\Composer\Composer $composer, \Composer\IO\IOInterface $io)
    {
        $this->changelogManager = new \Vaimo\ComposerChangelogs\Managers\ChangelogManager($composer, $io);
    }

    public static function getSubscribedEvents()
    {
        return array(
            \Composer\Script\ScriptEvents::POST_INSTALL_CMD => 'bootstrapChangelogs',
            \Composer\Script\ScriptEvents::POST_UPDATE_CMD => 'bootstrapChangelogs'
        );
    }

    /**
     * Commands
     */
    public function getCapabilities()
    {
        return array(
            'Composer\Plugin\Capability\CommandProvider' =>
                'Vaimo\ComposerChangelogs\Composer\Plugin\CommandsProvider'
        );
    }

    /**
     * Events
     */
    public function bootstrapChangelog()
    {
        $this->changelogManager->bootstrap();
    }
}
