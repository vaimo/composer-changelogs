<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Resolvers;

class VersionResolver
{
    /**
     * @var \Vaimo\ComposerChangelogs\Validators\ConstraintValidator
     */
    private $constraintValidator;
    
    public function __construct()
    {
        $this->constraintValidator = new \Vaimo\ComposerChangelogs\Validators\ConstraintValidator();
    }
    
    public function resolveValidVersion($value)
    {
        $segments = explode('.', $value);
        
        while (!empty($segments) && !$this->constraintValidator->isConstraint(implode('.', $segments))) {
            array_shift($segments);
        }

        return implode('.', $segments);
    }
}
