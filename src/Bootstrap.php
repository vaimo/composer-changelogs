<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs;

class Bootstrap
{
    /**
     * @var \Composer\Composer
     */
    private $composer;

    /**
     * @var \Vaimo\ComposerChangelogs\Factories\ConfigFactory
     */
    private $configFactory;

    /**
     * @param \Composer\Composer $composer
     * @param array $config
     */
    public function __construct(
        \Composer\Composer $composer
    ) {
        $this->composer = $composer;

        $this->configFactory = new \Vaimo\ComposerChangelogs\Factories\ConfigFactory();
    }

    public function applyScope($scopeCode)
    {
        $config = $this->configFactory->create($this->composer);

        $values = $config->getValuesForScope($scopeCode);

        foreach ($values as $name => $value) {
            if (getenv($name)) {
                continue;
            }

            putenv(sprintf('%s="%s"', $name, $value));
        }
    }
}
