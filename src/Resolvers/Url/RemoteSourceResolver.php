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
     * @var \Vaimo\ComposerChangelogs\Utils\PathUtils
     */
    private $pathUtils;
    
    /**
     * @param \Vaimo\ComposerChangelogs\Resolvers\PackageInfoResolver $packageInfoResolver,
     */
    public function __construct(
        \Vaimo\ComposerChangelogs\Resolvers\PackageInfoResolver $packageInfoResolver
    ) {
        $this->packageInfoResolver = $packageInfoResolver;

        $this->urlNormalizer = new \Vaimo\ComposerChangelogs\Normalizers\UrlNormalizer();
        
        $this->pathUtils = new \Vaimo\ComposerChangelogs\Utils\PathUtils();
    }

    public function resolveForPackage(\Composer\Package\PackageInterface $package)
    {
        if (!$package instanceof \Composer\Package\CompletePackageInterface) {
            return '';
        }

        $support = $package->getSupport();

        $queryCommands = array(
            '.hg' => 'hg path default',
            '.git' => 'git remote get-url origin'
        );
        
        if (!isset($support['source'])) {
            foreach ($queryCommands as $folder => $command) {
                $sourcePath = $this->packageInfoResolver->getInstallPath($package);

                if (!file_exists($this->pathUtils->composePath($sourcePath, $folder))) {
                    continue;
                }

                $process = new \Symfony\Component\Process\Process($command, $sourcePath);

                $process->setTimeout(null);

                try {
                    $process->mustRun();

                    $result = $process->getOutput();
                } catch (\Symfony\Component\Process\Exception\ProcessFailedException $exception) {
                    return '';
                }

                return $this->urlNormalizer->assureHttpAccessibility($result);
            }
        }


        return isset($support['source']) ? $support['source'] : '';
    }
}
