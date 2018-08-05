<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Resolvers;

class ChangelogContentsResolver
{
    /**
     * @var \Vaimo\ComposerChangelogs\Validators\ConstraintValidator
     */
    private $constraintValidator;

    public function __construct()
    {
        $this->constraintValidator = new \Vaimo\ComposerChangelogs\Validators\ConstraintValidator();
    }

    public function resolveLatestVersionedRelease(array $changelog)
    {
        $versions = array_keys($changelog);

        foreach ($versions as $version) {
            if (!$this->constraintValidator->isConstraint($version)) {
                continue;
            }

            return $version;

            break;
        }

        return false;
    }
}
