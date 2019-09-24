<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs;

use Vaimo\ComposerChangelogs\Managers;
use Vaimo\ComposerChangelogs\Analysers;

class Plugin implements
    \Composer\Plugin\PluginInterface,
    \Composer\EventDispatcher\EventSubscriberInterface,
    \Composer\Plugin\Capable
{
    /**
     * @var \Vaimo\ComposerChangelogs\Managers\ChangelogManager
     */
    private $changelogManager;

    /**
     * @var \Vaimo\ComposerChangelogs\Analysers\ComposerOperationAnalyser
     */
    private $operationAnalyser;

    /**
     * @var string[]
     */
    private $capabilitiesConfig = array();
    
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param \Composer\Composer $composer
     * @param \Composer\IO\IOInterface $cliIO
     */
    public function activate(\Composer\Composer $composer, \Composer\IO\IOInterface $cliIO)
    {
        $composerCtxFactory = new \Vaimo\ComposerChangelogs\Factories\ComposerContextFactory($composer);
        $composerCtx = $composerCtxFactory->create();

        $pluginBootstrap = new \Vaimo\ComposerChangelogs\Composer\Plugin\Bootstrap($composerCtx);

        $pluginBootstrap->preloadPluginClasses();
        
        $this->changelogManager = new Managers\ChangelogManager($composerCtx);
        $this->operationAnalyser = new Analysers\ComposerOperationAnalyser();

        if (!interface_exists('\Composer\Plugin\Capability\CommandProvider')) {
            return;
        }

        $this->capabilitiesConfig = array(
            'Composer\Plugin\Capability\CommandProvider' => 'Vaimo\ComposerChangelogs\Composer\Plugin\CommandsProvider',
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
        return $this->capabilitiesConfig;
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
