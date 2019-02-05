<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Normalizers;

class UrlNormalizer
{
    public function assureHttpAccessibility($url)
    {
        $urlComponents = parse_url(
            trim($url)
        );

        if (!isset($urlComponents['host'])) {
            return '';
        }

        if (!isset($urlComponents['scheme'])) {
            $urlComponents['scheme'] = 'https';
        }

        if ($urlComponents['scheme'] === 'ssh') {
            unset($urlComponents['user']);
            unset($urlComponents['pass']);

            $urlComponents['scheme'] = 'https';
        }

        $urlComponents['path'] = strtok($urlComponents['path'], '.');

        return (isset($urlComponents['scheme']) ? sprintf('%s:', $urlComponents['scheme']) : '') .
            ((isset($urlComponents['user']) || isset($urlComponents['host'])) ? '//' : '') .
            (isset($urlComponents['user']) ? $urlComponents['user'] : '') .
            (isset($urlComponents['pass']) ? sprintf(':%s', $urlComponents['pass']) : '') .
            (isset($urlComponents['user']) ? '@' : '') .
            (isset($urlComponents['host']) ? $urlComponents['host'] : '') .
            (isset($urlComponents['port']) ? sprintf(':%s', $urlComponents['port']) : '') .
            (isset($urlComponents['path']) ? $urlComponents['path'] : '') .
            (isset($urlComponents['query']) ? sprintf('?%s', $urlComponents['query']) : '') .
            (isset($urlComponents['fragment']) ? sprintf('#%s', $urlComponents['fragment']) : '');
    }
}
