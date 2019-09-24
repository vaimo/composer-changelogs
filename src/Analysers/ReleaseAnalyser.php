<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Analysers;

class ReleaseAnalyser
{
    /**
     * @var \Vaimo\ComposerChangelogs\Resolvers\ReleaseDetailsResolver
     */
    private $detailsResolver;

    /**
     * @var string[]
     */
    private $mainBranches = array('master', 'default');
        
    public function __construct()
    {
        $this->detailsResolver = new \Vaimo\ComposerChangelogs\Resolvers\ReleaseDetailsResolver();
    }

    public function isSameBranch(array $item, $branch)
    {
        $branch = urldecode($branch);

        $itemBranch = $this->detailsResolver->resolveBranch($item);

        if (!$itemBranch && !$branch) {
            return true;
        }
        
        if (!$itemBranch && in_array($branch, $this->mainBranches, true)) {
            return true;
        }

        if ($itemBranch === $branch && isset($item['branch'])) {
            return true;
        }

        return false;
    }
}
