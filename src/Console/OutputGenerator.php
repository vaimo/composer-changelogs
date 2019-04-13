<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Console;

use Vaimo\ComposerChangelogs\Exceptions\PackageResolverException;

class OutputGenerator
{
    public function generateForResolverException(PackageResolverException $exception)
    {
        return array_merge(
            array(sprintf('<error>%s</error>', $exception->getMessage())),
            array_filter((array)$exception->getExtraInfo())
        );
    }
}
