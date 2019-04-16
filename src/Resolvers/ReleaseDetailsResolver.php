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
        'bitbucket' => array(
            'link' => '{base}/src/{higher}',
            'diff' => '{base}/branches/compare/{higher}..{lower}#diff'
        ),
        'github' => array(
            'link' => '{base}/tree/{higher}',
            'diff' => '{base}/compare/{lower}...{higher}'
        )
    );

    /**
     * @var string[]
     */
    private $dateQueryTemplates = array(
        '.hg' => 'hg log --rev \'{version}\' --template=\'{date|isodate}\'',
        '.git' => 'git log {version}~1..{version} --simplify-by-decoration --pretty="format:%ai"',
    );
    
    private $initialQueryTemplates = array(
        '.hg' => 'hg log -r "branch(default) and 0:" -l 1 --template "{node}"',
        '.git' => 'git rev-list --max-parents=0 HEAD'
    );
    
    public function __construct()
    {
        $this->constraintValidator = new \Vaimo\ComposerChangelogs\Validators\ConstraintValidator();
        $this->pathUtils = new \Vaimo\ComposerChangelogs\Utils\PathUtils();
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

    public function resolveInitialCommitReference($repositoryRoot)
    {
        $result = '0';

        foreach ($this->initialQueryTemplates as $folder => $command) {
            if (!file_exists($this->pathUtils->composePath($repositoryRoot, $folder))) {
                continue;
            }

            $result = $this->getCommandStdIn($command, $repositoryRoot, '0');
        }
        
        return trim($result);
    }
    
    public function resolveReleaseLinks($repositoryUrl, $version, $lastVersion = '')
    {
        if (!$repositoryUrl) {
            return array();
        }
        
        $urlComponents = parse_url($repositoryUrl);

        if (!isset($urlComponents['host'])) {
            return array();
        }

        $hostCode = strtok($urlComponents['host'], '.');

        if (!isset($this->linkTemplates[$hostCode])) {
            return array();
        }
        
        $data = array();

        foreach ($this->linkTemplates[$hostCode] as $code => $template) {
            $data[$code] = str_replace(
                array('{base}', '{higher}', '{lower}'),
                array($repositoryUrl, $version, $lastVersion ? $lastVersion : '0'),
                $template
            );
        }

        return $data;
    }
    
    private function getCommandStdIn($command, $cwd, $default = '')
    {
        $process = new \Symfony\Component\Process\Process($command, $cwd);

        $process->setTimeout(null);

        try {
            $process->mustRun();

            return $process->getOutput();
        } catch (\Symfony\Component\Process\Exception\ProcessFailedException $exception) {
            return $default;
        }
    }
    
    public function resolveReleaseTime($repositoryRoot, $version)
    {
        if (!$repositoryRoot) {
            return array();
        }
        
        foreach ($this->dateQueryTemplates as $folder => $commandTemplate) {
            if (!file_exists($this->pathUtils->composePath($repositoryRoot, $folder))) {
                continue;
            }

            $result = $this->getCommandStdIn(
                str_replace('{version}', $version, $commandTemplate),
                $repositoryRoot,
                array()
            );

            if (!isset($result[1])) {
                return array();
            }
            
            $segments = explode(' ', trim($result));

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
}
