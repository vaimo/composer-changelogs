<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Repositories\Documentation;

class TypeRepository
{
    /**
     * @return \Vaimo\ComposerChangelogs\Interfaces\DocumentationGeneratorInterface[]
     */
    public function getAllTypes()
    {
        return array(
            'sphinx' => new \Vaimo\ComposerChangelogs\Generators\DocumentationTypes\Sphinx()
        );
    }
}
