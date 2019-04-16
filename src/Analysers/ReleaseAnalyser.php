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

        $isMainBranch = $branch === 'master' || $branch === 'default';
        
        if (!$itemBranch && $isMainBranch) {
            return true;
        }

        if (isset($item['branch']) && $itemBranch === $branch) {
            return true;
        }

        return false;
    }
}
