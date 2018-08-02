<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs;

class Config
{
    const CONFIG_ROOT = 'extra';
    const ENV_CONFIG_ROOT = 'environment-variables';
    const SCOPE_GLOBAL = '*';

    /**
     * @var array
     */
    private $config;

    /**
     * @param array $config
     */
    public function __construct(
        array $config
    ) {
        $this->config = $config;
    }

    public function getValuesForScope($scope)
    {
        return isset($this->config[$scope]) ? $this->config[$scope] : [];
    }
}
