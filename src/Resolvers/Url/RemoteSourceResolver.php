<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Resolvers\Url;

class RemoteSourceResolver implements \Vaimo\ComposerChangelogs\Interfaces\UrlResolverInterface
{
    /**
     * @var \Vaimo\ComposerChangelogs\Resolvers\PackageInfoResolver
     */
    private $packageInfoResolver;

    /**
     * @var \Vaimo\ComposerChangelogs\Normalizers\UrlNormalizer
     */
    private $urlNormalizer;
    
    /**
     * @param \Vaimo\ComposerChangelogs\Resolvers\PackageInfoResolver $packageInfoResolver,
     */
    public function __construct(
        \Vaimo\ComposerChangelogs\Resolvers\PackageInfoResolver $packageInfoResolver
    ) {
        $this->packageInfoResolver = $packageInfoResolver;

        $this->urlNormalizer = new \Vaimo\ComposerChangelogs\Normalizers\UrlNormalizer();
    }

    public function resolveForPackage(\Composer\Package\PackageInterface $package)
    {
        if (!$package instanceof \Composer\Package\CompletePackageInterface) {
            return '';
        }

        $support = $package->getSupport();

        if (!isset($support['source'])) {
            $sourcePath = $this->packageInfoResolver->getSourcePath($package);

            if (!file_exists($this->composePath($sourcePath, '.hg'))) {
                return '';
            }

            $process = new \Symfony\Component\Process\Process(
                'hg path default',
                $sourcePath
            );

            $process->setTimeout(null);

            try {
                $process->mustRun();

                $result = $process->getOutput();
            } catch (\Symfony\Component\Process\Exception\ProcessFailedException $exception) {
                return '';
            }

            return $this->urlNormalizer->assureHttpAccessibility($result);
        }


        return $support['source'];
    }

    private function composePath()
    {
        $pathSegments = array_map(function ($item) {
            return rtrim($item, DIRECTORY_SEPARATOR);
        }, func_get_args());

        return implode(
            DIRECTORY_SEPARATOR,
            array_filter($pathSegments)
        );
    }
}
