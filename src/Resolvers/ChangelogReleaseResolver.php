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

    public function resolveLatestVersionedRelease(array $changelog, $branch = false)
    {
        foreach ($changelog as $version => $item) {
            if (!$this->constraintValidator->isConstraint($version)) {
                continue;
            }

            if (!$this->matchBranch($item, $branch)) {
                continue;
            }

            return $version;

            break;
        }

        return false;
    }

    public function resolveUpcomingRelease(array $changelog, $branch = false)
    {
        foreach ($changelog as $version => $item) {
            if ($this->constraintValidator->isConstraint($version)) {
                break;
            }

            if (!$this->matchBranch($item, $branch)) {
                continue;
            }

            return $version;

            break;
        }

        return false;
    }

    private function matchBranch(array $item, $branch)
    {
        $branch = urldecode($branch);

        if (!isset($item['branch']) && !$branch) {
            return true;
        }

        if (!isset($item['branch']) && ($branch === 'master' || $branch === 'default')) {
            return true;
        }

        if (isset($item['branch']) && $item['branch'] === $branch) {
            return true;
        }

        return false;
    }
}
