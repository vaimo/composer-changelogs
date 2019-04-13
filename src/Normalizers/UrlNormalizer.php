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

        return
            $this->renderValue($urlComponents, 'scheme', '%s:') .
            ((isset($urlComponents['user']) || isset($urlComponents['host'])) ? '//' : '') .
            $this->renderValue($urlComponents, 'user', '%s') .
            $this->renderValue($urlComponents, 'pass', ':%s') .
            (isset($urlComponents['user']) ? '@' : '') .
            $this->renderValue($urlComponents, 'host', '%s') .
            $this->renderValue($urlComponents, 'port', ':%s') .
            $this->renderValue($urlComponents, 'path', '%s') .
            $this->renderValue($urlComponents, 'query', '?%s') .
            $this->renderValue($urlComponents, 'fragment', '#%s');
    }
    
    private function renderValue($data, $key, $format, $default = '')
    {
        if (!isset($data[$key])) {
            return $default;
        }

        return sprintf($format, $data[$key]);
    }
}
