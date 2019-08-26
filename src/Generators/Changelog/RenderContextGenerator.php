<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Generators\Changelog;

class RenderContextGenerator implements \Vaimo\ComposerChangelogs\Interfaces\RenderContextGeneratorInterface
{
    /**
     * @var \Vaimo\ComposerChangelogs\Resolvers\VcsDetailsResolver
     */
    private $vcsDetailsResolver;
    
    /**
     * @var \Vaimo\ComposerChangelogs\Resolvers\ReleaseDetailsResolver
     */
    private $releaseInfoResolver;

    public function __construct()
    {
        $this->vcsDetailsResolver = new \Vaimo\ComposerChangelogs\Resolvers\VcsDetailsResolver();
        $this->releaseInfoResolver = new \Vaimo\ComposerChangelogs\Resolvers\ReleaseDetailsResolver();
    }

    public function generate(array $changelog, $repositoryUrl = '', $repositoryRoot = '')
    {
        $lastVersion = $this->vcsDetailsResolver->resolveInitialCommitReference($repositoryRoot);

        $contextData = array();
        
        foreach (array_reverse($changelog) as $version => $details) {
            $item = $this->releaseInfoResolver->resolveOverview($details);
            
            $releaseLinks = $this->vcsDetailsResolver->resolveReleaseLinks(
                $repositoryUrl,
                $version,
                $lastVersion
            );

            $releaseDateTime = $this->vcsDetailsResolver->resolveReleaseTime(
                $repositoryRoot,
                $version
            );
            
            $changeGroups = $this->resolveChangeGroups($details);

            $contextData[] = array_filter(
                array_replace(
                    $item,
                    array('groups' => $changeGroups),
                    $releaseLinks,
                    $releaseDateTime
                )
            );
            
            $lastVersion = $version;
        }
        
        return array(
            'releases' => array_reverse($contextData)
        );
    }
    
    private function resolveChangeGroups(array $details)
    {
        $result = array();

        $changeGroups = $this->releaseInfoResolver->resolveChangeGroups($details);
        
        foreach ($changeGroups as $name => $groupItems) {
            $group = array();

            $group['label'] = ucfirst($name);
            $group['name'] = $name;
            $group['items'] = $groupItems;

            $result[] = $group;
        }
        
        return $result;
    }
}
