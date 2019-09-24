<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Composer\Plugin;

class Config
{
    const ROOT = 'changelog';
    
    public function getAvailableFormats()
    {
        return array('sphinx', 'html', 'md', 'yml', 'rst', 'txt', 'slack', 'json');
    }
    
    public function getEscapers()
    {
        return array(
            'sphinx' => array(
                '\\\\' => '\\\\\\\\',
                '^\.\. ' => '\\.\\. '
            ),
            'html' => array(
                '*' => array(
                    'htmlentities'
                )
            )
        );
    }
}
