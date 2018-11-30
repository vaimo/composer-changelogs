<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Resolvers;

class ReleaseDetailsResolver
{
    /**
     * @var \Vaimo\ComposerChangelogs\Validators\ConstraintValidator
     */
    private $constraintValidator;

    public function __construct()
    {
        $this->constraintValidator = new \Vaimo\ComposerChangelogs\Validators\ConstraintValidator();
    }

    public function resolveOverview(array $release)
    {
        return array(
            'version' => isset($release['version']) ? $release['version'] : '',
            'overview' => isset($release['overview'])
                ? (is_array($release['overview']) ? $release['overview'] : array($release['overview']))
                : array(),
            'summary' => isset($release['summary']) ? $release['summary'] : ''
        );
    }

    public function resolveChangeGroups(array $release)
    {
        return array_diff_key(
            $release,
            array('overview' => true, 'version' => true, 'branch' => true, 'summary' => true)
        );
    }
}
