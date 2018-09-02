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

    /**
     * @var \Vaimo\ComposerChangelogs\Analysers\ComposerOperationAnalyser
     */
    private $operationAnalyser;
    
    public function activate(\Composer\Composer $composer, \Composer\IO\IOInterface $io)
    {
        $this->changelogManager = new \Vaimo\ComposerChangelogs\Managers\ChangelogManager($composer, $io);
        
        $this->operationAnalyser = new \Vaimo\ComposerChangelogs\Analysers\ComposerOperationAnalyser(
            $composer
        );
    }

    public static function getSubscribedEvents()
    {
        return array(
            \Composer\Script\ScriptEvents::POST_INSTALL_CMD => 'bootstrapImplementation',
            \Composer\Script\ScriptEvents::POST_UPDATE_CMD => 'bootstrapImplementation',
            \Composer\Installer\PackageEvents::PRE_PACKAGE_UNINSTALL => 'onPackageUninstall'
        );
    }
    
    public function getCapabilities()
    {
        return array(
            'Composer\Plugin\Capability\CommandProvider' =>
                'Vaimo\ComposerChangelogs\Composer\Plugin\CommandsProvider'
        );
    }
    
    public function bootstrapImplementation()
    {
        if (!$this->changelogManager) {
            return;
        }
        
        $this->changelogManager->bootstrap();
    }
    
    public function onPackageUninstall(\Composer\Installer\PackageEvent $event)
    {
        if (!$this->operationAnalyser->isPluginUninstallOperation($event->getOperation())) {
            return;
        }

        $this->changelogManager = null;   
    }
}
