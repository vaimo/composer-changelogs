<?php declare(strict_types = 1);
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogsProxy;

require_once 'src/Plugin.php';

/**
 * @SuppressWarnings(PHPMD.ShortVariable)
 */
class Plugin extends \Vaimo\ComposerChangelogs\Plugin
{
    public function activate(\Composer\Composer $composer, \Composer\IO\IOInterface $io)
    {
        $namespacePrefix = implode('\\', array_slice(explode('\\', get_parent_class($this)), 0, 2)) . '\\';
        
        $autoloadFile = $this->composePath(
            $composer->getConfig()->get('vendor-dir'), 
            'autoload.php'
        );

        /**
         * When running through the initial installation, make sure that installing the proxy
         * command (to get the changelog commands) does not result in crashing the whole
         * installation process.
         */
        if (!file_exists($autoloadFile)) {
            return;
        }
        
        include $autoloadFile;

        $composerContextFactory = new \Vaimo\ComposerChangelogs\Factories\ComposerContextFactory($composer);
        $composerContext = $composerContextFactory->create();
        
        $this->bootstrapFileTree($composerContext, $namespacePrefix);

        parent::activate($composer, $io);
    }

    private function bootstrapFileTree(\Vaimo\ComposerChangelogs\Composer\Context $composerContext, $namespacePrefix)
    {
        $composer = $composerContext->getLocalComposer();
        
        $composerConfig = $composer->getConfig();
        
        $vendorDir = $composerConfig->get(\Vaimo\ComposerChangelogs\Composer\Config::VENDOR_DIR);

        $packageResolver = new \Vaimo\ComposerChangelogs\Resolvers\PluginPackageResolver(
            [$composer->getPackage()]
        );
        
        $pluginPackage = $packageResolver->resolveForNamespace(
            $composerContext->getActivePackages(), 
            $namespacePrefix
        );

        $this->createSymlink(
            realpath('.'),
            $this->composePath($vendorDir, $pluginPackage->getName()),
            true
        );
    }

    private function createSymlink($fromPath, $toPath, $graceful = false)
    {
        if (is_link($toPath)) {
            unlink($toPath);
        }
        
        if ($graceful && (file_exists($toPath) || !file_exists($fromPath))) {
            return;
        }

        symlink($fromPath, $toPath);
    }
    
    private function composePath()
    {
        $pathSegments = array_map(function ($item) {
            return rtrim($item, \DIRECTORY_SEPARATOR);
        }, func_get_args());

        return implode(
            DIRECTORY_SEPARATOR,
            array_filter($pathSegments)
        );
    }
}
