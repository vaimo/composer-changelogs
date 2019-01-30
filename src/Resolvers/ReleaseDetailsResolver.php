<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Resolvers;

class ReleaseDetailsResolver
{
    /**
     * @var \Vaimo\ComposerChangelogs\Validators\ConstraintValidator
     */
    private $constraintValidator;

    /**
     * @var string[][]
     */
    private $linkTemplates = array(
        'bitbucket.org' => array(
            'link' => '{base}/src/{higher}',
            'diff' => '{base}/branches/compare/{higher}..{lower}#commits'
        )
    );

    /**
     * @var string[] 
     */
    private $dateQueryTemplates = array(
        '.hg' => 'hg log --rev \'{version}\' --template=\'{date|isodate}\''
    );
    
    public function __construct()
    {
        $this->constraintValidator = new \Vaimo\ComposerChangelogs\Validators\ConstraintValidator();
    }

    public function resolveOverview(array $release)
    {
        $overviewLines = isset($release['overview'])
            ? (is_array($release['overview']) ? $release['overview'] : array($release['overview']))
            : array();

        $reducedLines = explode(PHP_EOL, implode(
            ' ', 
            array_map(function ($line) {
                return !trim($line) ? PHP_EOL . PHP_EOL: $line;
            }, $overviewLines)
        ));
        
        return array(
            'version' => isset($release['version']) ? $release['version'] : '',
            'overview' => $overviewLines,
            'summary' => isset($release['summary']) ? $release['summary'] : '',
            'overview-reduced' => $reducedLines,
        );
    }

    public function resolveReleaseLinks($repositoryUrl, $version, $lastVersion = false)
    {
        if (!$repositoryUrl) {
            return array();
        }
        
        $urlComponents = parse_url($repositoryUrl);

        if (!isset($urlComponents['host'])) {
            return array();
        }
        
        foreach ($this->linkTemplates as $urlKey => $templates) {
            if (strstr($urlComponents['host'], $urlKey) === false) {
                continue;
            }
            
            $data = array();
            
            foreach ($templates as $code => $template) {
                $data[$code] = str_replace(
                    array('{base}', '{higher}', '{lower}'),
                    array($repositoryUrl, $version, $lastVersion ? $lastVersion : '0'),
                    $template
                );
            }
            
            return $data;
        }
        
        return array();
    }
    
    public function resolveReleaseTime($repositoryRoot, $version)
    {
        if (!$repositoryRoot) {
            return array();
        }
        
        foreach ($this->dateQueryTemplates as $folder => $commandTemplate) {
            if (!file_exists($this->composePath($repositoryRoot, '.hg'))) {
                continue;
            }
            
            $process = new \Symfony\Component\Process\Process(
                str_replace('{version}', $version, $commandTemplate),
                $repositoryRoot
            );

            $process->setTimeout(null);

            try {
                $process->mustRun();

                $result = $process->getOutput();
            } catch (\Symfony\Component\Process\Exception\ProcessFailedException $exception) {
                return array();
            }

            if (!isset($result[1])) {
                return array();
            }
            
            $segments = explode(' ', $result);

            return array(
                'date' => array_shift($segments),
                'time' => array_shift($segments)
            );
        }
        
        return array();
    }
    
    public function resolveChangeGroups(array $release)
    {
        $reservedKeys = array(
            'overview',
            'version',
            'branch',
            'summary',
            'overview-reduced',
            'link',
            'diff',
            'date',
            'time'
        );
        
        return array_diff_key(
            $release,
            array_fill_keys($reservedKeys, true)
        );
    }

    private function composePath()
    {
        $pathSegments = array_map(function ($item) {
            return rtrim($item, \DIRECTORY_SEPARATOR);
        }, func_get_args());

        return implode(
            DIRECTORY_SEPARATOR,
            array_filter($pathSegments)
        );
    }
}
