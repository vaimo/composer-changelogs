<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Factories;

use Vaimo\ComposerChangelogs\Config as PluginConfig;

class ConfigFactory
{
    public function create(\Composer\Composer $composer)
    {
        $extra = $composer->getPackage()->getExtra();

        $envScopes = isset($extra[PluginConfig::ENV_CONFIG_ROOT])
            ? $extra[PluginConfig::ENV_CONFIG_ROOT]
            : array();

        return new PluginConfig($envScopes);
    }
}
