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

    /**
     * @var \Vaimo\ComposerChangelogs\Analysers\ReleaseAnalyser
     */
    private $releaseAnalyser;

    public function __construct()
    {
        $this->constraintValidator = new \Vaimo\ComposerChangelogs\Validators\ConstraintValidator();
        $this->releaseAnalyser = new \Vaimo\ComposerChangelogs\Analysers\ReleaseAnalyser();
    }

    public function resolveLatestVersionedRelease(array $changelog, $branch = '')
    {
        foreach ($changelog as $version => $item) {
            if (!$this->constraintValidator->isConstraint($version)) {
                continue;
            }

            if (!$this->releaseAnalyser->isSameBranch($item, $branch)) {
                continue;
            }

            return $version;
        }

        return false;
    }

    public function resolveUpcomingRelease(array $changelog, $branch = '')
    {
        foreach ($changelog as $version => $item) {
            if ($this->constraintValidator->isConstraint($version)) {
                break;
            }

            if (!$this->releaseAnalyser->isSameBranch($item, $branch)) {
                continue;
            }

            return $version;
        }

        return false;
    }
}
