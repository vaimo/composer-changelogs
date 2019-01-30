<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Resolvers\Url;

class CustomSourceResolver implements \Vaimo\ComposerChangelogs\Interfaces\UrlResolverInterface
{
    /**
     * @var string
     */
    private $customUrl;

    /**
     * @var \Vaimo\ComposerChangelogs\Normalizers\UrlNormalizer 
     */
    private $urlNormalizer;

    /**
     * @param string $customUrl
     */
    public function __construct(
        $customUrl
    ) {
        $this->customUrl = $customUrl;

        $this->urlNormalizer = new \Vaimo\ComposerChangelogs\Normalizers\UrlNormalizer();
    }

    public function resolveForPackage(\Composer\Package\PackageInterface $package)
    {
        return $this->urlNormalizer->assureHttpAccessibility($this->customUrl);
    }
}
