<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Interfaces;

interface RenderContextGeneratorInterface
{
    /**
     * @param string[][] $changelog
     * @return array
     */
    public function generate(array $changelog);
}
