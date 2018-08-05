<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Loaders;

class TemplateLoader
{
    private $content;

    public function load($path)
    {
        if (!isset($this->content[$path])) {
            if (!file_exists($path)) {
                throw new \Exception(
                    sprintf('Could not find template file %s', $path)
                );
            }

            $this->content[$path] = file_get_contents($path);
        }

        return $this->content[$path];
    }
}
