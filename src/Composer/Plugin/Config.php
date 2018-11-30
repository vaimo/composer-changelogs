<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Composer\Plugin;

class Config
{
    public function getAvailableFormats()
    {
        return array('sphinx', 'html', 'md', 'yml', 'rst', 'txt', 'slack');
    }
}
