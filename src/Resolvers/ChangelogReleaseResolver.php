<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Resolvers;

class ChangelogReleaseResolver
{
    /**
     * @var \Vaimo\ComposerChangelogs\Validators\ConstraintValidator
     */
    private $constraintValidator;

    public function __construct()
    {
        $this->constraintValidator = new \Vaimo\ComposerChangelogs\Validators\ConstraintValidator();
    }

    public function resolveUpcomingRelease(array $changelog, $branch = false)
    {
        foreach ($changelog as $version => $item) {
            if ($this->constraintValidator->isConstraint($version)) {
                break;
            }

            if (isset($item['branch']) && (!$branch || $item['branch'] !== $branch)) {
                continue;
            }

            return $version;

            break;
        }

        return false;
    }

    public function resolveLatestVersionedRelease(array $changelog, $branch = false)
    {
        foreach ($changelog as $version => $item) {
            if (!$this->constraintValidator->isConstraint($version)) {
                continue;
            }

            if (isset($item['branch']) && (!$branch || $item['branch'] !== $branch)) {
                continue;
            }

            return $version;

            break;
        }

        return false;
    }
}
