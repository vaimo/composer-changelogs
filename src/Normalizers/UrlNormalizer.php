<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Normalizers;

class UrlNormalizer
{
    /**
     * @var \Vaimo\ComposerChangelogs\Utils\DataUtils
     */
    private $dataUtils;
    
    public function __construct()
    {
        $this->dataUtils = new \Vaimo\ComposerChangelogs\Utils\DataUtils();
    }

    public function assureHttpAccessibility($url)
    {
        $components = parse_url(
            trim($url)
        );

        if (!isset($components['host'])) {
            return '';
        }

        $components = $this->normalizeComponentValues($components);
        
        return
            $this->dataUtils->renderValue($components, 'scheme', '%s:') .
            $this->dataUtils->renderConstant($components, array('user', 'host'), '//') .
            $this->dataUtils->renderValue($components, 'user', '%s') .
            $this->dataUtils->renderValue($components, 'pass', ':%s') .
            $this->dataUtils->renderConstant($components, array('user'), '@') .
            $this->dataUtils->renderValue($components, 'host', '%s') .
            $this->dataUtils->renderValue($components, 'port', ':%s') .
            $this->dataUtils->renderValue($components, 'path', '%s') .
            $this->dataUtils->renderValue($components, 'query', '?%s') .
            $this->dataUtils->renderValue($components, 'fragment', '#%s');
    }
    
    private function normalizeComponentValues(array $components)
    {
        if (!isset($components['scheme'])) {
            $components['scheme'] = 'https';
        }

        if ($components['scheme'] === 'ssh') {
            unset($components['user']);
            unset($components['pass']);

            $components['scheme'] = 'https';
        }

        $components['path'] = strtok($components['path'], '.');
        
        return $components;
    }
}
