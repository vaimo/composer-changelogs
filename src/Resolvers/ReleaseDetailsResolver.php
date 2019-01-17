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
        $overviewLines = isset($release['overview'])
            ? (is_array($release['overview']) ? $release['overview'] : array($release['overview']))
            : array();

        $reducedLines = explode(PHP_EOL, implode(
            '', 
            array_map(function ($line) {
                return !trim($line) ? PHP_EOL . PHP_EOL: $line;
            }, $overviewLines)
        ));
        
        return array(
            'version' => isset($release['version']) ? $release['version'] : '',
            'overview' => $overviewLines,
            'summary' => isset($release['summary']) ? $release['summary'] : '',
            'overview-reduced' => $reducedLines
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
