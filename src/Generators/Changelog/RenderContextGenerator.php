<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Generators\Changelog;

class RenderContextGenerator implements \Vaimo\ComposerChangelogs\Interfaces\RenderContextGeneratorInterface
{
    public function generate(array $changelog)
    {
        $contextData = array();

        $releaseDetailsResolver = new \Vaimo\ComposerChangelogs\Resolvers\ReleaseDetailsResolver();

        foreach ($changelog as $details) {
            $item = $releaseDetailsResolver->resolveOverview($details);

            $groups = array();

            foreach ($releaseDetailsResolver->resolveChangeGroups($details) as $name => $groupItems) {
                $group = array();

                $group['name'] = ucfirst($name);
                $group['items'] = $groupItems;

                $groups[] = $group;
            }

            $contextData[] = array_filter(
                array_replace(
                    $item,
                    array('groups' => $groups)
                )
            );
        }

        return array('releases' => $contextData);
    }
}
